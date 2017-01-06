$(document).ready(function(){
	$("input[type='submit'], button, a.button").button();
	$( 'a' ).tooltip({});
	
	$('body').on('click', 'input.date', function(){
		
		$(this).datepicker('destroy');
		$(this).datepicker({dateFormat: "dd.mm.yy"}).datepicker( "show" ).datepicker( "setDate", $(this).val());
		
		return false;
	});
	
	$('body').on('change', 'select', function(){
		setSelectStyle($(this))
	});
	
	$('select').each(function(){
		setSelectStyle($(this));
	});
	
	$('body').on('blur', ".currency", function(){
		currency_eval($(this));
	});
});

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
