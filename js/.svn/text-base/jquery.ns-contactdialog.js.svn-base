(function (win, doc, $) {
	
	$.fn.nsContactDialog = function (options) {
		
		var contactForm, defaults, dialog, eL, opts, statusMessage;
		
		defaults = {
			
			'recaptcha_id' : 'recaptcha',
			'recaptcha_theme' : 'clean',
			'url' : '/'
			
		};
		
		opts = $.extend(defaults, options);
		
		dialog = $('<div />');
		
		dialog.dialog({
			
			autoOpen: false, 
			modal: true,
			width: 510
			
		});
		
		contactForm = $(opts.contact_form);
		
		$('input[type=submit]', contactForm).before($('<div />', {
			
			id : opts.recaptcha_id
			
		}));
		
		statusMessage = $('<div />').appendTo(contactForm);
		
		contactForm.validate();
		
		contactForm.bind('submit', function () {
			
			statusMessage.text(opts.working_message).addClass('status-message');
			
			if (contactForm.valid()) {
				
				$.getJSON(opts.url, contactForm.serialize() + '&nscontactdialog=' + opts.nscontactdialog + '&nscdaction=' + opts.nscdaction, function (data, textStatus) {
					
					statusMessage.text(data.message);
					
					if ('success' ===  data.response) {
						
						$(':input', contactForm).attr('readonly', 'readonly');
						
						$(':input:not([type=submit])', contactForm).addClass('submitted-success');
						
					} else {
						
						if ('recaptcha' === data.type) {
							
							Recaptcha.reload();
							$('#' + opts.recaptcha_id).focus();
							
						}
						
						if ('email' === data.type) {
							
							$('input[name=email_address]', contactForm).focus();
							
						}
						
					}
					
				});
				
			} else {
				
				statusMessage.text(opts.invalid_message);
				
			}
			
			return false;
			
		})
		
		contactForm.appendTo(dialog);
		
		// Get the Recaptcha script.
		$.getScript('http://www.google.com/recaptcha/api/js/recaptcha_ajax.js', function() {
				
			Recaptcha.create(opts.recaptcha_api_public_key, 

				opts.recaptcha_id, {

					theme: opts.recaptcha_theme,
		 			callback: Recaptcha.focus_response_field

			});
			
			dialog.bind('dialogclose', function (e, ui) {
			
				$(':input:not([type=submit])', contactForm).val('').removeAttr('readonly');
				$(':input:not([type=submit])', contactForm).removeClass('submitted-success');
				Recaptcha.reload();
				statusMessage.text('');
				statusMessage.removeClass('status-message');
				
			});
			
		});
		
		$('.' + opts.contact_link_class + ', .' + opts.contact_link_class + ' a').bind('click', function () {
			
			dialog.dialog('open');
			
			return false;
			
		});
		
		return this;
		
	};
	
}(this, document, jQuery, undefined));