<?php
	$menusel = ['stations', 'station_mng'];
	include(base_path('resources/views/opwifi/common.php'));
?>
@extends('opwifi.common.layouts.default')

@section('title', '终端管理')

@section('content')
@include('opwifi.common.partials.group')
<div>
<h2 class="page-header">终端管理</h2>
<div class="toolbar form-inline">
    <div class="btn-group">
	    <button id="sta_add" class="btn btn-success">
            <i class="glyphicon glyphicon-plus"></i> 添加
        </button>
        <button id="sta_remove" class="btn btn-danger">
            <i class="glyphicon glyphicon-trash"></i> 删除
        </button>
    </div>
</div>
<table id="stastable"
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
    var $table = $('#stastable');
    function load_devices() {
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
                        field: 'station.mac',
                        title: 'MAC地址',
                    }, {
                        field: 'station.name',
                        title: '名称',
                        editable: {
                            url: "{{ '/'.Request::path().'/update' }}",
                        },
                    }, {
                        field: 'last_show',
                        title: '最近在线时间',
                        sortable: true
                    }, {
                        field: 'last_ondev',
                        title: '最近在线设备'
                    }, {
                        field: 'last_onssid',
                        title: '最近在线SSID',
                        sortable: true
                    }, {
                        field: 'last_onbssid',
                        title: '最近在线BSSID',
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
            '<a class="detail" href="javascript:void(0)" title="Detail">',
            '<i class="glyphicon glyphicon-eye-open"></i>',
            '</a>  ',
            '<a class="tags" href="javascript:void(0)" title="Tags">',
            '<i class="glyphicon glyphicon-file"></i>',
            '</a>  '
        ].join('');
    }

    window.operateEvents = {
        'click .detail': function (e, value, row, index) {
            alert('Detail Info: ' + JSON.stringify(row));
        },
        'click .tags': function (e, value, row, index) {
            $.getJSON("{{ '/'.Request::path().'/tags' }}?id="+row.id,
                function(d){alert(JSON.stringify(d));});
        }
    };

    function detailFormatter(index, row) {
        var html = [];
        function dump(key, value) {
            if (typeof(value) == "object") {
                $.each(value, dump);
            } else {
                html.push('<p><b>' + key + ':</b> ' + value + '</p>');
            }
        }
        $.each(row, dump);
        return html.join('');
    }

    $().ready(function(){
        load_devices();
        $('#sta_add').ajaxOpwifiAdd("{{ '/'.Request::path().'/add-root' }}", $table,
            'newdev', '添加设备',
            [ {title:'MAC地址', field:'mac'}, {title:'名称', field:'name'} ],
            []);
        $('#sta_remove').ajaxOpwifiOperation("{{ '/'.Request::path().'/delete-root' }}", $table);
    });

    </script>
    @include('opwifi.common.partials.group_js', [
        'tableId' => 'stastable',
        'tableUrl' => '/'.Request::path().'/select',
        'groupUrl' => '/'.Request::path().'/groups',
        'relationshipUrl' => '/'.Request::path().'/tag-relationships',
    ])

@endsection