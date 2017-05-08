$(document).ready(function(){
    $('body').on('click', 'input.date', function(){
        $(this).datepicker('destroy');
        $(this).datepicker({dateFormat: "dd.mm.yy"}).datepicker( "show" ).datepicker( "setDate", $(this).val());

        return false;
    });

    jQuery.datetimepicker.setLocale('ru');

    $('body').on('click', 'input.date_hm', function(){

        //$(this).datetimepicker('destroy');
        $(this).datetimepicker({
            formatDate: "d.m.Y",
            formatTime:'H:i',
            format: "d.m.Y H:i",
            timepickerScrollbar:false,
        }).datetimepicker( "show" );

        return false;
    });

    $('body').on('blur', ".currency", function(){
        currency_eval($(this));
    });

    //$(function () {
    //    $('[data-toggle="tooltip"]').tooltip();
    //});

    $('body').on('click', '.password-generator', function(){
        var newPwd = generatePass(10);
        $(this).closest('.input-group').find(".pwd").val(newPwd);
        return false;
    });

    $('body').on('click', '.password-toggle', function(){
        pwdToggle($(this));
        return false;
    });

    $('body').on('click', '.swich-disable', function(){
        swichDisable($(this));
    });

    $('body').on('click', '.submitModal', function(){
        $(this).closest('.modal-content').find('.modal-body form').submit();
    });

    $('body').on('click', '.tab-select', function(){

    });


    ajaxFormInit($('.ajaxform'));

});


