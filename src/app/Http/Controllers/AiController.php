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

        // Fetch buildings and current bookings
        $buildings = Building::with('floors.areas')->get()->toArray();
        $bookings  = Booking::with('area.floor.building')->get()->toArray();

        // Build AI context
        $context = $this->buildContext($buildings, $bookings);

        // GPT system prompt
        $systemPrompt = "You are a helpful booking assistant. 
            Always respond in two parts:
            1. [Answer] - Friendly text for the user (e.g., availability, confirmation, or error)
            2. [Instruction] - JSON for booking if possible

            JSON format:
            {
            \"action\": \"book\",
            \"user_name\": \"string\",
            \"area_id\": number,
            \"start_time\": \"Y-m-d H:i:s\",
            \"end_time\": \"Y-m-d H:i:s\"
            }

            If no booking is possible, return {\"action\":\"none\"}.";

        // Ask GPT for response
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'system', 'content' => $context],
                ['role' => 'user', 'content' => $query],
            ],
        ]);

        $answerText = $result->choices[0]->message->content ?? 'No response from AI';

        // Extract JSON instructions
        $json = $this->extractInstructionJson($answerText);

        // Insert booking if GPT requested and no conflict exists
        if ($json && isset($json['action']) && $json['action'] === 'book') {
            $start = Carbon::parse($json['start_time']);
            $end   = Carbon::parse($json['end_time']);
            $areaId = $json['area_id'];

            // Check for duplicate/conflict
            $conflict = Booking::where('area_id', $areaId)
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('start_time', [$start, $end])
                          ->orWhereBetween('end_time', [$start, $end])
                          ->orWhere(function($q) use ($start, $end) {
                              $q->where('start_time', '<', $start)
                                ->where('end_time', '>', $end);
                          });
                })
                ->exists();

            if ($conflict) {
                $answerText .= "\n\n Cannot create booking: Time slot conflicts with an existing booking.";
            } else {
                $booking = Booking::create([
                    'user_name' => $json['user_name'],
                    'area_id'   => $areaId,
                    'start_time'=> $start,
                    'end_time'  => $end,
                ]);

                $answerText .= "\n\n Booking successfully created (ID: {$booking->id}).";
            }
        }

        // Always return GPT's answer text
        return response()->json([
            'answer' => $answerText,
        ]);
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
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }
        return null;
    }
}
