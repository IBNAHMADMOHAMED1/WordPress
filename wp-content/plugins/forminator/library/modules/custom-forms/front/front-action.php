<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front ajax for custom forms
 *
 * @since 1.0
 */
class Forminator_CForm_Front_Action extends Forminator_Front_Action {

	/**
	 * Module slug
	 *
	 * @var string
	 */
	protected static $module_slug = 'form';

	/**
	 * Submit errors
	 *
	 * @var array
	 */
	private static $submit_errors = array();

	/**
	 * Hidden fields
	 *
	 * @var array
	 */
	private static $hidden_fields = array();

	/**
	 * Fields info
	 *
	 * @var array
	 */
	private static $info = array();

	/**
	 * Entry type
	 *
	 * @var string
	 */
	public $entry_type = 'custom-forms';

	/**
	 * Plugin instance
	 *
	 * @var null
	 */
	private static $instance = null;

	public function __construct() {
		parent::__construct();

		//Save entries
		if ( ! empty( $this->entry_type ) ) {
			add_action( 'wp_ajax_forminator_pp_create_order', array( $this, 'create_paypal_order' ) );
			add_action( 'wp_ajax_nopriv_forminator_pp_create_order', array( $this, 'create_paypal_order' ) );
		}
	}

	/**
	 * Return the plugin instance
	 *
	 * @since 1.0
	 * @return Forminator_Front_Action
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Create PayPal order
	 *
	 * @since 1.14.3
	 */
	public function create_paypal_order() {
		$body = trim( file_get_contents( 'php://input' ) );
		$data = json_decode( $body, true );

		// Check if form data is set
		if ( isset( $data['form_data'] ) && isset( $data['form_data']['purchase_units'] ) ) {

			// Check if payment amount is bigger than zero
			if ( floatval( $data['form_data']['purchase_units'][0]['amount']['value'] ) <= 0 ) {
				wp_send_json_error( esc_html__( 'The payment total must be greater than 0.', 'forminator' ) );
			}

			$amount = $data['form_data']['purchase_units'][0]['amount']['value'];
			$data['form_data']['purchase_units'][0]['amount']['value'] = number_format( (float) $amount, 2, '.', '' );

			$paypal = new Forminator_PayPal_Express();

			$request = array_merge( array( 'intent' => 'CAPTURE' ), $data['form_data'] );
			$request = apply_filters( 'forminator_paypal_create_order_request', $request, $data );

			$order = $paypal->create_order( $request, $data['mode'] );

			if ( is_wp_error( $order ) ) {
				wp_send_json_error( esc_html__( 'Cannot create a new order on PayPal. If the error persists, please contact us for further assistance.', 'forminator' ) );
			}

			$response = array(
				'order_id' => $order->id,
			);

			wp_send_json_success( $response );
		}
	}

	/**
	 * Update payment amount
	 *
	 * @since 1.7.3
	 */
	public function update_payment_amount() {
		$post_data = $this->get_post_data();
		$form_id   = isset( $post_data['form_id'] ) ? $post_data['form_id'] : false;

		if ( $form_id ) {
			$custom_form = Forminator_Form_Model::model()->load( $form_id );

			if ( is_object( $custom_form ) ) {
				$submitted_data = $post_data;

				$submitted_data = self::replace_hidden_field_values( $custom_form, $submitted_data );

				$pseudo_submitted_data = self::build_pseudo_submitted_data( $custom_form, $submitted_data );

				if ( $custom_form->has_stripe_field() ) {
					$fields        = $custom_form->get_fields();
					$field_classes = forminator_fields_to_array();

					foreach ( $fields as $field ) {
						$field_array = $field->to_formatted_array();
						$field_type  = isset( $field_array['type'] ) ? $field_array['type'] : '';

						if ( 'stripe' === $field_type ) {
							$field_id = Forminator_Field::get_property( 'element_id', $field_array );

							$forminator_stripe_field = isset( $field_classes[ $field_type ] ) ? $field_classes[ $field_type ] : null;

							if ( $forminator_stripe_field instanceof Forminator_Stripe ) {
								$forminator_stripe_field->update_paymentIntent(
									$submitted_data['paymentid'],
									$pseudo_submitted_data[ $field_id ],
									$submitted_data,
									$field_array,
									$pseudo_submitted_data,
									$custom_form
								);
							}

							// process only first stripe field
							break;
						}
					}
				} else {
					$response = array(
						'message' => __( "Error: Stripe field doesn't exist in your form!", 'forminator' ),
						'errors'  => array(),
					);
				}
			} else {
				$response = array(
					'message' => __( 'Error: Form object is corrupted!', 'forminator' ),
					'errors'  => array(),
				);
			}
		} else {
			$response = array(
				'message' => __( "Error: Your form ID doesn't exist!", 'forminator' ),
				'errors'  => array(),
			);
		}

		wp_send_json_error( $response );
	}

	/**
	 * Get default currency
	 *
	 * @return string
	 */
	private function get_default_currency() {
		try {
			$stripe = new Forminator_Gateway_Stripe();

			return $stripe->get_default_currency();

		} catch ( Forminator_Gateway_Exception $e ) {
			return 'USD';
		}
	}

	/**
	 * Check reCaptcha
	 *
	 * @param object $custom_form Model.
	 * @param array  $submitted_data Submitted data.
	 * @return string|null
	 */
	private static function check_captcha( $custom_form, $submitted_data ) {
		$form_id       = $custom_form->id;
		$field_forms   = forminator_fields_to_array();
		$captcha_field = $custom_form->get_captcha_field();
		if ( $captcha_field && isset( $field_forms['captcha'] ) && $field_forms['captcha'] instanceof Forminator_Captcha ) {
			$captcha_field_array   = $captcha_field->to_formatted_array();
			$field_id              = Forminator_Field::get_property( 'element_id', $captcha_field_array );
			$captcha_user_response = '';

			if ( isset( $submitted_data['g-recaptcha-response'] ) ) {
				$captcha_user_response = $submitted_data['g-recaptcha-response'];
			} elseif ( isset( $submitted_data['h-captcha-response'] ) ) {
				$captcha_user_response = $submitted_data['h-captcha-response'];
			}

			/**
			 * Filter captcha user response, default is from `g-recaptcha-response`
			 *
			 * @since 1.5.3
			 *
			 * @param string $captcha_user_response
			 * @param int $form_id
			 * @param array $submitted_data
			 *
			 * @return string captcha user response
			 */
			$captcha_user_response = apply_filters( 'forminator_captcha_user_response', $captcha_user_response, $form_id, $submitted_data );

			/** @var Forminator_Field $field_captcha_obj */
			$field_captcha_obj = $field_forms['captcha'];
			if ( $field_captcha_obj->is_available( $captcha_field_array ) ) {
				$field_captcha_obj->validate( $captcha_field_array, $captcha_user_response );

				$valid_response = $field_captcha_obj->is_valid_entry();
				if ( is_array( $valid_response ) && isset( $valid_response[ $field_id ] ) ) {
					return $valid_response[ $field_id ];
				}
			}
		}
	}

	/**
	 * Handle login if it's login form
	 *
	 * @param array  $setting Settings.
	 * @param object $custom_form Module.
	 * @param array  $submitted_data Submitted data.
	 * @param object $entry Entry.
	 * @param array  $field_data_array Field data.
	 * @return \WP_Error|boolean
	 */
	private static function maybe_login( $setting, $custom_form, $submitted_data, $entry, $field_data_array ) {
		if ( ! isset( $setting['form-type'] ) || 'login' !== $setting['form-type'] ) {
			return;
		}
		// Check who can login.
		if ( is_user_logged_in() ) {
			return;
		}

		$forminator_user_login = new Forminator_CForm_Front_User_Login();
		$login_user            = $forminator_user_login->process_login( $custom_form, $submitted_data, $entry, $field_data_array );
		if ( is_wp_error( $login_user['user'] ) ) {
			$message = $login_user['user']->get_error_message();

			return new WP_Error( 'forminator_authentication_failed', $message );
		}

		if ( ! empty( $login_user['authentication'] ) && 'invalid' === $login_user['authentication'] ) {
			self::$response_attrs['authentication'] = 'invalid';
			return new WP_Error( 'forminator_authentication_failed', __( 'Whoops, the passcode you entered was incorrect or expired.', 'forminator' ) );
		}

		if ( isset( $login_user['user']->ID ) ) {
			self::$response_attrs['user_id'] = $login_user['user']->ID;
		}
		if ( isset( $login_user['authentication'] ) ) {
			self::$response_attrs['authentication'] = $login_user['authentication'];
		}
		if ( isset( $login_user['auth_token'] ) ) {
			self::$response_attrs['auth_token'] = $login_user['auth_token'];
		}
		if ( isset( $login_user['auth_method'] ) ) {
			self::$response_attrs['auth_method'] = $login_user['auth_method'];
		}
        if ( isset( $login_user['auth_nav'] ) ) {
			self::$response_attrs['auth_nav'] = $login_user['auth_nav'];
		}
		if ( isset( $login_user['lost_url'] ) ) {
			self::$response_attrs['lost_url'] = $login_user['lost_url'];
		}

		return true;
	}

