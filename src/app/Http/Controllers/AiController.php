<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Building;

class AiController extends Controller
{
    public function index()
    {
        return view('ai.chat');
    }

    public function chat(Request $request)
    {
        $query = $request->input('query');

        // Fetch buildings and areas
        $buildings = Building::with('floors.areas')->get()->toArray();
        $bookings  = Booking::with('area.floor.building')->get()->toArray();

        $context = $this->buildContext($buildings, $bookings);

        $systemPrompt = "You are a booking assistant. Always respond in two parts:
        1. [Answer] - Human-readable confirmation or availability
        2. [Instruction] - JSON format:

        {
            \"action\": \"book\" or \"none\",
            \"user_name\": \"string\",
            \"area_id\": number,
            \"start_time\": \"YYYY-MM-DD HH:MM:SS\",
            \"end_time\": \"YYYY-MM-DD HH:MM:SS\"
        }

        If no booking possible, return {\"action\":\"none\"}. Ensure all dates are in YYYY-MM-DD HH:MM:SS.";

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'system', 'content' => $context],
                ['role' => 'user', 'content' => $query],
            ],
        ]);

        $answerText = $result->choices[0]->message->content ?? 'No response from AI';

        // Extract instruction JSON
        $json = $this->extractInstructionJson($answerText);

        // Attempt booking if action = book
        if ($json && isset($json['action']) && $json['action'] === 'book') {

            try {
                // Parse dates safely
                $start = Carbon::createFromFormat('Y-m-d H:i:s', $json['start_time']);
                $end   = Carbon::createFromFormat('Y-m-d H:i:s', $json['end_time']);
            } catch (\Exception $e) {
                return response()->json(['answer' => "Invalid date format in booking instruction."]);
            }

            // Check for conflict
            $conflict = Booking::where('area_id', $json['area_id'])
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_time', [$start, $end])
                      ->orWhereBetween('end_time', [$start, $end])
                      ->orWhere(function($q2) use ($start, $end) {
                          $q2->where('start_time', '<', $start)
                             ->where('end_time', '>', $end);
                      });
                })
                ->exists();

            if ($conflict) {
                // Try finding an alternative area of same capacity/type
                $alternative = $this->findAlternativeArea($buildings, $bookings, $json['area_id'], $start, $end);

                if ($alternative) {
                    $booking = Booking::create([
                        'user_name' => $json['user_name'],
                        'area_id'   => $alternative['id'],
                        'start_time'=> $start,
                        'end_time'  => $end,
                    ]);

                    $answerText = "Original area is booked, but an alternative has been reserved for you:\n";
                    $answerText .= $this->formatBookingMessage($json['user_name'], $booking->area_id, $buildings, $start, $end);
                } else {
                    $answerText .= "\n\n Cannot create booking: All similar areas are already booked for this time.";
                }
            } else {
                // Create booking
                $booking = Booking::create([
                    'user_name' => $json['user_name'],
                    'area_id'   => $json['area_id'],
                    'start_time'=> $start,
                    'end_time'  => $end,
                ]);

                $answerText .= "\n\n Booking successfully created:\n";
                $answerText .= $this->formatBookingMessage($json['user_name'], $booking->area_id, $buildings, $start, $end);
            }
        }

        return response()->json(['answer' => $answerText]);
    }

    private function buildContext(array $buildings, array $bookings): string
    {
        $context = "Current space management data:\n";

        foreach ($buildings as $b) {
            $context .= "Building: {$b['name']} (ID: {$b['id']})\n";
            foreach ($b['floors'] as $f) {
                $context .= "  └ Floor: {$f['name']} (ID: {$f['id']})\n";
                foreach ($f['areas'] as $a) {
                    $context .= "      └ Area: {$a['name']} (ID: {$a['id']}, Type: {$a['type']}, Capacity: {$a['capacity']})\n";
                }
            }
        }

        $context .= "Current bookings:\n";
        foreach ($bookings as $b) {
            $context .= "- {$b['user_name']} booked {$b['area']['name']} "
                      . "(Floor: {$b['area']['floor']['name']}, Building: {$b['area']['floor']['building']['name']}) "
                      . "from {$b['start_time']} to {$b['end_time']}\n";
        }

        return $context;
    }

    private function extractInstructionJson(string $text): ?array
    {
        if (preg_match('/\[Instruction\](.*)/s', $text, $matches)) {
            $json = json_decode(trim($matches[1]), true);
            if (json_last_error() === JSON_ERROR_NONE) return $json;
        }
        return null;
    }

    private function formatBookingMessage($userName, $areaId, $buildings, $start, $end): string
    {
        $area = null;
        $floorName = $buildingName = '';

        foreach ($buildings as $b) {
            foreach ($b['floors'] as $f) {
                foreach ($f['areas'] as $a) {
                    if ($a['id'] == $areaId) {
                        $area = $a;
                        $floorName = $f['name'];
                        $buildingName = $b['name'];
                        break 3;
                    }
                }
            }
        }

        if (!$area) return "Booking area details not found.";

        $startTime = $start->format('h:i A, Y-m-d');
        $endTime = $end->format('h:i A, Y-m-d');

        return "- **User:** {$userName}\n- **Building:** {$buildingName}\n- **Floor:** {$floorName}\n- **Area:** {$area['name']} (Capacity: {$area['capacity']})\n- **Time:** {$startTime} to {$endTime}\n";
    }

    private function findAlternativeArea($buildings, $bookings, $originalAreaId, $start, $end)
    {
        // Find original area type/capacity
        $originalArea = null;
        foreach ($buildings as $b) {
            foreach ($b['floors'] as $f) {
                foreach ($f['areas'] as $a) {
                    if ($a['id'] == $originalAreaId) {
                        $originalArea = $a;
                        break 3;
                    }
                }
            }
        }

        if (!$originalArea) return null;

        // Check for free areas with same type and sufficient capacity
        foreach ($buildings as $b) {
            foreach ($b['floors'] as $f) {
                foreach ($f['areas'] as $a) {
                    if ($a['id'] == $originalAreaId) continue; // skip original
                    if ($a['type'] == $originalArea['type'] && $a['capacity'] >= $originalArea['capacity']) {
                        // Check conflicts
                        $conflict = Booking::where('area_id', $a['id'])
                            ->where(function ($q) use ($start, $end) {
                                $q->whereBetween('start_time', [$start, $end])
                                  ->orWhereBetween('end_time', [$start, $end])
                                  ->orWhere(function($q2) use ($start, $end) {
                                      $q2->where('start_time', '<', $start)
                                         ->where('end_time', '>', $end);
                                  });
                            })
                            ->exists();

                        if (!$conflict) return $a;
                    }
                }
            }
        }

        return null;
    }
}
