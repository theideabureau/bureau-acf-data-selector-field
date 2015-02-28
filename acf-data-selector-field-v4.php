<?php

class acf_field_data_selector extends acf_field
{
	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function __construct() {

		// vars
		$this->name = 'data_selector';
		$this->label = __("Data Selector",'acf');
		$this->category = __("Data Selector",'acf');
		$this->defaults = array(
			'max' => ''
		);
		$this->l10n = array(
			'max'		=> __("Maximum values reached ( {max} values )",'acf'),
			'tmpl_li'	=> '
							<li>
								<a href="#" data-post_id="<%= post_id %>"><%= title %><span class="acf-button-remove"></span></a>
								<input type="hidden" name="<%= name %>[]" value="<%= post_id %>" />
							</li>
							'
		);
		
		
		// do not delete!
		parent::__construct();
		
		
		// settings
		$this->settings = array(
			'path' => apply_filters('acf/helpers/get_path', __FILE__),
			'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
			'version' => '1.0.0'
		);

		// extra
		add_action('wp_ajax_acf/fields/data_selector/get_data', array($this, 'get_data'), 99);
		add_action('wp_ajax_nopriv_acf/fields/data_selector/get_data', array($this, 'get_data'), 99);

	}
	

	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add css + javascript to assist your create_field() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function input_admin_enqueue_scripts()
	{
		// Note: This function can be removed if not used


		// register acf scripts
		wp_register_script('acf-input-data_selector', $this->settings['dir'] . 'assets/v4/js/input.js', array('acf-input'), $this->settings['version']);
		wp_register_style('acf-input-data_selector', $this->settings['dir'] . 'assets/v4/css/input.css', array('acf-input'), $this->settings['version']);


		// scripts
		wp_enqueue_script(array(
			'acf-input-data_selector',
		));

		// styles
		wp_enqueue_style(array(
			'acf-input-data_selector',
		));

	}

	
	/*
	*  load_field()
	*  
	*  This filter is appied to the $field after it is loaded from the database
	*  
	*  @type filter
	*  @since 3.6
	*  @date 23/01/13
	*  
	*  @param $field - the field array holding all the field options
	*  
	*  @return $field - the field array holding all the field options
	*/
	
	function load_field( $field )
	{
		
		// return
		return $field;
	}
	
	
	/*
	*  get_data
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 27/01/13
	*/
	
