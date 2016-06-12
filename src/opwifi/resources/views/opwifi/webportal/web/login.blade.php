<!doctype html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
  <meta http-equiv="Expires" content="0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Cache-control" content="no-cache">
  <meta http-equiv="Cache" content="no-cache">
  <meta name="apple-mobile-web-app-capable" content="yes"/>
  <meta name="format-detection" content="telphone=no, email=no"/>
  <title>SuperWRT Web Portal Login</title>
  <meta name="description" content="">
  <link rel="stylesheet" href="/res/css/pure-min.css">
  <link rel="stylesheet" href="/res/css/webportal.css">
</head>
<body>
  <div id="title"><h2>{{ $title }}</h2></div>
  <div id="login">
    <div class="title">
      <h2>用户登录</h2>
    </div>
    <form class="pure-form pure-form-stacked" action="/webportal/login" method="post">
        <fieldset>
          @if ($mode != 'confirm')
          <div id="login-user">
            <div class="pure-control-group" type="post">
                <label for="username">用户名</label>
                <input name="username" type="text" placeholder="用户名">
            </div>

            <div class="pure-control-group">
                <label for="password">密码</label>
                <input name="password" type="password" placeholder="密码">
            </div>
          </div>
          @endif
          <input name="mac" value="{{ $from['mac'] }}" hidden>
          <input name="usermac" value="{{ $from['usermac'] }}" hidden>
          <input name="redir" value="{{ $from['redir'] }}" hidden>
          <input name="gatewayip" value="{{ $from['gatewayip'] }}" hidden>
          <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />

          <div class="btn">
            <button type="submit" class="pure-button" id="submit-login">确认</button>
          </div>
        </fieldset>
    </form>
  </div>
  <div id="footer">Powered by <a href="http://superwrt.com/?s=owebp">SuperWRT</a></div>
  <script src="/res/js/jquery-1.12.3.min.js"></script>
  <script type="text/javascript">
  </script>
</body>
</html>