	/**
	 * Handle registration if it's registration form.
	 *
	 * @param array  $setting Settings.
	 * @param object $custom_form Module.
	 * @param array  $submitted_data Submitted data.
	 * @param object $entry Entry.
	 * @param array  $field_data_array Field data.
	 * @param array  $pseudo_submitted_data Pseudo submitted data.
	 * @return \WP_Error|boolean
	 */
	private static function maybe_registration( $setting, $custom_form, $submitted_data, $entry, $field_data_array, $pseudo_submitted_data ) {
		if ( isset( $setting['form-type'] ) && 'registration' === $setting['form-type'] ) {
			// Check who can register new users.
			if ( ! is_user_logged_in() ) {
				$can_creat_user = true;
			} elseif ( isset( $setting['hide-registration-form'] )
					&& '' === $setting['hide-registration-form']
			) {
				$can_creat_user = true;
			} else {
				$can_creat_user = false;
			}

			if ( ! $can_creat_user ) {
				return;
			}
			$form_id                      = $custom_form->id;
			$forminator_user_registration = new Forminator_CForm_Front_User_Registration();
			$registration_error           = $forminator_user_registration->process_validation( $custom_form, $submitted_data, $field_data_array, $pseudo_submitted_data );
			if ( true !== $registration_error ) {
				return new WP_Error( 'forminator_registration_failed', $registration_error );
			}

			$custom_error = apply_filters( 'forminator_custom_registration_form_errors', $registration_error, $form_id, $field_data_array );
			if ( true !== $custom_error ) {
				return new WP_Error( 'forminator_registration_failed', $custom_error );
			}

			$new_user_data = $forminator_user_registration->process_registration( $custom_form, $submitted_data, $entry );

			if ( ! is_array( $new_user_data ) ) {
				return new WP_Error( 'forminator_registration_failed', $new_user_data );
			}

			return true;
		}
	}

	/**
	 * Get fields info.
	 *
	 * @param object $custom_form Module.
	 * @param array  $fields Fields.
	 * @param array  $setting Settings.
	 * @param array  $submitted_data Submitted data.
	 * @param array  $pseudo_submitted_data Pseudo submitted data.
	 * @param int    $form_id form id
	 * @return type
	 */
	private function get_fields_info( $custom_form, $fields, $setting, $submitted_data, $pseudo_submitted_data, $form_id ) {
		$field_forms         = forminator_fields_to_array();
		$ignored_field_types = Forminator_Form_Entry_Model::ignored_fields();

		$field_data_array      = array();
		$field_suffix          = Forminator_Form_Entry_Model::field_suffix();
		$product_fields        = array();
		$calculation_fields    = array();
		$stripe_fields         = array();
		$paypal_fields         = false;
		$select_field_value    = array();
		$form_upload_data      = array();
		$postdata_fields       = array();
		$upload_in_customfield = array();

		if ( isset( $submitted_data['forminator-multifile-hidden'] ) ) {
			$form_upload_data = json_decode( stripslashes( $submitted_data['forminator-multifile-hidden'] ), true );
		}
		foreach ( $fields as $field_index => $field ) {
			$field_array = $field->to_formatted_array();
			$field_type  = $field_array['type'];
			if ( in_array( $field_type, $ignored_field_types, true ) ) {
				continue;
			}

			$is_hidden      = false;
			$form_field_obj = isset( $field_forms[ $field_type ] ) ? $field_forms[ $field_type ] : null;
			if ( $form_field_obj ) {
				$is_hidden = $form_field_obj->is_hidden( $field_array, $submitted_data, $pseudo_submitted_data, $custom_form, self::$hidden_fields );
				if ( 'stripe' === $field_type && ! $is_hidden ) {
					// Exclude stripe field, we will process later.
					$stripe_fields[] = $field_array;
					continue;
				}
			}

			// Store the hidden fields.
			if ( $is_hidden ) {
				self::$hidden_fields[] = $field->slug;
			}

			// Exclude calculation field, we will process later.
			if ( 'calculation' === $field_type ) {
				$calculation_fields[] = $field_array;
				continue;
			}

			// Exclude paypal field, we will process later.
			if ( 'paypal' === $field_type ) {
				$paypal_fields[] = $field_array;
				continue;
			}

			// Apply updated values to hidden-type fields after submission
			if ( 'hidden' === $field_type && ! empty( $field_array['element_id'] ) && ! empty( $field_array['default_value'] ) ) {

				if ( 'user_ip' === $field_array['default_value'] ) {
					$submitted_data[ $field_array['element_id'] ] = Forminator_Geo::get_user_ip();
				}

				if ( 'submission_time' === $field_array['default_value'] ) {
					$submitted_data[ $field_array['element_id'] ] = date_i18n( 'g:i:s a, T', forminator_local_timestamp(), true );
				}

			}

			if ( ! isset( $field->slug ) ) {
				continue;
			}
			$field_id   = Forminator_Field::get_property( 'element_id', $field_array );
			$field_data = array();
			$post_file  = false;

			if ( ! isset( $submitted_data[ $field_id ] ) ) {
				foreach ( $field_suffix as $suffix ) {
					$mod_field_id = $field_id . '-' . $suffix;
					if ( isset( $submitted_data[ $mod_field_id ] ) ) {
						$field_data[ $suffix ] = $submitted_data[ $mod_field_id ];
					} elseif ( isset( $_FILES[ $mod_field_id ] ) ) {
						if ( 'postdata' === $field_type && 'post-image' === $suffix ) {
							$post_file = $mod_field_id;
						}
					}
				}

				if ( 'postdata' === $field_type ) {
					$post_type     = Forminator_Field::get_property( 'post_type', $field_array, 'post' );
					$category_list = forminator_post_categories( $post_type );
					if ( ! empty( $category_list ) ) {
						foreach ( $category_list as $category ) {
							$mod_field_id = $field_id . '-' . $category['value'];
							if ( isset( $submitted_data[ $mod_field_id ] ) ) {
								$field_data[ $category['value'] ] = $submitted_data[ $mod_field_id ];
							}
						}
					}
					$custom_vars = Forminator_Field::get_property( 'post_custom_fields', $field_array );
					if ( ! empty( $custom_vars ) ) {
						$custom_meta = Forminator_Field::get_property( 'options', $field_array );
						if ( ! empty( $custom_meta ) ) {
							$i = 1;
							foreach ( $custom_meta as $meta ) {
								$value = ! empty( $meta['value'] ) ? trim( $meta['value'] ) : '';
								$label = $meta['label'];

								if ( strpos( $value, '{' ) !== false && strpos( $value, '{upload' ) === false ) {
									$value = forminator_replace_form_data( $value, $submitted_data, $custom_form );
									$value = forminator_replace_variables( $value, $form_id );
								} elseif ( isset( $submitted_data[ $value ] ) ) {
									$value = $submitted_data[ $value ];
								}

								// Store data that will be used later by upload fields.
								if ( strpos( $value, '{upload' ) !== false ) {
									$upload_in_customfield[] = array(
										'postdata_id' => $field_id,
										'upload_id'   => trim( $value, '{}' ),
										'uploads'     => '',
									);
								}

								$field_data['post-custom'][] = array(
									'key'   => $label,
									'value' => $value,
								);
								$i ++;
							}
						}
					}
				}
			} else {
				$field_data = $submitted_data[ $field_id ];
			}

			if ( $form_field_obj ) {

				// is conditionally hidden go to next field.
				if ( $form_field_obj->is_hidden( $field_array, $submitted_data, $pseudo_submitted_data, $custom_form, self::$hidden_fields ) ) {
					continue;
				}

				if ( 'upload' === $field_type ) {
					$file_type     = Forminator_Field::get_property( 'file-type', $field_array, 'single' );
					$upload_method = Forminator_Field::get_property( 'upload-method', $field_array, 'ajax' );
					/** @var  Forminator_Upload $form_field_obj */
					if ( 'multiple' === $file_type && 'ajax' === $upload_method ) {
						$upload_multiple_data = isset( $form_upload_data[ $field->slug ] ) ? $form_upload_data[ $field->slug ] : array();
						$upload_data          = $form_field_obj->handle_ajax_multifile_upload( $upload_multiple_data, $field_array );
					} elseif ( 'multiple' === $file_type && 'submission' === $upload_method ) {
						$upload_multiple_data = isset( $_FILES[ $field->slug ] ) ? $_FILES[ $field->slug ] : array();
						$upload_data          = $form_field_obj->handle_submission_multifile_upload( $field_array, $upload_multiple_data );
					} else {
						$upload_data = $form_field_obj->handle_file_upload( $field_array );
					}
					if ( isset( $upload_data['success'] ) && $upload_data['success'] ) {
						$field_data['file'] = $upload_data;

						// If upload is successful, add the upload data to custom field if tag is present.
						if ( ! empty( $upload_in_customfield ) ) {
							$file_url = $upload_data['file_url'];
							if ( 'multiple' === $file_type ) {
								$file_url = implode( ', ', $upload_data['file_url'] );
							}

							foreach ( $upload_in_customfield as $cf_key => $cf ) {
								if ( $field_id === $cf['upload_id'] ) {
									$upload_in_customfield[ $cf_key ]['uploads'] = $file_url;
								}
							}
						}
					} elseif ( isset( $upload_data['success'] ) && false === $upload_data['success'] ) {
						$error = isset( $upload_data['message'] ) ? $upload_data['message'] : $this->get_invalid_form_message( $setting, $form_id );

						self::$submit_errors[][ $field->slug ] = $error;
					} else {
						// no file uploaded for this field_id.
						$field_data = '';
					}
				}
				if ( 'postdata' === $field_type ) {
					if ( $post_file ) {
						$post_image = $form_field_obj->upload_post_image( $field_array, $post_file );
						if ( is_array( $post_image ) && $post_image['attachment_id'] > 0 ) {
							$field_data['post-image'] = $post_image;
						} else {
							$field_data['post-image'] = '';
						}
					}
				}
				if ( 'url' === $field_type ) {
					$field_data = $form_field_obj->add_scheme_url( $field_data );
				}

				if ( 'select' === $field_type ) {
					$is_limit = Forminator_Field::get_property( 'limit_status', $field_array );
					if ( isset( $is_limit ) && 'enable' === $is_limit ) {
						$options      = Forminator_Field::get_property( 'options', $field_array );
						$value_type   = Forminator_Field::get_property( 'value_type', $field_array );
						$select_array = is_array( $submitted_data[ $field_id ] ) ? $submitted_data[ $field_id ] : array( $submitted_data[ $field_id ] );
						foreach ( $options as $o => $option ) {
							if ( in_array( $option['value'], $select_array ) ) {
								$select_field_value[ $field_id ][ $o ]['limit'] = $option['limit'];
								$select_field_value[ $field_id ][ $o ]['value'] = $option['value'];
								$select_field_value[ $field_id ][ $o ]['label'] = $option['label'];
								$select_field_value[ $field_id ][ $o ]['type']  = $value_type;
							}
						}
					}
				}

				/**
				 * Filter handle specific field types
				 *
				 * @since 1.13
				 *
				 * @param array  $field_data Field data
				 * @param object $form_field_obj Form field object
				 * @param array  $field_array field settings
				 *
				 * @return array $field_data Set `return` element of the array as true for returning
				 */
				$field_data = apply_filters( 'forminator_handle_specific_field_types', $field_data, $form_field_obj, $field_array );

				if ( ! empty( $field_data['return'] ) ) {
					unset( $field_data['return'] );

					$field_data_array[] = $field_data;
					continue;
				}

				/**
				 * @since 1.0.5
				 * Load Autofill
				 */
				$form_field_obj->init_autofill( $setting );

				/**
				 * Sanitize data
				 *
				 * @since 1.0.2
				 *
				 * @param array $field
				 * @param array|string $data - the data to be sanitized.
				 */
				$field_data = $form_field_obj->sanitize( $field_array, $field_data );

				// Validate data when its available and not hidden on front end.
				if ( $form_field_obj->is_available( $field_array ) && ! $form_field_obj->is_hidden( $field_array, $submitted_data, $pseudo_submitted_data, $custom_form ) ) {

					/**
					 * @since 1.0.5
					 * Mayble re autofill, when autofill not editable, it should return autofill value
					 */
					$field_data = $form_field_obj->maybe_re_autofill( $field_array, $field_data, $setting );

					$form_field_obj->validate( $field_array, $field_data, $submitted_data );
				}
				$valid_response = $form_field_obj->is_valid_entry();

				if ( ! empty( $field_data ) || '0' === $field_data ) {
					if ( ! is_array( $valid_response ) ) {
						if ( 'postdata' === $field_type && ! $form_field_obj->is_hidden( $field_array, $submitted_data, $pseudo_submitted_data, $custom_form ) ) {
							// Store postdata' data here to be used later. This allows support for upload tags inside custom fields.
							$postdata_fields[] = array(
								'key'            => $field_index,
								'field_id'       => $field_id,
								'field_data'     => $field_data,
								'field_array'    => $field_array,
								'form_field_obj' => $form_field_obj,
							);
						}

						if ( 'date' === $field_type ) {

							if ( 'picker' !== $field->field_type ) {
								$date_format          = Forminator_Field::get_property( 'date_format', $field_array );
								$field_data['format'] = datepicker_default_format( $date_format );
							}

							/*
							 * If date field is not required and only year has value or
							 * if month, day, year are empty and field date type is input or select
							 * don't submit this field's data
							*/
							if (
								(
									'select' === $field->field_type &&
									(
										! empty( $field_data['year'] ) && empty( $field_data['day'] ) && empty( $field_data['month'] ) ||
										empty( $field_data['year'] ) && empty( $field_data['day'] ) && empty( $field_data['month'] )
									)
								)
								||
								(
									'input' === $field->field_type &&
									empty( $field_data['year'] ) && empty( $field_data['day'] ) && empty( $field_data['month'] )
								)
							) {
								continue;
							}
						}

						if ( 'product' === $field_type ) {
							$product_fields[] = array(
								'name'  => $field_id,
								'value' => $field_data,
							);
						}
						$field_data_array[] = array(
							'name'  => $field_id,
							'value' => $field_data,
						);
					} else {
						foreach ( $valid_response as $error_field => $error_response ) {
							self::$submit_errors[][ $error_field ] = $error_response;
						}
					}
				} else {
					if ( is_array( $valid_response ) && isset( $valid_response[ $field_id ] ) ) {
						self::$submit_errors[][ $field->slug ] = $valid_response[ $field_id ];
					}
				}
			}
		}

		self::$info = array(
			'calculation_fields'    => $calculation_fields,
			'stripe_fields'         => $stripe_fields,
			'paypal_fields'         => $paypal_fields,
			'field_data_array'      => $field_data_array,
			'submitted_data'        => $submitted_data,
			'select_field_value'    => $select_field_value,
			'product_fields'        => $product_fields,
			'postdata_fields'       => $postdata_fields,
			'upload_in_customfield' => $upload_in_customfield,
		);
	}

