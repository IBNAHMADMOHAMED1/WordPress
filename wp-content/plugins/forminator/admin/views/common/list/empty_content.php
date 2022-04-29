<?php
if ( isset( static::$module_slug ) ) {
	$module_slug = static::$module_slug;
} else {
	$module_slug = Forminator_Admin_Module_Edit_Page::get_slug_ajax();
}

$import_dialog = 'import_' . forminator_get_prefix( $module_slug, 'c' );
// Empty message.
$image_empty   = forminator_plugin_url() . 'assets/images/forminator-empty-message.png';
$image_empty2x = forminator_plugin_url() . 'assets/images/forminator-empty-message@2x.png';
// Search no results image.
$search_empty   = forminator_plugin_url() . 'assets/images/forminator-no-result.png';
$search_empty2x = forminator_plugin_url() . 'assets/images/forminator-no-result@2x.png';
?>

<div class="sui-box sui-message sui-message-lg">

	<?php if ( forminator_is_show_branding() ) : ?>
		<?php if ( ! $is_search ) : ?>

			<img src="<?php echo esc_url( $image_empty ); ?>"
				srcset="<?php echo esc_url( $image_empty2x ); ?> 1x, <?php echo esc_url( $image_empty2x ); ?> 2x"
				alt="<?php esc_html_e( 'Empty modules', 'forminator' ); ?>"
				class="sui-image sui-image-center"
				aria-hidden="true"/>

		<?php else : ?>

			<img src="<?php echo esc_url( $search_empty ); ?>"
				srcset="<?php echo esc_url( $search_empty2x ); ?> 1x, <?php echo esc_url( $search_empty2x ); ?> 2x"
				alt="<?php esc_html_e( 'No results', 'forminator' ); ?>"
				class="sui-image sui-image-center"
				aria-hidden="true"/>

		<?php endif; ?>
	<?php endif; ?>

	<div class="sui-message-content">

		<?php if ( ! $is_search ) : ?>

			<p><?php echo esc_html( $empty_title ); ?></p>

			<?php if ( Forminator::is_import_export_feature_enabled() ) : ?>

				<p>
					<button class="sui-button sui-button-blue wpmudev-button-open-modal" data-modal="<?php echo esc_attr( $create_dialog ); ?>"><i class="sui-icon-plus" aria-hidden="true"></i> <?php esc_html_e( 'Create', 'forminator' ); ?></button>

					<a href="#"
						class="sui-button wpmudev-open-modal"
						data-modal="<?php echo esc_attr( $import_dialog ); ?>"
						data-modal-title=""
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_popup_' . $import_dialog ) ); ?>">
						<i class="sui-icon-upload-cloud" aria-hidden="true"></i> <?php esc_html_e( 'Import', 'forminator' ); ?>
					</a>
				</p>

			<?php else : ?>

				<p><button class="sui-button sui-button-blue wpmudev-button-open-modal" data-modal="<?php echo esc_attr( $create_dialog ); ?>">
					<i class="sui-icon-plus" aria-hidden="true"></i> <?php esc_html_e( 'Create', 'forminator' ); ?>
				</button></p>

			<?php endif; ?>

		<?php else : // Search no-result message. ?>

			<h2>
			<?php
					echo sprintf(
						'%s "%s"',
						esc_html__( 'No results for', 'forminator' ),
						esc_html( $search_keyword )
					);
			?>
				</h2>

			<p><?php echo esc_html( sprintf( __( "We couldn't find any %s matching your search keyword. Perhaps try again?", 'forminator' ), forminator_get_prefix( $module_slug, '', false, true ) ) ); ?></p>

		<?php endif; ?>

	</div>

</div>
