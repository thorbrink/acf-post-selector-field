<?php
/*
Plugin Name: ACF Post Selector Field
Description: Enables a field in ACF from which to any post of any post type can be selected.
Version: 1.0
Author: Iseqqavoq
Author URI: http://www.iqq.se
*/

// create field
function register_post_selector_field()
{
	include_once('acf_field_post_selector.class.php');
}

add_action('acf/register_fields', 'register_post_selector_field');

?>