# ACF { Data Selector Field

Adds a 'Data Selector' field type for the [Advanced Custom Fields](http://wordpress.org/extend/plugins/advanced-custom-fields/) WordPress plugin.

-----------------------

### Overview

This add-on adds a Data Selector field type to the Advanced Custom Fields plugin for version 4 and up. This field allows you to select data from customisable data sources using the relationship format.

Data sources can be registered using one of the following formats:

```
add_action('acf_data_selector/data', function($data) {

	// basic array
	$data['countries'] = array(
		'label' => 'Countries',
		'data' => array(
			'US' => 'United States',
			'UK' => 'United Kingdom'
		)
	);

	// complex array
	$data['rooms'] = array(
		'label' => 'Rooms',
		'data' => array(
			'101' => array(
				'label' => 'Meeting Room',
				'room_number' => '101',
				'floor' => '1'
			),
			'102' => array(
				'label' => 'Stock Room',
				'room_number' => '102',
				'floor' => '1'
			),
			'202' => array(
				'label' => 'Manager Room 1',
				'room_number' => '202',
				'floor' => '2'
			),
		)
	);

	// json array
	$data['regions'] = array(
		'label' => 'Regions',
		'data' => json_decode(file_get_contents('/data/regions.json'), TRUE)
	);

	return $data;

});
```

When using the basic array format the array value is used as the field label. However when using the complex array format be sure to set a `label` item.

When using the complex array format, calling `get_field()` will return the entire array for any given key, this means that *any* additional data we add into the array (room_number and floor in the example above) will be returned.

### Compatibility

This add-on will work with:

* Versions 4 and 5


### Installation

This add-on can be treated as both a WP plugin and a theme include.

**Install as Plugin**

1. Copy the 'acf-data-selector' folder into your plugins folder
2. Activate the plugin via the Plugins admin page

**Include within theme**

1.	Copy the 'acf-data-selector' folder into your theme folder (can use sub folders). You can place the folder anywhere inside the 'wp-content' directory
2.	Edit your functions.php file and add the code below (Make sure the path is correct to include the acf-data-selector.php file)

```php
include_once('acf-data-selector/acf-data-selector.php');

```