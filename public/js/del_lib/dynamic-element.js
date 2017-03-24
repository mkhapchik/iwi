/**
*
*
$('#sel').dynamicElement({
	template_block : template_block_object,
	template_placeholder: "__index__",
	target_block : target_block_object,
	selection:{
		select_control: $("select[name='select_roles']"),
		map:{
			v: '.id',
			opt: '.label'
		}
	},
	buttons: {add:'Добавить', del:'Удалить'},
});
*/
(function($){
				
	jQuery.fn.dynamicElement = function(method, arguments){
		var setDelButton = function(el, index){
			var that = $(this);
			var options = that.data('options');
			el.append($("<button/>", {
				html: options.buttons.del,
				click: function() {
					that.dynamicElement('del', [index]);
					return false;
				},
				'class':'button del',
			}));
		}
		
		var setAddButton = function(){
			var that = $(this);
			var options = that.data('options');

			$(options.nav_block).append($("<button/>", {
				html: options.buttons.add,
				click: function() {
					that.dynamicElement('add');
					return false;
				},
				'class': 'button add',
			}));
		}
		
		var methods = {
			init:function(params) {
				var that = $(this);
			
				var options = params;
				
				if(typeof(options.template_placeholder)=='undefined') options.template_placeholder =  "__INDEX__";
								
				if(typeof(options.nav_block)=='undefined') 
				{
					options.nav_block = $('<div/>', {'class' : 'nav',}) ;
					$(this).prepend(options.nav_block);
				}
				
				$(this).data('options', options);
				
				if(typeof(options.buttons.add) !='undefined') setAddButton.call($(this));
				
				options.target_block.each(function(index){
					var name = $(this).find('input').eq(0).attr('name');
					var i = methods.getIndexByName.call(that, name);
					$(this).attr('data-index', i);
					if(typeof(options.buttons.del) !='undefined') setDelButton.call(that, $(this), i);
				});
				
				if(typeof(options.selection.select_control)!='undefined')
				{
                    options.selection.select_control.change(function(){
						var v = $(this).val();
						if(v)
						{
                            var opt = $(this).find('option:selected');
							methods.selection.call(that, v, opt.text());
							opt.remove();
						}
						return false;
						
					});
				}
				
				return this;
			},
			getIndexByName:function(name)
			{
				var options = $(this).data('options');
				var tpl_name = $(options.template_block).find('input').attr('name');
				var start_i = tpl_name.indexOf(options.template_placeholder, 0);
				var end_i = name.indexOf(']', start_i);
				var i = name.substr(start_i, end_i-start_i);
				
				return i;
			},
			getIndex:function(){
				var options = $(this).data('options');
				
				var block = $(this).find('[data-index]');
				var max = block.length;
				var that = this;
				
				block.each(function(index){
					var i = parseInt($(this).attr('data-index'));
					if(i>max) max=i;
				});
				
				max++;
				
				return max;
			},
			add:function(){
				var options = $(this).data('options');
				var new_el = options.template_block.clone(true, true);
				var index = methods.getIndex.apply( this );
				
				var html = new_el.html().split(options.template_placeholder).join(index);
				
				new_el.html(html);
				new_el.attr('data-index', index);
				
				var last = $(this).find('[data-index]').last();
				
				if(typeof(last) === 'object' && last.length>0)
				{
					last.after(new_el);
				}
				else
				{
					$(this).append(new_el);
				}
				
				if(typeof(options.buttons.del) !='undefined') setDelButton.call($(this), new_el, index);
				
				$(this).trigger('dynamic-element.added', [new_el]);
				
				return new_el;
			},
			selection:function(v, opt){
				var options = $(this).data('options');
				var new_el = $(this).dynamicElement('add');
				
				//el[0]
               
				var v_element = new_el.find(options.selection.map.v);
				var opt_element = new_el.find(options.selection.map.opt);
				   
                //if(v_element.attr('value')!== undefined) v_element.val(v);
				//else v_element.html(v);
                v_element.each(function(){
                    if($(this).attr('value')!== undefined) $(this).val(v);
                    else $(this).html(v);  
                });
				
				//if(opt_element.attr('value')!== undefined) opt_element.val(opt);
				//else opt_element.html(opt);
                opt_element.each(function(){
                    if($(this).attr('value')!== undefined) $(this).val(opt);
                    else $(this).html(opt);  
                });
				
				
			},
			del: function(i){
				var options = $(this).data('options');
				var p = $(this).find('[data-index='+i+']');
				if(typeof(options.selection.select_control)!='undefined')
				{
					var v_element = p.find(options.selection.map.v);
					var opt_element = p.find(options.selection.map.opt);
					
					var v = v_element.attr('value')!== undefined ? v_element.val() : v_element.text();
					var opt = opt_element.attr('value')!== undefined ? opt_element.val() : opt_element.text();
					
					var option = $('<option/>', {
						text: opt,
						value: v
					});
					options.selection.select_control.append(option);
				}
				p.remove();
			}
		};
		
		
		
		arguments = arguments || [];
		if ( methods[method] ) 
		{
			return methods[ method ].apply(this, arguments);
		} 
		else if ( typeof method === 'object' || ! method ) 
		{
			return methods.init.call( this, method );
			//return this.each(methods.init.call( this, method ));
		}
		//return this.each(methods.init.apply( this, params )); 
	};
})(jQuery);