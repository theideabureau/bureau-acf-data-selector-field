<?php

class acf_field_data_selector extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		$this->name = 'data_selector';
		$this->label = __('Data Selector', 'acf-data_selector');
		$this->category = 'Data Selector';
		$this->defaults = array(
			'max'	=> '',
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
		
		// get data
		add_action('wp_ajax_acf/fields/data_selector/get_data', array($this, 'get_data'), 99);
		add_action('wp_ajax_nopriv_acf/fields/data_selector/get_data', array($this, 'get_data'), 99);

		// do not delete!
    	parent::__construct();
    	
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field_settings( $field ) {
		
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

		acf_render_field_setting( $field, array(
			'label'			=> __('Data set', 'acf-data_selector'),
			'instructions'	=> __('Specify the data to be used for this field', 'acf-data_selector'),
			'type'			=> 'select',
			'name'			=> 'data_source',
			'choices'		=> $data_options
		));
		
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum choices', 'acf-data_selector'),
			'instructions'	=> __('Define the maximum number of choices the user can select','acf-data_selector'),
			'type'			=> 'number',
			'name'			=> 'max'
		));

	}
	
	
	

	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {
		
		// vars
		$values = array();

		// no row limit?
		if ( ! $field['max'] || $field['max'] < 1 ) {
			$field['max'] = 9999;
		}

		$atts = array(
			'id'				=> $field['id'],
			'class'				=> "acf-data_selector {$field['class']}",
			'data-max'			=> $field['max'],
			'data-s'			=> '',
			'data-paged'		=> 1,
			'field_key'			=> $field['key'],
			'data_source'		=> $field['data_source']
		);

		// get data sources
		$data_sources = apply_filters('acf_data_selector/data', array());

		// get the data from that data source		
		$data = $data_sources[$field['data_source']]['data'];

		// clean-up data array
		$data = $this->cleanup_array($data);
		
		// Lang
		if ( defined('ICL_LANGUAGE_CODE') ) {
			$atts['data-lang'] = ICL_LANGUAGE_CODE;
		}
		
		// width for select filters
		$width = array(
			'search'	=> 100
		);
			
		?>
<div <?php acf_esc_attr_e($atts); ?>>
	
	<div class="acf-hidden">
		<input type="hidden" name="<?php echo $field['name']; ?>" value="" />
	</div>
	
	<?php if( $width['search'] > 0 ): ?>
	<div class="filters">
		
		<ul class="acf-hl">
		
			<?php if( $width['search'] > 0 ): ?>
			<li style="width:<?php echo $width['search']; ?>%;">
				<div class="inner">
				<input class="filter" data-filter="s" placeholder="<?php _e("Search...",'acf'); ?>" type="text" />
				</div>
			</li>
			<?php endif; ?>
			
		</ul>
		
	</div>
	<?php endif; ?>
	
	<div class="selection acf-cf">
	
		<div class="choices">
		
			<ul class="acf-bl list"></ul>
			
		</div>
		
		<div class="values">
		
			<ul class="acf-bl list">
			
				<?php if ( $field['value'] ) : ?>

					<?php foreach ( $field['value'] as $id ) : ?>
						
						<li>
							
							<input type="hidden" name="<?php echo $field['name']; ?>[]" value="<?php echo $id; ?>" />
							
							<span data-id="<?php echo $id; ?>" class="acf-rel-item">
								<?php echo $data[$id]['label']; ?>
								<a href="#" class="acf-icon small dark" data-name="remove_item"><i class="acf-sprite-remove"></i></a>
							</span>

						</li>

					<?php endforeach; ?>

				<?php endif; ?>
				
			</ul>
			
		</div>
		
	</div>
	
</div>
		<?php
	}
	
	
		
	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function input_admin_enqueue_scripts() {
		
		$dir = plugin_dir_url( __FILE__ );
		
		wp_register_script('acf-input-data_selector', $dir . 'assets/v5/js/input.js');
		wp_enqueue_script('acf-input-data_selector');

		wp_register_style('acf-input-data_selector', $dir . 'assets/v5/css/input.css');
		wp_enqueue_style('acf-input-data_selector');
		
	}
	
	
	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
		
	function input_admin_head() {
	
		
		
	}
	
	*/
	
	
	/*
   	*  input_form_data()
   	*
   	*  This function is called once on the 'input' page between the head and footer
   	*  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and 
   	*  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
   	*  seen on comments / user edit forms on the front end. This function will always be called, and includes
   	*  $args that related to the current screen such as $args['post_id']
   	*
   	*  @type	function
   	*  @date	6/03/2014
   	*  @since	5.0.0
   	*
   	*  @param	$args (array)
   	*  @return	n/a
   	*/
   	
   	/*
   	
   	function input_form_data( $args ) {
	   	
		
	
   	}
   	
   	*/
	
	
	/*
	*  input_admin_footer()
	*
	*  This action is called in the admin_footer action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_footer)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
		
	function input_admin_footer() {
	
		
		
	}
	
	*/
	
	
	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
	
	function field_group_admin_enqueue_scripts() {
		
	}
	
	*/

	
	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
	
	function field_group_admin_head() {
	
	}
	
	*/


	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	/*
	
	function load_value( $value, $post_id, $field ) {
		
		return $value;
		
	}
	
	*/
	
	
	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	function update_value( $value, $post_id, $field ) {
		return $value;
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
		
	function format_value( $value, $post_id, $field ) {
	
		// empty?
		if ( ! $value ) {
			return $value;
		}
		
		
		// Pre 3.3.3, the value is a string coma seperated
		if ( is_string($value) ) {
			$value = explode(',', $value);
		}
		
		
		// empty?
		if( ! is_array($value) || empty($value) ) {
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
			$values[$data_key] = $data[$data_key];
		}

		// return value
		return $values;	

	}

	// custom methods

	function get_data() {

		// get data sources
		$data_sources = apply_filters('acf_data_selector/data', array());

		// get the data from that data source		
		$data = $data_sources['regions']['data'];
		// $data = $data_sources['$_POST['data_source']']['data'];

		// clean-up data array
		$data = $this->cleanup_array($data);
// print_r($data);
		// filter data by search
		if ( $_POST['s'] ) {

			foreach ( $data as $key => $value ) {

				if ( stripos($value['label'], $_POST['s']) === FALSE ) {
					unset($data[$key]);
				}

			}

		}
		
		$r = array();

		// loop
		foreach ( $data as $key => $value ) {
			
			// update html
			$r[] = array(
				'id' => $key,
				'text' => $value['label']
			);

		}
			
		// return JSON
		echo json_encode( $r );
		
		die();
			
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


// create field
new acf_field_data_selector();

?>
