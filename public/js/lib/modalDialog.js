(function($){
				
	jQuery.fn.modalDialog = function(){	
		var that = $(this);		
		var default_options = {
			'dialog'  : {
				show: false,
				keyboard: true,
				backdrop: false,
			},
			'is_ajax' : 0,
			'ajax':{
				url:'',
                cache: false,
				data:{},
				async: false,
				method: 'post',
				dataType:'html',
				dataFilter:function(res){
					return res;
				},
				success: function(res){
					//res = $('<div/>').html(res);
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
					if(!$(this).hasClass('modal')) $(this).addClass('modal');
					if(!$(this).hasClass('fade')) $(this).addClass('fade');
					if($(this).attr('tabindex') != -1) $(this).attr('tabindex', -1);
					if($(this).attr('role') != "dialog") $(this).attr('role', "dialog");
					
					var options = $(this).data('options');
					$(this).modal(options);
                    $(this).data('destroy', 1);
                    
                    $(this).on('hidden.bs.modal', function () {
                        if($(this).data('destroy'))
                        {
                            $(this).data('bs.modal', null);
                            $(this).removeData('bs.modal');
                            $(this).remove();
                        }
                    });
					//$(this).dialog(options.dialog);
				}
				return this;
			},
			
			instance: function(){
				return $(this);
			},
			isShow: function(){
				//return $(this).dialog( "isOpen" );
				return $(this).hasClass('in');
			},
			show: function(){
				if($(this).data('error')!=1)
				{
					$(this).modalDialog('hideAnother');
					$(this).modal('show');
				}
				return this;
			},
			showAnother : function(){
				//jQuery(".ui-dialog").not($(this)).find(".ui-dialog-content").dialog("open");
				$('.modal.in').not($(this)).modal('show');
			},
			hide: function(){
				$(this).data('destroy', 0);
                $(this).modal('hide');
				$(this).modalDialog('showAnother');
				return this;
			},
			hideAnother : function(){
				$('.modal.in').not($(this)).modal('hide');
				return this;
			},
			destroy: function(){
				$(this).data('destroy', 1);
                $(this).modal('hide');
                
                
              
				//$(this).modalDialog('showAnother');
				
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