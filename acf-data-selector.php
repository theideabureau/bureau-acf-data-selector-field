<?php

/*
Plugin Name: Advanced Custom Fields: Reusable Group Field
Plugin URI: https://github.com/elliotcondon/acf-location-field
Description: Adds a Location field to Advanced Custom Fields. This field allows you to find addresses and coordinates of a desired location.
Version: 1.0.0
Author: Elliot Condon
Author URI: http://advancedcustomfields.com/
License: GPL
*/


class acf_field_data_selector_plugin
{
	/*
	*  Construct
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 1/04/13
	*/
	
	function __construct()
	{
		// set text domain
		$domain = 'acf-data-selector-field';
		$mofile = trailingslashit(dirname(__File__)) . 'lang/' . $domain . '-' . get_locale() . '.mo';
		load_textdomain( $domain, $mofile );
		
		// version 4+
		add_action('acf/register_fields', array($this, 'register_fields'));	

	}
	

	/*
	*  register_fields
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 1/04/13
	*/
	
	function register_fields()
	{
		include_once('data-selector-v4.php');
	}
	
}

new acf_field_data_selector_plugin();