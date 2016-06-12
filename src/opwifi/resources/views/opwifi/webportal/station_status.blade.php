<?php
	$menusel = ['webportal', 'station_st'];
	include(base_path('resources/views/opwifi/common.php'));
?>
@extends('opwifi.common.layouts.default')

@section('title', '终端状态')

@section('content')

<div>
<h2 class="page-header">终端状态</h2>
<div class="toolbar form-inline">
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
	<script src="/res/pkgs/bootstrap-table/bootstrap-table.js"></script>
	<script src="/res/pkgs/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
	<script src="/res/pkgs/x-editable/bootstrap-editable.js"></script>
	<script src="/res/pkgs/bootstrap-table/bootstrap-table-editable.js"></script>
	<script src="/res/pkgs/bootstrap-table/bootstrap-table-fixed-columns.js"></script>
	<script src="/res/pkgs/bootstrap-table/extensions/toolbar/bootstrap-table-toolbar.js"></script>

	<script type="text/javascript">
    var $table = $('#stastable');
    function load_stations() {
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
                        field: 'mac',
                        title: 'MAC地址',
                        sortable: true
                    }, {
                        field: 'ondev',
                        title: '所在设备',
                        sortable: true,
                    }, {
                        field: 'online',
                        title: '在线状态',
                        sortable: true,
                    }, {
                        field: 'auth',
                        title: '认证状态',
                        sortable: true,
                    }, {
                        field: 'lastonline',
                        title: '最近上线时间',
                        sortable: true,
                    }, {
                        field: 'lastoffline',
                        title: '最近离线时间',
                        sortable: true,
                    }, {
                        field: 'online_time',
                        title: '上线时长',
                        sortable: true,
                    }, {
                        field: 'online_total',
                        title: '总上线时长',
                        sortable: true,
                    }, {
                        field: 'tx_rate',
                        title: '发送速率',
                        sortable: true,
                    }, {
                        field: 'rx_rate',
                        title: '接收速率',
                        sortable: true,
                    }, {
                        field: 'trx_used',
                        title: '本次流量',
                        sortable: true,
                    }, {
                        field: 'trx_total',
                        title: '总流量',
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
            '</a>  '
        ].join('');
    }

    window.operateEvents = {
        'click .detail': function (e, value, row, index) {
            alert('Detail Info: ' + JSON.stringify(row));
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
        load_stations();
    });

    </script>

@endsection