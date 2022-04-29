<?php
// defaults.
$vars = array(
	'error_message' => '',
	'is_close'      => false,
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>

<div id="forminator-integrations" class="wpmudev-settings--box">
	<div class="sui-box">
		<div class="sui-box-header">
			<h2 class="sui-box-title"><?php esc_html_e( 'Authorizing Trello', 'forminator' ); ?></h2>
		</div>
		<div class="sui-box-body">
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
			<?php elseif ( $vars['is_close'] ) : ?>
				<div
					role="alert"
					class="sui-notice sui-notice-green sui-active"
					style="display: block; text-align: left;"
					aria-live="assertive"
				>

					<div class="sui-notice-content">

						<div class="sui-notice-message">

							<span class="sui-notice-icon sui-icon-check-tick" aria-hidden="true"></span>

							<p>
								<?php
								esc_html_e(
									'Successfully authorized Trello, you can go back to integration settings.',
									'forminator'
								);
								?>
							</p>

						</div>

					</div>

				</div>
			<?php else : ?>
				<div
					role="alert"
					class="sui-notice sui-active"
					style="display: block; text-align: left;"
					aria-live="assertive"
				>

					<div class="sui-notice-content">

						<div class="sui-notice-message">

							<span class="sui-notice-icon sui-icon-loader sui-loading" aria-hidden="true"></span>

							<p><?php esc_html_e( 'Please wait...', 'forminator' ); ?></p>

						</div>

					</div>

				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
	// SO.
	function getHashParams() {

		var hashParams = {};
		var e,
			a          = /\+/g,  // Regex for replacing addition symbol with a space.
			r          = /([^&;=]+)=?([^&;]*)/g,
			d          = function (s) {
				return decodeURIComponent(s.replace(a, " "));
			},
			q          = window.location.hash.substring(1);

		while (e = r.exec(q))
			hashParams[d(e[1])] = d(e[2]);

		return hashParams;
	}

	(function ($) {
		$(document).ready(function (e) {
			<?php if ( $vars['is_close'] ) : ?>

			setTimeout(function () {
				window.close();
			}, 3000);

			<?php endif; ?>
			var hash_params = getHashParams();
			if (typeof hash_params.token !== 'undefined') {
				var current_href = window.location.href;
				current_href     = current_href.replace(window.location.hash, '');

				window.location.href = current_href + '&token=' + hash_params.token;
			}
		});

	})(jQuery);
</script>
