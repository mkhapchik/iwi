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
	
	$(function () {
	  $('[data-toggle="tooltip"]').tooltip()
	});
	
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

	ajaxFormInit($('.ajaxform'));
	
});

function ajaxFormInit(formObj)
{
	formObj.form();
	
	formObj.on('form.beforeValidate', function(formData, jqForm, options){
		$(this).find("[type='submit']").loader({autostart:true, container_options:{'class': 'loader'}});
	});
	
	formObj.on('form.aftervalidate', function(validate_result, formData, jqForm, options){
		if(!validate_result) $(this).find("[type='submit']").loader('stop');
	});
	
	formObj.on('form.stop', function(){
		$(this).find("[type='submit']").loader('stop');
	});
}
	
function pwdToggle(btn)
{
	var pwd = btn.closest('.input-group').find("input[name='pwd']");
	var title;
	btn.tooltip('destroy');
	
	if(btn.hasClass('pwd-show'))
	{
		btn.removeClass('pwd-show');
		btn.addClass('pwd-hide');
		pwd.attr('type', 'text');
		btn.find('.glyphicon-eye-open').removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close');
		btn.attr('title', 'Скрыть пароль');
	}
	else
	{
		btn.removeClass('pwd-hide');
		btn.addClass('pwd-show');
		pwd.attr('type', 'password');
		btn.find('.glyphicon-eye-close').removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open');
		btn.attr('title', 'Показать пароль');
	}
	
	btn.tooltip();
}


function swichDisable(check_el)
{
	var el = check_el.closest('.input-group').find("input").not(check_el);
			
	if(check_el.is(':checked')) 
	{
		el.prop('disabled', false);
	}
	else 
	{
		el.prop('disabled', true);
		el.val('');
	}
}

function setSelectStyle(sel)
{
	if(sel.val().length==0) sel.addClass('empty');
	else sel.removeClass('empty');
}

function currency_eval(currency)
{
	var val, dot_index, val_length;
	val = currency.val();
	if(val)
	{
		
		val = val.replace(/[,]+/g,'.').replace(/[^0-9\.+-/*()]/g,'0');
		dot_index = val.indexOf('.');
		
		if(dot_index==0) val = 0+val;
			
		val = eval(val)+'';
		dot_index = val.indexOf('.');
		val_length = val.length;
		if((dot_index+1)==0) val+='.00';
		else if((val_length-dot_index-1)==0) val+='00';
		else if((val_length-dot_index-1)==1) val+='0';
		
	}
	
	currency.val(val);
}

function makeRand(max){
	return Math.floor(Math.random() * max);
}

function generatePass(length)
{
	var result = '';
  
	var symbols = new Array(
		'q','w','e','r','t','y','u','i','o','p',
		'a','s','d','f','g','h','j','k','l',
		'z','x','c','v','b','n','m',
		'Q','W','E','R','T','Y','U','I','O','P',
		'A','S','D','F','G','H','J','K','L',
		'Z','X','C','V','B','N','M',
		1,2,3,4,5,6,7,8,9,0
	);
	
	for (i = 0; i < length; i++)
	{
		result += symbols[makeRand(symbols.length)];
	}
	
	return result;
}

function refresh_captcha(captcha_cont, url)
{
	$.ajax({
		method:'post',
		url:url,
		dataType:'json',
		success: function(res){
			if(res.captcha_id!=false && res.refresh_captcha_access!=0)
			{
				captcha_cont.find('img').attr('src', res.captcha_src);
				captcha_cont.find("input[name='captcha[id]']").val(res.captcha_id);
			}
			else
			{
				captcha_cont.find('img').unbind('click');
				captcha_cont.find('a.refresh_captcha').remove();
			}
		}
	});
}

	

