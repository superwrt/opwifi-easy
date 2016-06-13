<?php
	$menusel = ['webportal', 'device_cfg'];
	include(base_path('resources/views/opwifi/common.php'));
?>
@extends('opwifi.common.layouts.default')

@section('title', '配置管理')

@section('content')
<div>
<h2 class="page-header">配置管理</h2>
<div class="toolbar form-inline">
    <div class="btn-group">
	    <button id="cfg_add" class="btn btn-success">
            <i class="glyphicon glyphicon-plus"></i> 添加
        </button>
        <button id="cfg_remove" class="btn btn-danger">
            <i class="glyphicon glyphicon-trash"></i> 删除
        </button>
    </div>
</div>
<table id="cfgstable"
			data-show-columns="true"
			data-show-toggle="true"  
 			data-detail-view="true"
 			data-detail-formatter="detailFormatter"
 			data-show-pagination-switch="true"
			data-pagination="true"
			data-id-field="id"
			data-page-list="[5, 10, 25, 50, 100]"
			data-side-pagination="server"
			data-search="true"
			data-advanced-search="true"
			data-id-table="advancedTable"
>
</table>
</div>
@endsection

@section('header_css')
    <link rel="stylesheet" href="/res/pkgs/jstree/themes/default/style.min.css">
	<link rel="stylesheet" href="/res/pkgs/bootstrap-table/bootstrap-table.css">
	<link rel="stylesheet" href="/res/pkgs/bootstrap-table/bootstrap-table-fixed-columns.css">
@endsection
@section('footer_js')
    <script src="/res/pkgs/jstree/jstree.js"></script>
	<script src="/res/pkgs/bootstrap-table/bootstrap-table.js"></script>
	<script src="/res/pkgs/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
	<script src="/res/pkgs/x-editable/bootstrap-editable.js"></script>
	<script src="/res/pkgs/bootstrap-table/bootstrap-table-editable.js"></script>
	<script src="/res/pkgs/bootstrap-table/bootstrap-table-fixed-columns.js"></script>
	<script src="/res/pkgs/bootstrap-table/extensions/toolbar/bootstrap-table-toolbar.js"></script>

	<script type="text/javascript">
    var $table = $('#cfgstable');
    function load_configs() {
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
                        editable: true,
                        sortable: true,
                    }, {
                        field: 'redirect',
                        title: '重定向地址',
                        sortable: true,
                    }, {
                        field: 'force_timeout',
                        title: '强制超时时间',
                        sortable: true,
                    }, {
                        field: 'idle_timeout',
                        title: '空闲超时时间',
                        sortable: true,
					}, {
                        field: 'max_users',
                        title: '最大用户数',
                        sortable: true,
                    }, {
                        field: 'period',
                        title: '上报周期',
                        sortable: true,
					}, {
                        field: 'white_ip',
                        title: 'IP白名单',
                        sortable: true,
                    }, {
                        field: 'white_domain',
                        title: '域名白名单',
                        sortable: true,
                    }, {
                        field: 'operate',
                        title: '操作',
                        align: 'center',
                        events: operateEvents,
                        formatter: operateFormatter
                    }
                ]
            ],
            url: "{{ '/'.Request::path().'/select' }}",
            dataField: 'data',
            toolbar: '.toolbar'
        });
    }

    function operateFormatter(value, row, index) {
        return [
            '<a class="edit" href="javascript:void(0)" title="Edit">',
            '<i class="glyphicon glyphicon-pencil"></i>',
            '</a>'
        ].join('');
    }

    window.operateEvents = {
        'click .edit': function (e, value, row, index) {
            $.opwifi.ajaxOpwifiEdit("{{ '/'.Request::path().'/update' }}", $table,
                'editcfg', '修改配置',[
                    {field:'id', type:'hidden'},
                    {title:'名称', field:'name'},
                    {title:'重定向地址', field:'redirect', 'comment':'以http://开头。'},
                    {title:'强制超时时间', field:'force_timeout', 'comment':'秒，60-2592000。'},
                    {title:'空闲超时时间', field:'idle_timeout', 'comment':'秒，20-172800。'},
                    {title:'最大用户数', field:'max_users', 'comment':'为0时不限制。'},
                    {title:'上报周期', field:'period', 'comment':'秒，5-172800。'},
                    {title:'IP白名单', field:'white_ip', type:"textarea", 'comment':'多个之间以“,”号分隔。'},
                    {title:'域名白名单', field:'white_domain', type:"textarea", 'comment':'多个之间以“,”号分隔。'},
                    {title:'模式', field:'mode', type:"select", opts: [
                        {name: '用户登录', value: 'login'},
                        {name: '确认点击', value: 'confirm'},
                        {name: '外部', value: 'partner'}
                    ]},
                    {title:'外部Token', field:'access_token', 'comment':'仅外部模式时使用。'},
                ],row);
        }
    };

    function getHeight() {
        return $(window).height() - $('h1').outerHeight(true);
    }

    function detailFormatter(index, row) {
        var html = [];
        $.each(row, function (key, value) {
            html.push('<p><b>' + key + ':</b> ' + value + '</p>');
        });
        return html.join('');
    }

    $().ready(function(){
        load_configs();
        $('#cfg_add').ajaxOpwifiAdd("{{ '/'.Request::path().'/add' }}", $table,
            'newcfg', '添加配置',[
                {title:'名称', field:'name'}
            ],[]);
        $('#cfg_remove').ajaxOpwifiOperation("{{ '/'.Request::path().'/delete' }}", $table);
    });

    </script>

@endsection
