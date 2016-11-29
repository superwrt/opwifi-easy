<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Opwifi Admin Login</title>

    <!-- Styles -->
    <link href="/res/css/bootstrap.min.css" rel="stylesheet">
    <link href="/res/css/auth.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Lato';
        }

        .fa-btn {
            margin-right: 6px;
        }
    </style>
</head>
<body id="app-layout">

<div class="container">
            <div class="panel panel-default">
                <div class="panel-heading">OpWiFi</div>
                <div class="panel-body">
                    <form role="form" method="POST" action="{{ url('/m/auth/login') }}">
                        {!! csrf_field() !!}

                        <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                            <input type="text" class="form-control" name="username" placeholder="用户名" value="{{ old('username') }}">

                            @if ($errors->has('username'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('username') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <input type="password" class="form-control" name="password" placeholder="密码" >

                            @if ($errors->has('password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="remember"> 记住登录信息
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-btn fa-sign-in"></i>登录
                            </button>
                        </div>
                    </form>
                </div>
            </div>
</div>

    <!-- JavaScripts -->
    <script src="/res/js/jquery-1.12.3.min.js"></script>
    <script src="/res/js/bootstrap.min.js"></script>
</body>
</html>