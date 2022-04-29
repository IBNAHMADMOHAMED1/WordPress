<?php
// defaults.
$vars = array(
	'error_message' => '',
	'name'          => '',
	'name_error'    => '',
	'multi_id'      => '',
	'fields'        => array(),
	'form_fields'   => array(),
	'file_fields'   => array(),
	'pipeline'      => array(),
	'status'        => array(),
	'auth_url'      => '',
	'token'         => '',
	're-authorize'  => '',
);
/** @var array $template_vars */

foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>

<div class="forminator-integration-popup__header">

	<h3 id="forminator-integration-popup__title" class="sui-box-title sui-lg" style="overflow: initial; white-space: normal; text-overflow: initial;">
		<?php echo esc_html( __( 'Create Ticket', 'forminator' ) ); ?>
	</h3>

	<p id="forminator-integration-popup__description" class="sui-description">
		<?php esc_html_e( 'In addition to adding a new contact to your HubSpot account, you can also create a HubSpot ticket for each form submission.', 'forminator' ); ?>
	</p>

	<?php if ( ! empty( $vars['error_message'] ) ) : ?>
		<div
			role="alert"
			class="sui-notice sui-notice-red sui-active"
			style="display: block; text-align: left;"
			aria-live="assertive"
		>

			<div class="sui-notice-content">

				<div class="sui-notice-message">

					<span class="sui-notice-icon sui-icon-info" aria-hidden="true"></span>

					<p><?php echo esc_html( $vars['error_message'] ); ?></p>

				</div>

			</div>

		</div>
	<?php endif; ?>

</div>

