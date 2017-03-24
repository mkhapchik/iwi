(function($){
				
	jQuery.fn.loader = function(method){
		var methods = {
			init:function(options) {
				options = typeof options === 'object' ? options : {}
				var def_opt = {
					autostart : false,
					'container_options':{
						'class': 'loader',
						html: $('<span/>', {
							'text' : 'Загрузка',
							'class' : 'inside'
						}),
						css: {
							width: $(this).width(),
							height: $(this).height(),
							position: 'absolute',
							top: $(this).offset().top,
							left: $(this).offset().left,
							background: 'rgba(255,255,255,0.8)',
							
						}
					}
				}
				options = $.extend({}, def_opt, options);
				
				$(this).data('options', options);
				
				var div = $('<div/>', options.container_options);
				
				$(this).data('container', div);
				
				if(options.autostart){
					$(this).loader('start');
				}
				
				return this;
			},
			start:function(){				
				var container = $(this).data('container');
				container.remove();
				$(this).after(container);
			},
			stop: function(){
				$(this).data('container').remove();
			}
			
			
		};
		
		if ( methods[method] ) 
		{
			return methods[ method ].apply(this);
		} 
		else if ( typeof method === 'object' || ! method ) 
		{
			return methods.init.call( this, method );
			//return this.each(methods.init.call( this, method ));
		}
		//return this.each(methods.init.apply( this, params )); 
	};
})(jQuery);