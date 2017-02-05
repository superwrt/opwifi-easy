<?php
$menusel = ['devices', 'device_cfg'];
include(base_path('resources/views/opwifi/common.php'));
?>
@extends('opwifi.common.layouts.default')

@section('title', '配置编辑')

@section('content')
<div>
  <h3 class="page-header">配置编辑</h3>


  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#edit-mng" aria-controls="edit-mng" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-dashboard"><span>配置参数</a></li>
    <li role="presentation"><a href="#edit-wlan" aria-controls="edit-wlan" role="tab" data-toggle="tab"><span class="icon icon-podcast"><span>无线</a></li>
    <li role="presentation"><a href="#edit-net" aria-controls="edit-net" role="tab" data-toggle="tab"><span class="icon icon-sphere"><span>网络</a></li>
    <li role="presentation"><a href="#edit-sys" aria-controls="edit-sys" role="tab" data-toggle="tab"><span class="icon icon-cog"><span>系统</a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">

    <!-- Edit Mng Tab -->
    <div role="tabpanel" class="container-fluid tab-pane active" id="edit-mng">
      <p><span class="label label-info">提示</span>这里不对设备具体限制，请根据要配置的设备实际情况配置。</p>

      <div class="form-horizontal">
        <div class="form-group">
          <label class="col-sm-3 control-label">名称</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" id="db_name" name="db_name" value="{{ $config['name'] }}">
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">备注</label>
          <div class="col-sm-6">
            <textarea class="form-control input-md" id="db_comment" name="db_comment">{{ $config['comment'] }}</textarea>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">设备模型</label>
          <div class="col-sm-6">
            <select class="form-control" name="pv_model" id="pv_model">
              <option value="none">无WLAN</option>
              <option value="ng">单频 2.4G 11n</option>
              <option value="na">单频 5G 11n</option>
              <option value="ng_na">双频 2.4G 11n/5G 11n</option>
              <option value="ng_ca">双频 2.4G 11n/5G 11ac</option>
              <option value="ng_2ca">双频 2.4G 11n/2x 5G 11ac</option>
              <option value="cg_ca">双频 2.4G 11ac/5G 11ac</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">清空配置</label>
          <div class="col-sm-6">
            <form id="clear" method="POST">
              {!! csrf_field() !!}
              <input type="hidden" name="pdata" value="{}">
              <a type="submit" class="btn btn-danger">清空配置</a>
            </form>
          </div>
        </div>
      </div>


    </div>
    <!-- Edit WLAN Tab -->
    <div role="tabpanel" class="container-fluid tab-pane" id="edit-wlan">
      <p><span class="label label-warning">注意</span>访客网络功能仅路由模式生效。</p>

      <div class="form-group">
        <div class="form-inline">
            <div class="form-group">
              <label for="cf_wlan_mode">配置方式</label>
              <select class="form-control" id="cf_wlan_mode" data-cfg="wlan.m">
                <option value="replace">替换</option>
                <option value="change">修改</option>
              </select>
              <small>选择替换，将删除设备所有配置，完全使用下面配置</small>
            </div>
            
        </div>
      </div>

      <div id="wlan_ctx"></div>

    </div>
    <!-- Edit Net Tab -->
    <div role="tabpanel" class="container-fluid tab-pane" id="edit-net">

      <p><span class="label label-warning">注意</span>下面配置仅对路由模式生效。如未配置，将不改变设备原配置。</p>

      <div class="form-horizontal">
        <h4>局域网设置</h4>

        <div class="form-group">
          <label class="control-label col-sm-2">用户默认限速</label>
          <div class="form-inline col-sm-9">
            <div class="form-group">
              <label for="cf_lan_tc_chg">配置</label>
              <input type="checkbox" id="cf_lan_tc_cfg" data-cfg="net.v.lan.v.wan_traffic.c">
            </div>
            <div class="form-group">
              <label for="cf_lan_tc_tx">上行</label>
              <input type="text" class="form-control" id="cf_lan_tc_tx" data-cfg="net.v.lan.v.wan_traffic.v.tx.v" placeholder="kBps">
            </div>
            <div class="form-group">
              <label for="cf_lan_tc_rx">下行</label>
              <input type="email" class="form-control" id="cf_lan_tc_rx" data-cfg="net.v.lan.v.wan_traffic.v.rx.v" placeholder="kBps">
            </div>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>

      <div class="form-horizontal">
        <h4>访客网络设置</h4>

        <div class="form-group">
          <label class="control-label col-sm-2">用户默认限速</label>
          <div class="form-inline col-sm-9">
            <div class="form-group">
              <label for="cf_glan_tc_cfg">配置</label>
              <input type="checkbox" id="cf_glan_tc_cfg" data-cfg="net.v.glan.v.wan_traffic.c">
            </div>
            <div class="form-group">
              <label for="cf_glan_tc_tx">上行</label>
              <input type="text" class="form-control" id="cf_glan_tc_tx" data-cfg="net.v.glan.v.wan_traffic.v.tx.v" placeholder="kBps">
            </div>
            <div class="form-group">
              <label for="cf_glan_tc_rx">下行</label>
              <input type="email" class="form-control" id="cf_glan_tc_rx" data-cfg="net.v.glan.v.wan_traffic.v.rx.v" placeholder="kBps">
            </div>
          </div>
          <div class="clearfix"></div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-2">远程网页登录</label>
          <div class="form-inline col-sm-9">
            <div class="form-group">
              <label for="cf_webp_cfg">配置</label>
              <input type="checkbox" id="cf_webp_cfg" data-cfg="net.v.webportal.c">
            </div>
            <div class="form-group">
              <label for="cf_webp_enable">开启</label>
              <input type="checkbox" id="cf_webp_enable" data-cfg="net.v.webportal.v.enable.v">
              <input type="hidden" data-cfg="net.v.webportal.v.mode.v" value="remote">
            </div>
            <div class="form-group">
              <label for="cf_webp_serv">服务器地址</label>
              <input type="text" class="form-control" id="cf_webp_serv" data-cfg="net.v.webportal.v.remote.v.server.v" placeholder="http://xxxxx">
            </div>
            <div class="form-group">
              <label for="cf_webp_pwd">验证密码</label>
              <input type="email" class="form-control" id="cf_webp_pwd" data-cfg="net.v.webportal.v.remote.v.password.v" placeholder="可选">
            </div>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>

    </div>
    <!-- Edit Sys Tab -->
    <div role="tabpanel" class="container-fluid tab-pane" id="edit-sys">
      <p><span class="label label-warning">注意</span>如未开启或没有值，将不改变设备原配置。</p>

      <div class="form-horizontal">
        <h4>定时重启</h4>
        <div class="form-group">
          <label class="control-label col-sm-2">定时重启</label>
          <div class="form-inline col-sm-9">
            <div class="form-group">
              <label for="cf_sys_tmrb_cfg">配置</label>
              <input type="checkbox" id="cf_sys_tmrb_cfg" data-cfg="sys.v.reboot.v.time.c">
            </div>
            <div class="form-group">
              <label for="cf_sys_tmrb_enable">开启</label>
              <input type="checkbox" id="cf_sys_tmrb_enable" data-cfg="sys.v.reboot.v.time.v.enable.v">
            </div>
            <div class="form-group">
              <label for="cf_sys_tmrb_mode">模式</label>
              <select class="form-control" id="cf_sys_tmrb_mode" data-cfg="sys.v.reboot.v.time.v.mode.v">
                <option value="day">每天</option>
                <option value="week">每周</option>
                <option value="month">每月</option>
              </select>
            </div>
            <div class="form-group">
              <label for="cf_sys_tmrb_day">第几天</label>
              <input type="text" class="form-control" id="cf_sys_tmrb_day" data-cfg="sys.v.reboot.v.time.v.day.v" placeholder="周：0-6；月：1-31">
            </div>
            <div class="form-group">
              <label for="cf_sys_tmrb_time">重启时间</label>
              <input type="email" class="form-control" id="cf_sys_tmrb_time" data-cfg="sys.v.reboot.v.time.v.time.v" placeholder="0-23:xx">
            </div>
          </div>
        </div>
      </div>


    </div>
  </div><!-- End Tab panes -->

  <form id="edit" method="POST">
    {!! csrf_field() !!}
    <input type="hidden" id="fm_name" name="name">
    <input type="hidden" id="fm_comment" name="comment">
    <input type="hidden" id="fm_pdata" name="pdata">
    <button type="submit" class="btn btn-primary">保存</button>
    <a class="btn btn-link" href="../">取消</a>
  </form>

