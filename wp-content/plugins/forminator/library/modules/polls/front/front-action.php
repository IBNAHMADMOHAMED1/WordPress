<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Front action for polls
 *
 * @since 1.0
 */
class Forminator_Poll_Front_Action extends Forminator_Front_Action {

	/**
	 * Module slug
	 *
	 * @var string
	 */
	protected static $module_slug = 'poll';

	/**
	 * Entry type
	 *
	 * @var string
	 */
	public $entry_type = 'poll';

	/**
	 * Handle form action
	 *
	 * @since 1.0
	 *
	 * @param int  $form_id
	 * @param bool $preview
	 *
	 * @return bool|array
	 */
	protected function handle_form( $form_id, $preview = false ) {
		self::$module_id = $form_id;

		$poll = Forminator_Poll_Model::model()->load( $form_id );
		if ( ! is_object( $poll ) ) {
			return false;
		}

		// disable submissions if not published.
		if ( Forminator_Poll_Model::STATUS_PUBLISH !== $poll->status ) {
			return self::return_error( __( 'Poll submissions disabled.', 'forminator' ) );
		}

		// Check poll opening status.
		$status_info = $poll->opening_status();
		if ( 'open' !== $status_info['status'] ) {
			self::$response_attrs['notice'] = 'error';
			return self::return_error( $status_info['msg'] );
		}

		$user_can_vote = $poll->current_user_can_vote();
		/**
		 * Filter to check if current user can vote
		 *
		 * @since 1.0.2
		 *
		 * @param bool $user_can_vote - if can vote depending on above conditions.
		 * @param int $form_id - the form id.
		 *
		 * @return bool $user_can_vote - true|false
		 */
		$user_can_vote = apply_filters( 'forminator_poll_handle_form_user_can_vote', $user_can_vote, $form_id );

		if ( ! $user_can_vote ) {
			self::$response_attrs['form_id'] = $form_id;
			self::$response_attrs['notice']  = 'notice';
			return self::return_error( __( 'You have already submitted a vote to this poll', 'forminator' ) );
		}

		$post_data   = $this->get_post_data();
		$field_data  = isset( $post_data[ $form_id ] ) ? $post_data[ $form_id ] : false;
		$extra_field = isset( $post_data[ $form_id . '-extra' ] ) ? $post_data[ $form_id . '-extra' ] : false;
		if ( empty( $field_data ) ) {
			self::$response_attrs['form_id'] = $form_id;
			self::$response_attrs['notice']  = 'error';
			return self::return_error( __( 'You need to select a poll option', 'forminator' ) );
		}

		$entry             = new Forminator_Form_Entry_Model();
		$entry->entry_type = $this->entry_type;
		$entry->form_id    = $form_id;
		// get fields labels.
		$fields_labels    = $poll->pluck_fields_array( 'title', 'element_id', '1' );
		$field_data_array = array(
			array(
				'name'  => $field_data,
				'value' => isset( $fields_labels[ $field_data ] ) ? $fields_labels[ $field_data ] : '1',
			),
		);
		if ( $poll->is_method_browser_cookie() ) {
			$this->set_vote_browser_cookie( $form_id );
		} else {
			$field_data_array[] = array(
				'name'  => '_forminator_user_ip',
				'value' => Forminator_Geo::get_user_ip(),
			);
		}

		$setting = $poll->settings;
		if ( ! empty( $extra_field ) ) {
			$field_data_array[] = array(
				'name'  => 'extra',
				'value' => $extra_field,
			);

			/**
			 * Handle spam protection
			 * Add-ons use this filter to check if content has spam data
			 *
			 * @since 1.0.2
			 *
			 * @param bool false - defauls to false
			 * @param array $field_data_array - the entry data.
			 * @param int $form_id - the form id.
			 * @param string $form_type - the form type. In this case defaults to 'poll'.
			 *
			 * @return bool true|false
			 */
			$is_spam = apply_filters( 'forminator_spam_protection', false, $field_data_array, $form_id, 'poll' );

			if ( $is_spam ) {
				$fail_message = self::get_akismet_fail_message( $setting );
				if ( false !== $fail_message ) {
					return self::return_error( $fail_message );
				} else {
					$entry->is_spam = $is_spam;
				}
			}
		}

		// If preview, skip integrations.
		if ( ! $preview ) {
			// ADDON on_form_submit.
			$addon_error = $this->attach_addons_on_poll_submit( $form_id, $poll );

			if ( true !== $addon_error ) {
				self::$response_attrs['notice'] = 'error';
				return self::return_error( $addon_error );
			}
		}

		$prevent_store = $preview || $poll->is_prevent_store();
		if ( ! $prevent_store ) {
			$entry->save();
		}

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
		$field_data_array = apply_filters( 'forminator_polls_submit_field_data', $field_data_array, $form_id );

		/**
		 * Action called before setting fields to database
		 *
		 * @since 1.0.2
		 *
		 * @param Forminator_Form_Entry_Model $entry - the entry model.
		 * @param int $form_id - the form id.
		 * @param array $field_data_array - the entry data.
		 */
		do_action( 'forminator_polls_submit_before_set_fields', $entry, $form_id, $field_data_array );

		// ADDON add_entry_fields.
		$added_data_array = $this->attach_addons_add_entry_fields( $form_id, $poll, $field_data_array );
		$added_data_array = array_merge( $field_data_array, $added_data_array );

		$entry->set_fields( $added_data_array );

		// ADDON after_entry_saved.
		$this->attach_addons_after_entry_saved( $entry, $poll );

		// Email.
		$forminator_mail_sender = new Forminator_Poll_Front_Mail();
		$forminator_mail_sender->process_mail( $poll, $post_data, $entry );

		self::$response_attrs['notice']  = 'success';
		self::$response_attrs['form_id'] = $form_id;

		$response = self::return_success( __( 'Your vote has been saved', 'forminator' ) );
		if ( ! isset( $setting['results-behav'] ) || ! in_array( $setting['results-behav'], array( 'show_after', 'link_on' ), true ) ) {
			return $response;
		}

		$response = self::prepare_response( $response, $form_id, $poll, $setting, $post_data );

		return $response;
	}

