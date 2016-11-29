<?php
	$menusel = ['system', 'config'];
	include(base_path('resources/views/opwifi/common.php'));
?>
@extends('opwifi.common.layouts.default')

@section('title', '系统配置')

@section('content')
<div>
<h2 class="page-header">系统配置</h2>
</div>
<form class="form-horizontal" method="POST">
{!! csrf_field() !!}
  <div class="form-group">
    <label for="inputSiteUrl" class="col-sm-2 control-label">站点地址</label>
    <div class="col-sm-9">
      <input type="text" class="form-control" id="inputSiteUrl" name="site_url" placeholder="Url" value="{{ $configs['site_url'] }}">
    </div>
  </div>
  <div class="form-group">
    <label for="inputStatSta" class="col-sm-2 control-label">统计终端信息</label>
    <div class="checkbox col-sm-9">
      <label>
        <input type="checkbox" id="inputStatSta" name="fn_sta_status" value="on" <?php if($configs['fn_sta_status']=='on'){echo('checked');} ?> > 开启
      </label>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-9">
      <button type="submit" class="btn btn-success">保存更改</button>
    </div>
  </div>
</form>
@endsection

@section('header_css')
@endsection
@section('footer_js')
@endsection