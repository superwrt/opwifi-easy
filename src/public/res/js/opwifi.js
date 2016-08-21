/*!
 * opwifi JavaScript Library
 * http://opwifi.com/
 *
 * Copyright opwifi.com and other contributors
 * Released under the MIT license
 * http://opwifi.com/license
 */

!function($) {
    'use strict';

    var getItemField = function (item, field, escape) {
        var value = item;

        if (typeof field !== 'string' || item.hasOwnProperty(field)) {
            return escape ? escapeHTML(item[field]) : item[field];
        }
        var props = field.split('.');
        for (var p in props) {
            value = (value && value[props[p]] !== undefined)?value[props[p]]:null;
        }
        return escape ? escapeHTML(value) : value;
    };

    var setItemField = function (item, field, value) {
        if (typeof field !== 'string' || item.hasOwnProperty(field)) {
            return item[field] = value;
        }
        var props = field.split('.');
        for (var p in props) {
            if (p == props.length - 1) {
                item[props[p]] = value;
            } else {
                if (item[props[p]] === undefined || typeof(item[props[p]]) != "object") {
                    item[props[p]] = isNaN(Number(props[p]))?new Object():new Array();
                }
                item = item[props[p]];
            }
        }
    };

    var createModal = function(id, type, title) {
        var vModal = new Array();
        vModal.push('<div id="ow'+type+'Modal_'+id+'"  class="modal fade" tabindex="-1" role="dialog" aria-labelledby="owSmallModalLabel" aria-hidden="true">');
        vModal.push('<div class="modal-dialog modal-xs">');
        vModal.push(' <div class="modal-content">');
        vModal.push('  <div class="modal-header">');
        vModal.push('   <button type="button" class="close" data-dismiss="modal" aria-hidden="true" >&times;</button>');
        vModal.push('   <h4 class="modal-title">'+title+'</h4>');
        vModal.push('  </div>');
        vModal.push('  <div class="modal-body modal-body-custom">');
        vModal.push('   <div class="container-fluid" id="ow'+type+'ModalContent_'+id+'" style="padding-right: 0px;padding-left: 0px;" >');
        vModal.push('   </div>');
        vModal.push('  </div>');
        vModal.push('  </div>');
        vModal.push(' </div>');
        vModal.push('</div>');

        $("body").append($(vModal.join('')));
    }

    var pushModalOperation = function(id, type, htmlForm) {
        htmlForm.push('<div class="form-group">');
        htmlForm.push('<div class="col-sm-offset-8 col-sm-4">');
        htmlForm.push('<button type="submit" id="btnApply'+type+'_'+id+'" class="btn btn-default">应用</button>');
        htmlForm.push('<button type="button" id="btnClose'+type+'_'+id+'" class="btn btn-default">关闭</button>');
        htmlForm.push('</div>');
        htmlForm.push('</div>');
    }

    var showInputModal = function(id, modalTitle, opts, vals, apply) {

        if (!$("#owInputModal" + "_" + id).hasClass("modal")) {
            createModal(id, 'Input', modalTitle);

            var vFormInput = createFormInput(id, opts, vals, apply);

            $('#owInputModalContent' + "_" + id).append(vFormInput.join(''));

            $("#btnCloseInput" + "_" + id).click(function() {
                $("#owInputModal" + "_" + id).modal('hide');
            });
            if (apply && typeof(apply)!='string') {
	            $("#"+id).submit(function(e) {
	            	var $this = $(this);
	            	var fields = [];
	            	$this.find('input').each(function() {
	            		fields.push({'name':this.name, 'id':this.id, 'value':
                            ($(this).prop("type") == "checkbox")?$(this).prop("checked"):this.value});
	            	});
                    $this.find('textarea').each(function() {
                        fields.push({'name':this.name, 'id':this.id, 'value':this.value});
                    });
	            	$this.find('select').each(function() {
	            		fields.push({'name':this.name, 'id':this.id, 'value':this.value});
	            	});
                    try {
    	            	if (apply($this, fields)) {
    		                $("#owInputModal" + "_" + id).modal('hide');
    		            }
                    } catch(e) {
                        console.log(e);
                    }
	                return false;
	            });
	        }
        }
        $("#owInputModal" + "_" + id).modal();
    };

    var createFormInput = function(id, opts, vals, apply) {
        var htmlForm = [];
        htmlForm.push('<form class="form-horizontal" method="post" id="'+
        	id+'" '+(typeof(apply)=='string'?'action="'+apply+'"':'')+'>');
        for (var i in opts) {
            var opt = opts[i];
            var val = getItemField(vals, opt.field);
            if (opt.type != 'hidden') {
                htmlForm.push('<div class="form-group" id="group_'+id+'_'+opt.field+'"">');
                htmlForm.push('<label class="col-sm-4 control-label">'+opt.title+'</label>');
                htmlForm.push('<div class="col-sm-6">');
            }
            switch(opt.type) {
            case 'check':
                console.log(val);
            	htmlForm.push('<label><input type="checkbox" class="input-md" name="'+
            		opt.field+'" placeholder="'+opt.title+'" id="'+id+'_'+opt.field+'" '+(val?"checked":'')+'></label>');
            	break;
            case 'select':
				htmlForm.push('<select class="form-control input-md" name="'+
            		opt.field+'" placeholder="'+opt.title+'" id="'+id+'_'+opt.field+'" >');
				for (i in opt.opts) {
					var o = opt.opts[i];
					htmlForm.push('<option value="'+o.value+'" '+(val==o.value?'selected':'')+'>'+o.name+'</option>');
				}
		        htmlForm.push('</select>');
		        break;
            case 'textarea':
                htmlForm.push('<textarea class="form-control input-md" name="'+
                    opt.field+'" placeholder="'+opt.title+'" id="'+id+'_'+opt.field+'">'+(val?val:'')+'</textarea>');
                break;
            case 'hidden':
                htmlForm.push('<input type="text" name="'+opt.field+'" value="'+(val?val:'')+'" hidden>');
                break;
            default:
            	htmlForm.push('<input type="text" class="form-control input-md" name="'+
            		opt.field+'" placeholder="'+opt.title+'" id="'+id+'_'+opt.field+'" value="'+(val?val:'')+'">');
            }
            if (opt.type != 'hidden') {
                if (opt.comment) {
                    htmlForm.push('<p class="help-block">'+opt.comment+'</p>');
                }
                htmlForm.push('</div>');
                htmlForm.push('</div>');
            }
        }

        pushModalOperation(id, 'Input', htmlForm);
        htmlForm.push('</form>');

        return htmlForm;
    };


    var showSelectModal = function(id, title, srcUrl, dispField, bindField, apply) {

        if (!$("#owSelectModal_" + id).hasClass("modal")) {
            createModal(id, 'Select', title);

            var srcDat;
            $.ajax({
                async: false,
                cache: false,
                dataType:"json",
                contentType: "application/json; charset=utf-8",
                type: 'GET',
                url: srcUrl,
                success: function(d) {
                    srcDat = d;
                },
                error: function(e) {
                    $.opwifi.opalert($('#owcontent'), 'warning');
                }
            });

            if (!srcDat) {
                $("#owSelectModal_" + id).modal('hide');
                return;
            }

            var htmlForm = [];
            htmlForm.push('<form class="form-horizontal" method="post" id="'+
                id+'" '+(typeof(apply)=='string'?'action="'+apply+'"':'')+'>');
            htmlForm.push('<select class="form-control input-md" name="'+bindField+'" placeholder="'+title+'" id="'+id+'_sel" >');
            htmlForm.push('<option value="">无</option>');
            for (var i in srcDat) {
                var o = srcDat[i];
                htmlForm.push('<option value="'+o.id+'" >'+o[dispField]+'</option>');
            }
            htmlForm.push('</select>');
            pushModalOperation(id, 'Select', htmlForm);
            htmlForm.push('</form>');

            $('#owSelectModalContent_' + id).append(htmlForm.join(''));

            $("#btnCloseSelect_" + id).click(function() {
                $("#owSelectModal_" + id).modal('hide');
            });
            if (apply && typeof(apply)!='string') {
                $("#"+id).submit(function(e) {
                    var $this = $(this);
                    var val = $('#'+id+'_sel').val();
                    if (apply($this, val)) {
                        $("#owSelectModal_" + id).modal('hide');
                    }
                    return false;
                });
            }
        }
        $("#owSelectModal" + "_" + id).modal();
    };

    var insNum = 0;

    $.opwifi = {
        opalert: function ($parent, type, text) {
            if (text == null) {
                switch(type) {
                case 'success': text="操作成功"; break;
                case 'danger': text="操作失败"; break;
                default: text="内部错误"; break;
                }
            }
            var id = 'opalert_'+insNum++;
            var h = '<div id="'+id+'" class="owalert alert alert-dismissible alert-'+type+'" role="alert">\
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                text+'</div>';
            $parent.append(h);
            setTimeout(function() {if ($('#'+id).length)$('#'+id).remove();},1500);
        },
        getItemField: getItemField,
        setItemField: setItemField
    };

    $.fn.opwifi = function() {
    	return this;
    }
    $.fn.inputModal = function (id, modalTitle, opts, vals, f_apply) {
		var $this = $(this);
		$this.click(function(){
			showInputModal(id, modalTitle, opts, vals, f_apply);
		});
		return this;
    };

    function ajaxOpwifiApply(url, $table, data) {
        $.ajax({
            async: false,
            cache: false,
            dataType:"json",
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify(data),
            type: 'POST',
            url: url,
            success: function(d) {
                $.opwifi.opalert($('#owcontent'), 'success');
                $table.bootstrapTable('refresh');
                return true;
            },
            error: function(e) {
                $.opwifi.opalert($('#owcontent'), 'warning');
                return true;
            }
        });
    }

    $.fn.ajaxOpwifiAdd = function (url, $table, id, modalTitle, opts, vals) {
        var $this = $(this);
        $this.click(function(){
            showInputModal(id, modalTitle, opts, vals, function($form, field) {
                var newdat = {};
                for(var i in field) {
                    newdat[field[i]['name']] = field[i]['value'];
                }
                ajaxOpwifiApply(url, $table, [newdat]);
                return true;
            });
        });
    };

    $.fn.ajaxOpwifiOperation = function (url, $table, vals) {
        var $this = $(this);
        $this.click(function(){
            var rows = $table.bootstrapTable('getAllSelections');
            var items = [];
            for (var i in rows) {
                var it = {'id': rows[i].id};
                if (vals) it = $.extend(it, vals);
                items.push(it);
            }
            ajaxOpwifiApply(url, $table, items);
            return true;
        });
    };

    $.fn.ajaxOpwifiBind = function (url, $table, id, title, srcUrl, dispField, bindField) {
        var $this = $(this);
        $this.click(function(){
            var rows = $table.bootstrapTable('getAllSelections');
            if (!rows.length) return;
            function apply($form, val) {
                if (!val) return true;
                var items = [];
                for (var i in rows) {
                    var it = {'id': rows[i].id};
                    var fs = bindField.split('.');
                    var j, e = it;
                    for (j in fs) {
                        if (j < fs.length - 1) {
                            e[fs[j]] = {};
                            e = e[fs[j]];
                        } else {
                            e[fs[j]] = val;
                        }
                    }
                    
                    items.push(it);
                }
                ajaxOpwifiApply(url, $table, items);
                return true;
            }
            showSelectModal(id, title, srcUrl, dispField, bindField, apply);
        });
    };

    $.opwifi.ajaxOpwifiEdit = function (url, $table, id, modalTitle, opts, vals) {
        showInputModal(id, modalTitle, opts, vals, function($form, field) {
            var items = {};
            for(var i in field) {
                items[field[i]['name']] = field[i]['value'];
            }
            ajaxOpwifiApply(url, $table, items);
            return true;
        });
    };

    $.opwifi.rowOpwifiEdit = function (idx, $table, id, modalTitle, opts, vals) {
        showInputModal(id, modalTitle, opts, vals, function($form, field) {
            for(var i in field) {
                setItemField(vals, field[i]['name'], field[i]['value']);
            }
            $table.bootstrapTable('updateRow', {index: idx, row: vals});
            return true;
        });
    };



}(jQuery);