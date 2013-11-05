<?php

/*
Plugin Name: Advanced Custom Fields: Data Selector Field
Plugin URI: https://github.com/theideabureau/acf-data-selector-field
Description: Adds a custom data selector field to Advanced Custom Fields. Allows for custom data sets to be used within the relationship field.
Version: 1.1.0
Author: Ben Everard
Author URI: http://beneverard.co.uk/
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