<?php
	$menusel = ['webportal', 'device_st'];
	include(base_path('resources/views/opwifi/common.php'));
?>
@extends('opwifi.common.layouts.default')

@section('title', '设备状态')

@section('content')
@include('opwifi.common.partials.group')
<div>
<h2 class="page-header">设备状态</h2>
<div class="toolbar form-inline">
</div>
<table id="devstable"
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
    var $table = $('#devstable');
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
                        field: 'device.mac',
                        title: 'MAC地址',
                        sortable: true
                    }, {
                        field: 'device.name',
                        title: '名称',
                        sortable: true,
                    }, {
                        field: 'online',
                        title: '在线状态',
                        sortable: true,
                    }, {
                        field: 'last_show',
                        title: '最近上报时间',
                        sortable: true,
                    }, {
                        field: 'users',
                        title: '在线用户数',
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
        }
    };

    function getHeight() {
        return $(window).height() - $('h1').outerHeight(true);
    }

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
    });

    </script>
    @include('opwifi.common.partials.group_js', [
        'tableId' => 'devstable',
        'tableUrl' => '/'.Request::path().'/select',
        'groupUrl' => '/m/device/management/groups',
        'rootNode' => 'device',
        'relationshipUrl' => 'tag-relationships',
    ])

@endsection