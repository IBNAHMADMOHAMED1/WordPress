<?php

/**
 * Forminator_Poll_Front_Mail
 *
 * @since 1.6.1
 */
class Forminator_Poll_Front_Mail extends Forminator_Mail {

	/**
	 * Module slug
	 *
	 * @var string
	 */
	protected static $module_slug = 'poll';

	/**
	 * Replace all placeholders for mail properties
	 *
	 * @param array  $settings Settings.
	 * @param string $option_name Current option name.
	 * @param object $module Module.
	 * @param array  $data Submitted data.
	 * @param object $entry Saved entry.
	 * @return string
	 */
	private function replace_placeholders( $settings, $option_name, $module, $data, $entry ) {
		if ( ! isset( $settings[ $option_name ] ) ) {
			return '';
		}
		$text = forminator_replace_variables( $settings[ $option_name ], $module->id, $data['current_url'] );
		$text = forminator_replace_poll_form_data( $text, $module, $data, $entry );

		return $text;
	}

	/**
	 * Process mail
	 *
	 * @since 1.6.1
	 *
	 * @param Forminator_Poll_Model       $poll
	 * @param array                       $data
	 * @param Forminator_Form_Entry_Model $entry
	 */
	public function process_mail( $poll, $data, Forminator_Form_Entry_Model $entry ) {
		$setting = $poll->settings;

		if ( empty( $data['current_url'] ) ) {
			$data['current_url'] = forminator_get_current_url();
		}

		/**
		 * Message data filter
		 *
		 * @since 1.6.1
		 *
		 * @param array                       $data - the post data.
		 * @param Forminator_Poll_Model  $poll - the poll model.
		 * @param Forminator_Form_Entry_Model $entry
		 *
		 * @return array $data
		 */
		$data = apply_filters( 'forminator_poll_mail_data', $data, $poll, $entry );

		/**
		 * Action called before mail is sent
		 *
		 * @param Forminator_Poll_Model  $this - the current poll.
		 * @param Forminator_Poll_Model  $poll - the current poll.
		 * @param array                       $data - current data.
		 * @param Forminator_Form_Entry_Model $entry
		 */
		do_action( 'forminator_poll_mail_before_send_mail', $this, $poll, $data, $entry );

		// Process admin mail.
		if ( $this->is_send_admin_mail( $setting ) ) {
			$this->init();
			$recipients = $this->get_admin_email_recipients( $setting, $data, $poll, $entry );

			/**
			 * Custom form admin mail recipients filter
			 *
			 * @since 1.6.1
			 *
			 * @param array                      $recipients
			 * @param Forminator_Poll_Model $poll the current poll.
			 *
			 * @return array $recipients
			 */
			$recipients = apply_filters_deprecated(
				'forminator_poll_mail_admin_recipients',
				array( $recipients, $poll, $data, $entry, $this ),
				'1.6.2',
				'forminator_poll_get_admin_email_recipients'
			);

			if ( ! empty( $recipients ) ) {
				$subject = $this->replace_placeholders( $setting, 'admin-email-title', $poll, $data, $entry );

				/**
				 * Poll subject filter
				 *
				 * @since 1.6.1
				 *
				 * @param string                     $subject
				 * @param Forminator_Poll_Model $poll the current poll.
				 *
				 * @return string $subject
				 */
				$subject = apply_filters( 'forminator_poll_mail_admin_subject', $subject, $poll, $data, $entry, $this );

				$message = $this->replace_placeholders( $setting, 'admin-email-editor', $poll, $data, $entry );

				/**
				 * Poll mail message filter
				 *
				 * @since 1.6.1
				 *
				 * @param string                     $message
				 * @param Forminator_Poll_Model $poll the current poll.
				 * @param array                      $data
				 * @param Forminator_Poll_Front_Mail $this
				 *
				 * @return string $message
				 */
				$message = apply_filters( 'forminator_poll_mail_admin_message', $message, $poll, $data, $entry, $this );

				$headers = $this->prepare_headers( $setting, $poll, $data, $entry );
				$this->set_headers( $headers );

				$this->set_subject( $subject );
				$this->set_recipients( $recipients );
				$this->set_message_with_vars( $this->message_vars, $message );
				$this->send_multiple();

				/**
				 * Action called after admin mail sent
				 *
				 * @param Forminator_Poll_Front_Mail - the current poll
				 * @param Forminator_Poll_Model - the current poll
				 * @param array                       $data       - current data.
				 * @param Forminator_Form_Entry_Model $entry      - saved entry.
				 * @param array                       $recipients - array or recipients.
				 */
				do_action( 'forminator_poll_mail_admin_sent', $this, $poll, $data, $entry, $recipients );
			}
		}

		/**
		 * Action called after mail is sent
		 *
		 * @param Forminator_Poll_Front_Mail - the current poll
		 * @param Forminator_Poll_Model - the current poll
		 * @param array $data - current data.
		 */
		do_action( 'forminator_poll_mail_after_send_mail', $this, $poll, $data );
	}