	/**
	 * Handle stripe single payment
	 *
	 * @since 1.15
	 *
	 * @param object $custom_form Model.
	 * @param array  $submitted_data Submitted data.
	 * @param array  $pseudo_submitted_data Pseudo submitted data.
	 * @param array  $field_data_array Field data.
	 * @param object $entry Entry.
	 *
	 * @return array|WP_ERROR
	 */
	private static function handle_stripe_subscription( $field_object, $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array, $entry, $payment_plan ) {
		if ( class_exists( 'Forminator_Stripe_Subscription' ) ) {
			try {
				$stripe_addon = Forminator_Stripe_Subscription::get_instance();
				$amount_type  = isset( $payment_plan['subscription_amount_type'] ) ? $payment_plan['subscription_amount_type'] : 'fixed';
				$amount       = isset( $payment_plan['subscription_amount'] ) ? $payment_plan['subscription_amount'] : 0.0;

				if ( 'fixed' === $amount_type && empty( $amount ) ) {
					throw new Exception( __( 'Payment amount should be larger than 0.', 'forminator' ) );
				}

				return $stripe_addon->handle_subscription( $field_object, $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array, $entry, $payment_plan );
			} catch ( Exception $e ) {
				// Delete entry if paymentIntent confirmation is not successful
				$entry->delete();

				return new WP_Error( 'forminator_stripe_error', $e->getMessage() );
			}
		}
	}

	/**
	 * Handle stripe single payment
	 *
	 * @since 1.15
	 *
	 * @param array  $field_object
	 * @param object $custom_form Model.
	 * @param array  $submitted_data Submitted data.
	 * @param array  $pseudo_submitted_data Pseudo submitted data.
	 * @param array  $field_data_array Field data.
	 * @param object $entry Entry.
	 * @param string $mode Stripe payment mode
	 *
	 * @return array|WP_ERROR
	 */
	private static function handle_stripe_single( $field_object, $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array, $entry, $mode ) {
		// Try to get Payment Intent from submitted date
		try {
			$intent = $field_object->get_paymentIntent( $field_data_array, $submitted_data );

			if ( is_wp_error( $intent ) ) {
				return $intent;
			}

			$result = $intent->confirm();
		} catch ( Exception $e ) {
			// Delete entry if paymentIntent confirmation is not successful
			$entry->delete();

			return new WP_Error( 'forminator_stripe_error', $e->getMessage() );
		}

		// If we have 3D security on the card return for verification
		if ( 'requires_action' === $result->status ) {
			// Delete entry if 3d security is needed, we will store it on next attempt
			$entry->delete();

			self::$response_attrs['stripe3d'] = true;
			self::$response_attrs['secret']   = $result->client_secret;

			return new WP_Error( 'forminator_stripe_error', __( 'This payment require 3D Secure authentication! Please follow the instructions.', 'forminator' ) );
		}

		// Try to capture payment
		try {
			$capture = $intent->capture();
		} catch ( Exception $e ) {
			// Delete entry if capture is not successful
			$entry->delete();

			return new WP_Error( 'forminator_stripe_error', $e->getMessage() );
		}

		if ( isset( $capture->charges->data[0]->captured ) && $capture->charges->data[0]->captured === true ) {
			$transaction_link = 'https://dashboard.stripe.com/payments/' . rawurlencode( $intent->id );
			if ( 'test' === $mode ) {
				$transaction_link = 'https://dashboard.stripe.com/test/payments/' . rawurlencode( $intent->id );
			}

			return array(
				'status'           => 'COMPLETED',
				'transaction_id'   => $intent->id,
				'transaction_link' => $transaction_link,
			);
		} else {
			// Delete entry if capture is not successful
			$entry->delete();

			return new WP_Error( 'forminator_stripe_error', __( 'Payment failed, please try again!', 'forminator' ) );
		}
	}

	/**
	 * Handle stripe payments
	 *
	 * @param object $custom_form Model.
	 * @param array  $submitted_data Submitted data.
	 * @param array  $pseudo_submitted_data Pseudo submitted data.
	 * @param array  $field_data_array Field data.
	 * @param object $entry Entry.
	 * @return array
	 */
	private static function handle_stripe( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array, $entry ) {
		$stripe = new Forminator_Gateway_Stripe();

		if ( ! $stripe->is_ready() || ! self::$info['stripe_fields'] ) {
			return $field_data_array;
		}

		$field_data_array  = self::stripe_field_to_entry_data_array( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array );
		$fields_collection = forminator_fields_to_array();

		if ( ! isset( $fields_collection['stripe'] ) ) {
			return $field_data_array;
		}

		foreach ( self::$info['stripe_fields'] as $field ) {
			$element_id = isset( $field['element_id'] ) ? $field['element_id'] : false;
			$mode       = isset( $field['mode'] ) ? $field['mode'] : 'sandbox';

			$i = 0;
			foreach ( $field_data_array as $data ) {
				if ( $data['name'] === $element_id ) {
					$plan_data_array = array();
					$field_object    = $fields_collection['stripe'];

					$payment_plan = $field_object->get_payment_plan( $custom_form, $field, $submitted_data, $pseudo_submitted_data );

					if ( 'single' === $payment_plan['payment_method'] ) {
						$plan_data_array = self::handle_stripe_single( $field_object, $custom_form, $submitted_data, $pseudo_submitted_data, $field, $entry, $mode );
					} else {
						$plan_data_array = self::handle_stripe_subscription( $field_object, $custom_form, $submitted_data, $pseudo_submitted_data, $field, $entry, $payment_plan );
					}

					if ( is_wp_error( $plan_data_array ) ) {
						return $plan_data_array;
					}

					if ( 'subscription' !== $payment_plan['payment_method'] ) {
						$plan_data_array = array_merge( $field_data_array[ $i ]['value'], $plan_data_array );
					}

					$field_data_array[ $i ]['value'] = $plan_data_array;
				}

				$i++;
			}
		}

		return $field_data_array;
	}

