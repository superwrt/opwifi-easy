<?php
	$menusel = ['system', 'about'];
	include(base_path('resources/views/opwifi/common.php'));
?>
@extends('opwifi.common.layouts.default')

@section('title', '关于')

@section('content')
      <div class="row">
        <div class="col-md-9" role="main">
          <div class="bs-docs-section">
  			<h1 id="about" class="page-header">关于</h1>

  			<p class="lead">OpWiFi是一个用于管理SuperWRT系统设备的集中管理平台。</p>

  			<h2 id="about-version">版本信息</h2>
  			<p>目前为测试版本。</p>
			<div class="highlight"><pre>
版本号：{{ config('opwifi.version') }}
发布日期：{{ config('opwifi.publish_date') }}</pre></div>

  <h2 id="about-license">使用许可</h2>
  <p>OpWiFi使用Apache v2许可进行发布，是面向商业友好的使用许可。具体内容请参考：<a href="https://opensource.org/licenses/Apache-2.0">Apache 2.0</a></p>
			</div>
			</div>
			</div>
@endsection

@section('header_css')
@endsection
@section('footer_js')
@endsection