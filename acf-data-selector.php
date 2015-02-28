<?php

/*
Plugin Name: Advanced Custom Fields: Data Selector Field
Plugin URI: https://github.com/theideabureau/acf-data-selector-field
Description: Adds a custom data selector field to Advanced Custom Fields. Allows for custom data sets to be used within the relationship field.
Version: 2.0.0
Author: Ben Everard
Author URI: http://beneverard.co.uk/
License: GPL
*/



// 1. set text domain
// Reference: https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
load_plugin_textdomain( 'acf-data-selector-field', false, dirname( plugin_basename(__FILE__) ) . '/lang/' ); 




// 2. Include field type for ACF5
// $version = 5 and can be ignored until ACF6 exists
function include_field_types_data_selector_field( $version ) {
	
	include_once('acf-data-selector-field-v5.php');
	
}

add_action('acf/include_field_types', 'include_field_types_data_selector_field');	




// 3. Include field type for ACF4
function register_fields_data_selector_field() {
	
	include_once('acf-data-selector-field-v4.php');
	
}

add_action('acf/register_fields', 'register_fields_data_selector_field');	

