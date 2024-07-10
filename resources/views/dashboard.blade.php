@extends('layouts.layout')
@section('content')
    <div class="container-fluid text-center">
        <div class="row align-items-center">
            <div class="col-8">
                <h1 class="d-inline">Welcome to Dashboard</h1>
                <h3>{{ $user->name }}</h3>
            </div>
            <div class="col-4 text-right">
                <form action="{{ route('logout') }}" method="GET" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm align-middle" style="height: 100%;">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="container">
        <form action="{{ route('add.event') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="summary" class="form-label">Event Summary</label>
                <input type="text" class="form-control" id="summary" name="summary" >
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Event Location</label>
                <input type="text" class="form-control" id="location" name="location">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Event Description</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <div class="mb-3">
                <label for="start" class="form-label">Start Date and Time</label>
                <input type="datetime-local" class="form-control" id="start" name="start" >
            </div>
            <div class="mb-3">
                <label for="end" class="form-label">End Date and Time</label>
                <input type="datetime-local" class="form-control" id="end" name="end" >
            </div>
            <button type="submit" class="btn btn-primary">Add Google Calendar Event</button>
        </form>
    </div>
@endsection
