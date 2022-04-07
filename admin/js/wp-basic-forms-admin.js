(function( $ ) {
	'use strict';

	/**
	 * Validate JSON
	 */
	const validate_json = (str) => {
		try{
			JSON.parse(str);
		}catch (e){
			return false;
		}
		return true;
	}

	/**
	 * Clear messages
	 */
	const clear_messages = () => {
		$(".error_msg")
			.text("")
			.css("display","none");
		$(".success_msg")
			.text("")
			.css("display","none");
	}



	/**
	 * Show existing forms
	 */
	const show_forms = () => {
		$.ajax({
			type:"GET",
			url: wpbf_rest_api.rest_url + 'wpbf/v1/forms/view/all',
			beforeSend: function (xhr) {
				xhr.setRequestHeader( 'X-WP-Nonce',wpbf_rest_api.rest_nonce)
			},
			success: function(response){
				console.log('Available Forms:', response);

				$(function() {
					var $table = $('<table>');
					$table.addClass('ws_data_table');

					$('<tr>').append(
						$('<th>').text(response.schema.form_id),
						$('<th>').text(response.schema.form_name),
						$('<th>').text(response.schema.timestamp),
					).appendTo($table);

					$.each(response.data, function(i, item) {
						var $tr_item = $('<tr>')
							.append(
								$('<td>').text(item.form_id),
								$('<td>').text(item.form_name),
								$('<td>').text(item.timestamp),
							)
						var $tr_data = $('<tr>')
							.append(
								$('<td colspan="4">')
									.html('<textarea id="wpbf_config_' + item.form_id + '">'
										+ item.config +
										'</textarea><button data-id="" id="' + item.form_id + '" class="wpbf_update_form">Update</button><button class="wpbf_delete_form">Delete</button>'),
							)
						$table.append($tr_item, $tr_data)

					});

					$table.appendTo("#wpbf_forms_list");
				});
			},
			error: function(response){
				console.warn('Error:', response);
				$(".error_msg")
					.text(response.responseText)
					.css("display","block");
			}
		});
	}

	/**
	 *
	 * Admin AJAX form handlers.
	 */

	$(function() {

		// show list of forms
		if ( $("#wpbf_forms_list").length > 0 ) {
			show_forms();
		}

		// show list of submissions
		if ( $("#wpbf_submissions_list").length > 0 ) {
			$.ajax({
				type:"GET",
				url: wpbf_rest_api.rest_url + 'wpbf/v1/submissions/view',
				beforeSend: function (xhr) {
					xhr.setRequestHeader( 'X-WP-Nonce',wpbf_rest_api.rest_nonce)
				},
				success: function(response){
					console.log('Response:', response);

					$(function() {
						var $table = $('<table>');
						$table.addClass('ws_data_table');

						$('<tr>').append(
							$('<th>').text(response.schema.submission_id),
							$('<th>').text(response.schema.form_id),
							$('<th>').text(response.schema.form_name),
							$('<th>').text(response.schema.timestamp),
							$('<th>').text('View'),
						).appendTo($table);

						$.each(response.data, function(i, item) {
							var $tr_item = $('<tr>').append(
								$('<td>').text(item.submission_id),
								$('<td>').text(item.form_id),
								$('<td>').text(item.form_name),
								$('<td>').text(item.timestamp),
								$('<td>').html('<button id="' + item.form_id + '" class="wpbf_view_submission">Data</button>')
							);

							var $tr_data = $('<tr>')
								.append(
									$('<td colspan="4">')
										.html('<p>'
											+ JSON.stringify(item.metadata, null, 2) +
											'</p>'),
								);
							$table.append($tr_item, $tr_data);
						});

						$table.appendTo("#wpbf_submissions_list");
					});
				},
				error: function(response){
					console.warn('Error:', response);
					$(".error_msg").css("display","block");
				}
			});
		}

		// add new form script
		$("#wpbf_add_form")
			.on('submit', function(e){
				e.preventDefault();
				clear_messages();

				var schema = $('#add_form_config').val()

				// validate JSON
				if (!validate_json(schema)) {
					console.warn('Error:', 'Schema JSON is not valid.');
					$(".error_msg").css("display","block");
					return;
				}

				var data = {
					form_id: $('#add_form_id').val(),
					form_name: $('#add_form_name').val(),
					config: schema,
				};

				$.ajax({
					type:"POST",
					url: wpbf_rest_api.rest_url + 'wpbf/v1/forms/add',
					data: data,
					beforeSend: function (xhr) {
						xhr.setRequestHeader( 'X-WP-Nonce',wpbf_rest_api.rest_nonce)
					},
					success: function(response){
						console.log('Response:', response);
						$(".success_msg")
							.text('Form created successfully.')
							.css("display","block");
					},
					error: function(response){
						console.warn('Error:', response);
						$(".error_msg")
							.innerHTML(response.responseText)
							.css("display","block");
					}
				});
				show_forms();
				$('#wpbf_add_form')[0].reset();
			});


		// update form schema
		$("#wpbf_forms_list")
			.on('click', '.wpbf_update_form', function(e){
				clear_messages();
				var form_id = e.target.id;
				var schema = $('#wpbf_config_' + form_id).val();

				console.log(form_id, schema)

				// validate JSON
				if (!validate_json(schema)) {
					console.warn('Error:', 'Schema JSON is not valid.');
					$(".error_msg")
						.text('Form schema JSON is invalid.')
						.css("display","block");
					return;
				}

				$.ajax({
					type: "POST",
					url: wpbf_rest_api.rest_url + 'wpbf/v1/forms/update/',
					data: {
						form_id: form_id,
						config: schema,
					},
					beforeSend: function (xhr) {
						xhr.setRequestHeader( 'X-WP-Nonce',wpbf_rest_api.rest_nonce)
					},
					success: function(response){
						console.log('Response:', response);
						$(".success_msg")
							.text('Form schema updated successfully.')
							.css("display","block");
					},
					error: function(response){
						console.warn('Error:', response);
						$(".error_msg")
							.text(response.hasOwnProperty('responseText') ? response.responseText : '')
							.css("display","block");
						show_forms();
					}
				});
			});
	});

})( jQuery );
