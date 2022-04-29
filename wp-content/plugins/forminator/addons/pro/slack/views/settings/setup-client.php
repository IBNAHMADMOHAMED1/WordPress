<?php
// defaults.
$vars = array(
	'token'               => '',
	'error_message'       => '',
	'client_id'           => '',
	'client_secret'       => '',
	'client_secret_error' => '',
	'client_id_error'     => '',
	'redirect_url'        => '',
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
		echo esc_html( sprintf( __( 'Setup %1$s Client', 'forminator' ), 'Slack' ) );
		?>
	</h3>

	<?php if ( ! empty( $vars['token'] ) ) : ?>

		<p id="forminator-integration-popup__description" class="sui-description"><?php esc_html_e( 'Your Slack account is already authorized. Edit info below to re-authorize.', 'forminator' ); ?> </p>

	<?php else : ?>

		<p id="forminator-integration-popup__description" class="sui-description"><?php esc_html_e( 'Setup Slack to be used by Forminator to communicating with Slack server.', 'forminator' ); ?></p>

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

	<?php endif ?>

</div>

<form>

	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['client_id_error'] ) ? 'sui-form-field-error' : '' ); ?>">
		<label class="sui-label"><?php esc_html_e( 'Client ID', 'forminator' ); ?></label>
		<input
				class="sui-form-control"
				name="client_id" placeholder="<?php echo esc_attr( __( 'Client ID', 'forminator' ) ); ?>"
				value="<?php echo esc_attr( $vars['client_id'] ); ?>">
		<?php if ( ! empty( $vars['client_id_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['client_id_error'] ); ?></span>
		<?php endif; ?>
	</div>

	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['client_secret_error'] ) ? 'sui-form-field-error' : '' ); ?>">
		<label class="sui-label"><?php esc_html_e( 'Client Secret', 'forminator' ); ?></label>
		<input
				class="sui-form-control"
				name="client_secret" placeholder="<?php echo esc_attr( __( 'Client Secret', 'forminator' ) ); ?>"
				value="<?php echo esc_attr( $vars['client_secret'] ); ?>">
		<?php if ( ! empty( $vars['client_secret_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['client_secret_error'] ); ?></span>
		<?php endif; ?>
		<span class="sui-description">
				<?php esc_html_e( 'Follow these instructions to retrieve your Client ID and Secret.', 'forminator' ); ?>
			<ol class="instructions" id="clientid-instructions">
					<li>
						<?php
						echo sprintf(/* translators: ... */
							esc_html__( 'Go %1$shere%2$s to create new Slack App.', 'forminator' ),
							'<a href="https://api.slack.com/apps?new_app=1" target="_blank">',
							'</a>'
						); //phpcs:ignore Standard.Category.SniffName.ErrorCode
						?>
					</li>
					<li>
						<?php
						esc_html_e(
							'You will need to enter App Name and Development Slack Workspace.',
							'forminator'
						);
						?>
					</li>
					<li>
						<?php
						echo sprintf(/* translators: ... */
							esc_html__( 'Once the Project creation is completed go to the %1$sBasic Information%2$s. Then scroll through %3$sApp Credentials%4$s, to take a note of %5$sClient ID%6$s and %7$sClient Secret%8$s.', 'forminator' ),
							'<strong>',
							'</strong>',
							'<strong>',
							'</strong>',
							'<strong>',
							'</strong>',
							'<strong>',
							'</strong>'
						); //phpcs:ignore Standard.Category.SniffName.ErrorCode
						?>
					</li>
					<li>
						<?php
						echo sprintf(/* translators: ... */
							esc_html__( 'Next, go to the %1$sFeatures%2$s &gt; %3$sOAuth & Permissions%4$s &gt; %5$sRedirect URLs%6$s section.', 'forminator' ),
							'<strong>',
							'</strong>',
							'<strong>',
							'</strong>',
							'<strong>',
							'</strong>'
						); //phpcs:ignore Standard.Category.SniffName.ErrorCode
						?>
						<ol>
							<li>
								<?php
								echo sprintf(/* translators: ... */
									esc_html__( 'Click %1$sAdd a new Redirect URL%2$s.', 'forminator' ),
									'<strong>',
									'</strong>'
								); //phpcs:ignore Standard.Category.SniffName.ErrorCode
								?>
							</li>
							<li>
								<?php esc_html_e( 'In the shown input field, put this value below', 'forminator' ); ?>
								<pre class="sui-code-snippet"><?php echo esc_html( ! empty( $vars['redirect_url'] ) ? $vars['redirect_url'] : '' ); ?></pre>.</li>
							<li>
								<?php
								echo sprintf(/* translators: ... */
									esc_html__( 'Then click the %1$sAdd%2$s button.', 'forminator' ),
									'<strong>',
									'</strong>'
								); //phpcs:ignore Standard.Category.SniffName.ErrorCode
								?>
							</li>
							<li>
								<?php
								echo sprintf(/* translators: ... */
									esc_html__( 'Then click the %1$sSave URLs%2$s button.', 'forminator' ),
									'<strong>',
									'</strong>'
								); //phpcs:ignore Standard.Category.SniffName.ErrorCode
								?>
							</li>
						</ol>
				</ol>
			</span>
	</div>

</form>
