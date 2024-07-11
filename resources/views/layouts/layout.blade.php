<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Document</title>
</head>
<body>
<div class="container-fluid text-center">
    @include('messages.message')
    <div class="row align-items-center">
        <div class="col-8">
            <h1 class="d-inline">Welcome to Dashboard</h1>
            <h3>{{ $user->name }}</h3>
            <p>{{ $user->email }}</p>
        </div>
        <div class="col-4 text-right">
            <form action="{{ route('logout') }}" method="GET" class="d-inline">
                @csrf
                <button
                    type="submit"
                    class="btn btn-danger btn-sm align-middle"
                    style="height: 100%;"
                >
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
@yield('content')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>
