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
                <input type="text" class="form-control" id="summary" name="summary">
                @error('summary')
                    <span class="text-xs text-danger">
                        {{ $message }}
                    </span>
                @enderror
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
                <input type="datetime-local" class="form-control" id="start" name="start">
                @error('start')
                    <span class="text-xs text-danger">
                        {{ $message }}
                    </span>
                @enderror
            </div>
            <div class="mb-3">
                <label for="end" class="form-label">End Date and Time</label>
                <input type="datetime-local" class="form-control" id="end" name="end">
                @error('end')
                    <span class="text-xs text-danger">
                        {{ $message }}
                    </span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Add Google Calendar Event</button>
        </form>
    </div>
    <div class="container mt-5">
        <h2>Google Events List</h2>
        <table class="table">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Summary</th>
                <th scope="col">Location</th>
                <th scope="col">Description</th>
                <th scope="col">Start</th>
                <th scope="col">End</th>
                <th scope="col">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($events as $key => $event)
                <tr>
                    <th scope="row">{{ $key + 1 }}</th>
                    <td>{{ $event->summary }}</td>
                    <td>{{ $event->location }}</td>
                    <td>{{ $event->description }}</td>
                    <td>{{ $event->start }}</td>
                    <td>{{ $event->end }}</td>
                    <td>
                        <form
                            action="{{ route('event.delete', $event->id) }}"
                            method="POST"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure?')"
                            >
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td>
                        <p style="font-weight: bolder">No events yet</p>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

@endsection
