@extends('emails.layouts.app')

@section('content')
    <h2>Password Reset</h2>
    <p><b>Hello, {{ ucwords($content['name']) }}</b></p>
    <p class="info">Your password reset was successful. Below is your new password</p>
    <br />
    @if ($content['password'] !== null)
        <p><b>New Password: {{ $content['password'] }}</b></p>
        <i class="info">Ensure you sign in and change your account password.</i>
    @endif
    <br />
    <br />
    <div class="button-link">
        <a class="btn-link" href="#login">Sign In</a>
    </div>
@endsection
