<header>
<nav id="opadminbar" class="navbar">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/m/">OpWiFi</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">官网 <span class="caret"></span></a>
          <ul class="dropdown-menu">
          	<li><a href="//superwrt.com/opwifi">官网首页</a></li>
          	<li role="separator" class="divider"></li>
            <li><a href="//superwrt.com/opwifi/usermanual">用户手册</a></li>
            <li><a href="//forum.superwrt.com/">用户社区</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="//superwrt.com/opwifi/support">技术支持</a></li>
          </ul>
        </li>
        <li><a href=""><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></a></li>

      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">你好，<?php echo Auth::User()['username'] ?>&nbsp;<span class="glyphicon glyphicon-user" aria-hidden="true"></span></a>
          <ul class="dropdown-menu">
            <li><a href="{{URL::route('opwifi::system.user')}}">管理用户</a></li>
            <li><a href="{{URL::route('opwifi::system.about')}}">关于</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/m/auth/logout">注销</a></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
</header>

<div id="middle">
	<div id="adminmenuback"></div>
	<div id="adminmenuwrap">
		<ul id="adminmenu" role="navigation" class="dropdown-hover">
		@if (isset($menu))
			@foreach ($menu as $key => $item)
				@include('opwifi.common.partials.menu_item')
			@endforeach
		@endif
		</ul>
	</div>