	/**
	 * Prepare headers.
	 *
	 * @param array  $setting Settings.
	 * @param object $poll Module.
	 * @param array  $data Submitted data.
	 * @param object $entry Saved entry.
	 * @return array
	 */
	private function prepare_headers( $setting, $poll, $data, $entry ) {
		$from_name = $this->replace_placeholders( $setting, 'admin-email-from-name', $poll, $data, $entry );
		if ( empty( $from_name ) ) {
			$from_name = $this->sender_name;
		}

		/**
		 * Filter `From` name of mail that send to admin
		 *
		 * @since 1.6.1
		 *
		 * @param string                      $from_name
		 * @param Forminator_Poll_Model  $poll  current poll Model.
		 * @param array                       $data  POST data.
		 * @param Forminator_Form_Entry_Model $entry entry model.
		 * @param Forminator_Poll_Front_Mail  $this  mail class.
		 */
		$from_name = apply_filters( 'forminator_poll_mail_admin_from_name', $from_name, $poll, $data, $entry, $this );

		$from_email = $this->replace_placeholders( $setting, 'admin-email-from-address', $poll, $data, $entry );
		if ( ! is_email( $from_email ) ) {
			$from_email = $this->sender_email;
		}

		/**
		 * Filter `From` email address of mail that send to admin
		 *
		 * @since 1.6.1
		 *
		 * @param string                      $from_email
		 * @param Forminator_Poll_Model  $poll  current poll Model.
		 * @param array                       $data  POST data.
		 * @param Forminator_Form_Entry_Model $entry entry model.
		 * @param Forminator_Poll_Front_Mail  $this  mail class.
		 */
		$from_email = apply_filters( 'forminator_poll_mail_admin_from_email', $from_email, $poll, $data, $entry, $this );

		$reply_to_address = $this->replace_placeholders( $setting, 'admin-email-reply-to-address', $poll, $data, $entry );
		if ( ! is_email( $reply_to_address ) ) {
			$reply_to_address = '';
		}

		/**
		 * Filter `Reply To` email address of mail that send to admin
		 *
		 * @since 1.6.1
		 *
		 * @param string                      $reply_to_address
		 * @param Forminator_Poll_Model  $poll  current poll Model.
		 * @param array                       $data  POST data.
		 * @param Forminator_Form_Entry_Model $entry entry model.
		 * @param Forminator_Poll_Front_Mail  $this  mail class.
		 */
		$reply_to_address = apply_filters( 'forminator_poll_mail_admin_reply_to', $reply_to_address, $poll, $data, $entry, $this );

		$cc_addresses = array();
		if ( isset( $setting['admin-email-cc-address'] ) && ! empty( $setting['admin-email-cc-address'] ) && is_array( $setting['admin-email-cc-address'] ) ) {
			$setting_cc_addresses = $setting['admin-email-cc-address'];

			foreach ( $setting_cc_addresses as $key => $setting_cc_address ) {
				$setting_cc_address = $this->replace_placeholders( $setting_cc_addresses, $key, $poll, $data, $entry );
				if ( is_email( $setting_cc_address ) ) {
					$cc_addresses[] = $setting_cc_address;
				}
			}
		}
		/**
		 * Filter `CC` email addresses of mail that send to admin
		 *
		 * @since 1.6.1
		 *
		 * @param array                       $cc_addresses
		 * @param Forminator_Poll_Model  $poll  current poll Model.
		 * @param array                       $data  POST data.
		 * @param Forminator_Form_Entry_Model $entry entry model.
		 * @param Forminator_Poll_Front_Mail  $this  mail class.
		 */
		$cc_addresses = apply_filters( 'forminator_poll_mail_admin_cc_addresses', $cc_addresses, $poll, $data, $entry, $this );

		$bcc_addresses = array();
		if ( isset( $setting['admin-email-bcc-address'] ) && ! empty( $setting['admin-email-bcc-address'] ) && is_array( $setting['admin-email-bcc-address'] ) ) {
			$setting_bcc_addresses = $setting['admin-email-bcc-address'];

			foreach ( $setting_bcc_addresses as $key => $setting_bcc_address ) {
				$setting_bcc_address = $this->replace_placeholders( $setting_bcc_addresses, $key, $poll, $data, $entry );
				if ( is_email( $setting_bcc_address ) ) {
					$bcc_addresses[] = $setting_bcc_address;
				}
			}
		}
		/**
		 * Filter `BCC` email addresses of mail that send to admin
		 *
		 * @since 1.6.1
		 *
		 * @param array                       $bcc_addresses
		 * @param Forminator_Poll_Model  $poll  current poll Model.
		 * @param array                       $data  POST data.
		 * @param Forminator_Form_Entry_Model $entry entry model.
		 * @param Forminator_Poll_Front_Mail  $this  mail class.
		 */
		$bcc_addresses = apply_filters( 'forminator_poll_mail_admin_bcc_addresses', $bcc_addresses, $poll, $data, $entry, $this );

		$content_type = $this->content_type;
		/**
		 * Filter `Content-Type` of mail that send to admin
		 *
		 * @since 1.6.1
		 *
		 * @param string                      $content_type
		 * @param Forminator_Poll_Model  $poll  current poll Model.
		 * @param array                       $data  POST data.
		 * @param Forminator_Form_Entry_Model $entry entry model.
		 * @param Forminator_Poll_Front_Mail  $this  mail class.
		 */
		$content_type = apply_filters( 'forminator_poll_mail_admin_content_type', $content_type, $poll, $data, $entry, $this );

		$headers = array();

		// only change From header if these two are valid.
		if ( ! empty( $from_name ) && ! empty( $from_email ) ) {
			$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
		}

		if ( ! empty( $reply_to_address ) ) {
			$headers[] = 'Reply-To: ' . $reply_to_address;
		}

		if ( ! empty( $cc_addresses ) && is_array( $cc_addresses ) ) {
			$headers[] = 'Cc: ' . implode( ', ', $cc_addresses );
		}

		if ( ! empty( $bcc_addresses ) && is_array( $bcc_addresses ) ) {
			$headers[] = 'Bcc: ' . implode( ', ', $bcc_addresses );
		}

		if ( ! empty( $content_type ) ) {
			$headers[] = 'Content-Type: ' . $content_type;
		}

		/**
		 * Filter headers of mail that send to admin
		 *
		 * @since 1.6.1
		 *
		 * @param array                       $headers
		 * @param Forminator_Poll_Model  $poll  current poll Model.
		 * @param array                       $data  POST data.
		 * @param Forminator_Form_Entry_Model $entry entry model.
		 * @param Forminator_Poll_Front_Mail  $this  mail class.
		 */
		$headers = apply_filters( 'forminator_poll_mail_admin_headers', $headers, $poll, $data, $entry, $this );

		return $headers;
	}

	/**
	 * Get Recipients of admin emails
	 *
	 * @since 1.6.1
	 * @since 1.6.2 add $data, $poll model, $entry
	 *
	 * @param array                       $setting backward compat param.
	 * @param array                       $data
	 * @param Forminator_Poll_Model       $poll
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @return array
	 */
	public function get_admin_email_recipients( $setting, $data = array(), $poll = null, $entry = null, $pseudo_submitted_data = array(), $lead_model = array() ) {

		// use settings from model if applicable.
		if ( $poll instanceof Forminator_Poll_Model ) {
			$setting = $poll->settings;
		}
		$email = array();
		if ( isset( $setting['admin-email-recipients'] ) && ! empty( $setting['admin-email-recipients'] ) ) {
			if ( is_array( $setting['admin-email-recipients'] ) ) {
				$email = $setting['admin-email-recipients'];
			}
		}

		return apply_filters( 'forminator_poll_get_admin_email_recipients', $email, $setting, $data, $poll, $entry );
	}

}