	/**
	 * Handle paypal
	 *
	 * @param object $custom_form Model.
	 * @param array  $submitted_data Submitted data.
	 * @param array  $pseudo_submitted_data Pseudo submitted data.
	 * @param array  $field_data_array Field data.
	 * @param object $entry Entry.
	 * @return array
	 */
	private static function handle_paypal( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array, $entry ) {
		if ( ! self::$info['paypal_fields'] ) {
			return $field_data_array;
		}

		if ( $custom_form->is_payment_require_ssl() && ! is_ssl() ) {
			return new WP_Error(
				'forminator_paypal_error',
				apply_filters(
					'forminator_payment_require_ssl_error_message',
					__( 'SSL required to submit this form, please check your URL.', 'forminator' )
				)
			);
		}

		$field_data_array = self::paypal_field_to_entry_data_array( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array );

		$fields_collection = forminator_fields_to_array();
		if ( ! isset( $fields_collection['paypal'] ) ) {
			return $field_data_array;
		}
		foreach ( self::$info['paypal_fields'] as $field ) {
			$element_id = isset( $field['element_id'] ) ? $field['element_id'] : false;
			$mode       = isset( $field['mode'] ) ? $field['mode'] : 'sandbox';

			$i = 0;
			foreach ( $field_data_array as $data ) {
				if ( isset( $data['name'] ) && $data['name'] === $element_id ) {
					$paypal  = new Forminator_PayPal_Express();
					$capture = $paypal->capture_order( $submitted_data[ $element_id ], $mode );

					if ( isset( $capture->status ) && 'COMPLETED' === $capture->status ) {
						$field_data_array[ $i ]['value']['status'] = 'COMPLETED';

						if ( isset( $capture->purchase_units[0]->payments->captures[0]->id ) ) {
							$transaction_id = $capture->purchase_units[0]->payments->captures[0]->id;

							$field_data_array[ $i ]['value']['transaction_id'] = $transaction_id;
							$transaction_link                                  = 'https://www.paypal.com/activity/payment/' . rawurlencode( $transaction_id );

							if ( 'sandbox' === $mode ) {
								$transaction_link = 'https://www.sandbox.paypal.com/activity/payment/' . rawurlencode( $transaction_id );
							}

							$field_data_array[ $i ]['value']['transaction_link'] = $transaction_link;
						}
					} else {
						// Delete entry if capture is not successful
						$entry->delete();

						return new WP_Error( 'forminator_paypal_error', __( 'Payment failed, please try again!', 'forminator' ) );
					}
				}

				$i++;
			}
		}

		return $field_data_array;
	}


	/**
	 * Handle form
	 *
	 * @since 1.0
	 * @since 1.1 change superglobal POST to `get_post_data`
	 * @since 1.5.1 utilize `_post_data` which already defined on submit
	 *
	 * @param $form_id
	 * @param $preview
	 *
	 * @return array|bool
	 */
	public function handle_form( $form_id, $preview = false ) {
		self::$module_id       = $form_id;
		$submitted_data        = $this->_post_data;

		/** @var Forminator_Form_Model $custom_form */
		$custom_form = Forminator_Form_Model::model()->load( $form_id );
		if ( ! is_object( $custom_form ) ) {
			return false;
		}
		$setting     = $this->get_form_settings( $custom_form );
		$form_submit = $custom_form->form_can_submit();
		$form_type   = isset( $setting['form-type'] ) ? $setting['form-type'] : '';

		if ( ! $form_submit['can_submit'] ) {
			return self::return_error( $form_submit['error'] );
		}
		$entry             = new Forminator_Form_Entry_Model();
		$entry->entry_type = $this->entry_type;
		$entry->form_id    = $form_id;
		$fields            = $custom_form->get_fields();

		if ( is_null( $fields ) ) {
			return self::return_error( __( 'At least one field must be filled out to submit the form.', 'forminator' ) );
		}

		// Replace consent field's value to 'checked'.
		// $submitted_data = self::replace_consent_value( $submitted_data );.

		// Ignore captcha re-check if we have Stripe field.
		if ( ! $custom_form->has_stripe_field() ) {
			// verify captcha before any else.
			$captcha_invalid = $this->check_captcha( $custom_form, $submitted_data );
			if ( $captcha_invalid ) {
				return self::return_error( $captcha_invalid );
			}
		}

		$submitted_data = self::replace_hidden_field_values( $custom_form, $submitted_data );

		// build pseudo submit data first to later usage
		$pseudo_submitted_data = self::build_pseudo_submitted_data( $custom_form, $submitted_data );

		$this->get_fields_info( $custom_form, $fields, $setting, $submitted_data, $pseudo_submitted_data, $form_id );

		$field_data_array = self::$info['field_data_array'];
		$submitted_data   = self::$info['submitted_data'];

		$submitted_data = self::replace_hidden_field_values( $custom_form, $submitted_data );

		self::$submitted_data        = $submitted_data;
		self::$pseudo_submitted_data = $pseudo_submitted_data;

		// For login or registration forms.
		$is_login = self::maybe_login( $setting, $custom_form, $submitted_data, $entry, $field_data_array );
		if ( is_wp_error( $is_login ) ) {
			return self::return_error( $is_login->get_error_message() );
		} elseif ( $is_login ) {
			$field_data_array = self::remove_password( $field_data_array );
		}

		/**
		 * Filter submission errors
		 *
		 * @since 1.0.2
		 *
		 * @param array $submit_errors - the submission errors.
		 * @param int $form_id - the form id.
		 *
		 * @return array $submit_errors
		 */
		self::$submit_errors = apply_filters( 'forminator_custom_form_submit_errors', self::$submit_errors, $form_id, $field_data_array );
		if ( ! empty( self::$submit_errors ) ) {
			return self::return_error( $this->get_invalid_form_message( $setting, $form_id ), self::$submit_errors );
		}

		// Check honeypot.
		if ( isset( $setting['honeypot'] ) && filter_var( $setting['honeypot'], FILTER_VALIDATE_BOOLEAN ) ) {
			$total_fields = count( $fields ) + 1;
			if ( ! empty( $submitted_data[ "input_$total_fields" ] ) ) {
				// show success but dont save form.
				return self::return_success();
			}
		}

		/**
		 * Handle spam protection
		 * Add-ons use this filter to check if content has spam data
		 *
		 * @since 1.0.2
		 *
		 * @param bool false - defauls to false
		 * @param array $field_data_array - the entry data.
		 * @param int $form_id - the form id.
		 * @param string $form_type - the form type. In this case defaults to 'custom_form'.
		 *
		 * @return bool true|false
		 */
		$is_spam = apply_filters( 'forminator_spam_protection', false, $field_data_array, $form_id, 'custom_form' );
		if ( $is_spam ) {
			$fail_message = self::get_akismet_fail_message( $setting );
			if ( false !== $fail_message ) {
				return self::return_error( $fail_message );
			} else {
				$entry->is_spam = $is_spam;
			}
		}

		if ( empty( $field_data_array ) ) {
			return self::return_error( __( 'At least one field must be filled out to submit the form.', 'forminator' ) );
		}

		// If preview, skip integrations
		if ( ! $preview ) {
			//ADDON on_form_submit
			$addon_error = $this->attach_addons_on_form_submit( $form_id, $custom_form );

			if ( true !== $addon_error ) {
				return self::return_error( $addon_error );
			}
		}

		$prevent_store = $this->prevent_store( $custom_form, $form_type, $submitted_data, $preview );
		if ( ! $prevent_store ) {
			if ( 'leads' === $form_type && isset( $submitted_data['lead_quiz'] ) ) {
				$entry->entry_type = 'quizzes';
				$entry->form_id    = $submitted_data['lead_quiz'];
			}

			$entry->save();
		}
		if ( isset( $submitted_data['product-shipping'] ) && intval( $submitted_data['product-shipping'] > 0 ) ) {
			$field_data_array[] = array(
				'name'  => 'product_shipping',
				'value' => $submitted_data['product-shipping'],
			);
		}
		$field_data_array[] = array(
			'name'  => '_forminator_user_ip',
			'value' => Forminator_Geo::get_user_ip(),
		);
		// Calculation.
		$field_data_array = self::calculate_fields_to_entry_data_array( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array );

		// Stripe.
		$field_data_array = self::handle_stripe( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array, $entry );
		if ( is_wp_error( $field_data_array ) ) {
			return self::return_error( $field_data_array->get_error_message() );
		}

		// PayPal.
		$field_data_array = self::handle_paypal( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array, $entry );
		if ( is_wp_error( $field_data_array ) ) {
			return self::return_error( $field_data_array->get_error_message() );
		}

		/**
		 * Get saved postdata fields data and replace upload tags with uploaded data
		 * move to function ++++
		 */
		if ( ! empty( self::$info['postdata_fields'] ) ) {
			$postdata_return = $this->create_post_from_postdata( self::$info['postdata_fields'], self::$info['upload_in_customfield'] );

			if ( isset( $postdata_return['type'] ) && 'error' === $postdata_return['type'] ) {
				return self::return_error( $postdata_return['value'] );
			}

			foreach ( $postdata_return as $postdata ) {

				if ( 'success' === $postdata['type'] ) {

					foreach ( $field_data_array as $field_key => $field_datum ) {
						if ( $field_datum['name'] === $postdata['field_id'] ) {
							$field_data_array[ $field_key ] = array(
								'name'  => $postdata['field_id'],
								'value' => $postdata['field_data'],
							);
						}
					}
				} else {
					return self::return_error( $postdata['value'] );
				}
			}
		}

		$field_data_array_for_registration = $field_data_array;
		$field_data_array                  = self::remove_password( $field_data_array );

		/**
		 * Filter saved data before persisted into the database
		 *
		 * @since 1.0.2
		 *
		 * @param array $field_data_array - the entry data.
		 * @param int $form_id - the form id.
		 *
		 * @return array $field_data_array
		 */
		$field_data_array = apply_filters( 'forminator_custom_form_submit_field_data', $field_data_array, $form_id );

		/**
		 * Action called before setting fields to database
		 *
		 * @since 1.0.2
		 *
		 * @param Forminator_Form_Entry_Model $entry - the entry model.
		 * @param int $form_id - the form id.
		 * @param array $field_data_array - the entry data.
		 */
		do_action( 'forminator_custom_form_submit_before_set_fields', $entry, $form_id, $field_data_array );

		// ADDON add_entry_fields.
		// @since 1.2 Add field_data_array to param.
		$added_data_array = $this->attach_addons_add_entry_fields( $form_id, $custom_form, $field_data_array );
		$added_data_array = array_merge( $field_data_array, $added_data_array );
		$added_data_array = self::replace_values_to_labels( $added_data_array, $submitted_data, $custom_form, $entry );

		if ( 'leads' === $form_type ) {
			self::$response_attrs['entry_id'] = $entry->entry_id;

			$added_data_array[] = array(
				'name'  => 'skip_form',
				'value' => '0',
			);
		}

		$entry->set_fields( $added_data_array );

		//ADDON after_entry_saved.
		$this->attach_addons_after_entry_saved( $entry, $custom_form );

		// After $entry->set_fields() to get all data for {all_fields}.

		// If it's registration form.
		$registration = self::maybe_registration( $setting, $custom_form, $submitted_data, $entry, $field_data_array_for_registration, $pseudo_submitted_data );
		if ( is_wp_error( $registration ) ) {
			return self::return_error( $registration->get_error_message() );
		} elseif ( $registration ) {
			// Do not send emails later.
			$custom_form->notifications = array();
		}

		// send email.
		if ( 'leads' !== $form_type ) {
			$forminator_mail_sender = new Forminator_CForm_Front_Mail();
			$forminator_mail_sender->process_mail( $custom_form, $submitted_data, $entry, $pseudo_submitted_data );
		}

		self::set_behaviour_settings( $setting, $submitted_data, $pseudo_submitted_data, $custom_form, $entry, $form_id );
		$response = self::return_success();

		if ( ! isset( $setting['enable-ajax'] ) || empty( $setting['enable-ajax'] ) ) {
			$is_ajax_enabled = false;
		} else {
			$is_ajax_enabled = filter_var( $setting['enable-ajax'], FILTER_VALIDATE_BOOLEAN );
		}
		if ( $is_ajax_enabled && ! empty( self::$info['select_field_value'] ) ) {
			$response['select_field'] = self::get_limited_select_values( $form_id );
		}

		if ( ! empty( self::$info['product_fields'] ) ) {
			// Process purchase.
			$page_id  = $submitted_data['page_id']; // use page id to get permalink for redirect.
			$entry_id = $entry->entry_id;
			$shipping = isset( $submitted_data['product-shipping'] ) ? $submitted_data['product-shipping'] : 0;

			/**
			 * Process purchase
			 *
			 * @since 1.0.0
			 *
			 * @param array $response - the response array.
			 * @param array $product_fields - the product fields.
			 * @param int $entry_id - the entry id ( reference for callback).
			 * @param int $page_id - the page id. Used to generate a return url.
			 * @param int $shipping - the shipping cost.
			 */
			$response = apply_filters( 'forminator_cform_process_purchase', $response, self::$info['product_fields'], $field_data_array, $entry_id, $page_id, $shipping );
		}

		return $response;
	}