	function get_data()
	{

		// get data sources
		$data_sources = apply_filters('acf_data_selector/data', array());

		// get the data from that data source		
		$data = $data_sources[$_POST['data_source']]['data'];

		// clean-up data array
		$data = $this->cleanup_array($data);

		// filter data by search
		if ( $_POST['s'] ) {

			foreach ( $data as $key => $value ) {

				if ( stripos($value['label'], $_POST['s']) === FALSE ) {
					unset($data[$key]);
				}

			}

		}
		

		// loop
		foreach ( $data as $key => $value ) {
			
			// right aligned info
			$title = $value['label'];
			
			// filters
			$title = apply_filters('acf/fields/data_selector/result', $title, $post, $field, $the_post);
			$title = apply_filters('acf/fields/data_selector/result/name=' . $field['name'] , $title, $post, $field, $the_post);
			$title = apply_filters('acf/fields/data_selector/result/key=' . $field['key'], $title, $post, $field, $the_post);
			
			// update html
			$r['html'] .= '<li><a href="#" data-post_id="' . $key . '">' . $title .  '<span class="acf-button-add"></span></a></li>';

		}
			
		// return JSON
		echo json_encode( $r );
		
		die();
			
	}
	
	
	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function create_field( $field )
	{

		// global
		global $post;

		// no row limit?
		if ( !$field['max'] || $field['max'] < 1 ) {
			$field['max'] = 9999;
		}
		
		$attributes = array(
			'max' => $field['max'],
			'field_key' => $field['key'],
			'data_source' => $field['data_source']
		);
		
		
		// Lang
		if ( defined('ICL_LANGUAGE_CODE') ) {
			$attributes['lang'] = ICL_LANGUAGE_CODE;
		}
			
		?>
<div class="acf_data_selector<?php echo $class; ?>"<?php foreach( $attributes as $k => $v ): ?> data-<?php echo $k; ?>="<?php echo $v; ?>"<?php endforeach; ?>>
	
	
	<!-- Hidden Blank default value -->
	<input type="hidden" name="<?php echo $field['name']; ?>" value="" />
	
	
	<!-- Left List -->
	<div class="data_selector_left">
		<table class="widefat">
			<thead>

				<tr>
					<th>
						<input class="data_selector_search" placeholder="<?php _e("Search...",'acf'); ?>" type="text" id="data_selector_<?php echo $field['name']; ?>" />
					</th>
				</tr>
				
			</thead>
		</table>
		<ul class="bl data_selector_list">
			<li class="load-more">
				<div class="acf-loading"></div>
			</li>
		</ul>
	</div>
	<!-- /Left List -->
	
	<!-- Right List -->

	<div class="data_selector_right">

		<ul class="bl data_selector_list">

			<?php if ( $field['value'] ) : ?>

				<?php foreach ( $field['value'] as $id => $label ) : ?>
					
					<?php

						// find title. Could use get_the_title, but that uses get_post(), so I think this uses less Memory
						$title = $label;
						
						// filters
						$title = apply_filters('acf/fields/data_selector/result', $title, $p, $field, $post);
						$title = apply_filters('acf/fields/data_selector/result/name=' . $field['name'] , $title, $p, $field, $post);
						$title = apply_filters('acf/fields/data_selector/result/key=' . $field['key'], $title, $p, $field, $post);

					?>
					
					<li>
						<a href="#" class="" data-post_id="<?php echo $id; ?>"><?php echo $title; ?><span class="acf-button-remove"></span></a>
						<input type="hidden" name="<?php echo $field['name']; ?>[]" value="<?php echo $id; ?>" />
					</li>
					
				<?php endforeach; ?>

			<?php endif; ?>

		</ul>

	</div>

	<!-- / Right List -->
	
</div>
		<?php
	}
	
	
	
	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function create_options( $field )
	{
		// vars
		$key = $field['name'];

		// get data sources
		$data_sources = apply_filters('acf_data_selector/data', array());

		// build a list of data options
		$data_options = array();

		foreach ( $data_sources as $data_souce_key => $data_source ) {
			$data_options[$data_souce_key] = $data_source['label'];
		}

		// if the data_source value isn't set, set it as null
		if ( ! isset($field['data_source']) ) {
			$field['data_source'] = NULL;
		}

		?>

			<tr class="field_option field_option_<?php echo $this->name; ?>">
				<td class="label">
					<label><?php _e("Data Set",'acf'); ?></label>
				</td>
				<td>

					<?php if ( ! empty($data_options) ) : ?>

						<?php

							do_action('acf/create_field', array(
								'type'	=>	'select',
								'name'	=>	'fields[' . $key . '][data_source]',
								'value'	=>	$field['data_source'],
								'choices' => $data_options
							));

						?>

					<?php else : ?>

						<strong>No data sets available</strong>

					<?php endif; ?>

				</td>
			</tr>

			<tr class="field_option field_option_<?php echo $this->name; ?>">
				<td class="label">
					<label><?php _e("Maximum choices",'acf'); ?></label>
				</td>
				<td>
					<?php 
					do_action('acf/create_field', array(
						'type'	=>	'number',
						'name'	=>	'fields['.$key.'][max]',
						'value'	=>	$field['max'],
					));
					?>
				</td>
			</tr>

		<?php
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed to the create_field action
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value( $value, $post_id, $field )
	{

		// empty?
		if( !$value )
		{
			return $value;
		}
		
		
		// Pre 3.3.3, the value is a string coma seperated
		if( is_string($value) )
		{
			$value = explode(',', $value);
		}
		
		
		// empty?
		if( !is_array($value) || empty($value) )
		{
			return $value;
		}
	
		
		// get data sources
		$data_sources = apply_filters('acf_data_selector/data', array());

		// get the data from that data source		
		$data = $data_sources[$field['data_source']]['data'];

		// clean-up data array
		$data = $this->cleanup_array($data);

		$values = array();

		foreach ( $value as $data_key ) {
			$values[$data_key] = $data[$data_key]['label'];
		}

		// return value
		return $values;	
	}
	
	
	/*
	*  format_value_for_api()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value_for_api($value, $post_id, $field) {

		// empty?
		if ( ! $value ) {
			return $value;
		}
		
		// empty?
		if ( ! is_array($value) || empty($value) ) {
			return $value;
		}
		
		// get data sources
		$data_sources = apply_filters('acf_data_selector/data', array());

		// get the data from that data source		
		$data = $data_sources[$field['data_source']]['data'];

		$values = array();

		foreach ( $value as $data_key ) {
			$values[$data_key] = $data[$data_key];
		}

		// return value
		return $values;
		
	}
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		return $value;
	}
	
	/*
	*  cleanup_array()
	*
	*  Takes custom data and makes sure it has a label set
	*
	*  @param	$data - the data set values
	*
	*  @return	$value - the modified value
	*/

	function cleanup_array($data) {

		foreach ( $data as $key => $value ) {

			// if the data item is not an array, use the value as the array label
			if ( ! is_array($value) ) {
				$data[$key] = array('label' => $value);
			}

		}

		return $data;

	}
	
}

new acf_field_data_selector();

?>