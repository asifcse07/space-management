@extends('layouts.app')

@section('content')
<h2>All Bookings</h2>
<a href="{{ route('bookings.create') }}">Create Booking</a>
<table border="1" cellpadding="5">
    <tr>
        <th>User</th>
        <th>Area</th>
        <th>Floor</th>
        <th>Building</th>
        <th>Start</th>
        <th>End</th>
    </tr>
    @foreach($bookings as $b)
    <tr>
        <td>{{ $b->user_name }}</td>
        <td>{{ $b->area->name }}</td>
        <td>{{ $b->area->floor->name }}</td>
        <td>{{ $b->area->floor->building->name }}</td>
        <td>{{ $b->start_time }}</td>
        <td>{{ $b->end_time }}</td>
    </tr>
    @endforeach
</table>
@endsection