	/**
	 * Set behavior settings
	 *
	 * @param array  $setting Module settings.
	 * @param array  $submitted_data Submitted data.
	 * @param array  $pseudo_submitted_data Pseudo submitted data.
	 * @param object $custom_form Module.
	 * @param object $entry Entry.
	 * @param int    $form_id Module id.
	 */
	private static function set_behaviour_settings( $setting, $submitted_data, $pseudo_submitted_data, $custom_form, $entry, $form_id ) {
		$all_behaviours   = array( 'behaviour-thankyou', 'behaviour-hide', 'behaviour-redirect' );
		$behavior_options = self::get_relevant_behavior_options( $setting, $submitted_data, $pseudo_submitted_data, $custom_form );
		if ( isset( $behavior_options['submission-behaviour'] ) && in_array( $behavior_options['submission-behaviour'], $all_behaviours, true ) ) {
			self::$response_attrs['behav'] = $custom_form->get_submission_behaviour( $behavior_options );
			if ( 'behaviour-redirect' === $behavior_options['submission-behaviour'] && ! empty( $behavior_options['redirect-url'] ) ) {
				self::$response_attrs['redirect'] = true;
				// replace form data vars with value.
				$redirect_url = forminator_replace_form_data( $behavior_options['redirect-url'], $submitted_data, $custom_form, $entry );
				$tab_value    = isset( $behavior_options['newtab'] ) ? $behavior_options['newtab'] : 'sametab';
				$newtab       = forminator_replace_form_data( $tab_value, $submitted_data, $custom_form, $entry );
				// replace misc data vars with value.
				$redirect_url                   = forminator_replace_variables( $redirect_url, $form_id );
				$newtab                         = forminator_replace_variables( $newtab, $form_id );
				self::$response_attrs['url']    = esc_url_raw( $redirect_url );
				self::$response_attrs['newtab'] = esc_html( $newtab );
			}

			if ( ( ! isset( $tab_value ) || 'newtab_thankyou' === $tab_value ) && ! empty( $behavior_options['thankyou-message'] ) ) {
				/**
				 * Filter thankyou message
				 *
				 * @since 1.11
				 *
				 * @param string $behavior_options ['thankyou-message'].
				 * @param array $submitted_data
				 * @param Forminator_Form_Model $custom_form
				 *
				 * @return string
				 */
				$behavior_options['thankyou-message'] = apply_filters( 'forminator_custom_form_thankyou_message', $behavior_options['thankyou-message'], $submitted_data, $custom_form );
				// replace form data vars with value.
				$thankyou_message = forminator_replace_form_data( $behavior_options['thankyou-message'], $submitted_data, $custom_form, $entry, true );
				// replace misc data vars with value.
				$thankyou_message                = forminator_replace_variables( $thankyou_message, $form_id );
				self::$response_attrs['message'] = $thankyou_message;
				if ( ! empty( $behavior_options['autoclose'] ) ) {
					self::$response_attrs['fadeout']      = $behavior_options['autoclose'];
					self::$response_attrs['fadeout_time'] = ! empty( $behavior_options['autoclose-time'] )
							? $behavior_options['autoclose-time'] * 1000 : 0;
				}
			}
		}
	}

	/**
	 * Get the relevant behavior which will be applied according conditions
	 *
	 * @param array  $setting Settings.
	 * @param array  $submitted_data Submitted data.
	 * @param array  $pseudo_submitted_data Pseudo submitted data.
	 * @param object $module Module object.
	 * @return array|false Return the relevant behavior or false if no behavior found.
	 */
	private static function get_relevant_behavior_options( $setting, $submitted_data, $pseudo_submitted_data, $module ) {
		$behavior_array = Forminator_Form_Model::get_behavior_array( $module, $setting );

		foreach ( $behavior_array as $behavior ) {
			if ( empty( $behavior['conditions'] ) ) {
				// If this behavior doesn't have any conditions - return it.
				return $behavior;
			}
			$condition_rule      = isset( $behavior['condition_rule'] ) ? $behavior['condition_rule'] : 'all';
			$condition_fulfilled = 0;

			foreach ( $behavior['conditions'] as $condition ) {
				$is_matched = Forminator_Field::is_condition_matched( $condition, $submitted_data, $pseudo_submitted_data );
				if ( $is_matched ) {
					if ( 'any' === $condition_rule ) {
						// If this behavior is matched the conditions - return it. No need to check others.
						return $behavior;
					}
					$condition_fulfilled ++;
				}
			}
			if ( 'all' === $condition_rule && count( $behavior['conditions'] ) === $condition_fulfilled ) {
				// Return this behavior if all conditions are matched.
				return $behavior;
			}
		}

		// If all behaviors aren't matched - return false.
		return false;
	}

	/**
	 * Get select values according limit.
	 *
	 * @param int $form_id Module id.
	 * @return array
	 */
	private static function get_limited_select_values( $form_id ) {
		$result = array();
		foreach ( self::$info['select_field_value'] as $select_name => $select_field ) {
			$select_value = array();
			foreach ( $select_field as $s => $select ) {
				$entries = Forminator_Form_Entry_Model::select_count_entries_by_meta_field( $form_id, $select_name, $select['value'], $select['label'], $select['type'] );
				if ( ! empty( $select['limit'] ) && $select['limit'] <= $entries ) {
					$select_value[ $s ]['value'] = $select['value'];
					$select_value[ $s ]['type']  = $select['type'];
				}
			}
			if ( ! empty( $select_value ) ) {
				$result[ $select_name ] = array_values( $select_value );
			}
		}
		return $result;
	}

