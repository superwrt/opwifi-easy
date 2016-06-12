<?php
	$menusel = ['devices', 'device_fw'];
	include(base_path('resources/views/opwifi/common.php'));
?>
@extends('opwifi.common.layouts.default')

@section('title', '固件管理')

@section('content')
<div>
<h2 class="page-header">固件管理</h2>
<div class="toolbar form-inline">
    <div class="btn-group">
	    <button id="fw_add" class="btn btn-success">
            <i class="glyphicon glyphicon-plus"></i> 上传
        </button>
        <button id="fw_remove" class="btn btn-danger">
            <i class="glyphicon glyphicon-trash"></i> 删除
        </button>
    </div>
</div>
<table id="fwstable"
			data-show-columns="true"
			data-show-toggle="true"  
 			data-show-pagination-switch="true"
			data-pagination="true"
			data-id-field="id"
			data-page-list="[5, 10, 25, 50, 100]"
			data-side-pagination="server"
			data-search="true"
			data-id-table="advancedTable"
>
</table>
</div>

@include('opwifi.common.tools.upload_file', [
		'inputs' => [['name'=>'name', 'title'=>'名称']],
		'action' => '/'.Request::path().'/upload'
	])
@endsection

@section('header_css')
    <link rel="stylesheet" href="/res/pkgs/jstree/themes/default/style.min.css">
	<link rel="stylesheet" href="/res/pkgs/bootstrap-table/bootstrap-table.css">
@endsection
@section('footer_js')
	<script src="/res/pkgs/bootstrap-table/bootstrap-table.js"></script>
	<script src="/res/pkgs/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
	<script src="/res/pkgs/x-editable/bootstrap-editable.js"></script>
	<script src="/res/pkgs/bootstrap-table/bootstrap-table-editable.js"></script>
	<script src="/res/pkgs/bootstrap-table/extensions/toolbar/bootstrap-table-toolbar.js"></script>
    <script src="/res/js/jquery.form.js"></script>
	<script src="/res/js/opwifi_upload_file.js"></script>

	<script type="text/javascript">
    var $table = $('#fwstable');
    function load_firmwares() {
        $table.bootstrapTable({
            columns: [
                [
                    {
                        field: 'state',
                        checkbox: true,
                    }, {
                        field: 'id',
                        title: 'ID',
                        sortable: true
                    }, {
                        field: 'name',
                        title: '名称',
                        editable: {
                            url: "{{ '/'.Request::path().'/rename' }}",
                        },
                        sortable: true,
                    }, {
                        field: 'version',
                        title: '固件版本',
                        sortable: true,
                    }, {
                        field: 'url',
                        title: 'URL地址',
                        sortable: true,
                    }, {
                        field: 'sha1',
                        title: 'sha1值',
                        sortable: true,
                    }, {
                        field: 'created_at',
                        title: '上传日期',
                        sortable: true,
                    }
                ]
            ],
            url: "{{ '/'.Request::path().'/select' }}", 
            dataField: 'data',
            toolbar: '.toolbar'
        });
    }

    $().ready(function(){
        load_firmwares();
        $('#fw_add').uploadFile('firmware', function() {
            $table.bootstrapTable('refresh');
        });
        $('#fw_remove').ajaxOpwifiOperation("{{ '/'.Request::path().'/delete' }}", $table);
    });

    </script>

@endsection