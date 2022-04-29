<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Shortcode_Generator
 */
class Forminator_Shortcode_Generator {

	/**
	 * Forminator_Shortcode_Generator constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		global $pagenow;

		$page = filter_input( INPUT_GET, 'page' );
		// Hide Shortcode Generator for pages with Smartcrawl until SUI is updated to latest version.
		if (
			( defined( 'SMARTCRAWL_VERSION' ) && ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ) ||
			'hustle_popup' !== $page
		) {
			return;
		}

		add_action( 'media_buttons', array( $this, 'attach_button' ) );
		add_action( 'admin_footer', array( $this, 'enqueue_js_scripts' ) );
		if ( function_exists( 'hustle_activated' ) ) {
			add_action( 'admin_footer', array( $this, 'enqueue_preview_scripts_for_hustle' ) );
		}
	}

	/**
	 * Check if current page is Hustle wizard page
	 *
	 * @since 1.0.5
	 *
	 * @return bool
	 */
	public function is_hustle_wizard() {
		// Some plugins throw error of get_current_screen as undefined.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		// If no screen id, abort.
		if ( ! isset( $screen->id ) ) {
			return false;
		}

		// Hustle wizard pages.
		$pages = array(
			'hustle_page_hustle_popup',
			'hustle_page_hustle_slidein',
			'hustle_page_hustle_embedded',
			'hustle_page_hustle_sshare',
			'hustle-pro_page_hustle_popup',
			'hustle_pro_page_hustle_slidein',
			'hustle_pro_page_hustle_embedded',
			'hustle_pro_page_hustle_sshare',
		);

		// Check if current page is hustle wizard page.
		if ( in_array( $screen->id, $pages, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Attach button
	 *
	 * @since 1.0
	 */
	public function attach_button() {
		global $pagenow;

		// If page different than Post or Page, abort.
		if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow && ! $this->is_hustle_wizard() ) {
			return;
		}

		// Button markup.
		printf(
			'<button type="button" id="%s" class="button" data-editor="content" data-a11y-dialog-show="forminator-popup">%s<span>%s</span></button>',
			'forminator-generate-shortcode',
			'<i class="forminator-scgen-icon" aria-hidden="true"></i>',
			esc_html__( 'Add Form', 'forminator' )
		);
	}

	/**
	 * @since 1.0
	 * @param $content
	 *
	 * @return mixed
	 */
	public function enqueue_js_scripts( $content ) {

		global $pagenow;

		$sui_version = FORMINATOR_SUI_VERSION;
		$page        = filter_input( INPUT_GET, 'page' );
		if (
			defined( 'HUSTLE_SUI_VERSION' ) &&
			version_compare( HUSTLE_SUI_VERSION, '2.8.0', '>' ) &&
			version_compare( FORMINATOR_SUI_VERSION, '2.8.0', '<' ) &&
			'hustle_popup' === $page
		) {
			$sui_version = HUSTLE_SUI_VERSION;
		}

		// $sanitize_version = str_replace( '.', '-', FORMINATOR_SUI_VERSION );.
		$sanitize_version = str_replace( '.', '-', $sui_version );
		$sui_body_class   = "sui-$sanitize_version";

		// If page different than Post or Page, abort.
		if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow && ! $this->is_hustle_wizard() ) {
			return $content;
		}

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-mouse' );
		wp_enqueue_script( 'jquery-ui-tabs' );

		// Get shortcode generator styles.
		wp_enqueue_style(
			'forminator-shortcode-generator-styles',
			forminator_plugin_url() . 'assets/css/forminator-scgen.min.css',
			array(),
			FORMINATOR_VERSION
		);

		// Get SUI JS.
		$sui_handle = 'shared-ui';
		wp_enqueue_script(
			$sui_handle,
			forminator_plugin_url() . 'assets/js/shared-ui.min.js',
			array( 'jquery', 'clipboard' ),
			$sui_body_class,
			true
		);

		// Get shortcode generator scripts.
		wp_enqueue_script(
			'forminator-shortcode-generator',
			forminator_plugin_url() . 'build/admin/scgen.min.js',
			array( 'jquery' ),
			FORMINATOR_VERSION,
			false
		);

		wp_localize_script(
			'forminator-shortcode-generator',
			'forminatorScgenData',
			array(
				'suiVersion' => $sui_body_class,
			)
		);

		$this->print_markup();

		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				jQuery("#forminator-generate-shortcode").on( 'click', function(e) {
					e.preventDefault();
				});
			});
		</script>
		<?php
	}

	/**
	 * @since 1.0
	 * @param $content
	 *
	 * @return mixed
	 */
	public function enqueue_preview_scripts_for_hustle( $content ) {

		// If page is not Hustle module settings page, abort.
		if ( ! $this->is_hustle_wizard() ) {
			return $content;
		}

		/**
		 * Forminator UI
		 * These stylesheets currently works with "forms" only.
		 *
		 * @since 1.7.0
		 */
		wp_enqueue_style( 'forminator-scgen-global', forminator_plugin_url() . 'assets/forminator-ui/css/forminator-global.min.css', array(), FORMINATOR_VERSION );
		wp_enqueue_style( 'forminator-scgen-icons', forminator_plugin_url() . 'assets/forminator-ui/css/forminator-icons.min.css', array(), FORMINATOR_VERSION );
		wp_enqueue_style( 'forminator-scgen-forms', forminator_plugin_url() . 'assets/forminator-ui/css/forminator-forms.min.css', array(), FORMINATOR_VERSION );

	}

	/**
	 * Print modal markup
	 *
	 * @since 1.0
	 */
	public function print_markup() {
		?>
		<div id="forminator-scgen-modal" class="sui-wrap" style="display:none;">

			<div class="sui-modal sui-modal-md">

				<div
					role="dialog"
					id="forminator-popup"
					class="sui-modal-content"
					aria-modal="true"
					aria-labelledby="scgenDialogTitle"
					aria-describedby="scgenDialogDescription"
				>

					<div class="sui-box" style="margin-bottom: 0;">

						<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">

							<h3 id="scgenDialogTitle" class="sui-box-title sui-lg"><?php esc_html_e( 'Forminator Shortcodes', 'forminator' ); ?></h3>

							<p id="scgenDialogDescription" class="sui-description"><?php esc_html_e( 'Select an option from the dropdown menu and generate a shortcode to insert in your post or page.', 'forminator' ); ?></p>

							<button class="sui-modal-skip sui-button-icon sui-button-float--right sui-dialog-close" data-modal-close="">
								<span class="sui-icon-close sui-md" aria-hidden="true"></span>
								<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog window.', 'forminator' ); ?></span>
							</button>

						</div>

						<div class="sui-box-body sui-box-body-slim">

							<div class="sui-tabs sui-tabs-flushed">

								<?php if ( 'hustle_popup' === filter_input( INPUT_GET, 'page' ) ) : ?>

									<div role="tablist" class="sui-tabs-menu">

										<button
											id="forminator-shortcode-type--forms"
											type="button"
											role="tab"
											class="sui-tab-item active"
											aria-controls="forminator-custom-forms"
											aria-selected="true"
										>
											<?php esc_html_e( 'Forms', 'forminator' ); ?>
										</button>
										<button
											id="forminator-shortcode-type--polls"
											type="button"
											role="tab"
											class="sui-tab-item"
											aria-controls="forminator-polls"
											aria-selected="false"
											tabindex="-1"
										>
											<?php esc_html_e( 'Polls', 'forminator' ); ?>
										</button>
										<button
											id="forminator-shortcode-type--quizzes"
											type="button"
											role="tab"
											class="sui-tab-item"
											aria-controls="forminator-quizzes"
											aria-selected="false"
											tabindex="-1"
										>
											<?php esc_html_e( 'Quizzes', 'forminator' ); ?>
										</button>

									</div>

									<div class="sui-tabs-content">

										<!-- Forms -->
										<div
											role="tabpanel"
											tabindex="0"
											id="forminator-custom-forms"
											class="sui-tab-content active"
											aria-labelledby="forminator-shortcode-type--forms"
										>

											<div class="sui-form-field">

												<label for="forminator-select-forms" class="sui-label"><?php esc_html_e( 'Choose an option', 'forminator' ); ?></label>

												<?php echo $this->get_forms(); ?>

												<span class="sui-error-message" style="display: none;"><?php esc_html_e( 'Please, select an option before you proceed.', 'forminator' ); ?></span>

											</div>

											<div class="fui-simulate-footer">

												<button class="sui-button sui-button-blue wpmudev-insert-cform">
													<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
													<span class="sui-loading-text"><?php esc_html_e( 'Generate Shortcode', 'forminator' ); ?></span>
												</button>

											</div>

										</div>

										<!-- Polls -->
										<div
											role="tabpanel"
											tabindex="0"
											id="forminator-polls"
											class="sui-tab-content"
											aria-labelledby="forminator-shortcode-type--polls"
											hidden
										>

											<div class="sui-form-field">

												<label for="forminator-select-forms" class="sui-label"><?php esc_html_e( 'Choose an option', 'forminator' ); ?></label>

												<?php echo $this->get_polls(); ?>

												<span class="sui-error-message" style="display: none;"><?php esc_html_e( 'Please, select an option before you proceed.', 'forminator' ); ?></span>

											</div>

											<div class="fui-simulate-footer">

												<button class="sui-button sui-button-blue wpmudev-insert-poll">
													<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
													<span class="sui-loading-text"><?php esc_html_e( 'Generate Shortcode', 'forminator' ); ?></span>
												</button>

											</div>

										</div>

										<!-- Quizzes -->
										<div
											role="tabpanel"
											tabindex="0"
											id="forminator-quizzes"
											class="sui-tab-content"
											aria-labelledby="forminator-shortcode-type--quizzes"
											hidden
										>

											<div class="sui-form-field">

												<label for="forminator-select-forms" class="sui-label"><?php esc_html_e( 'Choose an option', 'forminator' ); ?></label>

												<?php echo $this->get_quizzes(); ?>

												<span class="sui-error-message" style="display: none;"><?php esc_html_e( 'Please, select an option before you proceed.', 'forminator' ); ?></span>

											</div>

											<div class="fui-simulate-footer">

												<button class="sui-button sui-button-blue wpmudev-insert-quiz">
													<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
													<span class="sui-loading-text"><?php esc_html_e( 'Generate Shortcode', 'forminator' ); ?></span>
												</button>

											</div>

										</div>

									</div>

								<?php else : ?>

									<div data-tabs>

										<div id="forminator-shortcode-type--forms" class="active"><?php esc_html_e( 'Forms', 'forminator' ); ?></div>
										<div id="forminator-shortcode-type--polls"><?php esc_html_e( 'Polls', 'forminator' ); ?></div>
										<div id="forminator-shortcode-type--quizzes"><?php esc_html_e( 'Quizzes', 'forminator' ); ?></div>

									</div>

									<div data-panes>

										<!-- Forms -->
										<div id="forminator-custom-forms" class="active">

											<div class="sui-form-field">

												<label for="forminator-select-forms" class="sui-label"><?php esc_html_e( 'Choose an option', 'forminator' ); ?></label>

												<?php echo $this->get_forms(); ?>

												<span class="sui-error-message" style="display: none;"><?php esc_html_e( 'Please, select an option before you proceed.', 'forminator' ); ?></span>

											</div>

											<div class="fui-simulate-footer">

												<button class="sui-button sui-button-blue wpmudev-insert-cform">
													<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
													<span class="sui-loading-text"><?php esc_html_e( 'Generate Shortcode', 'forminator' ); ?></span>
												</button>

											</div>

										</div>

										<!-- Polls -->
										<div id="forminator-polls">

											<div class="sui-form-field">

												<label for="forminator-select-forms" class="sui-label"><?php esc_html_e( 'Choose an option', 'forminator' ); ?></label>

												<?php echo $this->get_polls(); ?>

												<span class="sui-error-message" style="display: none;"><?php esc_html_e( 'Please, select an option before you proceed.', 'forminator' ); ?></span>

											</div>

											<div class="fui-simulate-footer">

												<button class="sui-button sui-button-blue wpmudev-insert-poll">
													<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
													<span class="sui-loading-text"><?php esc_html_e( 'Generate Shortcode', 'forminator' ); ?></span>
												</button>

											</div>

										</div>

										<!-- Quizzes -->
										<div id="forminator-quizzes">

											<div class="sui-form-field">

												<label for="forminator-select-forms" class="sui-label"><?php esc_html_e( 'Choose an option', 'forminator' ); ?></label>

												<?php echo $this->get_quizzes(); ?>

												<span class="sui-error-message" style="display: none;"><?php esc_html_e( 'Please, select an option before you proceed.', 'forminator' ); ?></span>

											</div>

											<div class="fui-simulate-footer">

												<button class="sui-button sui-button-blue wpmudev-insert-quiz">
													<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
													<span class="sui-loading-text"><?php esc_html_e( 'Generate Shortcode', 'forminator' ); ?></span>
												</button>

											</div>

										</div>

									</div>

								<?php endif; ?>

							</div>

						</div>

					</div>

				</div>

			</div>

		</div>
		<?php
	}

	/**
	 * Print forms select
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_forms() {

		$html        = '';
		$modules     = Forminator_API::get_forms( null, 1, 999 );
		$search      = ( count( $modules ) > 4 ) ? 'true' : 'false';
		$placeholder = __( 'Select Custom Form', 'forminator' );

		$html .= '<select id="forminator-select-forms" name="forms" class="sui-select forminator-custom-form-list" data-placeholder="' . $placeholder . '" data-search="' . $search . '">';

			$html .= '<option></option>';

			foreach ( $modules as $module ) {
				$module = (array) $module;

				$title = forminator_get_form_name( $module['id'] );

				if ( mb_strlen( $title ) > 25 ) {
					$title = mb_substr( $title, 0, 25 ) . '...';
				}

				$html .= '<option value="' . $module['id'] . '">' . $title . ' - ID: ' . $module['id'] . '</option>';

			}

		$html .= '</select>';

		return $html;

	}

	/**
	 * Print polls select
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_polls() {

		$html        = '';
		$modules     = Forminator_API::get_polls( null, 1, 999 );
		$search      = ( count( $modules ) > 4 ) ? 'true' : 'false';
		$placeholder = __( 'Select Poll', 'forminator' );

		$html .= '<select id="forminator-select-polls" name="forms" class="sui-select forminator-insert-poll" data-placeholder="' . $placeholder . '" data-search="' . $search . '">';

			$html .= '<option></option>';

			foreach ( $modules as $module ) {
				$module = (array) $module;

				$title = forminator_get_form_name( $module['id'] );

				if ( mb_strlen( $title ) > 25 ) {
					$title = mb_substr( $title, 0, 25 ) . '...';
				}

				$html .= '<option value="' . $module['id'] . '">' . $title . ' - ID: ' . $module['id'] . '</option>';

			}

		$html .= '</select>';

		return $html;
	}

	/**
	 * Print quizzes select
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_quizzes() {

		$html        = '';
		$modules     = Forminator_API::get_quizzes( null, 1, 999 );
		$search      = ( count( $modules ) > 4 ) ? 'true' : 'false';
		$placeholder = __( 'Select Quiz', 'forminator' );

		$html .= '<select id="forminator-select-quizzes" name="forms" class="sui-select forminator-quiz-list" data-placeholder="' . $placeholder . '" data-search="' . $search . '">';

			$html .= '<option value="">' . __( 'Select Quiz', 'forminator' ) . '</option>';

			foreach ( $modules as $module ) {
				$module = (array) $module;

				$title = forminator_get_form_name( $module['id'] );

				if ( mb_strlen( $title ) > 25 ) {
					$title = mb_substr( $title, 0, 25 ) . '...';
				}

				$html .= '<option value="' . $module['id'] . '">' . $title . ' - ID: ' . $module['id'] . '</option>';

			}

		$html .= '</select>';

		return $html;

	}
}
