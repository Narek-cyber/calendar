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
@endsection
