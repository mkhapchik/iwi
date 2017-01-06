/**
* Показывает диалог c кнопками
* @param dialogText - html код
* @param okFunc - функция, при нажатии на кн. сохранить
* @param cancelFunc - функция, при нажатии на кн. отмена
* @param options - опции диалога
* @param is_confirm - флаг, 1 - confirm, 0 - alert
* @param button_name - массив списка имен

function modalDialog(dialogText, okFunc, cancelFunc, options, is_confirm, button_name) {
	
	if(typeof(is_confirm)=='undefined') is_confirm=1;
	
	if(typeof(button_name)!='object')
	{
		if(is_confirm)
		{
			button_name = ["Сохранить", "Отменить"];
		}
		else
		{
			button_name = ["OK"];
		}
	}
	
	
	jQuery(".constructor_dialog .ui-dialog-content").dialog("close");
	options = options || {}
	
	
	
	var h = jQuery(window).height() * 0.9;
	
	default_options = {
		draggable: false,
		modal: true,
		resizable: false,
		//width : '800px',
		//height: h,
		width : 'auto',
		height: 'auto',
		maxHeight: h,
		
		resizableBtn: false,
		closeOnEscape: false,
		create: function( event, ui ) {
			var d = jQuery(this);
			var closeBtn = d.parents(".constructor_dialog:first").find(".ui-dialog-titlebar-close");
			closeBtn.unbind('click');
			closeBtn.click(function(){
				d.dialog('destroy');
				modalDialogOpen();
			});
		},
		dialogClass : 'constructor_dialog'
	};
	
	if(is_confirm)
	{
		default_options.buttons = {}
		default_options.buttons[button_name[0]] = function () {
			if (typeof (okFunc) == 'function') 
			{
				result = okFunc(jQuery(this));
				if(result) 
				{
					jQuery(this).dialog('destroy');
					modalDialogOpen();
				}
			}
			else
			{
				jQuery(this).dialog('destroy');
				modalDialogOpen();
			}
		};
		
		default_options.buttons[button_name[1]] = function () {
			if (typeof (cancelFunc) == 'function') 
			{
				result = cancelFunc(jQuery(this));
				if(result) jQuery(this).dialog('destroy');
			}
			else	
			{		
				jQuery(this).dialog('destroy');
			}
			modalDialogOpen();
		}
		
	}
	else
	{
		default_options.buttons = {}
		default_options.buttons[button_name[0]] = function () 
		{
			if (typeof (okFunc) == 'function') {
				
				result = okFunc(jQuery(this));
				if(result) jQuery(this).dialog('destroy');
			}
			else	
			{		
				jQuery(this).dialog('destroy');
			}
			modalDialogOpen();
		}

	}
	
	dialogOptions = jQuery.extend( {}, default_options, options );
	
	jQuery('<div/>',{
		style : "padding: 10px;",
		html : dialogText,
		'class': 'confirm_body'
	}).dialog(dialogOptions);
	
}
*/
/**
* Открывает скрытые диалоги

function modalDialogOpen()
{
	jQuery(".constructor_dialog .ui-dialog-content").dialog("open");
}
*/

(function($){
				
	jQuery.fn.modalDialog = function(){	
		var that = $(this);		
		var default_options = {
			'dialog'  : {
				autoOpen: false,
				modal:true,
				create: function() {
					var d = $(this);
					$(this).closest('div.ui-dialog')
						.find('.ui-dialog-titlebar-close')
						.click(function(e) {
							d.modalDialog('destroy');
							return false;
							//e.preventDefault();
						});
				},
				draggable: false,
				resizable: false,
				resizableBtn: false,
				closeOnEscape: false,
				width:800,
				title: null
			},
			'is_ajax' : 0,
			'ajax':{
				url:'',
				data:{},
				async: false,
				method: 'post',
				dataType:'html',
				dataFilter:function(res){
					return res;
				},
				success: function(res){
					res = $('<div/>').html(res);
					that.html(res);
				},
				error: function (jqXHR, exception){
					
					var msg = '';
					if (jqXHR.status === 0) msg = 'Not connect.\n Verify Network.';
					else if (jqXHR.status == 404) msg = 'Requested page not found. [404]';
					else if (jqXHR.status == 500) msg = 'Internal Server Error [500].';
					else if (exception === 'parsererror') msg = 'Requested parse failed:<br>' + jqXHR.responseText;
					else if (exception === 'timeout') msg = 'Time out error.';
					else if (exception === 'abort') msg = 'Ajax request aborted.';
					else msg = 'Uncaught Error.\n' + jqXHR.responseText;    
					
					$('<div/>', {html:msg}).modalDialog({is_ajax:0}).modalDialog('show');
					that.data('error', 1);
				}
			}
		}
		
		var getRemotely = function(){
			var options = $(this).data('options');
			var ajaxopt = $.extend({}, default_options.ajax, options.ajax);
			
			$.ajax(ajaxopt);
		}
		
		var methods = {
			init:function(params) {
				params = params || {}
				var that = $(this);
				var options = $.extend({}, default_options, params);
				$(this).data('options', options);	
				
				if(options.is_ajax==1)
				{
					getRemotely.call(this);
				}
				
				$(this).modalDialog('create');
				
				return this;
			},
			create: function(obj){
				if($(this).data('error')!=1)
				{
					var options = $(this).data('options');
					$(this).dialog(options.dialog);
				}
				return this;
			},
			
			instance: function(){
				return $(this);
			},
			isShow: function(){
				return $(this).dialog( "isOpen" );
			},
			show: function(){
				if($(this).data('error')!=1)
				{
					$(this).modalDialog('hideAnother');
					$(this).dialog( "open" );
				}
				return this;
			},
			showAnother : function(){
				jQuery(".ui-dialog").not($(this)).find(".ui-dialog-content").dialog("open");
			},
			hide: function(){
				$(this).dialog( "close" );
				$(this).modalDialog('showAnother');
				return this;
			},
			hideAnother : function(){
				jQuery(".ui-dialog").not($(this)).find(".ui-dialog-content").dialog("close");
			},
			destroy: function(){
				$(this).dialog( "destroy" );
				$(this).modalDialog('showAnother');
				return this;
			},
			setOption: function(name, value){
				$(this).dialog( "option", name, value);
				return this;
			},
			getOption: function(name){
				return $(this).dialog( "option", name);
			}
		};

		var args = [], result;
		for (var i = 0; i < arguments.length; i++) args[i] = arguments[i];		
		
		switch(typeof(args[0]))
		{
			case 'object': result = methods.init.call( this, args[0] ); break;
			case 'undefined': result = methods.init.call( this, {} ); break;
			case 'string':
				if(methods[args[0]])
				{
					if(args.length>1) args = args.slice(1);
					result = methods[ arguments[0] ].apply(this, args);
				}
				break;	
		}
		
		return result;
	};
})(jQuery);