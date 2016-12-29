{{-- Need include jquery.form.js --}}

<div class="upload-file-mask">
</div>
<div class="panel panel-info upload-file">
    <div class="panel-heading">
        上传文件
        <span class="close pull-right">关闭</span>
    </div>
    <div class="panel-body">
        <div id="validation-errors"></div>
        <form method="post" action="{{ $action }}" id='uploadFileForm'>
        <div class="form-group">
            <label>上传</label>
            <span class="require">(*)</span>
            @if (isset($inputs))
            @foreach ($inputs as $in)
            <div class="form-group">
               <label class="col-sm-4 control-label">{{$in['title']}}</label>
               <div class="col-sm-6"><input name="{{$in['name']}}" type="text"></div>
            </div>
            @endforeach
            @endif
            <div class="form-group">
                <input id="uploadFileThumb" name="file" type="file"  required="required">
            </div>
            <input id="uploadFileID"  type="hidden" name="id" value="">
            {!! csrf_field() !!}
        </div>
        </form>
    </div>
    <div class="panel-footer">
        <div id="uploadFileProgress" class="progress">
            <div class="progress-bar progress-bar-success"></div>
        </div>
    </div>
</div>