(function( $ ) {
	'use strict';

	/**
	 * Form validation handlers.
	 */

	$(function() {

		$("#wpbf_submission_form")
			.on('submit', function(e){
				e.preventDefault();
				console.log('Validate form here..')

				});
	});

	/**
	 * Form submission handler.
	 */

	$(function() {

		$("#wpbf_submission_form")
			.on('submit', function(e){
				e.preventDefault();

				var form_id = $('#wpbf_form_id').val();

				// sort input data into arrays
				var $inputs = $('#wpbf_submission_form :input');
				var fields = {};
				var metadata = {};
				$inputs.each(function() {
					if ( $(this).attr('class') === 'wpbf_data_field' ) {
						fields[this.name] = $(this).val();
					} else {
						metadata[this.name] = $(this).val();
					}
				});
				var data = {
					form_id: form_id,
					data: fields,
					metadata: metadata
				};
				console.log(data);

				$.ajax({
					type:"POST",
					url: wpbf_rest_api.rest_url + 'wpbf/v1/submissions/add',
					data: data,
					beforeSend: function (xhr) {
						xhr.setRequestHeader( 'X-WP-Nonce',wpbf_rest_api.rest_nonce)
					},
					success: function(response){
						console.log('Response:', response);
						$(".success_msg").css("display","block");
					},
					error: function(response){
						console.warn('Error:', response);
						$(".error_msg")
							.innerHTML(response.responseText)
							.css("display","block");
					}
				});
				$('#wpbf_submission_form')[0].reset();
			});
	});



})( jQuery );
