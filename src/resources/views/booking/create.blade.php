@extends('layouts.app')

@section('content')
<h2>Create Booking</h2>
<form method="POST" action="{{ route('bookings.store') }}">
    @csrf
    <label>User Name:</label><br>
    <input type="text" name="user_name"><br><br>

    <label>Select Area:</label><br>
    <select name="area_id">
        @foreach($areas as $area)
        <option value="{{ $area->id }}">
            {{ $area->name }} (Floor: {{ $area->floor->name }}, Building: {{ $area->floor->building->name }})
        </option>
        @endforeach
    </select><br><br>

    <label>Start Time:</label><br>
    <input type="datetime-local" name="start_time"><br><br>

    <label>End Time:</label><br>
    <input type="datetime-local" name="end_time"><br><br>

    <button type="submit">Book</button>
</form>
@endsection
