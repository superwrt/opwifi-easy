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
        $('#uploadFileForm').submit(function(){
            $('#uploadFileForm').ajaxSubmit({
                dataType: 'json',
                beforeSubmit: function () {
                        $("#validation-errors").hide().empty();
                        $("#output").css('display','none');
                        return true;
                    },
                success: function (response) {
                    if(response.success == false) {
                        var responseErrors = response.errors;
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
                        if (success) success(response);
                    }
                }
            });
            return false;
        });
        $(this).click(function(){
            $('.upload-file-mask').show();
            $('.upload-file').show();
            if (id) $('#uploadFileID').attr('value',id);
        });
    };

})