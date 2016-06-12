<!doctype html>
<html lang="zh-cn">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
	<meta name="_token" content="{{ csrf_token() }}"/>
	<title>@yield('title')</title>
	
	<link rel="stylesheet" href="/res/css/bootstrap.css">
	<link rel="stylesheet" href="/res/css/bootstrap-theme.css">
@section('header_css')
@show
	<link rel="stylesheet" href="/res/css/opwifi.css">
</head>
<body>	
	<div id="owwrap">
		@include('opwifi.common.partials.header')

		<div id="owcontent">
		@yield('content')
		</div>

		@include('opwifi.common.partials.footer')
	</div>

	<script src="/res/js/jquery-1.12.3.js"></script>
	<script src="/res/js/bootstrap.js"></script>
	<script src="/res/js/opwifi.js"></script>
	<script type="text/javascript">
	$(function(){
	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
	        }
	    });
    });
    </script>
@section('footer_js')
@show

</body>
</html>