	/**
	 * Prepare response array
	 *
	 * @param array  $response response array.
	 * @param int    $form_id Module id.
	 * @param object $poll Module.
	 * @param array  $setting Settings.
	 * @param array  $post_data Post data.
	 * @return array
	 */
	private static function prepare_response( $response, $form_id, $poll, $setting, $post_data ) {
		$url = add_query_arg(
			array(
				'saved'     => 'true',
				'form_id'   => $form_id,
				'render_id' => $post_data['render_id'],
			),
			$post_data['_wp_http_referer']
		);
		$url = apply_filters( 'forminator_poll_submit_url', $url, $form_id );

		if ( ! isset( $setting['enable-ajax'] ) || empty( $setting['enable-ajax'] ) ) {
			$is_ajax_enabled = false;
		} else {
			$is_ajax_enabled = filter_var( $setting['enable-ajax'], FILTER_VALIDATE_BOOLEAN );
		}

		// Results behav
		$response['results_behav'] = (string) $setting['results-behav'];

		// Votes count
		$response['votes_count'] = isset( $setting['show-votes-count'] ) ? filter_var( $setting['show-votes-count'], FILTER_VALIDATE_BOOLEAN ) : false;

		// Chart basic colors
		$response['grids_color']   = ! empty( $setting['grid_lines'] ) ? $setting['grid_lines'] : '#E5E5E5';
		$response['labels_color']  = ! empty( $setting['grid_labels'] ) ? $setting['grid_labels'] : '#777771';
		$response['onchart_label'] = ! empty( $setting['onbar_votes'] ) ? $setting['onbar_votes'] : '#333333';

		// Tooltips
		$response['tooltips_bg']    = ! empty( $setting['tooltips_background'] ) ? $setting['tooltips_background'] : '#333333';
		$response['tooltips_color'] = ! empty( $setting['tooltips_text'] ) ? $setting['tooltips_text'] : '#FFFFFF';

		// On chart label text
		$response['votes_text'] = (string) esc_html__( 'vote(s)', 'forminator' );

		// View results link
		$response['results_link'] = sprintf(
			'<a href="%s" class="forminator-link">%s</a>',
			esc_url( $url ),
			esc_html__( 'View results', 'forminator' )
		);

		if ( $is_ajax_enabled ) {
			// ajax enabled send result data to front end
			$response['chart_data'] = self::get_chart_data( $poll );

			if ( isset( $setting['enable-votes-limit'] ) && 'true' === $setting['enable-votes-limit'] ) {
				$response['back_button'] = '<button type="button" class="forminator-button forminator-button-back">' . __( 'Back To Poll', 'forminator' ) . '</button>';
			}
		} else {
			// its not ajax enabled, send url result to front end
			$response['url'] = $url;
		}

		return $response;
	}

