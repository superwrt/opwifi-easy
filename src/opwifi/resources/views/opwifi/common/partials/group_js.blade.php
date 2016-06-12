 <script type="text/javascript">
    function group_sel_tags() {
        var ref = $('#jstree_group').jstree(true),
            sels = ref.get_selected();
        if(!sels.length) return false;
        var tag_ids = [];
        for (i in sels) {
            function add_tag(id) {
                if (!id) return;
                if (id.substr(0, 4) != 'tag_') {
                    add_tags(id);
                } else {
                    tag_ids[tag_ids.length] = id.substr(4);
                }
            }
            function add_tags(pid) {
                var nodes = ref.get_children_dom(pid);
                for(i in nodes) {
                    add_tag(nodes[i]['id']);
                }
            }
            add_tag(sels[i]);
        }
        return tag_ids;
    }
    function group_create(group) {
        var ref = $('#jstree_group').jstree(true),
            sel = ref.get_selected();
        sel = sel.length?sel[0]:null;
        sel = ref.create_node(sel, {"type":group?"group":"tag"});
        if(sel) {
            ref.edit(sel);
        }
    };
    function group_rename() {
        var ref = $('#jstree_group').jstree(true),
            sel = ref.get_selected();
        if(!sel.length) { return false; }
        sel = sel[0];
        ref.edit(sel);
    };
    function group_delete() {
        var ref = $('#jstree_group').jstree(true),
            sel = ref.get_selected();
        if(!sel.length) { return false; }
        ref.delete_node(sel);
    };
    function group_apply() {
    	var ref = $('#jstree_group').jstree(true);
        $.ajax({
            url: '{{ $groupUrl }}',
            type: "POST",
            dataType: 'json',
            data: JSON.stringify(ref.get_json()),
            contentType: "application/json; charset=utf-8",
            success: function(r) {
                if (r)
                    $.opwifi.opalert($('#owcontent'), 'success');
                $('#jstree_group').jstree({
                    "core" : {'data' : r}
                });
            },
            error: function(e) {
                $.opwifi.opalert($('#owcontent'), 'error');
            }
        });
    };
    function group_bind(op) {
        var devs = $('#{{ $tableId }}').bootstrapTable('getAllSelections');
        if(!devs.length) { return false; }
        var tag_ids = group_sel_tags();
        var macs = [];
        var ops = [];
        for (i in devs) {
            macs[macs.length] = devs[i].device.mac;
        }
        if (op == "remove") {
            for (i in tag_ids) {
                var t = {"id":tag_ids[i]};
                t[op] = macs;
                ops[ops.length] = t;
            }
        } else {
            var t = {"id":tag_ids[0]};
            t[op] = macs;
            ops[0] = t;
        }
        $.ajax({
            url: '{{ $relationshipUrl }}',
            type: "POST",
            dataType: 'json',
            data: JSON.stringify(ops),
            contentType: "application/json; charset=utf-8",
            success: function(r) {
                if (r)
                    $.opwifi.opalert($('#owcontent'), 'success');
            },
            error: function(e) {
                $.opwifi.opalert($('#owcontent'), 'error');
            }
        });
    }
    function group_add() {
        group_bind("add");
    }
    function group_remove() {
        group_bind("remove");
    }

    function group_filter(reset) {
        var $table = $('#{{ $tableId }}');
        if (reset) {
            $table.bootstrapTable('refresh', {
                url: "{{ $tableUrl }}"
            });
            return;
        }
        var tag_ids = group_sel_tags();
        
        if (!tag_ids.length) return false;
        $table.bootstrapTable('refresh', {
            url: "{{ $tableUrl }}?tag_ids="+tag_ids.join(',')
        });
    }
    $(function () {
        var to = false;
        $('#group_search').keyup(function () {
            if(to) { clearTimeout(to); }
            to = setTimeout(function () {
                var v = $('#group_search').val();
                $('#jstree_group').jstree(true).search(v);
            }, 250);
        });

        $('#jstree_group').jstree({
            "core" : {
                "animation" : 0,
                "check_callback" : true,
                'force_text' : true,
                "themes" : { "stripes" : true },
                'data' : {
                    'url' : '{{ $groupUrl }}',
                }
            },
            "types" : {
                "#" : { "valid_children" : ["group", "tag"] },
                "group" : { "valid_children" : ["group","tag"] },
                "tag" : { "icon" : "glyphicon glyphicon-file", "valid_children" : [] }
            },
            "plugins" : [ "contextmenu", "dnd", "search", "state", "types", "wholerow", "unique" ]
        });
    });
</script>