<form style="display: block; margin-top: -10px; margin-bottom: 0;">

	<div class="fui-section-toggle">

		<label class="sui-toggle">
			<input
				type="checkbox"
				name="create_ticket"
				value="1"
				id="create-ticket"
				aria-labelledby="create-ticket-label"
				<?php checked( $vars['create_ticket'], 1 ); ?>
			/>
			<span class="sui-toggle-slider"></span>
		</label>

		<label for="create-ticket" id="create-ticket-label" class="sui-toggle-label"><?php esc_html_e( 'Create a HubSpot ticket for each submission', 'forminator' ); ?></label>

	</div>

	<div
		tabindex="0"
		role="group"
		class="fui-section-toggle-content ticket-fields"
		<?php echo '1' === $vars['create_ticket'] ? '' : 'style="display: none;"'; ?>
		<?php echo '1' === $vars['create_ticket'] ? '' : 'hidden'; ?>
	>
		<?php if ( empty( $vars['re-authorize'] ) && ! empty( $vars['token'] ) ) { ?>

			<div
				role="alert"
				class="sui-notice sui-notice-blue sui-active"
				style="display: block;"
				aria-live="assertive"
			>

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info" aria-hidden="true"></span>

						<p><strong><?php esc_html_e( 'Authorize Forminator to access HubSpot tickets', 'forminator' ); ?></strong></p>

						<p style="margin-top: 5px; margin-bottom: 10px;"><?php esc_html_e( 'Forminator requires additional permissions to create HubSpot tickets. Note that you will be taken to HubSpot website to grant Forminator access to HubSpot tickets and redirected back here.', 'forminator' ); ?></p>

						<p style="margin-top: 10px;"><a href="<?php echo esc_attr( $vars['auth_url'] ); ?>" target="_blank" class="sui-button sui-button-primary forminator-addon-connect"><?php esc_html_e( 'Authorize', 'forminator' ); ?></a></p>

					</div>

				</div>

			</div>

		<?php } else { ?>

			<!-- FIELD: Pipeline -->
			<div class="sui-form-field">

				<label for="hubspot-support-request" id="hubspot-support-request-label" class="sui-label"><?php esc_html_e( 'Pipeline', 'forminator' ); ?></label>

				<?php // DEV NOTE: Select without JS. ?>
				<select
					name="pipeline_id"
					id="hubspot-support-request"
					style="max-width: none;"
					aria-labelledby="hubspot-support-request-label"
					aria-describedby="hubspot-support-request-error"
				>
					<?php
					if ( ! empty( $vars['pipeline'] ) ) :

						foreach ( $vars['pipeline'] as $pipeline_id => $pipeline_name ) :
						?>

							<option value="<?php echo esc_attr( $pipeline_id ); ?>" <?php selected( $vars['pipeline_id'], $pipeline_id ); ?>><?php echo esc_html( $pipeline_name ); ?></option>

						<?php
						endforeach;

					endif;
					?>

				</select>

				<span id="hubspot-support-request-error" class="sui-error-message">
					<?php
					if ( ! empty( $vars['pipeline_error'] ) ) :
						echo esc_html( $vars['pipeline_error'] );
					endif; ?>
				</span>

			</div>

			<!-- FIELD: Ticket Status -->
			<div class="sui-form-field">

				<label for="hubspot-ticket-status" id="hubspot-ticket-status-label" class="sui-label"><?php esc_html_e( 'Ticket Status', 'forminator' ); ?></label>

				<?php // DEV NOTE: Select without JS. ?>
				<select
					name="status_id"
					id="hubspot-ticket-status"
					style="max-width: none;"
					aria-labelledby="hubspot-ticket-status-label"
					aria-describedby="hubspot-ticket-status-error"
				>

					<?php
					if ( ! empty( $vars['status'] ) ) {

						foreach ( $vars['status'] as $stages => $stage ) {

							if ( isset( $stages ) && isset( $stage ) ) :
							?>

								<option value="<?php echo esc_attr( $stages ); ?>" <?php selected( $vars['status_id'], $stages ); ?>><?php echo esc_html( $stage ); ?></option>

							<?php
							endif;

						}

					}
					?>

				</select>

				<span id="hubspot-ticket-status-error" class="sui-error-message">
					<?php
					if ( ! empty( $vars['status_error'] ) ) :
						echo esc_html( $vars['status_error'] );
					endif;
					?>
				</span>

			</div>

			<!-- FIELD: Ticket Name -->
			<div class="sui-form-field<?php echo ( ! empty( $vars['ticket_name_error'] ) ) ? ' sui-form-field-error' : ''; ?>">

				<label for="ticket-name-input" id="ticket-name-input-label" class="sui-label">
					<?php esc_html_e( 'Ticket Name', 'forminator' ); ?>
					<span class="sui-label-note"><?php esc_html_e( 'Use the "+" icon to add form fields', 'forminator' ); ?></span>
				</label>

				<div class="sui-insert-variables">

					<input
						type="text"
						name="ticket_name"
						value="<?php echo esc_attr( $vars['ticket_name'] ); ?>"
						placeholder="Enter ticket name"
						id="ticket-name-input"
						class="sui-form-control ticket-text"
						aria-labelledby="ticket-name-input-label"
						aria-describedby="ticket-name-input-error"
					/>

					<select id="select-ticket-name" class="sui-variables select-field">

						<?php
						if ( ! empty( $vars['form_fields'] ) ) :

							foreach ( $vars['form_fields'] as $key => $field_title ) :
							?>

								<option value="{<?php echo esc_attr( $field_title['element_id'] ); ?>}" data-content="{<?php echo esc_attr( $field_title['element_id'] ); ?>}"><?php echo esc_html( $field_title['field_label'] . ' | ' . $field_title['element_id'] ); ?></option>

							<?php
							endforeach;

						endif;
						?>

					</select>

				</div>

				<span id="ticket-name-input-error" class="sui-error-message">
					<?php
					if ( ! empty( $vars['ticket_name_error'] ) ) :
						echo esc_html( $vars['ticket_name_error'] );
					endif;
					?>
				</span>

			</div>

			<!-- FIELD: Ticket Description -->
			<div class="sui-form-field">

				<label for="ticket-description" id="ticket-description-label" class="sui-label">
					<?php esc_html_e( 'Ticket Description (optional)', 'forminator' ); ?>
					<span class="sui-label-note"><?php esc_html_e( 'Use the "+" icon to add form fields', 'forminator' ); ?></span>
				</label>

				<div class="sui-insert-variables">

					<textarea
						name="ticket_description"
						placeholder="Enter ticket description"
						id="ticket-description"
						class="sui-form-control ticket-text"
						aria-labelledby="ticket-description-label"
					>
						<?php echo esc_attr( $vars['ticket_description'] ); ?>
					</textarea>

					<select id="select-ticket-description" class="sui-variables select-field">

						<?php
						if ( ! empty( $vars['form_fields'] ) ) :

							foreach ( $vars['form_fields'] as $key => $field_title ) :
							?>

								<option value="{<?php echo esc_attr( $field_title['element_id'] ); ?>}" data-content="{<?php echo esc_attr( $field_title['element_id'] ); ?>}"><?php echo esc_html( $field_title['field_label'] . ' | ' . $field_title['element_id'] ); ?></option>

							<?php
							endforeach;

						endif;
						?>

					</select>

				</div>

			</div>

			<!-- FIELD: Supported File -->
			<div class="sui-form-field">

				<label for="hubspot-support-file" id="hubspot-support-file-label" class="sui-label"><?php esc_html_e( 'Supported File (optional)', 'forminator' ); ?></label>

				<?php // DEV NOTE: Select without JS. ?>
				<select
					name="supported_file"
					id="hubspot-support-file"
					style="max-width: none;"
					aria-labelledby="hubspot-support-file-label"
				>

					<option value=""><?php esc_html_e( 'Select a file upload field', 'forminator' ); ?></option>

					<?php
					$file_selected = $vars['supported_file'];

					if ( ! empty( $vars['file_fields'] ) ) :

						foreach ( $vars['file_fields'] as $file => $file_field ) :
						?>

							<option value="<?php echo esc_attr( $file_field['element_id'] ); ?>" <?php selected( $file_selected, $file_field['element_id'] ); ?>><?php echo esc_html( $file_field['field_label'] . ' | ' . $file_field['element_id'] ); ?></option>

						<?php
						endforeach;

					endif;
					?>

				</select>

			</div>

		<?php } ?>

	</div>

	<input
		type="hidden"
		name="multi_id"
		value="<?php echo esc_attr( $vars['multi_id'] ); ?>"
	/>

    <input
        type="hidden"
        name="re-authorize"
        value="<?php echo esc_attr( $vars['re-authorize'] ); ?>"
    />