	/**
	 * Should it prevent storing submission?
	 *
	 * @param object $custom_form Module.
	 * @param string $form_type Module type.
	 * @param array  $submitted_data Submitted data.
	 * @param bool   $preview Is it preview or not.
	 * @return boolean
	 */
	private function prevent_store( $custom_form, $form_type, $submitted_data, $preview ) {
		if ( $preview ) {
			return true;
		}
		if ( 'leads' === $form_type && isset( $submitted_data['lead_quiz'] ) ) {
			$quiz_model = Forminator_Quiz_Model::model()->load( $submitted_data['lead_quiz'] );
			if ( isset( $quiz_model->settings ) ) {
				$prevent_store = $custom_form->is_prevent_store( $submitted_data['lead_quiz'], $quiz_model->settings );
			}
		} else {
			$prevent_store = $custom_form->is_prevent_store();
		}

		return $prevent_store;
	}

	/**
	 * Remove a password field.
	 *
	 * @param array $field_data_array Data.
	 * @return array
	 */
	public static function remove_password( $field_data_array ) {
		foreach ( $field_data_array as $key => $field_arr ) {
			if ( false !== stripos( $field_arr['name'], 'password-' ) ) {
				unset( $field_data_array[ $key ] );
				break;
			}
		}

		return $field_data_array;
	}

	/**
	 * Replace values to labels for radios, selectboxes and checkboxes
	 *
	 * @param type $data
	 * @param type $submitted_data
	 * @param type $custom_form
	 * @param type $entry
	 * @return type
	 */
	private static function replace_values_to_labels( $data, $submitted_data, $custom_form, $entry ) {
		foreach ( $data as $key => $value ) {
			if ( empty( $value['name'] ) ) {
				continue;
			}
			$slug = $value['name'];
			if ( strpos( $slug, 'radio' ) !== false
					|| strpos( $slug, 'select' ) !== false
					|| strpos( $slug, 'checkbox' ) !== false
					) {
				$data[ $key ]['value'] = forminator_replace_form_data( '{' . $slug . '}', $submitted_data, $custom_form, $entry, true );
			}
		}

		return $data;
	}

	/**
	 * Multiple File upload
	 */
	public function multiple_file_upload() {

		$response  = array();
		$post_data = $this->get_post_data();

		if ( ! isset( $post_data['nonce'] ) || ! wp_verify_nonce( $post_data['nonce'], 'forminator_submit_form' ) ) {
			wp_send_json_error( new WP_Error( 'invalid_code' ) );
		}
		$form_id = isset( $post_data['form_id'] ) ? $post_data['form_id'] : false;

		if ( $form_id ) {
			$custom_form = Forminator_Form_Model::model()->load( $form_id );
			if ( is_object( $custom_form ) ) {
				$fields      = $custom_form->get_fields();
				$field_forms = forminator_fields_to_array();
				foreach ( $fields as $field ) {
					$field_array = $field->to_formatted_array();
					$element_id  = isset( $field_array['element_id'] ) ? $field_array['element_id'] : '';
					$field_type  = isset( $field_array['type'] ) ? $field_array['type'] : '';
					if ( isset( $post_data['element_id'] ) && 'upload' === $field_type && $post_data['element_id'] === $element_id ) {
						$upload_field_obj = isset( $field_forms[ $field_type ] ) ? $field_forms[ $field_type ] : null;
						$response         = $upload_field_obj->handle_file_upload( $field_array, $post_data, 'upload' );

						if ( ! $response['success'] || isset( $response['errors'] ) ) {
							wp_send_json_error( $response );
						} else {
							wp_send_json_success( $response );
						}
					}
				}
			}
		} else {
			$response = array(
				'success' => false,
				'message' => __( 'form not found', 'forminator' ),
			);
		}

		wp_send_json_error( $response );
	}

	/**
	 * Response message
	 *
	 * @since 1.0
	 * @since 1.1 change superglobal POST to `get_post_data`
	 * @since 1.5.1 utilize `_post_data` which already defined on submit
	 *
	 * @param $form_id
	 * @param $render_id
	 */
	public function form_response_message( $form_id, $render_id ) {
		$submitted_data = $this->_post_data;

		$post_form_id   = isset( $submitted_data['form_id'] ) ? sanitize_text_field( $submitted_data['form_id'] ) : 0;
		$post_render_id = isset( $submitted_data['render_id'] ) ? sanitize_text_field( $submitted_data['render_id'] ) : 0;
		$response       = self::$response;

		//only show to related form
		if ( ! empty( $response ) && is_array( $response ) && (int) $form_id === (int) $post_form_id && (int) $render_id === (int) $post_render_id ) {
			$label_class = $response['success'] ? 'forminator-success' : 'forminator-error';
			?>
			<div class="forminator-response-message forminator-show <?php echo esc_attr( $label_class ); ?>"
				 tabindex="-1">
				<label class="forminator-label--<?php echo esc_attr( $label_class ); ?>"><?php echo wp_kses_post( $response['message'] ); ?></label>
				<?php
				if ( isset( $response['errors'] ) && ! empty( $response['errors'] ) ) {
					?>
					<ul class="forminator-screen-reader-only">
						<?php
						foreach ( $response['errors'] as $key => $error ) {
							foreach ( $error as $id => $value ) {
								?>
								<li><?php echo esc_html( $value ); ?></li>
								<?php
							}
						}
						?>
					</ul>
					<?php
				}
				?>
			</div>
			<?php

			if ( isset( $response['success'] ) && $response['success'] && isset( $response['behav'] ) && ( 'behaviour-hide' === $response['behav'] || ( isset( $response['newtab'] ) && 'newtab_hide' === $response['newtab'] ) ) ) {
				$selector = '#forminator-module-' . $form_id . '[data-forminator-render="' . $render_id . '"]';
				?>
				<script type="text/javascript">var ForminatorFormHider =
					<?php
					echo wp_json_encode(
						array(
							'selector' => $selector,
						)
					);
					?>
				</script>
				<?php
			}
			if ( isset( $response['success'] ) && $response['success'] && isset( $response['behav'] ) && 'behaviour-redirect' === $response['behav'] && isset( $response['newtab'] ) && ( 'newtab_hide' === $response['newtab'] || 'newtab_thankyou' === $response['newtab'] ) ) {
				$url = $response['url'];
				?>
				<script type="text/javascript">var ForminatorFormNewTabRedirect =
					<?php
					echo wp_json_encode(
						array(
							'url' => $url,
						)
					);
					?>
				</script>
				<?php
			}
		}
	}

	/**
	 * @since 1.0
	 *
	 * @param array $setting - the form settings.
	 * @param int   $form_id - the form id.
	 *
	 * @return mixed
	 */
	public function get_invalid_form_message( $setting, $form_id ) {
		$invalid_form_message = __( 'Error: Your form is not valid, please fix the errors!', 'forminator' );
		if ( isset( $setting['submitData']['custom-invalid-form-message'] ) && ! empty( $setting['submitData']['custom-invalid-form-message'] ) ) {
			$invalid_form_message = $setting['submitData']['custom-invalid-form-message'];
		}

		return apply_filters( 'forminator_custom_form_invalid_form_message', $invalid_form_message, $form_id );
	}

	/**
	 * Executor On form submit for attached addons
	 *
	 * @see   Forminator_Addon_Form_Hooks_Abstract::on_form_submit()
	 * @since 1.1
	 *
	 * @param                              $form_id
	 *
	 * @param Forminator_Form_Model $custom_form_model
	 *
	 * @return bool true on success|string error message from addon otherwise
	 */
	private function attach_addons_on_form_submit( $form_id, Forminator_Form_Model $custom_form_model ) {
		$allowed_form_fields = forminator_addon_format_form_fields( $custom_form_model );
		$submitted_data      = forminator_format_submitted_data_for_addon( $allowed_form_fields );
		// find is_form_connected.
		$connected_addons = forminator_get_addons_instance_connected_with_module( $form_id, 'form' );

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$form_hooks = $connected_addon->get_addon_form_hooks( $form_id );
				if ( $form_hooks instanceof Forminator_Addon_Form_Hooks_Abstract ) {
					$addon_return = $form_hooks->on_form_submit( $submitted_data );
					if ( true !== $addon_return ) {
						return $form_hooks->get_submit_form_error_message();
					}
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to attach_addons_on_form_submit', $e->getMessage() );
			}
		}

