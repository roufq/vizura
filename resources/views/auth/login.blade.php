<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{ config('app.name', 'Laravel') }} - Login</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/style.min.css') }}">

    <!-- Waves Effect -->
    <link rel="stylesheet" href="{{ asset('assets/plugin/waves/waves.min.css') }}">
</head>

<body>
<div id="single-wrapper">
    <form method="POST" action="{{ route('login') }}" class="frm-single">
        @csrf
        <div class="inside">
            <div class="title"><strong>Ninja</strong>Admin</div>
            <div class="frm-title">Login</div>

            @if (session('status'))
                <div class="alert alert-success margin-bottom-20">
                    {{ session('status') }}
                </div>
            @endif

            <div class="frm-input">
                <input type="email" name="email" placeholder="Email" class="frm-inp" value="{{ old('email') }}" required autofocus autocomplete="username">
                <i class="fa fa-user frm-ico"></i>
            </div>
            @error('email')
                <div class="text-danger margin-bottom-20">{{ $message }}</div>
            @enderror

            <div class="frm-input">
                <input type="password" name="password" placeholder="Password" class="frm-inp" required autocomplete="current-password">
                <i class="fa fa-lock frm-ico"></i>
            </div>
            @error('password')
                <div class="text-danger margin-bottom-20">{{ $message }}</div>
            @enderror

            <div class="clearfix margin-bottom-20">
                <div class="pull-left">
                    <div class="checkbox primary">
                        <input type="checkbox" id="rememberme" name="remember">
                        <label for="rememberme">Remember me</label>
                    </div>
                </div>
                <div class="pull-right">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="a-link">
                            <i class="fa fa-unlock-alt"></i>Forgot password?
                        </a>
                    @endif
                </div>
            </div>

            <button type="submit" class="frm-submit">
                Login <i class="fa fa-arrow-circle-right"></i>
            </button>

            <a href="{{ route('register') }}" class="a-link">
                <i class="fa fa-key"></i>New to NinjaAdmin? Register.
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
