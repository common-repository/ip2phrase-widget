jQuery(document).ready(function($) {
	$('#ip2phrase-widget-feedback-modal').dialog({
		title: 'Quick Feedback',
		dialogClass: 'wp-dialog',
		autoOpen: false,
		draggable: false,
		width: 'auto',
		modal: true,
		resizable: false,
		closeOnEscape: false,
		position: {
			my: 'center',
			at: 'center',
			of: window
		},
				
		open: function() {
			$('.ui-widget-overlay').bind('click', function() {
				$('#ip2phrase-widget-feedback-modal').dialog('close');
			});
		},
			
		create: function() {
			$('.ui-dialog-titlebar-close').addClass('ui-button');
		},
	});

	$('.deactivate a').each(function(i, ele) {
		if ($(ele).attr('href').indexOf('ip2phrase-widget') > -1) {
			$('#ip2phrase-widget-feedback-modal').find('a').attr('href', $(ele).attr('href'));

			$(ele).on('click', function(e) {
				e.preventDefault();

				$('#ip2phrase-widget-feedback-response').html('');
				$('#ip2phrase-widget-feedback-modal').dialog('open');
			});

			$('input[name="ip2phrase-widget-feedback"]').on('change', function(e) {
				if($(this).val() == 4) {
					$('#ip2phrase-widget-feedback-other').show();
				} else {
					$('#ip2phrase-widget-feedback-other').hide();
				}
			});

			$('#ip2phrase-widget-submit-feedback-button').on('click', function(e) {
				e.preventDefault();

				$('#ip2phrase-widget-feedback-response').html('');

				if (!$('input[name="ip2phrase-widget-feedback"]:checked').length) {
					$('#ip2phrase-widget-feedback-response').html('<div style="color:#cc0033;font-weight:800">Please select your feedback.</div>');
				} else {
					$(this).val('Loading...');
					$.post(ajaxurl, {
						action: 'ip2phrase_widget_submit_feedback',
						feedback: $('input[name="ip2phrase-widget-feedback"]:checked').val(),
						others: $('#ip2phrase-widget-feedback-other').val(),
					}, function(response) {
						window.location = $(ele).attr('href');
					}).always(function() {
						window.location = $(ele).attr('href');
					});
				}
			});
		}
	});
});