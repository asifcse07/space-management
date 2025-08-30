<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Area;

class BookingController extends Controller
{
    // Show all bookings
    public function index()
    {
        $bookings = Booking::with('area.floor.building')->get();
        return view('booking.index', compact('bookings'));
    }

    // Show form
    public function create()
    {
        $areas = Area::with('floor.building')->get();
        return view('booking.create', compact('areas'));
    }

    // Store booking
    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'user_name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        Booking::create($request->all());

        return redirect()->route('bookings.index')->with('success', 'Booking created!');
    }
}
