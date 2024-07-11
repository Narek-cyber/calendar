@extends('layouts.layout')
@section('content')
    <div class="container">
    <form
        action="{{ route('update.event', $event->id) }}"
        method="POST"
    >
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label
                for="summary"
                class="form-label"
            >
                Event Summary
            </label>
            <input
                type="text"
                class="form-control"
                id="summary"
                name="summary"
                value="{{ $event->summary ?? old('summary') }}"
            >
            @error('summary')
                <span class="text-xs text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="mb-3">
            <label
                for="location"
                class="form-label"
            >
                Event Location
            </label>
            <input
                type="text"
                class="form-control"
                id="location"
                name="location"
                value="{{ $event->location ?? old('location') }}"
            >
        </div>
        <div class="mb-3">
            <label
                for="description"
                class="form-label"
            >Event Description
            </label>
            <textarea
                class="form-control"
                id="description"
                name="description">{{ $event->description ?? old('description') }}</textarea>
        </div>
        <div class="mb-3">
            <label
                for="start"
                class="form-label"
            >
                Start Date and Time
            </label>
            <input
                type="datetime-local"
                class="form-control"
                id="start"
                name="start"
                value="{{ $event->start ?? old('start') }}"
            >
            @error('start')
                <span class="text-xs text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="mb-3">
            <label
                for="end"
                class="form-label"
            >
                End Date and Time
            </label>
            <input
                type="datetime-local"
                class="form-control"
                id="end"
                name="end"
                value="{{ $event->end ?? old('end') }}"
            >
            @error('end')
                <span class="text-xs text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <button
            type="submit"
            class="btn btn-primary"
        >
            Edit Google Calendar Event
        </button>
    </form>
    </div>
@endsection
