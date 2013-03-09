<?php
if (!defined('EVENT_ESPRESSO_VERSION') )
	exit('NO direct script access allowed');

/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for Wordpress
 *
 * @package		Event Espresso
 * @author		Seth Shoultes
 * @copyright	(c)2009-2012 Event Espresso All Rights Reserved.
 * @license		http://eventespresso.com/support/terms-conditions/  ** see Plugin Licensing **
 * @link		http://www.eventespresso.com
 * @version		3.2.P
 *
 * ------------------------------------------------------------------------
 *
 * Registration_Form_Questions_Admin_List_Table
 *
 * Class for preparing the table listing all the custom event questions
 *
 * note: anywhere there are no php docs it is because the docs are available in the parent class.
 *
 * @package		Registration_Form_Questions_Admin_List_Table
 * @subpackage	includes/core/admin/events/Registration_Form_Questions_Admin_List_Table.class.php
 * @author		Darren Ethier
 *
 * ------------------------------------------------------------------------
 */

class Registration_Form_Questions_Admin_List_Table extends EE_Admin_List_Table {


	public function __construct( $admin_page ) {
		parent::__construct($admin_page);
	}




	protected function _setup_data() {
		$this->_per_page = $this->get_items_per_page( $this->_screen . '_per_page' );
		$this->_data = $this->_view != 'trash' ? $this->_admin_page->get_questions( $this->_per_page,$this->_current_page, FALSE ) : $this->_admin_page->get_trashed_questions( $this->_per_page,$this->_current_page, FALSE );
		$this->_all_data_count = $this->_view != 'trash' ? $this->_admin_page->get_questions( $this->_per_page,$this->_current_page, TRUE ) : $this->_admin_page->get_trashed_questions( $this->_per_page,$this->_current_page, TRUE );
	}





	protected function _set_properties() {
		$this->_wp_list_args = array(
			'singular' => __('question', 'event_espresso' ),
			'plural' => __('questions', 'event_espresso' ),
			'ajax' => TRUE, //for now,
			'screen' => $this->_admin_page->get_current_screen()->id 
			);

		$this->_columns = array(
			'cb' => '<input type="checkbox" />',
			'id' => __('ID', 'event_espresso'),
			'display_text' => __('Question', 'event_espresso'),
			'admin_label' => __('Admin Label', 'event_espresso'),
			'type' => __('Type', 'event_espresso'),
			'values' => __('Values', 'event_espresso'),
			'required' => __('Required', 'event_espresso'),
			'admin_only' => __('Admin Only', 'event_espresso')
			);

		$this->_sortable_columns = array(
			'id' => array( 'QST_ID' => FALSE ),
			'display_text' => array( 'QST_display_text' => FALSE )
			);

		$this->_hidden_columns = array(
			);

		$this->_ajax_sorting_callback = 'update_question_order';

	}



	//not needed
	protected function _get_table_filters() {
		return array();
	}



	protected function _add_view_counts() {
		$this->_views['all']['count'] = $this->_admin_page->get_questions( $this->_per_page,$this->_current_page, TRUE );
		$this->_views['trash']['count'] = $this->_admin_page->get_trashed_questions( $this->_per_page,$this->_current_page, TRUE );
	}



	public function column_cb(EE_Question $item) {
		return sprintf( '<input type="checkbox" class="QST_ID" name="checkbox[%d]" value="%d" />', $item->ID(), $item->ID() );
	}



	public function column_default($item) {
		//return ( isset( $item->$column_name )) ? $item->$column_name : '';
	}



	public function column_id(EE_Question $item) {	
		return $item->ID();
	}



	public function column_display_text(EE_Question $item) {
		//return $item->display_text();
		
		if ( !defined('REG_ADMIN_URL') )
			define('REG_ADMIN_URL', EVENTS_ADMIN_URL);

		$edit_query_args = array(
				'action' => 'edit_question',
				'QST_ID' => $item->ID()
			);

		$trash_query_args = array(
				'action' => 'trash_question',
				'QST_ID' => $item->ID()
			);



		$edit_link = EE_Admin_Page::add_query_args_and_nonce( $edit_query_args, EE_FORMS_ADMIN_URL );
		$trash_link = EE_Admin_Page::add_query_args_and_nonce( $trash_query_args, EE_FORMS_ADMIN_URL );
		
		$actions = array(
			'edit' => '<a href="' . $edit_link . '" title="' . __('Edit Event', 'event_espresso') . '">' . __('Edit', 'event_espresso') . '</a>',
			'delete' => '<a href="' . $trash_link . '" title="' . __('Delete Event', 'event_espresso') . '">' . __('Trash', 'event_espresso') . '</a>',
			);

		$content = '<strong><a class="row-title" href="' . $edit_link . '">' . $item->display_text() . '</a></strong>';
		$content .= $this->row_actions($actions);
		return $content;
		
	}



	public function column_admin_label(EE_Question $item) {
		return $item->admin_label();
	}



	public function column_values(EE_Question $item) {		
		$optionNames=array();
		$options= $item->options();
		if(empty($options)){
			return "N/A";
		}else{
			foreach($options as $optionID=>$option){
				/* @var $option EE_Question_Option */
				$optionNames[]=$option->value();
			}
			return implode(', ',$optionNames);
		}
	}



	public function column_type(EE_Question $item) {
		return $item->type();
	}



	public function column_required(EE_Question $item) {
		return $item->required() ? 'Yes' : '';
	}



	public function column_admin_only(EE_Question $item) {
		return $item->admin_only() ? 'Yes' : '';
	}





} //end class Registration_Form_Questions_Admin_List_Table