</div>

@endsection

@section('header_css')
<link rel="stylesheet" href="/res/pkgs/jstree/themes/default/style.min.css">
<link rel="stylesheet" href="/res/pkgs/bootstrap-table/bootstrap-table.css">
<link rel="stylesheet" href="/res/css/swrt-fonts.css">
@endsection
@section('footer_js')
<script src="/res/pkgs/bootstrap-table/bootstrap-table.js"></script>
<script src="/res/pkgs/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
<script src="/res/pkgs/x-editable/bootstrap-editable.js"></script>
<script src="/res/pkgs/bootstrap-table/bootstrap-table-editable.js"></script>
<script src="/res/pkgs/bootstrap-table/extensions/toolbar/bootstrap-table-toolbar.js"></script>

<script type="text/javascript">
  var config = {{ $config['config']?$config['config']:'{}' }};
  var pdata = <?php $config['pdata']?print($config['pdata']):print('{}'); ?>;

  function load_vaps($t, data) {
    $t.bootstrapTable({
            columns: [[
            {
              field: 'id',
              title: 'ID'
            }, {
              field: '_v.name.v',
              title: '接口'
            }, {
              field: '_v.ssid.v',
              title: 'SSID'
            }, {
              field: '_v.enable.v',
              title: '开启',
              formatter: function (value, row, index) {
                return value==true?'✔':'';
              }
            }, {
              field: '_v.auth.v',
              title: '认证',
              formatter: function (value, row, index) {
                switch(value) {
                  case 'open': return '开放 (Open)';
                  case 'wpa2-psk': return '加密 (WPA2-PSK)';
                }
              }
            }, {
              field: '_v.vlan.v',
              title: 'VLAN'
            }, {
              field: '_v.guest.v',
              title: '访客网络',
              formatter: function (value, row, index) {
                return value==true?'✔':'';
              }
            }, {
              field: 'operate',
              title: '操作',
              align: 'center',
              formatter: function (value, row, index) {
                return [
                '<a class="edit" href="javascript:void(0)" title="Edit">',
                '<i class="glyphicon glyphicon-pencil"></i>修改',
                '</a>  ',
                '<a class="delete" href="javascript:void(0)" title="Delete">',
                '<i class="glyphicon glyphicon-trash"></i>删除',
                '</a>  '
                ].join('');
              },
              events: {
                'click .edit': function (e, value, row, index) {
                  $.opwifi.rowOpwifiEdit(index, $t,
                      'editcfg'+row.id, '修改'+row._v.name.v+'配置',[
                          {title:'开启', field:'_v.enable.v', type:'check'},
                          {title:'SSID', field:'_v.ssid.v'},
                          {title:'隐藏', field:'_v.hidden.v', type:'check'},
                          {title:'认证', field:'_v.auth.v', type:"select", opts: [
                              {name: '开放 (Open)', value: 'open'},
                              {name: '加密 (WPA2-PSK)', value: 'wpa2-psk'}
                          ]},
                          {title:'密码', field:'_v.password.v'},
                          {title:'VLAN', field:'_v.vlan.v'},
                          {title:'访客网络', field:'_v.guest.v', type:'check'},
                          {title:'隔离', field:'_v.isolate.v', type:'check'},
                          {title:'最大终端数', field:'_v.max_sta.v'},
                          {title:'接入信号门限', field:'_v.weak_signal.v', comment:'-30 ~ -90, dbm。'},
                      ],row);
                },
                'click .delete': function (e, value, row, index) {
                  $t.bootstrapTable('remove', {field: 'id', values: [row.id]});
                }
              }
            }
            ]],
            dataField: 'data',
            data: data
          });
  }

  function wlan_add_vap(idx) {
    var i;
    var $t = $('#cf_wlan'+idx+'_vaps');
    var all = $t.bootstrapTable('getData');
    var id = 0;
    for (id = 0; id < 8; id++) {
      for(i = 0; i < all.length; i++) {
        if (all[i].id == id) break;
      }
      if (i == all.length) break;
    }
    if (id == 8) return false;

    for (i = 0; i < all.length; i++) {
      if (all[i].id > id) break;
    }

    $t.bootstrapTable('insertRow', {index:i,
      row:{'id':id, '_v':
        {'id':{'v':1},
        'name': {'v':'wlan'+(idx?idx:'')+id},
        'auth': {'v':'open'},
        'mode': {'v':'ap'},
        'ssid': {'v':'superwrt_wlan'+(idx?idx:'')+id},
        'enable': {'v':true},
        'guest': {'v':false},
        }
      }});
    return false;
  }

  function load_wlan_cfg(idx) {
    var wlans = $.opwifi.getItemField(pdata, "config.wlan.v");
    if (wlans && typeof(wlans[idx]) != "undefined" && typeof(wlans[idx].v) != "undefined") {
      $('#cf_wlan'+idx+'_cfg').prop('checked', wlans[idx].c);
      var wlan = wlans[idx].v;
      var channel = $.opwifi.getItemField(wlan, "channel.v");
      var bandwidth = $.opwifi.getItemField(wlan, "bandwidth.v");
      var power = $.opwifi.getItemField(wlan, "txpower.v");
      if (channel !== null) $('#cf_wlan'+idx+'_ch').val(channel);
      if (bandwidth !== null) $('#cf_wlan'+idx+'_bw').val(bandwidth);
      if (power !== null) $('#cf_wlan'+idx+'_pw').val(power);
      var vs = [];
      var vaps = $.opwifi.getItemField(wlan, "vaps.v");
      if (vaps) {
        for (var i in vaps) {
          if (typeof(vaps[i].v) != "undefined") {
            var vap = vaps[i].v;
            var id = $.opwifi.getItemField(vap, "id.v");
            if (id !== null) vs.push({'id':id, '_v':vap});
          }
        }
      }
      load_vaps($('#cf_wlan'+idx+'_vaps'), vs);
    } else {
      load_vaps($('#cf_wlan'+idx+'_vaps'), []);
    }
  }

  var wlan_model = "none";
  function load_wlan(model) {
    if (model == wlan_model)
      return;
    wlan_model = model;

    function add_wlan(rf, mode, idx) {
      var wlan = Array();
      wlan.push('<div id="cf_wlan'+idx+'">\
          <div><h4>射频'+(rf==2?'2.4G':'5G')+'</h4></div>\
          <div class="form-group"><div class="checkbox">\
              <label><input type="checkbox" name="cf_wlan'+idx+'_cfg" id="cf_wlan'+idx+'_cfg">\
                <strong>配置</strong>（如不选择配置，将不更改设备原配置）</label>\
          </div></div>');

      wlan.push('<input type="hidden" name="cf_wlan'+idx+'_nm" id="cf_wlan'+idx+'_nm" value="phy'+idx+'">');

      wlan.push('<div class="form-group"><div class="form-inline">');
      wlan.push('<div class="form-group"><label for="cf_wlan'+idx+'_bw">频宽</label>\
                <select class="form-control" id="cf_wlan'+idx+'_bw" name="cf_wlan'+idx+'_bw">\
                  <option value="">不变更</option>\
                  <option value="20">20MHz</option>');
      if (mode > 1) wlan.push('<option value="40">40MHz</option>');
      if (mode > 2 && rf != 5) wlan.push('<option value="80">80MHz</option>');
      wlan.push('</select></div>');

      wlan.push('<div class="form-group"><label for="cf_wlan'+idx+'_ch">信道</label>\
                <select class="form-control" id="cf_wlan'+idx+'_ch" name="cf_wlan'+idx+'_ch">\
                  <option value="">不变更</option>\
                  <option value="0">自动</option>');
      if (rf!=5) {
        for (var i = 1; i < 14; i++) {
          wlan.push('<option value="'+i+'">'+i+' ('+(2407+i*5)+'MHz)</option>');
        }
      }
      if (rf!=2) {
        for (var i = 36; i < 165; i++) {
          wlan.push('<option value="'+i+'">'+i+' ('+(5180+(i-36)*5)+'MHz)</option>');
        }
      }
      wlan.push('</select></div>');

      wlan.push('<div class="form-group"><label for="cf_wlan'+idx+'_pw">功率</label>\
                <select class="form-control" id="cf_wlan'+idx+'_pw" name="cf_wlan'+idx+'_pw">\
                  <option value="">不变更</option>');
      for(var i = 27; i >= 0; i--) {
        wlan.push('<option value="'+i+'">'+i+'dBm</option>');
      }
      wlan.push('</select></div>');

      wlan.push('<div class="form-group"><a id="cf_wlan'+idx+'_add" class="btn btn-success btn-sm" onClick="wlan_add_vap('+idx+')"><span class="glyphicon glyphicon-plus"></span> 增加SSID</a></div>');

      wlan.push('</div>');
      wlan.push('</div>');

      wlan.push('<table id="cf_wlan'+idx+'_vaps" data-id-field="id"></table>');
      wlan.push('</div>');

      $('#wlan_ctx').append(wlan.join(''));

      load_wlan_cfg(idx);
    }

    $('#wlan_ctx').empty();
    switch(model) {
    case "ng": add_wlan(2, 2, 0); break;
    case "na": add_wlan(5, 2, 0); break;
    case "ng_na": add_wlan(2, 2, 0); add_wlan(5, 2, 1); break;
    case "ng_ca": add_wlan(2, 2, 0); add_wlan(5, 3, 1); break;
    case "ng_2ca":  add_wlan(2, 2, 0); add_wlan(5, 3, 1); add_wlan(5, 3, 2); break;
    case "cg_ca": add_wlan(2, 3, 0); add_wlan(5, 3, 1); break;
    case "none": break;
    }
  }

  function load_pdata() {
    var wlan_model = $.opwifi.getItemField(pdata, "priv.wlan_model");
    if (wlan_model) {
      $('#pv_model').val(wlan_model);
      load_wlan(wlan_model);
    }

    $('[data-cfg]').each(function() {
      var v = $.opwifi.getItemField(pdata, "config."+$(this).data("cfg"));
      if (v !== null) {
        if ($(this).prop("type") == "checkbox") {
          $(this).prop("checked",v)
        } else {
          $(this).val(v);
        }
      }
    });
  }

  function submit_edit() {
    var $this = $(this);
    var pd = { priv:{}, config:{}};
    try{
      pd['priv']['wlan_model'] = $('#pv_model').val();
      pd['config']['wlan'] = {v:[]};
      var wlans = pd.config.wlan.v;
      for (var i = 0; $('#cf_wlan'+i).length ; i++) {
        var p = '#cf_wlan'+i+'_';
        var wlan = {
          name: {v:$(p+'nm').val(),c:true},
          channel: {v:$(p+'ch').val(),c:$(p+'ch').val()?true:false},
          bandwidth: {v:$(p+'bw').val(),c:$(p+'bw').val()?true:false},
          txpower: {v:$(p+'pw').val(),c:$(p+'pw').val()?true:false},
          vaps: {v:[], c:true}
        };
        var vaps = wlan.vaps.v;
        var cvaps = $(p+'vaps').bootstrapTable('getData');
        for (var j = 0; j < cvaps.length; j++) {
          vaps.push({v:cvaps[j]._v, c:true});
        }
        wlans.push({v:wlan, c:$(p+'cfg').prop('checked')});
      }

      $('[data-cfg]').each(function() {
        $.opwifi.setItemField(pd, "config."+$(this).data("cfg"),
          ($(this).prop("type") == "checkbox")?$(this).prop("checked"):this.value);
      });

      $('#fm_pdata').val(JSON.stringify(pd));
      $('#fm_name').val($('#db_name').val());
      $('#fm_comment').val($('#db_comment').val());
      return true;
    } catch(e) {
      console.log(e);
    }
    return false;
  }

  $().ready(function(){
    $('#pv_model').change(function(){
      load_wlan($(this).val());
    });
    $('#edit').submit(submit_edit);
    load_pdata();
  });

</script>

@endsection