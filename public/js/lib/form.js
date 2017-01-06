/*
* Плагин использует http://malsup.com/jquery/form/ 
*
* Options
* 	Все опции jQuery Form Plugin
*	Все опции jQuery Ajax
*	validation_rules
*	validation_messages
*
* API
* 	init
* 	validate
* 	submit
* 	setMessage
* 	setElementsMsg
* 	getElementByName
* 	setElementMsg
* 	clear
* 	decoratorMsg
* 	redirect
* 	setOption
* 	getOption
*
* Events
*	form.start - старт отправки формы
*	form.beforeValidate - событие до валидации на js
*	form.aftervalidate - событие после валидации на js
*	form.beforeSubmit - событие перед отправкой формы на сервер
*	form.uploadProgress - событие прогресса загрузки
*	form.success - событие в случае успешной отправки формы
*	form.error - событие в случае неуспешной отправки формы
*	form.ajaxError - событие ошибки ajax запроса
*   form.complete - событие завершения ajax запроса
*   form.stop - событие завершения отправки формы (ответ от сервера или непрохождение валидации)
*/

(function($){
	jQuery.fn.form = function(method){	
		var that = $(this);		
		
		var SuccessClass = 'success';
		var ErrorClass = 'error';
		
		var default_options = {
			dataType: 'json',
			url : $(this).attr('action'),
			beforeSubmit: function(formData, jqForm, options){
				that.trigger('form.start', [formData, jqForm, options]);
				that.trigger('form.beforeValidate', [formData, jqForm, options]);
				if(!that.form('validate', formData, jqForm, options))
				{
					that.trigger('form.stop');
					return false;
				}
				else return true;
			},
			uploadProgress: function(event, position, total, percentComplete) {
				that.trigger('form.uploadProgress', [event, position, total, percentComplete]);
			},
			success: function(res){
				if(res.is_success) that.trigger('form.success', [res.message, res]);
				else that.trigger('form.error', [res.message, res]);
			},
			error: function(jqXHR, exception){
				var msg = '';
				if (jqXHR.status === 0) msg = 'Not connect.\n Verify Network.';
				else if (jqXHR.status == 404) msg = 'Requested page not found. [404]';
				else if (jqXHR.status == 500) msg = 'Internal Server Error [500].';
				else if (exception === 'parsererror') msg = 'Requested parse failed:<br>' + jqXHR.responseText;
				else if (exception === 'timeout') msg = 'Time out error.';
				else if (exception === 'abort') msg = 'Ajax request aborted.';
				else msg = 'Uncaught Error.\n' + jqXHR.responseText;    
				
				that.trigger('form.ajaxError', [msg, jqXHR, exception]);
			},
			complete: function(XMLHttpRequest, textStatus){
				that.trigger('form.complete', [XMLHttpRequest, textStatus]);
				that.trigger('form.stop');
			},
			validation_rules : {
				'required' : function(v){ return v!='' },
				'email' : function(v){ return v.match(/.+@.+\..+/); },
			},
			validation_messages : {
				'required' : "Это поле обязательно для заполнения",
				'email' : "Неверный формат email адреса",
			},
			
		}
		
		var methods = {
			version : function(){ return '3.0'; },
			init:function(params) {
				params = params || {}
				var that = $(this);
				var options = $.extend({}, default_options, params);
				that.data('options', options);	
					
				that.submit(function() {
					that.form('submit');
					return false;
				});
					
				that.on('form.ajaxError', function(event, msg){
					that.form('setMessage', msg, 0);
				});
					
				that.on('form.error', function(event, msg, res){
					that.form('setMessage', msg, 0);
					that.form('setElementsMsg', res.form_messages, 0);
					that.form('redirect', res);
				});
				
				that.on('form.success', function(event, msg, res){
					that.form('setMessage', msg, 1);
					that.form('redirect', res);
				});	

				that.on('form.beforeSubmit', function(){
					that.form('clear');
				});
				
				that.on('form.start', function(){
					that.find("[type='submit']").prop('disabled', 1);
				});	
				
				that.on('form.stop', function(){
					that.find("[type='submit']").prop('disabled', 0);
				});	
				
				return this;
			},
			validate: function(formData, jqForm, options){
				var that = $(this);
				var options = $(this).data('options');
				var validate_result=true;
			
				var l = formData.length;
				
				for (var i=0; i < l; i++) 
				{ 
					var el = $(jqForm[0][i]);
											
					var attr_class = el.attr('class');
					
					if(typeof(attr_class)=='undefined' || attr_class=='') continue;
					
					var classList = el.attr('class').split(/\s+/);
					
					$.each(classList, function(index, item) {
						if(item=='') return true;
						
						var f_v = options.validation_rules[item];

						if(typeof(f_v)=='function' && !f_v(el.val()))
						{
							validate_result = false;
						
							if(typeof(options.validation_messages[item])!='undefined')
							{
								that.form('setElementMsg', el, options.validation_messages[item], 0);
								return false;
							}
						}
					});
				}

				$(this).trigger('form.aftervalidate', [validate_result, formData, jqForm, options]);
				
				return validate_result;
			},
			submit: function(){
				var options = $(this).data('options');
				$(this).trigger('form.beforeSubmit');
				$(this).ajaxSubmit(options); 
				return this;
			},
			setMessage: function(msg, is_success){
				var d = that.form('decoratorMsg', msg, is_success);
				if(msg) $(this).before(d);
				return this;
			},
			setElementsMsg: function(messages, is_success, start_element_name){
				start_element_name = start_element_name || false;
				
				for(var name in messages)
				{
					var element_name = start_element_name===false ? name : start_element_name+"["+name+"]";
					for(var error_name in messages[name])
					{
						if(typeof(messages[name][error_name])=='object')
						{
							$(this).form('setElementsMsg', messages[name], is_success, element_name);
						}
						else
						{
							var el = that.form('getElementByName', element_name);
							$(this).form('setElementMsg', el, messages[name][error_name], is_success);
						}
					}
				}
			},
			getElementByName : function(element_name){
				var el = $(this).find("[name='"+element_name+"']");
				
				if(el.length==0)
				{
					el = $(this).find("[name^='"+element_name+"']").last();
				}
				return el;
			},
			setElementMsg : function(el, msg, is_success){
				if(msg!='')
                {
                    var msgCont = el.next('.help-block');
                    if(msgCont.length==0) 
                    {
                        msgCont = $('<div/>',{'class':'help-block'});
                        el.after(msgCont);
                    }
                    
                    var m = $('<span/>',{'class':is_success?SuccessClass:ErrorClass, html:msg});
                    msgCont.append(m);
				}
				//el.addClass(is_success?SuccessClass:ErrorClass);
				el.closest('.form-group').addClass(is_success? 'has-success': 'has-error');
				
			},
			clear : function(){
				$(this).prev('.message').remove();
				$(this).find('.help-block').remove();
				$(this).find('.has-error').removeClass('has-error');
				$(this).find('.has-success').removeClass('has-success');
			},
			decoratorMsg: function(msg, is_success){
				var d = $('<div/>',{'class':'message',html:$('<span/>',{'class':is_success? 'bg-success' : 'bg-danger', html:msg})})
				return d;
			},
			redirect: function(res){
				if(typeof(res.redirect)!='undefined')
				{
					window.location = res.redirect;
				}
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
            test: function()
            {
                $(this).css({'border':'1px solid #f00'});
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