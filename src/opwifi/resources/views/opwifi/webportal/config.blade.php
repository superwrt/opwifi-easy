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
                        formatter: function (value, row, index) {
                            if(row['mode']=='partner') {
                                return value;
                            } else {
                                return row['mode']=='login'?'(用户登录)':'(确认点击)';
                            }
                        }
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

            var tags;
            $.ajax({
                async: false,
                cache: false,
                dataType:"json",
                contentType: "application/json; charset=utf-8",
                type: 'GET',
                url: '/m/station/management/all-tags',
                success: function(d) {
                    tags = d;
                },
                error: function(e) {
                    $.opwifi.opalert($('#owcontent'), 'warning');
                }
            });
            if (!tags) {
                return;
            }
            var taglist = [{name:"无", value:0}];
            for (var i in tags) {
                taglist.push({name:tags[i].name, value:tags[i].id});
            }

            $.opwifi.ajaxOpwifiEdit("{{ '/'.Request::path().'/update' }}", $table,
                'editcfg'+row.id, '修改配置',[
                    {field:'id', type:'hidden'},
                    {title:'名称', field:'name'},
                    {title:'模式', field:'mode', type:"select", opts: [
                        {name: '用户登录', value: 'login'},
                        {name: '确认点击', value: 'confirm'},
                        {name: '直接放行', value: 'pass'},
                        {name: '外部', value: 'partner'}
                    ]},
                    {title:'重定向地址', field:'redirect', comment:'以http://开头。'},
                    {title:'外部Token', field:'access_token', comment:'仅外部模式时使用。'},
                    {title:'强制超时时间', field:'force_timeout', comment:'秒，60-2592000。'},
                    {title:'空闲超时时间', field:'idle_timeout', comment:'秒，20-172800。'},
                    {title:'最大用户数', field:'max_users', comment:'为0时不限制。'},
                    {title:'上报周期', field:'period', comment:'秒，5-172800。'},
                    {title:'IP白名单', field:'white_ip', type:"textarea", comment:'多个之间以“,”号分隔。'},
                    {title:'域名白名单', field:'white_domain', type:"textarea", comment:'多个之间以“,”号分隔。'},
                    {title:'允许漫游', field:'roaming', type:"check"},
                    {title:'终端MAC地址过滤', field:'mac_filter_type', type:"select", opts: [
                        {name: '无', value: 'none'},
                        {name: '仅允许Tag中终端', value: 'allow'},
                        {name: '不允许Tag中终端', value: 'deny'}
                    ]},
                    {title:'终端MAC地址Tag', field:'mac_filter_tag', type:"select", opts: taglist}
                ],row);
            function onModeChg() {
                var dis=($('#editcfg'+row.id+'_mode').val() == 'partner')?"block":"none";
                $('#group_editcfg'+row.id+'_redirect').css("display", dis);
                $('#group_editcfg'+row.id+'_access_token').css("display", dis);
                $('#group_editcfg'+row.id+'_mac_filter_tag').css("display",
                    ($('#editcfg'+row.id+'_mac_filter_type').val() == 'none')?"none":"block");
            }
            onModeChg();
            $('#editcfg'+row.id+'_mode').change(onModeChg);
            $('#editcfg'+row.id+'_mac_filter_type').change(onModeChg);
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
