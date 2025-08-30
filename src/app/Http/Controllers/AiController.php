<?php

namespace App\Http\Controllers;

use App\Models\Booking;
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

        $buildings = Building::with('floors.areas')->get()->toArray();
        $bookings  = Booking::with('area.floor.building')->get()->toArray();

        $context = "Here is the current space management data:\n";
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
            $context .= "- {$b['user_name']} booked {$b['area']['name']} (Floor: {$b['area']['floor']['name']}, Building: {$b['area']['floor']['building']['name']}) from {$b['start_time']} to {$b['end_time']}\n";
        }

        $result = OpenAI::chat()->create([
            'model' => 'gpt-5',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a booking assistant for a space management system. Use ONLY the provided data (buildings, floors, areas, bookings). If a requested space is already booked, suggest alternatives. Never invent entities not in context. Respond clearly and concisely."
                ],
                ['role' => 'system', 'content' => $context],
                ['role' => 'user', 'content' => $query],
            ],
        ]);

        return response()->json([
            'answer' => $result->choices[0]->message->content ?? 'No response from AI',
        ]);
    }
}