		return true;
	}

	/**
	 * Get submitted data
	 *
	 * @param type $module_model Model.
	 * @param type $current_entry_fields Fields.
	 * @return array
	 */
	protected static function get_submitted_data( $module_model, $current_entry_fields ) {
		$allowed_form_fields = forminator_addon_format_form_fields( $module_model );
		$submitted_data      = forminator_format_submitted_data_for_addon( $allowed_form_fields, $current_entry_fields );

		$submitted_data['form_id'] = filter_input( INPUT_POST, 'form_id', FILTER_VALIDATE_INT );
		$submitted_data            = self::replace_hidden_field_values( $module_model, $submitted_data );
		unset( $submitted_data['form_id'] ); // we need it only for replace_hidden_field_values().

		return $submitted_data;
	}

	/**
	 * Return Form Settings
	 *
	 * @since 1.1
	 *
	 * @param Forminator_Form_Model $form
	 *
	 * @return mixed
	 */
	private function get_form_settings( $form ) {
		// If not using the new "submission-behaviour" setting, set it according to the previous settings
		if ( ! isset( $form->settings['submission-behaviour'] ) ) {
			$redirect = ( isset( $form->settings['redirect'] ) && filter_var( $form->settings['redirect'], FILTER_VALIDATE_BOOLEAN ) );

			if ( $redirect ) {
				$form->settings['submission-behaviour'] = 'behaviour-redirect';
			} else {
				$form->settings['submission-behaviour'] = 'behaviour-thankyou';
			}
		}

		return $form->settings;
	}

	/**
	 * Calculate fields and convert it to entry data array to be saved or processed later
	 *
	 * @since 1.7
	 *
	 * @param Forminator_Form_Model $custom_form
	 * @param array                 $submitted_data
	 * @param array                 $pseudo_submitted_data
	 *
	 * @return array
	 */
	private static function calculate_fields_to_entry_data_array( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array ) {
		if ( ! self::$info['calculation_fields'] ) {
			return $field_data_array;
		}

		$fields_collection = forminator_fields_to_array();
		if ( ! isset( $fields_collection['calculation'] ) ) {
			return $field_data_array;
		}

		foreach ( self::$info['calculation_fields'] as $field ) {

			/**
			 * Fires before calculate each `calculation` field
			 *
			 * Note : one form can have multiple calculation fields,
			 * in this case this action fired multiple times too
			 *
			 * @since 1.7
			 *
			 * @param Forminator_Form_Model $custom_form
			 * @param array $field field properties.
			 */
			do_action( 'forminator_custom_form_before_calculate_field', $custom_form, $field );

			/** @var Forminator_Calculation $field_object */
			$field_object = $fields_collection['calculation'];
			if ( ! $field_object instanceof Forminator_Calculation ) {
				continue;
			}

			// skip on hidden
			if ( $field_object->is_hidden( $field, $submitted_data, $pseudo_submitted_data, $custom_form, self::$hidden_fields ) ) {
				continue;
			}

			// RECALCULATE, to retrieve error message if available
			$formula           = $field_object->get_calculable_value( $submitted_data, $field );
			$converted_formula = $field_object->get_converted_formula( $submitted_data, $pseudo_submitted_data, $field, $custom_form, self::$hidden_fields );
			$calculation_error = '';
			$result            = 0.0;

			try {
				$result = $field_object->get_calculated_value( $converted_formula, $submitted_data, $field );
			} catch ( Forminator_Calculator_Exception $e ) {
				$calculation_error = $e->getMessage();
			}

			$formatting_result = Forminator_Field::forminator_number_formatting( $field, $result );

			$calculation_entry_data = array(
				'name'  => $field['element_id'],
				'value' => array(
					'formula'           => $formula,
					'converted_formula' => $converted_formula,
					'error'             => $calculation_error,
					'result'            => $result,
					'formatting_result' => $formatting_result,
				),
			);

			/**
			 * Filter calculation entry data that might be stored/used later
			 *
			 * @since 1.7
			 *
			 * @param array $calculation_entry_data
			 * @param Forminator_Form_Model $custom_form
			 * @param array $field field_properties.
			 * @param string $formula original formula.
			 * @param string $converted_formula real formula that already replaced with field values.
			 *
			 * @return array
			 */
			$calculation_entry_data = apply_filters( 'forminator_custom_form_calculation_entry_data', $calculation_entry_data, $custom_form, $field, $formula, $converted_formula );

			$field_data_array[] = $calculation_entry_data;

			/**
			 * Fires after calculate each `calculation` field
			 *
			 * Note : one form can have multiple calculation fields,
			 * in this case this action fired multiple times too
			 *
			 * @since 1.7
			 *
			 * @param Forminator_Form_Model $custom_form
			 * @param array $field field properties.
			 */
			do_action( 'forminator_custom_form_after_calculate_field', $custom_form, $field );
		}

		return $field_data_array;
	}

	/**
	 * Process stripe charge
	 *
	 * @since 1.7
	 *
	 * @param Forminator_Form_Model $custom_form
	 * @param array                 $submitted_data
	 * @param array                 $pseudo_submitted_data
	 * @param array                 $field_data_array
	 *
	 * @return array
	 */
	private static function stripe_field_to_entry_data_array( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array ) {
		$fields_collection = forminator_fields_to_array();

		if ( ! isset( $fields_collection['stripe'] ) ) {
			return $field_data_array;
		}

		foreach ( self::$info['stripe_fields'] as $field ) {

			/**
			 * Fires before process stripe
			 *
			 * @since 1.7
			 *
			 * @param Forminator_Form_Model $custom_form
			 * @param array $field field properties.
			 * @param array $submitted_data
			 * @param array $field_data_array
			 */
			do_action( 'forminator_custom_form_before_stripe_charge', $custom_form, $field, $submitted_data, $field_data_array );

			/** @var Forminator_Stripe $field_object */
			$field_object = $fields_collection['stripe'];

			$entry_data = $field_object->process_to_entry_data( $field, $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array );

			$stripe_entry_data = array(
				'name'  => $field['element_id'],
				'value' => $entry_data,
			);

			/**
			 * Filter stripe entry data that might be stored/used later
			 *
			 * @since 1.7
			 *
			 * @param array $calculation_entry_data
			 * @param Forminator_Form_Model $custom_form
			 * @param array $field field_properties.
			 * @param array $field_data_array
			 *
			 * @return array
			 */
			$stripe_entry_data = apply_filters( 'forminator_custom_form_stripe_entry_data', $stripe_entry_data, $custom_form, $field, $field_data_array );

			if ( ! empty( $stripe_entry_data ) ) {
				$stripe_meta_value = $stripe_entry_data['value'];
				forminator_maybe_log( __METHOD__, $stripe_meta_value );
				$field_data_array[] = $stripe_entry_data;
			}

			/**
			 * Fires after charge stripe
			 *
			 * @since 1.7
			 *
			 * @param Forminator_Form_Model $custom_form
			 * @param array $field field properties.
			 * @param array $stripe_entry_data
			 * @param array $submitted_data
			 * @param array $field_data_array
			 */
			do_action( 'forminator_custom_form_after_stripe_charge', $custom_form, $field, $stripe_entry_data, $submitted_data, $field_data_array );

			// only process first
			break;
		}

		return $field_data_array;
	}

	/**
	 * Process PayPal charge
	 *
	 * @since 1.7
	 *
	 * @param Forminator_Form_Model $custom_form
	 * @param array                 $submitted_data
	 * @param array                 $pseudo_submitted_data
	 * @param array                 $field_data_array
	 *
	 * @return array
	 */
	private static function paypal_field_to_entry_data_array( $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array ) {
		$fields_collection = forminator_fields_to_array();

		if ( ! isset( $fields_collection['paypal'] ) ) {
			return $field_data_array;
		}

		foreach ( self::$info['paypal_fields'] as $field ) {

			/**
			 * Fires before process paypal
			 *
			 * @since 1.7
			 *
			 * @param Forminator_Form_Model $custom_form
			 * @param array $field field properties.
			 * @param array $submitted_data
			 * @param array $field_data_array
			 */
			do_action( 'forminator_custom_form_before_paypal_charge', $custom_form, $field, $submitted_data, $field_data_array );

			/** @var Forminator_PayPal $field_object */
			$field_object = $fields_collection['paypal'];

			if ( $field_object->is_hidden( $field, $submitted_data, $pseudo_submitted_data, $custom_form, self::$hidden_fields ) ) {
				continue;
			}

			$entry_data        = $field_object->process_to_entry_data( $field, $custom_form, $submitted_data, $pseudo_submitted_data, $field_data_array );
			$paypal_entry_data = array(
				'name'  => $field['element_id'],
				'value' => $entry_data,
			);

			/**
			 * Filter paypal entry data that might be stored/used later
			 *
			 * @since 1.7
			 *
			 * @param array $calculation_entry_data
			 * @param Forminator_Form_Model $custom_form
			 * @param array $field field_properties.
			 * @param array $field_data_array
			 *
			 * @return array
			 */
			$paypal_entry_data = apply_filters( 'forminator_custom_form_paypal_entry_data', $paypal_entry_data, $custom_form, $field, $field_data_array );

			if ( ! empty( $paypal_entry_data ) ) {
				$paypal_meta_value = $paypal_entry_data['value'];
				forminator_maybe_log( __METHOD__, $paypal_meta_value );
				// Error
				if ( ! isset( $paypal_meta_value['status'] ) || 'APPROVED' !== $paypal_meta_value['status'] ) {
					return new WP_Error( 'forminator_paypal_error', $paypal_meta_value['error'] );
				}
				$field_data_array[] = $paypal_entry_data;
			}

			/**
			 * Fires after charge paypal
			 *
			 * @since 1.7
			 *
			 * @param Forminator_Form_Model $custom_form
			 * @param array $field field properties.
			 * @param array $paypal_entry_data
			 * @param array $submitted_data
			 * @param array $field_data_array
			 */
			do_action( 'forminator_custom_form_after_paypal_charge', $custom_form, $field, $paypal_entry_data, $submitted_data, $field_data_array );

			// only process first
			break;
		}

		return $field_data_array;
	}

	/**
	 * Build Pseudo Submit Data
	 * Pseudo Submit Data is used to later process on submit
	 * Its needed for fields that virtually not available on the user submitted data
	 * - Calculation : its not available on $_POST even its displayed on the form, because the value is re-calculated on backend
	 * - Stripe : Stripe field is not visually available on the form, the `amount` or value will be gathered on backend
	 *
	 * @since 1.7
	 *
	 * @param Forminator_Form_Model $custom_form
	 * @param array                 $submitted_data = $_POST.
	 *
	 * @return array
	 */
	public static function build_pseudo_submitted_data( $custom_form, $submitted_data ) {
		$pseudo_submitted_data = array();
		/** @var Forminator_Field[] $field_classes */
		$field_classes = forminator_fields_to_array();
		$fields        = $custom_form->get_fields();

		if ( $custom_form->has_calculation_field() ) {
			foreach ( $fields as $field ) {
				$field_array = $field->to_formatted_array();
				$field_type  = isset( $field_array['type'] ) ? $field_array['type'] : '';

				if ( 'calculation' !== $field_type ) {
					continue;
				}

				$field_id                     = Forminator_Field::get_property( 'element_id', $field_array );
				$forminator_calculation_field = isset( $field_classes[ $field_type ] ) ? $field_classes[ $field_type ] : null;
				if ( ! ( $forminator_calculation_field instanceof Forminator_Calculation ) ) {
					continue;
				}

				try {
					$converted_formula = $forminator_calculation_field->get_converted_formula( $submitted_data, $pseudo_submitted_data, $field_array, $custom_form, self::$hidden_fields );
					$result            = $forminator_calculation_field->get_calculated_value( $converted_formula, $submitted_data, $field_array );
				} catch ( Forminator_Calculator_Exception $e ) {
					$result = 0.0;
				}
				$pseudo_submitted_data[ $field_id ] = $result;
			}
		}

		// Stripe / payments go last, because it's amount can be dependant on other pseudo submitted data (calc
		if ( class_exists( 'Forminator_Stripe' ) && $custom_form->has_stripe_field() ) {
			foreach ( $fields as $field ) {
				$field_array = $field->to_formatted_array();
				$field_id    = Forminator_Field::get_property( 'element_id', $field_array );
				$field_type  = isset( $field_array['type'] ) ? $field_array['type'] : '';

				if ( 'stripe' === $field_type ) {

					$forminator_stripe_field = isset( $field_classes[ $field_type ] ) ? $field_classes[ $field_type ] : null;

					if ( $forminator_stripe_field instanceof Forminator_Stripe ) {
						$pseudo_submitted_data[ $field_id ] = $forminator_stripe_field->get_payment_amount( $field_array, $custom_form, $submitted_data, $pseudo_submitted_data );
					}

					// only process first single stripe
					break;

				}
			}
		}
		// PayPal / payments go last, because it's amount can be dependant on other pseudo submitted data (calc
		if ( class_exists( 'Forminator_PayPal' ) && $custom_form->has_paypal_field() ) {
			foreach ( $fields as $field ) {
				$field_array = $field->to_formatted_array();
				$field_id    = Forminator_Field::get_property( 'element_id', $field_array );
				$field_type  = isset( $field_array['type'] ) ? $field_array['type'] : '';

				if ( 'paypal' === $field_type ) {

					$forminator_paypal_field = isset( $field_classes[ $field_type ] ) ? $field_classes[ $field_type ] : null;

					if ( $forminator_paypal_field instanceof Forminator_PayPal ) {
						$pseudo_submitted_data[ $field_id ] = $forminator_paypal_field->get_payment_amount( $field_array, $custom_form, $submitted_data, $pseudo_submitted_data );
					}

					// only process first single paypal
					break;

				}
			}
		}

		/**
		 * Filter Pseudo submitted data on Custom Form
		 *
		 * @since 1.7
		 *
		 * @param array $pseudo_submitted_data
		 * @param Forminator_Form_Model $custom_form
		 * @param array $submitted_data
		 *
		 * @return array
		 */
		$pseudo_submitted_data = apply_filters( 'forminator_custom_form_pseudo_submitted_data', $pseudo_submitted_data, $custom_form, $submitted_data );

		return $pseudo_submitted_data;
	}

	/**
	 * Replace field values hidden by conditions
	 *
	 * @param $custom_form
	 * @param $submitted_data
	 *
	 * @return mixed
	 */
	private static function replace_hidden_field_values( $custom_form, $submitted_data ) {
		$field_forms = forminator_fields_to_array();

		// build pseudo submit data first to later usage
		$pseudo_submitted_data = self::build_pseudo_submitted_data( $custom_form, $submitted_data );

		foreach ( $custom_form->get_fields() as $field ) {
			$field_array    = $field->to_formatted_array();
			$field_id       = Forminator_Field::get_property( 'element_id', $field_array );
			$field_type     = isset( $field_array['type'] ) ? $field_array['type'] : '';
			$form_field_obj = isset( $field_forms[ $field_type ] ) ? $field_forms[ $field_type ] : null;

			if ( $form_field_obj && $form_field_obj->is_hidden( $field_array, $submitted_data, $pseudo_submitted_data, $custom_form, self::$hidden_fields ) ) {
				$replace = 0;

				foreach ( $custom_form->get_fields() as $calc_field ) {
					$calc_field_array = $calc_field->to_array();
					if ( 'calculation' !== $calc_field_array['type'] ) {
						$replace = '';
						continue;
					}

					$replace = 0;
					$formula = $calc_field_array['formula'];
					$replace = self::replace_to( $field_id, $formula );
				}

				$submitted_data[ $field_id ] = $replace;
			}
		}

		return $submitted_data;
	}

	/**
	 * Returns what the current field should be replaced to (0 or 1)
	 *
	 * @param string $field_id Field id.
	 * @param string $formula Formula.
	 * @return int 0|1
	 */
	public static function replace_to( $field_id, $formula ) {
		$replace        = 0;
		$quoted_operand = preg_quote( '{' . $field_id . '}', '/' );
		$pattern        = '/([\\+\\-\\*\\/]?)[^\\+\\-\\*\\/\\(]*' . $quoted_operand
				. '[^\\)\\+\\-\\*\\/]*([\\+\\-\\*\\/]?)/';

		$matches = array();
		if ( preg_match( $pattern, $formula, $matches ) ) {
			// if operand in multiplication or division set value = 1.
			if ( '*' === $matches[1] || '/' === $matches[1] || '*' === $matches[2] || '/' === $matches[2] ) {
				$replace = 1;
			}
		}

		return $replace;
	}

	/**
	 * Create new post from postdata field
	 * Add upload file urls to postdata custom fields if necessary
	 *
	 * @param $postdata_fields       array.
	 * @param $upload_in_customfield array.
	 *
	 * @return array
	 */
	public function create_post_from_postdata( $postdata_fields, $upload_in_customfield ) {
		if ( empty( $postdata_fields ) ) {
			return array(
				'type'  => 'error',
				'value' => __( 'Failed to supply necessary data.', 'forminator' ),
			);
		}

		$postdata_result = array();
		foreach ( $postdata_fields as $postdata_field ) {
			$field_id       = $postdata_field['field_id'];
			$field_data     = $postdata_field['field_data'];
			$field_array    = $postdata_field['field_array'];
			$form_field_obj = $postdata_field['form_field_obj'];

			// check if field_data of post values not empty (happen when postdata is not required).
			$filtered   = array_filter( $field_data );
			$post_value = $field_data;
			if ( ! empty( $filtered ) ) {
				if ( isset( $filtered['post-custom'] ) ) {
					foreach ( $filtered['post-custom'] as $custom_field_index => $custom_field ) {
						if ( preg_match( '/\{upload-(\d+)\}/', $custom_field['value'] ) ) {
							$upload_id = trim( $custom_field['value'], '{}' );

							if ( ! empty( $upload_in_customfield ) ) {
								foreach ( $upload_in_customfield as $cf_data ) {
									if ( $upload_id === $cf_data['upload_id'] && $field_id === $cf_data['postdata_id'] ) {
										$field_data['post-custom'][ $custom_field_index ]['value'] = $cf_data['uploads'];
									}
								}
							}
						}
					}
				}

				$post_id = $form_field_obj->save_post( $field_array, $field_data );
				if ( $post_id ) {
					$field_data = array(
						'postdata' => $post_id,
						'value'    => $field_data,
					);
				} else {
					return array(
						'type'     => 'error',
						'field_id' => $field_id,
						'value'    => __( 'There was an error saving the post data. Please try again', 'forminator' ),
					);
				}
			} else {
				$field_data = array(
					'postdata' => null,
					'value'    => $post_value,
				);
			}

			$postdata_result[] = array(
				'type'       => 'success',
				'field_id'   => $field_id,
				'field_data' => $field_data,
			);
		}

		return $postdata_result;
	}

	/**
	 * REMOVE after 1.15.3 if all goes well
	 * Replace consent field value to checked
	 *
	 * @param $submitted_data       array
	 *
	 * @return array
	 */
	/* public function replace_consent_value ( $submitted_data ) {
		foreach ( $submitted_data as $key => $val ) {
			if ( false !== strpos( $key, 'consent-' ) ) {
				$submitted_data[ $key ] = __( 'checked', 'forminator' );
				break;
			}
		}

		return $submitted_data;
	} */
    
	/**
     * Retrieve the backup code if lost phone.
     *
	 * @return void
	 */
	public function fallback_email() {
		$defender_data    = forminator_defender_compatibility();
		$two_fa_component = new $defender_data['two_fa_component']();
		$post_data        = $this->get_post_data();
		$token            = isset( $post_data['token'] ) ? $post_data['token'] : '';
		$ret              = $two_fa_component->send_otp_to_email( $token );
		if ( false === $ret ) {
			wp_send_json_error( array(
				'message' => __( 'Please try again.', 'forminator' ),
			) );
		}

		if ( is_wp_error( $ret ) ) {
			wp_send_json_error( array(
				'message' => $ret->get_error_message(),
			) );
		}

        wp_send_json_success( array(
	        'message' => __( 'Your code has been sent to your email.', 'forminator' ),
        ) );
	}
}
