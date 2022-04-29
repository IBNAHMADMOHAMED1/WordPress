<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Quiz_View_Page
 *
 * @since 1.0
 */
class Forminator_Quiz_View_Page extends Forminator_Admin_View_Page {

	/**
	 * Fields
	 *
	 * @var array
	 */
	protected $lead_fields = array();

	/**
	 * @since 1.6.2
	 * @var Forminator_Addon_Abstract[]
	 */
	protected $lead_cform = null;

	/**
	 * Moduel type
	 *
	 * @var string
	 */
	protected static $module_slug = 'quiz';

	/**
	 * Initialise variables
	 *
	 * @since 1.0
	 */
	public function before_render() {
		$this->maybe_redirect();

		$form_id = (int) Forminator_Core::sanitize_text_field( 'form_id' );
		if ( $form_id ) {
			$this->form_id = $form_id;
			parent::before_render();
			$this->total_fields++;
			$this->process_request();
			$this->prepare_results();
		}

		if ( $this->has_leads() ) {
			$this->lead_cform = new Forminator_CForm_View_Page( 'forminator-quiz-view', 'custom-form/entries', __( 'Submissions:', 'forminator' ), __( 'View Custom Form', 'forminator' ), 'forminator' );
			$this->lead_cform->before_render( $this->lead_id() );
			$this->lead_fields = $this->lead_cform->get_fields();
		}
	}

	/**
	 * Action delete_all
	 */
	public function delete_all_action() {
		$ids = filter_input( INPUT_GET, 'ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( $ids ) {
			$entries = implode( ',', $ids );
			Forminator_Form_Entry_Model::delete_by_entrys( $this->model->id, $entries );
			$this->maybe_redirect_to_referer();
			exit;
		}
	}

	/**
	 * Get fields
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_lead_fields() {
		return $this->lead_fields;
	}

	/**
	 * Check if quiz has leads
	 *
	 * @since 1.14
	 *
	 * @return bool
	 */
	public function has_leads() {
		if ( isset( $this->model->settings['hasLeads'] ) && in_array( $this->model->settings['hasLeads'], array( true, 'true' ), true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if quiz lead id
	 *
	 * @since 1.14
	 *
	 * @return int
	 */
	public function lead_id() {
		if ( isset( $this->model->settings['leadsId'] ) ) {
			return $this->model->settings['leadsId'];
		}

		return 0;
	}

	/**
	 * Get fields table
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_table() {
		$per_page = $this->get_per_page();
		$entries  = Forminator_Form_Entry_Model::list_entries( $this->form_id, $per_page, ( $this->get_paged() - 1 ) * $per_page );

		return $entries;
	}

	/**
	 * Get paged
	 *
	 * @since 1.0
	 * @return int
	 */
	public function get_paged() {
		$paged = filter_input( INPUT_GET, 'paged', FILTER_VALIDATE_INT );
		$paged = $paged ? $paged : 1;

		return $paged;
	}

	/**
	 * @since 1.0
	 * @return int
	 */
	public function get_total_entries() {
		$count = Forminator_Form_Entry_Model::count_entries( $this->form_id );

		return $count;
	}

	/**
	 * Get form type
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function get_form_type() {
		return $this->model->quiz_type;
	}

	/**
	 * Get form type param
	 *
	 * @since 1.5.4
	 * @return string
	 */
	protected function forminator_get_form_type() {
		return Forminator_Core::sanitize_text_field( 'form_type' );
	}

	/**
	 * Get integrations data
	 *
	 * @since 1.6.2
	 *
	 * @param Forminator_Form_Entry_Model $entry
	 *
	 * @return array
	 */
	public function get_integrations_data_from_entry( Forminator_Form_Entry_Model $entry ) {
		return $this->attach_addon_on_render_entry( $entry );
	}

	/**
	 * @return array
	 */
	public function entries_iterator() {
		$entries_data = array();
		$entries      = $this->entries;
		if ( $this->lead_cform ) {
			$entries_data = $this->lead_cform->entries_iterator( $entries, 'quiz' );
		} else {
			foreach ( $entries as $entry ) {
				$entries_data = array(
					'entry_id'   => $entry->entry_id,
					'entry_date' => $entry->time_created,
					'summary'    => array(),
					'detail'     => array(),
				);

				$entries_data['summary']['num_fields_left'] = 0;
				$entries_data['summary']['items']           = array();

				$entries_data['detail']['colspan'] = 0;
				$entries_data['detail']['items']   = array();

				$entries_data['detail']['quiz_entry'] = isset( $entry->meta_data['entry'] ) ? $entry->meta_data['entry'] : array();
				$entries_data['detail']['quiz_url']   = isset( $entry->meta_data['quiz_url'] ) ? $entry->meta_data['quiz_url'] : array();
			}
		}

		return $entries_data;

	}

	/**
	 * Build Html Entries Header
	 */
	public function entries_header() {
		if ( $this->lead_cform ) {
			$this->lead_cform->entries_header();
		} else { ?>
			<thead>
				<tr>
					<th>
						<label class="sui-checkbox">
							<input id="wpf-cform-check_all" type="checkbox">
							<span></span>
							<div class="sui-description"><?php esc_html_e( 'ID', 'forminator' ); ?></div>
						</label>
					</th>
					<th colspan="5"><?php esc_html_e( 'Date Submitted', 'forminator' ); ?></th>
				</tr>
			</thead>
			<?php
		}
	}
}
