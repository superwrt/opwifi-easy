<?php
	$menusel = ['home'];
	include(base_path('resources/views/opwifi/common.php'));
?>
@extends('opwifi.common.layouts.default')

@section('title', 'Opwifi首页')

@section('content')
<div>
<h2 class="page-header">首页</h2>
</div>
<div class="page-notice">
</div>
<div>
<p class="lead">欢迎使用OpWiFi管理平台。本平台是配合SuperWRT，进行设备远程管理的工具。<br/>目前，该项目及SuperWRT还在开发测试阶段，欢迎给我们提出问题和建议。</p>
</div>
@endsection

@section('header_css')
@endsection
@section('footer_js')
<script type="text/javascript">
function upgrade_notice(info) {
	$(".page-notice").append(
'<div class="alert alert-warning alert-dismissible" role="alert">\
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
  <strong>新版本可用!</strong> <a href="'+info.web+'" class="alert-link">'+info.version+'</a> '+info.description['zh-cn']+'\
</div>');
}
var verraw = {{ config('opwifi.version_raw' )}};
$.ajax({
	url:"http://ver.opwifi.com/s/check/1/jsonp?verraw={{ config('opwifi.version_raw' )}}",
	dataType: 'jsonp',
	data:'',  
    jsonp:'callback',  
    success: function(r){
    	if (r.version_raw > verraw) {
    		upgrade_notice(r);
    	}
    }
});  
</script>
@endsection