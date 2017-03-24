(function($){
	var options = {
		action_url : '/filemanager', // контроллер
		nav : ['back', 'update', 'mkdir', 'upload', 'select_all', 'unselect_all', 'rename', 'delete'], // перечень доступной навигации
		choosen  : 0, // режим работы файлового менеджера для выбора файла или директории
		choosen_callback : function(path, item){ // callback функция при выборе файла в соответствующем режиме
			window.open('/'+path);
		},
		fileclick_callback : function(path, item){ // callback функция при клике файла
			window.open('/'+path);
		},
		path : 'files' // корневая директория false по умолчанию
	};

	//var multiple_objects_actions = ['delete'];
	var single_object_actions = ['rename'];
	
	var getParentItem = function(el){
		return el.closest('.item');
	}
	
	jQuery.fn.filemanager = function(method, params){
		var that = this;
				
		var initHTML = function(html){
			var div = $('<div/>', {html:html, 'class':'filemanager'});
			var link = div.find('div.link');
			var checkbox = div.find('.item .select input:checkbox');
			
			that.currentDir = div.find('#current_dir').val();
				
			link.click(function(){
				var item = getParentItem($(this));
				if(!item.hasClass('renaming'))
				{
					//var path = $(this).attr('href');
					var path = $(this).next('.select').find('input:checkbox').val();
					
					if(item.hasClass('dir'))
					{
						methods.list.call(that, path);	
					}
					else
					{
						if(typeof(options.fileclick_callback)=='function') options.fileclick_callback.call(that, path, item);
					}
				}
			});
			
			checkbox.change(function(event){
				var item = getParentItem($(this));
				if($(this).is(':checked')) 
				{
					item.addClass('selected');
					if(options.choosen)
					{
						var other_els = checkbox.not($(this));
						other_els.prop('checked', false);
						other_els.trigger("change");
						
						if(typeof(options.choosen_callback)=='function') options.choosen_callback.call(that, $(this).val(), item);
					}	
				}
				else 
				{
					item.removeClass('selected');
				}
				
				var check_length = checkbox.filter(':checked').length;
					
				for(var i=0; i<single_object_actions.length; i++)
				{
					var n = that.find('.nav').find('.'+single_object_actions[i]);
					
					if(check_length > 1) n.prop('disabled', true);
					else n.prop('disabled', false);
				}
			});
			
			div.find('.bread_crumbs	a').click(function(){
				methods.list.call(that, $(this).attr('href'));
				return false;
			});
			
			var div_nav = $('<div/>',{'class':'nav'});
			var nav_l = options.nav.length;
			for(var i=0; i<nav_l; i++)
			{
				var f = nav[options.nav[i]];
				var el = f();
				div_nav.append(el);
			}
			div.prepend(div_nav);
			
			
			$(this).html(div)
		}
		
		var send = function(url_name, data, success, ajax_options){
			var url_list = {
				list : '/list',
				upload:'/upload',
				'delete':'/delete',
				rename:'/rename',
				mkdir:'/mkdir'
			}
					
			var def_options = {
				url : options.action_url + url_list[url_name],
				method : 'post',
				data : data,
				dataType : 'html',
				success : function(html){
					if(typeof(success)=='function') success.call( that, html);
				}
			}
			
			var ajax_options = $.extend({}, def_options, ajax_options);
			
			$.ajax(ajax_options);
		}
		
		var nav = {
			back: function(){
				var b = $('<button/>',{html:'back', 'class':'back' });
			
				if(that.currentDir.indexOf('/')>0)
				{
					b.click(function(){
						methods.back.call(that);
					});
				}
				else 
				{
					b.prop('disabled', true);
					b.addClass('disabled');
				}
				
				return b;
			},
			upload: function(){
				var input = $('<input/>',{name:'uploadfile[]', type:'file', multiple:'true'})
				var b = $('<button/>',{html:'upload', 'class':'upload'});		
				b.click(function(){input.click()});	
				input.change(function(){
					if(this.files.length>0) methods.upload.call(that, this.files);
				});
				return b;
			},
			mkdir: function(){
				var b = $('<button/>',{html:'mkdir', 'class':'mkdir'});		
				b.click(function(){
					methods.mkdir.call(that, that.currentDir);
				});	
				
				return b;
			},
			update:function(){
				var b = $('<button/>',{html:'update', 'class':'update'});		
				b.click(function(){
					methods.list.call(that, that.currentDir);
				});
				return b;
			},
			select_all:function(){
				var b = $('<button/>',{html:'select_all', 'class':'select_all'});		
				b.click(function(){
					$(that).find('.select input').prop('checked', true);
				});
				return b;
			},
			unselect_all:function(){
				var b = $('<button/>',{html:'unselect_all', 'class':'unselect_all'});		
				b.click(function(){
					$(that).find('.select input').prop('checked', false);
				});
				return b;
			},
			'delete' : function(){
				var b = $('<button/>',{html:'delete', 'class':'delete'});		
				b.click(function(){
					files = [];
					$(that).find('.select input:checked').each(function(){
						files.push($(this).val());
					});
					methods['delete'].call(that, files);
				});
				return b;
			},
			rename : function(){
				var b = $('<button/>',{html:'rename', 'class':'rename'});		
				b.click(function(){
					var c = $(that).find('.select input:checked');
					if(c.length==1)
					{
						var item = getParentItem(c);
						item.addClass('renaming');
						var name = item.find('.name');
						var input = $('<input/>',{'value':name.text()});
						
						input.focusout(function(){
							
							var newName = $(this).val();
							$(this).after(newName);	
							$(this).remove();
							item.removeClass('renaming');
							methods['rename'].call(that, c.val(), that.currentDir+'/'+newName);
						});
						
						name.html(input);
						input.focus();
					}
				});
				return b;
			}
		}

		var methods = {
			init:function(params) {
				options = $.extend({}, options, params);
				methods.list.call(that, options.path);
			},
			list:function(path){
				path = path || '';
				send('list', {path:path}, function(html){initHTML.call( that, html);});
			},
			back:function(){
				var path = that.currentDir+'/../';
				methods.list.call(that, path);
			},
			update:function(){
				methods.list.call(that, that.currentDir);
			},
			upload:function(files){
				var data = new FormData();
				data.append( 'path', that.currentDir );
				$.each( files, function( key, value ){
					data.append( key, value );
				});

				send('upload', data, function(html){methods['update'].call(that)}, {processData: false, contentType: false});
			},
			'delete':function(files){
				var data = new FormData();
				
				$.each( files, function( key, value ){
					data.append( 'files[]', value );
				});
				
				send('delete', data, function(html){methods['update'].call(that);}, {processData: false, contentType: false});
			},
			rename : function(old_path, new_path){
				send('rename', {old_path : old_path, new_path : new_path}, function(html){methods['update'].call(that);});
			},
			mkdir: function(path)
			{
				send('mkdir', {path : path}, function(html){methods['update'].call(that);});
			}
			
		};
		
		if ( methods[method] ) 
		{
			return methods[ method ].call( this, params);
		} 
		else if ( typeof method === 'object' || ! method ) 
		{
			return methods.init.call( this, method, params);
		}
		//return this.each(methods.init.apply( this, params )); 
	};
})(jQuery);