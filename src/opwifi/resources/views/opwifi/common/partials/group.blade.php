<div id="page-meta-links">
<div class="collapse" id="collapseGroup">
  <div class="well">
    <div class="row">
        <div class="col-md-6 col-sm-8 col-xs-8">
            <button type="button" class="btn btn-success btn-sm" onclick="group_create();"><i class="glyphicon glyphicon-file"></i> 创建Tag</button>
            <button type="button" class="btn btn-info btn-sm" onclick="group_create(true);"><i class="glyphicon glyphicon-plus"></i> 创建组</button>
            <button type="button" class="btn btn-warning btn-sm" onclick="group_rename();"><i class="glyphicon glyphicon-pencil"></i> 重命名</button>
            <button type="button" class="btn btn-danger btn-sm" onclick="group_delete();"><i class="glyphicon glyphicon-remove"></i> 删除</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="group_apply();"><i class="glyphicon glyphicon-ok"></i> 应用更改</button>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-4" style="text-align:right;">
            <input type="text" value="" style="box-shadow:inset 0 0 4px #eee; width:120px; margin:0; padding:6px 12px; border-radius:4px; border:1px solid silver; font-size:1.1em;" id="group_search" placeholder="Search" />
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div id="jstree_group" class="demo" style="margin-top:1em; min-height:200px;"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-sm-8 col-xs-8">
        <button type="button" class="btn btn-primary btn-sm" onclick="group_filter();"><i class="glyphicon glyphicon-filter"></i> 筛选</button>
        <button type="button" class="btn btn-warning btn-sm" onclick="group_filter(true);"><i class="glyphicon glyphicon-erase"></i> 重置</button>
        <button type="button" class="btn btn-info btn-sm" onclick="group_add();"><i class="glyphicon glyphicon-open-file"></i> 加入</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="group_remove();"><i class="glyphicon glyphicon-save-file"></i> 移除</button>
        </div>
    </div>

  </div>
</div>
<div class="page-meta-toggle">
<a class="btn" role="button" data-toggle="collapse" href="#collapseGroup" aria-expanded="false" aria-controls="collapseGroup">
  Tag分组
</a>
</div>
<div style="clear: both"></div>
</div>