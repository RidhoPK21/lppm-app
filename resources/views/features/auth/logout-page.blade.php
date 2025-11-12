@extends('layouts.auth')

@section('title', 'Logout')

@section('content')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Remove auth token from local storage
            localStorage.removeItem('authToken');
            window.location.href = '/auth/login';
        });
    </script>
@endsection
