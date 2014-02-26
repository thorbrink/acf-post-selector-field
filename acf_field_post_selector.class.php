<?php

class acf_field_post_selector extends acf_field
{
	// vars
	var $settings, // will hold info such as dir / path
		$defaults; // will hold default field options


	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/

	function __construct()
	{
		// vars
		$this->name = 'post_selector';
		$this->label = __('Post Selector');
		$this->category = __("Basic",'acf'); // Basic, Content, Choice, etc
		$this->defaults = array(
			// add default here to merge into your field.
			// This makes life easy when creating the field options as you don't need to use any if( isset('') ) logic. eg:
			//'preview_size' => 'thumbnail'
		);

		// do not delete!
    parent::__construct();

    // settings
		$this->settings = array(
			'path' => apply_filters('acf/helpers/get_path', __FILE__),
			'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
			'version' => '1.0.0'
		);
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

	function create_options($field)
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/

		// key is needed in the field names to correctly save the data
		$key = $field['name'];


		// Create Field Options HTML
		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Post Types", 'acf-post-selector'); ?></label>
				<p class="description"><?php _e("Selected Post Types will appear in the field.", 'acf-post-selector'); ?></p>
			</td>
			<td>
				<?php
				    $post_types = get_post_types();
				    foreach($post_types as &$post_type)
				    {
				    	$post_type_object = get_post_type_object($post_type);
				    	$post_type = $post_type_object->labels->name;
				    }
				    
				    do_action('acf/create_field', array(
				    	'type'    =>  'checkbox',
				    	'name'    =>  'fields[' . $key . '][post_types]',
				    	'value'   =>  $field['post_types'],
				    	'layout'  =>  'horizontal',
				    	'choices' =>  $post_types
				    )); ?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Taxonomies", 'acf-post-selector'); ?></label>
				<p class="description"><?php _e("Selected Taxonomies will appear in the field.", 'acf-post-selector'); ?></p>
			</td>
			<td>
				<?php
				    $taxonomies 			= get_taxonomies('', 'objects');
				    $sanitized_taxonomies 	= array();
				    
				    foreach($taxonomies as $taxonomy)
				    {
						$sanitized_taxonomies[$taxonomy->name] = $taxonomy->label;
				    }
				    
				    do_action('acf/create_field', array(
				    	'type'    =>  'checkbox',
				    	'name'    =>  'fields[' . $key . '][taxonomies]',
				    	'value'   =>  $field['taxonomies'],
				    	'layout'  =>  'horizontal',
				    	'choices' =>  $sanitized_taxonomies
				    )); ?>
			</td>
		</tr><?php
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
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/

		// perhaps use $field['preview_size'] to alter the markup?


		// create Field HTML
		// Get posts to be shown in select box.
		$posts = $this->get_posts($field);
		$taxonomies = $this->get_taxonomies($field);
		?>
		<select class="post-selector" name="<?php echo $field['name'] ?>">

			<option value=""><?php _e('None', 'acf-post-selector') ?></option>

			<?php foreach($posts as $post_type => $post_type_posts) : ?>

				<?php $post_type_object = get_post_type_object($post_type);	$post_type_name = $post_type_object->labels->name; ?>

				<optgroup label="<?php echo $post_type_name ?>">

					<?php foreach( $post_type_posts as $post ) : ?>

						<?php $selected = ($field['value'] === 'post-' . $post->ID) ? 'selected' : '' ?>

						<option <?php echo $selected ?> value="<?php echo 'post-' . $post->ID ?>"><?php echo $post->post_title ?></option>

					<?php endforeach ?>

				</optgroup>

			<?php endforeach ?>
			
			<?php foreach($taxonomies as $taxonomy => $terms) : ?>

				<?php $taxonomy_object = get_taxonomy($taxonomy); $taxonomy_name = $taxonomy_object->label; ?>

				<optgroup label="<?php echo $taxonomy_name ?>">

					<?php foreach( $terms as $term ) : ?>

						<?php $selected = ( $field['value'] === 'term-' . $term->term_id ) ? 'selected' : '' ?>

						<option <?php echo $selected ?> value="<?php echo 'term-' . $term->term_id ?>"><?php echo $term->name ?></option>

					<?php endforeach ?>

				</optgroup>

			<?php endforeach ?>
			
		</select>
		<?php
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


		wp_register_script(
			'select2',
			$this->settings['dir'] . 'js/select2.js',
			array('jquery'),
			$this->settings['version']
		);

		wp_register_script(
			'acf-input-post_selector',
			$this->settings['dir'] . 'js/input.js',
			array('acf-input', 'jquery', 'select2'),
			$this->settings['version']
		);
		
		wp_register_style(
			'select2', $this->settings['dir'] . 'css/select2.css',
			array('acf-input'),
			$this->settings['version']
		);

		// scripts
		wp_enqueue_script(array(
			'acf-input-post_selector',
		));

		// styles
		wp_enqueue_style(array(
			'select2',
		));

	}


	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add css and javascript to assist your create_field() action.
	*
	*  @info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function input_admin_head()
	{
		// Note: This function can be removed if not used
	}


	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add css + javascript to assist your create_field_options() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function field_group_admin_enqueue_scripts()
	{
		// Note: This function can be removed if not used
	}


	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add css and javascript to assist your create_field_options() action.
	*
	*  @info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_head
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	function field_group_admin_head()
	{
		// Note: This function can be removed if not used
	}


	/*
	*  load_value()
	*
	*  This filter is appied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value found in the database
	*  @param	$post_id - the $post_id from which the value was loaded from
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the value to be saved in te database
	*/

	function load_value($value, $post_id, $field)
	{
		// Note: This function can be removed if not used
		return $value;
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

	function update_value($value, $post_id, $field)
	{
		// Note: This function can be removed if not used
		return $value;
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

	function format_value($value, $post_id, $field)
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/

		// perhaps use $field['preview_size'] to alter the $value?


		// Note: This function can be removed if not used
		return $value;
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

	function format_value_for_api($value, $post_id, $field)
	{
		// defaults?
		/*
		$field = array_merge($this->defaults, $field);
		*/

		// perhaps use $field['preview_size'] to alter the $value?


		// Note: This function can be removed if not used
		return $value;
	}


	/*
	*  load_field()
	*
	*  This filter is appied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/

	function load_field($field)
	{
		// Note: This function can be removed if not used
		return $field;
	}


	/*
	*  update_field()
	*
	*  This filter is appied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = acf)
	*
	*  @return	$field - the modified field
	*/

	function update_field($field, $post_id)
	{
		// Note: This function can be removed if not used
		return $field;
	}
	
	function get_posts($field)
	{
		$posts = array();
		
		if( $field['post_types'] )
		{
			foreach( $field['post_types'] as $post_type )
			{				
				$post_type_posts = get_posts(array(
				    'post_type'			=> $post_type,
				    'posts_per_page'	=> -1,
				    'status'			=> 'publish'
				));
				
				if( sizeof($post_type_posts) > 0 ) $posts[$post_type] = $post_type_posts;
			}
		}
		
		return $posts;
	}
	
	function get_taxonomies($field)
	{
		$taxonomies = array();
		
		if( $field['taxonomies'] )
		{
			foreach( $field['taxonomies'] as $taxonomy )
			{				
				$taxonomies[$taxonomy] = get_terms( $taxonomy );
			}
		}
		
		return $taxonomies;
	}
}

new acf_field_post_selector();