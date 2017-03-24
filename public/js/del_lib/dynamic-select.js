/**
*
*
$('#sel').dynamicSelect({
	
});
*/
(function($){
				
	jQuery.fn.dynamicSelect = function(method){			
		var defaultOptions = {
			
		}
		
		var methods = {
			init:function(params) {
				var that = $(this);
				var options = params;
				$(this).data('options', options);	
				return this;
			},
			add:function(id, label){
				var new_option = $('<option/>', {value: id, text: label});
				$(this).append(new_option);
				$(this).val(id);
				return this;
			},
			edit:function(id, label){
				var option = $(this).dynamicSelect('get', [id]);
				option.text(label);
				return this;
			},
			del:function(id){
				var option = $(this).dynamicSelect('get', [id]);
				option.remove();
				return this;
			},
			get:function(id){
				var options = $(this).data('options');
				var option = $(this).find("option[value='"+id+"']");
				return option;
			},
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