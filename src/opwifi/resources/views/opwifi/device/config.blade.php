<?php
	$menusel = ['devices', 'device_cfg'];
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
            <i class="glyphicon glyphicon-plus"></i> 新建
        </button>
        <button id="cfg_remove" class="btn btn-danger">
            <i class="glyphicon glyphicon-trash"></i> 删除
        </button>
    </div>
</div>
<table id="cfgstable"
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

	<script type="text/javascript">
    var $table = $('#cfgstable');
    function load_configs() {
        $table.bootstrapTable({
            /*
            fixedColumns: true,
            fixedColumn: 2,*/
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
                            url: "{{ '/'.Request::path().'/update' }}",
                        },
                        sortable: true,
                    }, {
                        field: 'comment',
                        title: '备注',
                        editable: {
                            url: "{{ '/'.Request::path().'/update' }}",
                        },
                    }, {
                        field: 'created_at',
                        title: '创建日期',
                        sortable: true,
                    }, {
                        field: 'updated_at',
                        title: '修改日期',
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
            '</a>  '
        ].join('');
    }

    window.operateEvents = {
        'click .edit': function (e, value, row, index) {
            $.opwifi.ajaxOpwifiEdit("{{ '/'.Request::path().'/update' }}", $table,
                'edit', '修改配置',
                [ {type:'hidden', field:'id'}, {title:'配置', field:'config', type:"textarea"} ],
                row);
        }
    };

    $().ready(function(){
        load_configs();
        $('#cfg_add').ajaxOpwifiAdd("{{ '/'.Request::path().'/add' }}", $table,
            'newcfg', '添加配置',
            [ {title:'名称', field:'name'}, {title:'备注', field:'comment'} ],
            []);
        $('#cfg_remove').ajaxOpwifiOperation("{{ '/'.Request::path().'/delete' }}", $table);
    });

    </script>

@endsection