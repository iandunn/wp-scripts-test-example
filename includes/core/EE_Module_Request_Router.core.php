<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package			Event Espresso
 * @ author			Seth Shoultes
 * @ copyright		(c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license			http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link					http://www.eventespresso.com
 * @ version		 	4.0
 *
 * ------------------------------------------------------------------------
 *
 * EE_Module_Request_Router
 *
 * @package			Event Espresso
 * @subpackage	/core/
 * @author				Brent Christensen 
 *
 * ------------------------------------------------------------------------
 */
final class EE_Module_Request_Router {

	/**
	 * 	@var 	array	$_previous_routes
	 *  @access 	private
	 */
	private static $_previous_routes = array();

	/**
	 * 	@var 	string	$_current_route
	 *  @access 	private
	 */
	private $_current_route = NULL;

	/**
	 * 	EE_Registry Object
	 *	@var 	EE_Registry	$EE	
	 * 	@access 	protected
	 */
	protected $EE = NULL;





	/**
	 * 	class constructor
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function __construct() {
		$this->EE = EE_Registry::instance();
	}



	/**
	 * 	setter
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function get_routes() {
		// assume this if first route being called
		$previous_route = FALSE;
		// but is it really ???
		if ( ! empty( self::$_previous_routes )) {
			// get last run route
			$previous_route = array_pop( array_values( self::$_previous_routes ));
		}
		//  has another route already been run ?
		if ( $previous_route ) {			
			// check if  forwarding has been set
			$this->_current_route = $this->get_forward();
			try {
				//check for recursive forwarding
				if ( isset( self::$_previous_routes[ $this->_current_route ] )) {
					throw new EE_Error( 
						sprintf( __('An error occured. The %s route has already been called, and therefore can not be forwarded to, because an infinite loop would be created and break the interweb.','event_espresso'),  $this->_current_route )
					);
				}
			} catch ( EE_Error $e ) {
				$e->get_error();
			}			
		} else {
			// first route called		
			// check request for module route
			if ( ! $this->EE->REQ->is_set( 'ee' )) {
				return NULL;
			}
			// grab and sanitize module route
			$this->_current_route = $this->EE->REQ->get( 'ee' );
		}
		// sorry, but I can't read what you route !
		if ( empty( $this->_current_route )) {
			return NULL;
		}
		// get module method that route has been mapped to 
		$module_method = EE_Config::get_route( $this->_current_route );
//		printr( $module_method, '$module_method  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		// verify result was returned
		if ( empty( $module_method )) {
			$msg = sprintf( __( 'An error has occured. The requested route %s could not be mapped to any registered modules.', 'event_espresso' ), $this->_current_route );
			EE_Error::add_error( $msg, __FILE__, __FUNCTION__, __LINE__ );
			return FALSE;
		}
		// verfiy that result is an array
		if ( ! is_array( $module_method )) {
			$msg = sprintf( __( 'An error has occured. The %s  route has not been properly registered.', 'event_espresso' ), $this->_current_route );
			EE_Error::add_error( $msg . '||' . $msg, __FILE__, __FUNCTION__, __LINE__ );
			return FALSE;
		}
		// grab module name
		$module_name = $module_method[0];
		// verfiy that a class method was registered properly
		if ( ! isset( $module_method[1] )) {
			$msg = sprintf( __( 'An error has occured. A class method for the %s  route has not been properly registered.', 'event_espresso' ), $this->_current_route );
			EE_Error::add_error( $msg . '||' . $msg, __FILE__, __FUNCTION__, __LINE__ );
			return FALSE;
		}
		// grab method
		$method = $module_method[1];
		// verfiy that class exists
		if ( ! class_exists( $module_name )) {
			$msg = sprintf( __( 'An error has occured. The requested %s class could not be found.', 'event_espresso' ), $module_name );
			EE_Error::add_error( $msg, __FILE__, __FUNCTION__, __LINE__ );
			return FALSE;
		}
		// verfiy that method exists
		if ( ! method_exists( $module_name, $method )) {
			$msg = sprintf( __( 'An error has occured. The class method %s for the %s route is in invalid.', 'event_espresso' ), $method, $this->_current_route );
			EE_Error::add_error( $msg . '||' . $msg, __FILE__, __FUNCTION__, __LINE__ );
			return FALSE;
		}
		// instantiate module and call route method
		if ( $module = $this->_route_factory( $module_name, $method )) {
			// if module is successfully created, then add it to previous routes array
			self::$_previous_routes[] = $this->_current_route;
			return $module;
		}		
		return FALSE;
	}



	/**
	 * 	getter
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	private function _route_factory( $module_name, $method ) {
		// let's pause to reflect on this...
		$mod_reflector = new ReflectionClass( $module_name );
		// ensure that class is actually a module
		if ( ! $mod_reflector->isSubclassOf( 'EED_Module' )) {
			$msg = sprintf( __( 'An error has occured. The requested %s module is not of the class EED_Module.', 'event_espresso' ), $module_name );
			EE_Error::add_error( $msg, __FILE__, __FUNCTION__, __LINE__ );
			return FALSE;
		}
		// and pass the request object to the run method
		$module = $mod_reflector->newInstance( $this->EE );
		// now add a hook for whatever action is being called
		add_action( 'wp_loaded', array( $module, $method ));
		return $module;
	}


	/**
	 * 	getter
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function get_forward() {
		return EE_Config::get_forward( $this->_current_route );
	}

	/**
	 * 	getter
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function get_view() {
		return EE_Config::get_view( $this->_current_route );
	}



	/**
	 *		@ override magic methods
	 *		@ return void
	 */	
	public function __set($a,$b) { return FALSE; }
	public function __get($a) { return FALSE; }
	public function __isset($a) { return FALSE; }
	public function __unset($a) { return FALSE; }
	public function __clone() { return FALSE; }
	public function __wakeup() { return FALSE; }	
	public function __destruct() { return FALSE; }		

}
// End of file EE_Module_Request_Router.core.php
// Location: /core/EE_Module_Request_Router.core.php