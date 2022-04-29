<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * User data for registration and login forms
 *
 * @since 1.11
 */
class Forminator_CForm_User_Data {

	public function __construct() {
		if ( is_admin() ) {
			// Handle user popups.
			add_action( 'wp_ajax_forminator_approve_user_popup', array( $this, 'approve_user' ) );
			add_action( 'wp_ajax_forminator_delete_unconfirmed_user_popup', array( $this, 'delete_unconfirmed_user' ) );
			// Change submission entries.
			add_filter( 'forminator_custom_form_entries_iterator', array( $this, 'change_entries_iterator' ), 11, 2 );
			// Resend activation link.
			add_action( 'wp_ajax_forminator_resend_activation_link', array( $this, 'resend_activation_link' ) );
			// Delete user signup.
			if ( ! is_multisite() ) {
				add_action( 'delete_user', array( $this, 'delete_signup_user' ) );
			}
		} else {
			// Approve user.
			add_action( 'wp', array( $this, 'admin_approve_user_by_link' ) );
		}
	}

	/**
	 * Approve user
	 *
	 * @return string JSON
	 */
	public function approve_user() {
		forminator_validate_ajax( 'forminatorFormEntries' );

		$activation_key = Forminator_Core::sanitize_text_field( 'activation_key' );
		if ( $activation_key ) {
			try {
				require_once __DIR__ . '/class-forminator-cform-user-signups.php';

				$userdata = Forminator_CForm_User_Signups::activate_signup( $activation_key, true );
				if ( is_wp_error( $userdata ) ) {
					throw new Exception( $userdata->get_error_message() );
				}

				wp_send_json_success();

			} catch ( Exception $e ) {
				wp_send_json_error( $e->getMessage() );
			}
		} else {
			wp_send_json_error( __( 'Invalid activation key.', 'forminator' ) );
		}
	}

	/**
	 * Delete unconfirmed user
	 *
	 * @return string JSON
	 */
	public function delete_unconfirmed_user() {
		forminator_validate_ajax( 'forminatorFormEntries' );

		$activation_key = Forminator_Core::sanitize_text_field( 'activation_key' );
		if ( $activation_key ) {
			try {
				require_once __DIR__ . '/class-forminator-cform-user-signups.php';

				$result = Forminator_CForm_User_Signups::delete_signup( $activation_key );

				if ( is_wp_error( $result ) ) {
					throw new Exception( $result->get_error_message() );
				}

				$entry_id = Forminator_Core::sanitize_text_field( 'entry_id' );
				if ( ! $entry_id ) {
					wp_send_json_error( __( 'Invalid entry ID.', 'forminator' ) );
				}
				$form_id = Forminator_Core::sanitize_text_field( 'form_id' );
				if ( ! $form_id ) {
					wp_send_json_error( __( 'Invalid form ID.', 'forminator' ) );
				}

				if ( false === Forminator_Form_Entry_Model::delete_by_entrys( $form_id, $entry_id ) ) {
					wp_send_json_error( __( 'Error! Entry was not deleted.', 'forminator' ) );
				}

				wp_send_json_success();

			} catch ( Exception $e ) {
				wp_send_json_error( $e->getMessage() );
			}
		} else {
			wp_send_json_error( __( 'Invalid activation key.', 'forminator' ) );
		}
	}

	/**
	 * Change submission entries.
	 *
	 * @param array                       $iterator
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @return array
	 */
	public function change_entries_iterator( $iterator, $entry ) {
		$activation_method = $entry->get_meta( 'activation_method', '' );
		// Add entry's iterators for forms with Email and Manual user activation methods.
		if ( isset( $activation_method ) && '' !== $activation_method ) {
			$activation_key = $entry->get_meta( 'activation_key', '' );
			if ( false !== $activation_key && '' !== $activation_key ) {
				require_once __DIR__ . '/class-forminator-cform-user-signups.php';

				if ( ! is_null( Forminator_CForm_User_Signups::get_pending_activations( $activation_key ) ) ) {
					$iterator['activation_key'] = $activation_key;
				}
				$iterator['activation_method'] = $activation_method;
			}
		}

		return $iterator;
	}

	/**
	 * Resend activation link of entry
	 *
	 * @param array                       $iterator
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @return array
	 */
	public function resend_activation_link() {
		forminator_validate_ajax( 'forminatorResendActivation' );
		$key = Forminator_Core::sanitize_text_field( 'key' );

		if ( isset( $key ) ) {
			global $wpdb;
			$url = add_query_arg(
				array(
					'page' => 'forminator_activation',
					'key'  => $key,
				),
				home_url( '/' )
			);

			$signup    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->base_prefix}signups WHERE activation_key = %s", $key ) );
			$username  = $signup->user_login;
			$recipient = $signup->user_email;

			$urlparts = parse_url( home_url() );
			$domain   = $urlparts['host'];
			$headers  = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . html_entity_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . ' <admin@' . $domain . '>',
			);

			$subject  = sprintf( esc_html__( 'Activation link for %s', 'forminator' ), $username );
			$message  = '<p>' . esc_html__( 'To activate your user account, please click the following link:', 'forminator' ) . '</p>';
			$message .= '<p>' . esc_url_raw( $url ) . '</p>';
			$message .= '<p>' . esc_html__( 'After you activate, you will receive *another email* with your login.', 'forminator' ) . '</p>';

			if ( ! empty( $recipient ) && ! empty( $subject ) && ! empty( $message ) ) {
				$sent = wp_mail( $recipient, $subject, $message, $headers );
			}

			if ( ! empty( $sent ) ) {
				wp_send_json_success( esc_html__( 'Activation link has been sent successfully.', 'forminator' ) );
			} else {
				wp_send_json_error( esc_html__( 'Failed to send activation email.', 'forminator' ) );
			}
		} else {
			wp_send_json_error( esc_html__( 'Activation key is not set.', 'forminator' ) );
		}
	}

	/**
	 * Approve user by link
	 */
	public function admin_approve_user_by_link() {
		$activation_key = Forminator_Core::sanitize_text_field( 'key' );
		$page           = Forminator_Core::sanitize_text_field( 'page' );
		if ( 'forminator_activation' === $page && $activation_key ) {
			require_once __DIR__ . '/class-forminator-cform-user-signups.php';

			$userdata = Forminator_CForm_User_Signups::activate_signup( $activation_key, false );

			if ( ! is_wp_error( $userdata ) ) {
				// For Email-activation.
				if ( isset( $userdata['redirect_page'] ) ) {
					wp_redirect( get_permalink( $userdata['redirect_page'] ) );
				} elseif (
					current_user_can( 'manage_options' ) &&
					isset( $userdata['form_id'] ) && ! empty( $userdata['form_id'] ) &&
					isset( $userdata['entry_id'] ) && ! empty( $userdata['entry_id'] )
				) {
					wp_redirect( admin_url( 'admin.php?page=forminator-entries&form_type=forminator_forms&form_id=' . $userdata['form_id'] . '&entry_id=' . $userdata['entry_id'] ) );
				}
				exit();

			} else {
				wp_die( wp_kses_post( $userdata->get_error_message() ) );
			}
		}
	}

	/**
	 * Delete user signup.
	 *
	 * @param int $user_id
	 */
	public function delete_signup_user( $user_id ) {
		$user = new WP_User( $user_id );
		require_once __DIR__ . '/class-forminator-cform-user-signups.php';

		Forminator_CForm_User_Signups::delete_by_user( 'user_email', $user->user_email );
	}
}
