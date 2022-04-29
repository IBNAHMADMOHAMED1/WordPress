<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Author: Hoang Ngo
 */
class Forminator_Form_Model extends Forminator_Base_Form_Model {

	/**
	 * Module slug
	 *
	 * @var string
	 */
	public static $module_slug = 'form';

	protected $post_type = 'forminator_forms';

	/**
	 * Get field
	 *
	 * @since 1.0
	 *
	 * @param      $id
	 * @param bool $to_array
	 *
	 * @return array|null|Forminator_Form_Field_Model
	 */
	public function get_field( $id, $to_array = true ) {
		foreach ( $this->get_fields() as $field ) {
			if ( $field->slug === $id ) {
				if ( $to_array ) {
					return $field->to_array();
				} else {
					return $field;
				}
			}
		}

		return null;
	}

	/**
	 * Return fields as array
	 *
	 * @since 1.5
	 *
	 * @param string
	 *
	 * @return array
	 */
	public function get_fields_by_type( $type ) {
		$fields = array();

		if ( empty( $this->fields ) ) {
			return $fields;
		}

		foreach ( $this->fields as $field ) {
			$field_settings = $field->to_array();

			if ( isset( $field_settings['type'] ) && $field_settings['type'] === $type ) {
				$fields[] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Get wrapper
	 *
	 * @since 1.5
	 *
	 * @param $id
	 *
	 * @return array|null
	 */
	public function get_wrapper( $id ) {
		$position = 0;
		foreach ( $this->get_fields_grouped() as $wrapper ) {
			if ( $wrapper['wrapper_id'] === $id ) {
				$wrapper['position'] = $position;

				return $wrapper;
			}

			$position ++;
		}

		return null;
	}

	/**
	 * Delete form field by ID
	 *
	 * @since 1.5
	 *
	 * @param $id
	 *
	 * @return string|false
	 */
	public function delete_field( $id ) {
		$counter = 0;
		foreach ( $this->fields as $field ) {
			if ( $field->slug === $id ) {
				unset( $this->fields[ $counter ] );

				return $field->form_id;
			}

			$counter ++;
		}

		return false;
	}

	/**
	 * Update fields cols in specific wrapper
	 *
	 * @since 1.5
	 *
	 * @param $wrapper_id
	 *
	 * @return bool
	 */
	public function update_fields_by_wrapper( $wrapper_id ) {
		// Get wrapper.
		$wrapper = $this->get_wrapper( $wrapper_id );

		// Check if any fields in the wrapper.
		if ( ! isset( $wrapper['fields'] ) ) {
			return false;
		}

		// Get total fields in the wrapper.
		$total = count( $wrapper['fields'] );

		if ( $total > 0 ) {
			$cols = 12 / $total;

			// Update fields.
			foreach ( $wrapper['fields'] as $field ) {
				$field_object = $this->get_field( $field['element_id'], false );

				// Update field object.
				$field_object->import(
					array(
						'cols' => $cols,
					)
				);
			}

			return true;
		}

		return false;
	}

	/**
	 * Prepare data for preview
	 *
	 * @param object $form_model Model.
	 * @param array  $data Passed data.
	 *
	 * @return object
	 */
	public static function prepare_data_for_preview( $form_model, $data ) {
		// build the field.
		$fields = array();
		if ( isset( $data['wrappers'] ) ) {
			$fields = $data['wrappers'];
			unset( $data['wrappers'] );
		}

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $row ) {
				foreach ( $row['fields'] as $f ) {
					$field          = new Forminator_Form_Field_Model();
					$field->form_id = $row['wrapper_id'];
					$field->slug    = $f['element_id'];
					$field->import( $f );
					$form_model->add_field( $field );
				}
			}
		}

		return $form_model;
	}

	/**
	 * Check if can submit the form
	 *
	 * @since 1.6
	 * @return array
	 */
	public function form_can_submit() {
		$form_settings = $this->settings;
		$can_show      = array(
			'can_submit' => true,
			'error'      => '',
		);

		if ( ! empty( $form_settings['logged-users'] ) ) {
			if ( filter_var( $form_settings['logged-users'], FILTER_VALIDATE_BOOLEAN ) && ! is_user_logged_in() ) {
				$can_show = array(
					'can_submit' => false,
					'error'      => __( 'Only logged in users can submit this form.', 'forminator' ),
				);
			}
		}
		if ( $can_show['can_submit'] ) {
			if ( isset( $form_settings['form-expire'] ) ) {
				if ( 'submits' === $form_settings['form-expire'] ) {
					if ( isset( $form_settings['expire_submits'] ) && ! empty( $form_settings['expire_submits'] ) ) {
						$submits       = intval( $form_settings['expire_submits'] );
						$total_entries = Forminator_Form_Entry_Model::count_entries( $this->id );
						if ( $total_entries >= $submits ) {
							$can_show = array(
								'can_submit' => false,
								'error'      => __( 'You reached the maximum allowed submissions for this form.', 'forminator' ),
							);
						}
					}
				} elseif ( 'date' === $form_settings['form-expire'] ) {
					if ( isset( $form_settings['expire_date'] ) && ! empty( $form_settings['expire_date'] ) ) {
						$expire_date  = strtotime( $form_settings['expire_date'] );
						$current_date = strtotime( 'now' );
						if ( $current_date > $expire_date ) {
							$can_show = array(
								'can_submit' => false,
								'error'      => __( 'Unfortunately this form expired.', 'forminator' ),
							);
						}
					}
				}
			}
		}

		if ( $can_show['can_submit'] ) {
			// disable submit if status is draft.
			if ( self::STATUS_DRAFT === $this->status ) {
				$can_show = array(
					'can_submit' => false,
					'error'      => __( 'This form is not published.', 'forminator' ),
				);
			}
		}

		return apply_filters( 'forminator_cform_form_is_submittable', $can_show, $this->id, $form_settings );
	}

	/**
	 * Check if can show the form
	 *
	 * @param $is_preview
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function form_is_visible( $is_preview ) {
		$form_settings = $this->settings;
		$can_show      = true;

		if ( isset( $form_settings['logged-users'] ) && ! empty( $form_settings['logged-users'] ) ) {
			if ( filter_var( $form_settings['logged-users'], FILTER_VALIDATE_BOOLEAN ) && ! is_user_logged_in() ) {
				$can_show = false;
			}
		}
		if ( $can_show ) {
			if ( isset( $form_settings['form-expire'] ) ) {
				if ( 'submits' === $form_settings['form-expire'] ) {
					if ( isset( $form_settings['expire_submits'] ) && ! empty( $form_settings['expire_submits'] ) ) {
						$submits       = intval( $form_settings['expire_submits'] );
						$total_entries = Forminator_Form_Entry_Model::count_entries( $this->id );
						if ( $total_entries >= $submits && ! $is_preview ) {
							$can_show = false;
						}
					}
				} elseif ( 'date' === $form_settings['form-expire'] ) {
					if ( isset( $form_settings['expire_date'] ) && ! empty( $form_settings['expire_date'] ) ) {
						$expire_date  = strtotime( $form_settings['expire_date'] );
						$current_date = strtotime( 'now' );
						if ( $current_date > $expire_date && ! $is_preview ) {
							$can_show = false;
						}
					}
				}
			}
		}

		return apply_filters( 'forminator_cform_form_is_visible', $can_show, $this->id, $form_settings );
	}

	/**
	 * Get first captcha field in form if available
	 *
	 * @since 1.5.3
	 *
	 * @return bool|Forminator_Form_Field_Model
	 */
	public function get_captcha_field() {
		$captcha_field = false;
		$form_id       = (int) $this->id;
		$fields        = $this->fields;
		foreach ( $fields as $field ) {
			$field_array = $field->to_formatted_array();
			if ( isset( $field_array['type'] ) && 'captcha' === $field_array['type'] ) {
				$captcha_field = $field;
				break;
			}
		}

		$captcha_field = apply_filters( 'forminator_custom_form_get_captcha_field', $captcha_field, $form_id, $fields );

		return $captcha_field;
	}

	/**
	 * Get submission behavior
	 *
	 * @since 1.6
	 *
	 * @return string
	 */
	public function get_submission_behaviour( $behavior_options ) {
		$form_id              = (int) $this->id;
		$submission_behaviour = 'behaviour-thankyou';

		if ( isset( $behavior_options['submission-behaviour'] ) ) {
			$submission_behaviour = $behavior_options['submission-behaviour'];
		}

		// If Stripe field exist & submit is AJAX we fall back to hide to force page reload when form submitted.
		if ( self::has_stripe_or_paypal( $this ) && $this->is_ajax_submit() ) {
			$submission_behaviour = 'behaviour-hide';
		}

		$submission_behaviour = apply_filters( 'forminator_custom_form_get_submission_behaviour', $submission_behaviour, $form_id, $behavior_options );

		return $submission_behaviour;
	}

	/**
	 * Return behaviors options.
	 *
	 * @param object $module Module obkect.
	 * @param array  $settings Module settings.
	 * @return array
	 */
	public static function get_behavior_array( $module, $settings ) {
		if ( ! isset( $module ) || empty( $module->behaviors ) ) {
			// Backward compatibility | Default.
			return array(
				array(
					'slug'                    => 'behavior-1234-4567',
					'label'                   => '',
					'autoclose-time'          => isset( $settings['autoclose-time'] ) ? $settings['autoclose-time'] : 5,
					'autoclose'               => isset( $settings['autoclose'] ) ? $settings['autoclose'] : true,
					'newtab'                  => isset( $settings['newtab'] ) ? $settings['newtab'] : 'sametab',
					'thankyou-message'        => isset( $settings['thankyou-message'] ) ? $settings['thankyou-message'] : '',
					'email-thankyou-message'  => isset( $settings['email-thankyou-message'] ) ? $settings['email-thankyou-message'] : '',
					'manual-thankyou-message' => isset( $settings['manual-thankyou-message'] ) ? $settings['manual-thankyou-message'] : '',
					'submission-behaviour'    => isset( $settings['submission-behaviour'] ) ? $settings['submission-behaviour'] : 'behaviour-thankyou',
					'redirect-url'            => isset( $settings['redirect-url'] ) ? $settings['redirect-url'] : '',
				),
			);
		}

		return $module->behaviors;
	}

	/**
	 * Check if submit is handled with AJAX
	 *
	 * @since 1.9
	 *
	 * @return bool
	 */
	public function is_ajax_submit() {
		$form_id       = (int) $this->id;
		$form_settings = $this->settings;

		// Force AJAX submit if form contains Stripe payment field.
		if ( $this->has_stripe_field() ) {
			return true;
		}

		if ( ! isset( $form_settings['enable-ajax'] ) || empty( $form_settings['enable-ajax'] ) ) {
			return false;
		}

		return filter_var( $form_settings['enable-ajax'], FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Get field
	 *
	 * Call this method when you need get field and migrate it as well
	 *
	 * @since 1.7
	 *
	 * @param      $id
	 *
	 * @return array|null
	 */
	public function get_formatted_array_field( $id ) {
		foreach ( $this->get_fields() as $field ) {
			if ( $field->slug === $id ) {
				return $field->to_formatted_array();
			}
		}

		return null;
	}

	/**
	 * Flag whether ssl required when payment exists
	 *
	 * @since 1.7
	 *
	 * @return bool
	 */
	public function is_payment_require_ssl() {
		$form_id        = (int) $this->id;
		$form_settings  = $this->settings;
		$global_enabled = defined( 'FORMINATOR_PAYMENT_REQUIRE_SSL' ) && FORMINATOR_PAYMENT_REQUIRE_SSL;

		$enabled = isset( $form_settings['payment_require_ssl'] ) ? $form_settings['payment_require_ssl'] : false;
		$enabled = filter_var( $enabled, FILTER_VALIDATE_BOOLEAN );

		$enabled = $global_enabled || $enabled;

		/**
		 * Filter is ajax load for Custom Form
		 *
		 * @since 1.6.1
		 *
		 * @param bool  $enabled
		 * @param bool  $global_enabled
		 * @param int   $form_id
		 * @param array $form_settings
		 *
		 * @return bool
		 */
		$enabled = apply_filters( 'forminator_custom_form_is_payment_require_ssl', $enabled, $global_enabled, $form_id, $form_settings );

		return $enabled;
	}

	/**
	 * Check if Custom form has calculation field
	 *
	 * @since 1.7
	 * @return bool
	 */
	public function has_calculation_field() {
		$fields = $this->fields;
		foreach ( $fields as $field ) {
			$field = $field->to_formatted_array();
			if ( isset( $field['type'] ) && 'calculation' === $field['type'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if Custom form has stripe field
	 *
	 * @since 1.7
	 * @return bool
	 */
	public function has_stripe_field() {
		$fields = $this->fields;
		foreach ( $fields as $field ) {
			$field = $field->to_formatted_array();
			if ( isset( $field['type'] ) && 'stripe' === $field['type'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if form has stripe or paypal field
	 *
	 * @since 1.9.3
	 * @return bool
	 */
	public static function has_stripe_or_paypal( $form ) {
		$fields = isset( $form->fields ) ? $form->fields : array();

		foreach ( $fields as $field ) {
			$field = $field->to_formatted_array();
			if ( isset( $field['type'] ) && ( 'stripe' === $field['type'] || 'paypal' === $field['type'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if Custom form has paypal field
	 *
	 * @since 1.7
	 * @return bool
	 */
	public function has_paypal_field() {
		$fields = $this->fields;
		foreach ( $fields as $field ) {
			$field = $field->to_formatted_array();
			if ( isset( $field['type'] ) && 'paypal' === $field['type'] ) {
				return true;
			}
		}

		return false;
	}
}
