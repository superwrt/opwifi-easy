<?php
	$menusel = ['webportal', 'user_mng'];
	include(base_path('resources/views/opwifi/common.php'));
?>
@extends('opwifi.common.layouts.default')

@section('title', '用户管理')

@section('content')
<div>
<h2 class="page-header">用户管理</h2>
<div class="toolbar form-inline">
    <div class="btn-group">
        <button id="user_add" class="btn btn-success">
            <i class="glyphicon glyphicon-plus"></i> 添加
        </button>
        <button id="user_remove" class="btn btn-danger">
            <i class="glyphicon glyphicon-trash"></i> 删除
        </button>
    </div>
    <div class="btn-group">
        <button id="user_pause" class="btn btn-default">
            <i class="glyphicon glyphicon-pause"></i> 暂停
        </button>
        <button id="user_open" class="btn btn-default">
            <i class="glyphicon glyphicon-play"></i> 启用
        </button>
        <button id="user_reset" class="btn btn-default">
            <i class="glyphicon glyphicon-erase"></i> 重置
        </button>
    </div>
</div>
<table id="userstable"
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
    var $table = $('#userstable');
    function load_users() {
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
                        field: 'username',
                        title: '用户名',
                        sortable: true
                    }, {
                        field: 'comment',
                        title: '备注',
                        sortable: true
                    }, {
                        field: 'disable',
                        title: '暂停',
                        sortable: true
                    }, {
                        field: 'tx_rate',
                        title: '发送限速',
                        sortable: true
                    }, {
                        field: 'rx_rate',
                        title: '接收限速',
                        sortable: true
                    }, {
                        field: 'trx_limit',
                        title: '流量限制',
                        sortable: true
                    }, {
                        field: 'time_limit',
                        title: '时长限制',
                        sortable: true
                    }, {
                        field: 'login_count',
                        title: '登录次数',
                        sortable: true
                    }, {
                        field: 'trx_used',
                        title: '已用流量',
                        sortable: true
                    }, {
                        field: 'time_used',
                        title: '已用时长',
                        sortable: true
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
                    {title:'用户名', field:'username'},
                    {title:'密码', field:'password'},
                    {title:'备注', field:'comment', type:"textarea"},
                    {title:'发送限速', field:'tx_rate'},
                    {title:'接收限速', field:'rx_rate'},
                    {title:'流量限制', field:'trx_limit'},
                    {title:'时长限制', field:'time_limit'}
                ],row);
        }
    };

    function detailFormatter(index, row) {
        var html = [];
        $.each(row, function (key, value) {
            html.push('<p><b>' + key + ':</b> ' + value + '</p>');
        });
        return html.join('');
    }

    $().ready(function(){
        load_users();
        $('#user_add').ajaxOpwifiAdd("{{ '/'.Request::path().'/add' }}", $table,
            'newcfg', '添加配置',[
                {title:'用户名', field:'username'},
                {title:'密码', field:'password'},
                {title:'备注', field:'comment', type:"textarea"}
            ],[]);
        $('#user_remove').ajaxOpwifiOperation("{{ '/'.Request::path().'/delete' }}", $table);
        $('#user_reset').ajaxOpwifiOperation("{{ '/'.Request::path().'/reset' }}", $table);
        $('#user_pause').ajaxOpwifiOperation("{{ '/'.Request::path().'/update' }}", $table, {disable:true});
        $('#user_open').ajaxOpwifiOperation("{{ '/'.Request::path().'/update' }}", $table, {disable:false});
    });

    </script>

@endsection