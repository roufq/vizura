<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{ config('app.name', 'Laravel') }} - Register</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/style.min.css') }}">

    <!-- Waves Effect -->
    <link rel="stylesheet" href="{{ asset('assets/plugin/waves/waves.min.css') }}">
</head>

<body>
<div id="single-wrapper">
    <form method="POST" action="{{ route('register') }}" class="frm-single">
        @csrf
        <div class="inside">
            <div class="title"><strong>Ninja</strong>Admin</div>
            <div class="frm-title">Register</div>

            <div class="frm-input">
                <input type="email" name="email" placeholder="Email" class="frm-inp" value="{{ old('email') }}" required autocomplete="username">
                <i class="fa fa-envelope frm-ico"></i>
            </div>
            @error('email')
                <div class="text-danger margin-bottom-20">{{ $message }}</div>
            @enderror

            <div class="frm-input">
                <input type="text" name="name" placeholder="Nama" class="frm-inp" value="{{ old('name') }}" required autofocus autocomplete="name">
                <i class="fa fa-user frm-ico"></i>
            </div>
            @error('name')
                <div class="text-danger margin-bottom-20">{{ $message }}</div>
            @enderror

            <div class="frm-input">
                <input type="password" name="password" placeholder="Password" class="frm-inp" required autocomplete="new-password">
                <i class="fa fa-lock frm-ico"></i>
            </div>
            @error('password')
                <div class="text-danger margin-bottom-20">{{ $message }}</div>
            @enderror

            <div class="frm-input">
                <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" class="frm-inp" required autocomplete="new-password">
                <i class="fa fa-lock frm-ico"></i>
            </div>
            @error('password_confirmation')
                <div class="text-danger margin-bottom-20">{{ $message }}</div>
            @enderror

            <div class="clearfix margin-bottom-20">
                <div class="checkbox primary">
                    <input type="checkbox" id="accept">
                    <label for="accept">I accept Terms and Conditions</label>
                </div>
            </div>

            <button type="submit" class="frm-submit">
                Register <i class="fa fa-arrow-circle-right"></i>
            </button>

            <a href="{{ route('login') }}" class="a-link">
                <i class="fa fa-sign-in"></i>Already have account? Login.
            </a>
            <div class="frm-footer">NinjaAdmin Ac 2016.</div>
        </div>
    </form>
</div>

<!--[if lt IE 9]>
    <script src="{{ asset('assets/script/html5shiv.min.js') }}"></script>
    <script src="{{ asset('assets/script/respond.min.js') }}"></script>
<![endif]-->
<script src="{{ asset('assets/scripts/jquery.min.js') }}"></script>
<script src="{{ asset('assets/scripts/modernizr.min.js') }}"></script>
<script src="{{ asset('assets/plugin/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/plugin/nprogress/nprogress.js') }}"></script>
<script src="{{ asset('assets/plugin/waves/waves.min.js') }}"></script>
<script src="{{ asset('assets/scripts/main.min.js') }}"></script>
</body>
</html>
