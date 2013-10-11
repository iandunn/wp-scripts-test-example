<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package			Event Espresso
 * @ author				Seth Shoultes
 * @ copyright		(c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license			http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link					http://www.eventespresso.com
 * @ version		 	4.0
 *
 * ------------------------------------------------------------------------
 *
 * Line Item Model. MOstly used for storing a snapshot of all the items in a transaction
 * as they were recorded at teh time of transaction completion (purchase)
 *
 * This links Registrations with datetimes for recording checkin's and checkouts (and attendance)
 *
 * @package			Event Espresso
 * @subpackage		includes/models/EEM_Checkin.model.php
 * @author			Darren Ethier
 *
 * ------------------------------------------------------------------------
 */

class EEM_Line_Item extends EEM_Base {

	// private instance of the EEM_Checkin object
	private static $_instance = NULL;



	/**
	 * 		This funtion is a singleton method used to instantiate the EEM_Line_Item object
	 *
	 * 		@access public
	 * 		@param string $timezone string representing the timezone we want to set for returned Date Time Strings (and any incoming timezone data that gets saved).  Note this just sends the timezone info to the date time model field objects.  Default is NULL (and will be assumed using the set timezone in the 'timezone_string' wp option)
	 * 		@return EEM_Checkin instance
	 */
	public static function instance( $timezone = NULL ) {

		// check if instance of EEM_Checkin already exists
		if (self::$_instance === NULL) {
			// instantiate Price_model
			self::$_instance = new self( $timezone );
		}

		//set timezone if we have in incoming string
		if ( !empty( $timezone ) )
			self::$_instance->set_timezone( $timezone );
		
		// EEM_Checkin object
		return self::$_instance;
	}



	/**
	 * 		private constructor to prevent direct creation
	 * 		@Constructor
	 * 		@access protected
	 * 		@param string $timezone string representing the timezone we want to set for returned Date Time Strings (and any incoming timezone data that gets saved).  Note this just sends the timezone info to the date time model field objects.  Default is NULL (and will be assumed using the set timezone in the 'timezone_string' wp option)
	 * 		@return void
	 */
	protected function __construct( $timezone ) {
		$this->singlular_item = __('Line Item','event_espresso');
		$this->plural_item = __('Line Items','event_espresso');		

		$this->_tables = array(
			'Line_Item'=>new EE_Primary_Table('esp_line_item','LIN_ID')
		);
		$this->_fields = array(
			'Line_Item'=> array(
				'LIN_ID'=>new EE_Primary_Key_Int_Field('LIN_ID', __("ID", "event_espresso")),
				'TXN_ID'=>new EE_Foreign_Key_Int_Field('TXN_ID', __("Transaction ID", "event_espresso"), true, null, 'Transaction'),
				'LIN_name'=>new EE_Full_HTML_Field('LIN_name', __("Line Item Name", "event_espresso"), false, ''),
				'LIN_desc'=>new EE_Full_HTML_Field('LIN_desc', __("Line Item Description", "event_espresso"), true),
				'LIN_amount'=>new EE_Float_Field('LIN_amount', __("Amount", "event_espresso"), false, 0),
				'LIN_quantity'=>new EE_Integer_Field('LIN_quantity', __("Quantity", "event_espresso"), true, null),
				'LIN_taxable'=>new EE_Boolean_Field('LIN_taxable', __("Taxable?", "event_espresso"), true,false),
				'LIN_item_id'=>new EE_Plain_Text_Field('LIN_item_id', __("ID of Item purchased. NOT for querying", "event_espresso"), true,null),
				'LIN_item_type'=>new EE_Plain_Text_Field('LIN_item_type', __("Type of Line Item purchased. NOT for querying", "event_espresso"), true,null),
			)
		);
		$this->_model_relations = array(
			'Transaction'=>new EE_Belongs_To_Relation()
		);
		parent::__construct( $timezone );
	}
}