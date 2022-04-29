<?php
$nonce = wp_create_nonce( 'forminator_save_import_custom_form_ninja' );
$forms = forminator_list_thirdparty_contact_forms( 'ninjaforms' );
?>

<div class="sui-box-body wpmudev-popup-form">

	<div
		role="alert"
		id="wpmudev-ajax-error-placeholder"
		class="sui-notice sui-notice-error"
		aria-live="assertive"
	>
		<!-- Nothing should be placed here -->
	</div>

	<div class="sui-form-field">
		<select class="sui-form-dropdown" name="ninjaforms">
			<option value="all"><?php esc_html_e( 'All Forms', 'forminator' ); ?></option>
			<?php
			if ( ! empty( $forms ) ) :
				foreach ( $forms as $key => $value ) {
					echo sprintf(
						'<option value="%f">%s</option>',
						absint( $value->get_id() ),
						esc_html( $value->get_setting( 'title' ) )
					);
				}
			endif;
			?>
		</select>

		<span class="sui-description"><?php esc_html_e( 'Select the form.', 'forminator' ); ?></span>

	</div>

</div>

<div class="sui-box-footer">

	<button class="sui-button forminator-popup-cancel" data-a11y-dialog-hide="forminator-popup">
		<span class="sui-loading-text"><?php esc_html_e( 'Cancel', 'forminator' ); ?></span>
		<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
	</button>

	<div class="sui-actions-right">

		<button class="sui-button sui-button-primary wpmudev-action-ajax-done" data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<span class="sui-loading-text"><?php esc_html_e( 'Import', 'forminator' ); ?></span>
			<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
		</button>

	</div>

</div>