</form>

<script>
	(function ($) {
		$(document).ready(function (e) {
			$('#create-ticket').on('change', function () {
                let ticketField = $('.ticket-fields'),
                    ticketActivate = $('div#ticket-activate');
                ticketField.hide();
                ticketActivate.show();
                if (this.checked) {
                    ticketField.show();
                    ticketActivate.hide();
                }
			});
			$('.select-field').on('change', function () {
				let value = $(this).val(),
					ticket_text = $(this).closest('.sui-form-field').find('.ticket-text'),
					text_val = ticket_text.val();
				text_val += value;
				ticket_text.val(text_val);
			});
			$('#hubspot-support-request').on('change', function () {
				let value = $(this).val();
				$.ajax({
					url: '<?php echo esc_url( forminator_ajax_url() ); ?>',
					type: "POST",
					data: {
						action: "forminator_hubspot_support_request",
						value: value
					},
					success: function (response) {
                        if ( response.success && Object.keys(response.data).length > 0 ) {
							let options = '';
							$.each(response.data, function (i, value) {
								options += '<option value="' + i + '">' + value + '</option>';
							});
							$('#hubspot-ticket-status').html('').append(options);
						} else {
							$('#hubspot-ticket-status').html('');
						}
					}
				});
			});
		});
	})(jQuery);
</script>