	/**
	 * Get Chart data of Poll
	 *
	 * @param Forminator_Poll_Model $poll
	 *
	 * @return array
	 */
	private static function get_chart_data( Forminator_Poll_Model $poll ) {

		$chart_colors         = forminator_get_poll_chart_colors( $poll->id );
		$default_chart_colors = $chart_colors;
		$chart_datas          = array();

		$form_settings        = $poll->settings;
		$number_votes_enabled = false; // TO-DO: Remove later. This will be handled through ChartJS function.

		$fields_array = $poll->get_fields_as_array();
		$map_entries  = Forminator_Form_Entry_Model::map_polls_entries( $poll->id, $fields_array );
		$fields       = $poll->get_fields();

		if ( ! is_null( $fields ) ) {

			foreach ( $fields as $field ) {

				// Label
				$label = addslashes( $field->title );

				// Votes
				$slug    = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
				$entries = 0;

				if ( in_array( $slug, array_keys( $map_entries ), true ) ) {
					$entries = $map_entries[ $slug ];
				}

				$color = $field->color;

				if ( empty( $color ) ) {
					// Colors.
					if ( empty( $chart_colors ) ) {
						$chart_colors = $default_chart_colors;
					}

					$color = array_shift( $chart_colors );
				}

				$chart_datas[] = array(
					(string) $label,
					(int) $entries,
					(string) $color,
				);
			}
		}

		return $chart_datas;

	}

	/**
	 * Response message
	 *
	 * @since 1.0
	 */
	public function form_response_message( $form_id, $render_id ) {
		$response     = self::$response;
		$post_form_id = isset( $response['form_id'] ) ? sanitize_text_field( $response['form_id'] ) : 0;

		if ( empty( $response ) || ! is_array( $response ) ) {
			return;
		}

        // Only show to related form
		if ( ! empty( $response ) && is_array( $response ) && (int) $form_id === (int) $post_form_id ) {
			$label_class = $response['success'] ? 'forminator-success' : 'forminator-error';
			if ( isset( $response['notice'] ) && $response['notice'] === 'error'
				|| isset( $response['success'] ) && $response['success'] ) {
				?>
				<div class="forminator-response-message forminator-show <?php echo esc_attr( $label_class ); ?>">
					<p class="forminator-label--<?php echo esc_attr( $label_class ); ?>">
						<?php echo esc_html( $response['message'] ); ?>
					</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Executor On form submit for attached addons
	 *
	 * @see   Forminator_Addon_Poll_Hooks_Abstract::on_poll_submit()
	 * @since 1.6.1
	 *
	 * @param                              $poll_id
	 * @param Forminator_Poll_Model $poll_model
	 *
	 * @return bool true on success|string error message from addon otherwise
	 */
	private function attach_addons_on_poll_submit( $poll_id, Forminator_Poll_Model $poll_model ) {
		$submitted_data = forminator_addon_format_poll_submitted_data();
		// find is_form_connected.
		$connected_addons = forminator_get_addons_instance_connected_with_module( $poll_id, 'poll' );

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$poll_hooks = $connected_addon->get_addon_poll_hooks( $poll_id );
				if ( $poll_hooks instanceof Forminator_Addon_Poll_Hooks_Abstract ) {
					$addon_return = $poll_hooks->on_poll_submit( $submitted_data );
					if ( true !== $addon_return ) {
						return $poll_hooks->get_submit_poll_error_message();
					}
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to attach_addons_on_poll_submit', $e->getMessage() );
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
		$submitted_data = forminator_addon_format_poll_submitted_data();

		return $submitted_data;
	}

	/**
	 * Set Browser Cookie when poll submit
	 *
	 * @param $form_id
	 */
	public function set_vote_browser_cookie( $form_id ) {
		$poll        = Forminator_Poll_Model::model()->load( $form_id );
		$settings    = $poll->settings;
		$duration    = 1;
		$expire      = time() + YEAR_IN_SECONDS * $duration;
		$cookie_name = 'poll-cookie-' . md5( $form_id );
		if ( $poll->is_allow_multiple_votes() ) {
			$duration           = ! empty( $settings['vote_limit_input'] ) ? absint( $settings['vote_limit_input'] ) : 1;
			$vote_limit_options = ! empty( $settings['vote_limit_options'] ) ? $settings['vote_limit_options'] : 'Y';
			switch ( $vote_limit_options ) {
				case 'h':
					$expire = time() + HOUR_IN_SECONDS * $duration;
					break;
				case 'd':
					$expire = time() + DAY_IN_SECONDS * $duration;
					break;
				case 'W':
					$expire = time() + WEEK_IN_SECONDS * $duration;
					break;
				case 'M':
					$expire = time() + MONTH_IN_SECONDS * $duration;
					break;
				case 'm':
					$expire = time() + MINUTE_IN_SECONDS * $duration;
					break;
				case 'Y':
					$expire = time() + YEAR_IN_SECONDS * $duration;
					break;
				default:
					$expire = time() + YEAR_IN_SECONDS * $duration;
					break;
			}
		}
		$current_date = date_i18n( 'Y-m-d H:i:s' );
		$secure       = ( 'https' === parse_url( wp_login_url(), PHP_URL_SCHEME ) );
		setcookie( $cookie_name, $current_date, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure, true );
	}
}
