/**
<div data-toggle="helper" data-index="1" data-function="func1" data-function-after="func2" data-associated="#id" data-delay="5000">
	подсказка
</div>
*/
(function($){ 
	jQuery.fn.helper = function(method){	
		var that = $(this);		
		
		var default_options = {
					
		}
		
		var methods = {
			version : function(){ return '1.0'; },
			init:function(params) {
				params = params || {}
				var that = $(this);
				var options = $.extend({}, default_options, params);
					
				
				var overlay = $('<div/>', {'class':'help-overlay'}).hide();
				$('body').prepend(overlay);
				
				var helps = $(this).find("[data-toggle='helper']");
				
				helps.sort(function(a, b){
					var distA = parseInt(jQuery(a).attr('data-index'));
					var distB = parseInt(jQuery(b).attr('data-index'));
					return (distA < distB) ? -1 : (distA > distB) ? 1 : 0;
				});
				
				that.data('helps', helps);
				that.data('overlay', overlay);
				
				return this;
			},
			setOption: function(name, value)
			{
				var options = $(this).data('options');	
				options[name] = value;
				$(this).data('options', options);	
			}, 
			getOption: function(name)
			{
				var options = $(this).data('options');	
				return options[name];
			},
			start: function()
			{
				
				methods.showOverlay.call(this, {});
				methods.next.call(this, 0);
				
				//.call( this, {} );
			},
			next : function(index){
				clearTimeout($(this).data('timerId'));
				var helps = that.data('helps')
			
				if(index>=helps.length) 
				{
					methods.hideOverlay.call(this, {});
					return this;
				}
				
				var help = helps.eq(index);
				
				var delay = help.attr('data-delay') || 2000;
				var associated = help.attr('data-associated') || null;
				var func = 	help.attr('data-function') || null;	
				var func_after = help.attr('data-function-after') || null;	
				
				if(associated) 
				{
					that.find(associated).addClass('helper_focus');
				}
				
				help.show();
				if(func && typeof(window[func])=='function') window[func].call(this, help, $(associated));
				
				var timerId = window.setTimeout(function(){
					if(func_after && typeof(window[func_after])=='function') window[func_after].call(this, help, $(associated));
					
					help.hide();
					that.find('.helper_focus').removeClass('helper_focus');
								
					methods.next.call(that, ++index);
				}, delay);
				
				$(this).data('timerId', timerId);
			},
			stop: function()
			{
				clearTimeout($(this).data('timerId'));
				that.find('.helper_focus').removeClass('helper_focus');
				methods.destroy.call( this, {} );
			},
			showOverlay: function()
			{
				if(typeof(that.data('overlay')!='undefined')) that.data('overlay').show();
			},
			hideOverlay: function()
			{
				if(typeof(that.data('overlay')!='undefined')) that.data('overlay').hide();
			},
			destroy : function(){
				if(typeof(that.data('overlay')!='undefined')) that.data('overlay').remove();
				
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