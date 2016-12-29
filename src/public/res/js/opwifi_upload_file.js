$(function(){
    $('.upload-file-mask').on('click',function(){
        $(this).hide();
        $('.upload-file').hide();
    })

    $('.upload-file .close').on('click',function(){
        $('.upload-file-mask').hide();
        $('.upload-file').hide();
    })

    $.fn.uploadFile = function(id, success){
        $('#uploadFileProgress .progress-bar').css('width','0%');
        $('#uploadFileForm').fileupload({
            dataType: 'json',
            done: function (e, data) {
                if(data.success == false) {
                    var responseErrors = data.errors;
                    $.each(responseErrors, function(index, value){
                        if (value.length != 0) {
                            $.opwifi.opalert($('#owcontent'), 'warning', value);
                        }
                    });
                    $.opwifi.opalert($('#owcontent'), 'warning');
                } else {
                    $('.upload-file-mask').hide();
                    $('.upload-file').hide();

                    $.opwifi.opalert($('#owcontent'), 'success');
                    if (success) success(data);
                }
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#uploadFileProgress .progress-bar').css(
                    'width',
                    progress + '%'
                );
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');

        $(this).click(function(){
            $('.upload-file-mask').show();
            $('.upload-file').show();
            if (id) $('#uploadFileID').attr('value',id);
        });
    };

})