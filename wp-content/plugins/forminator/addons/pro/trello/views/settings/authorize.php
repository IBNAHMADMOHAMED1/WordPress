<?php
// defaults.
$vars = array(
	'auth_url'          => '',
	'token'             => '',
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>

<div class="forminator-integration-popup__header">

    <h3 id="forminator-integration-popup__title" class="sui-box-title sui-lg" style="overflow: initial; white-space: normal; text-overflow: initial;">
		<?php
		/* translators: ... */
		echo esc_html( sprintf( __( 'Connect %1$s', 'forminator' ), 'Trello' ) );
		?>
    </h3>

</div>

<?php if ( ! empty( $vars['token'] ) ) : ?>
    <p class="sui-description" style="text-align: center;"><?php esc_html_e( 'Click button below to re-authorize.', 'forminator' ); ?> </p>
<?php else : ?>
    <p class="sui-description" style="text-align: center;"><?php esc_html_e( 'Authorize Forminator to connect with your Trello in order to send data from your forms.', 'forminator' ); ?></p>
<?php endif ?>

<div class="forminator-integration-popup__footer-temp">
	<?php if ( empty( $vars['token'] ) ) : ?>
        <a href="<?php echo esc_attr( $vars['auth_url'] ); ?>" target="_blank" class="sui-button sui-button-primary forminator-addon-connect forminator-integration-popup__close"><?php esc_html_e( 'Authorize', 'forminator' ); ?></a>
	<?php else : ?>
        <a href="<?php echo esc_attr( $vars['auth_url'] ); ?>" target="_blank" class="sui-button sui-button-primary forminator-addon-connect forminator-integration-popup__close"><?php esc_html_e( 'Re-Authorize', 'forminator' ); ?></a>
	<?php endif ?>
</div>
