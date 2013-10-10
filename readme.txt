=== Advanced Custom Fields: Location Field ===
Contributors: beneverard
Tags: admin, advanced, custom, field, custom field, data, selector, source
Requires at least: 4
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a Data Selector field to the Advanced Custom Fields plugin for version 4 and up. This field allows you to select data from customisable data sources using the relationship format.

== Description ==

This is an add-on for the [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/)
WordPress plugin and will not provide any functionality to WordPress unless Advanced Custom Fields is installed
and activated.

**This plugin has been written to work version 4 and up of ACF.**

= Source Repository on GitHub =
https://github.com/beneverard/acf-data-selector-field

= Bugs, Questions or Suggestions =
https://github.com/beneverard/acf-data-selector-field/issues

= Usage =

Make sure you read the [Advanced Custom Fields](http://www.advancedcustomfields.com/docs/getting-started/)'s documentation first.

**Back-end**

The Data Selector field comes with 2 options:

1. The data source
2. The maximum selectable values

**Front-end**

Retrieving the value(s) on the front-end differs according to the Map address options.

* Using get_field() will return the entire data source array.
* Using the_field() currently does not work very well

== Installation ==

This software can be treated as both a WP plugin and a theme include.

= Plugin =
1. Copy the 'acf-data-selector' folder into your plugins folder
2. Activate the plugin via the Plugins admin page

= Include =
1. Copy the 'acf-data-selector' folder into your theme folder (can use sub folders)
   * You can place the folder anywhere inside the 'wp-content' directory
2. Edit your functions.php file and add the following code to include the field:

`
add_action('acf/register_fields', 'my_register_fields');

function my_register_fields()
{
	include_once('acf-data-selector/acf-data-selector.php');
}
`

3. Make sure the path is correct to include the acf-data-selector.php file


== Screenshots ==

1. Data selector field and its options
2. Data selector field on an edit-post page
3. Search and some selected values


== Changelog ==

= 0.0.1 =
* Initial Release.
