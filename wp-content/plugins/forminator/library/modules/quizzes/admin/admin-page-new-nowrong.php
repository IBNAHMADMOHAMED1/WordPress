<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Quiz_New_NoWrong
 *
 * @since 1.0
 */
class Forminator_Quiz_New_NoWrong extends Forminator_Admin_Page {

	/**
	 * Return wizard title
	 *
	 * @since 1.0
	 */
	public function getWizardTitle() {
		$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
		if ( $id ) {
			return __( 'Edit Quiz', 'forminator' );
		} else {
			return __( 'New Quiz', 'forminator' );
		}
	}


	/**
	 * Add page screen hooks
	 *
	 * @since 1.6.2
	 * @param $hook
	 */
	public function enqueue_scripts( $hook ) {
		// Load admin scripts.
		wp_register_script(
			'forminator-admin',
			forminator_plugin_url() . 'assets/js/personality-scripts.js',
			array(
				'jquery',
				'wp-color-picker',
				'react',
				'react-dom',
			),
			FORMINATOR_VERSION,
			true
		);

		wp_enqueue_script( 'forminator-jquery-ui-touch', forminator_plugin_url() . 'assets/js/library/jquery.ui.touch-punch.min.js', array( 'jquery' ), FORMINATOR_VERSION, true );
		forminator_common_admin_enqueue_scripts( true );
	}
}
