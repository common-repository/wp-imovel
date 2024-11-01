<?php
/*
Plugin Name: WP-Imovel
Plugin URI: http://omniwp.com.br
Description: Property and Real Estate Management Plugin for WordPress.
Author: Gabriel Reguly 
Version: 1.0.1
Author URI: http://omniwp.com.br/
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Text Domain: wp-imovel

Copyright 2010  TwinCitiesTech.com Inc.   (website: twincitiestech.com)
Copyright 2012  omniWP                    (website: http://omniwp.com.br/)
*/

function wp_imovel_load_textdomain() {
	load_plugin_textdomain( 'wp-imovel', false, '/wp-imovel/languages' );
}
add_action( 'plugins_loaded', 'wp_imovel_load_textdomain', 2 );

/** Path for front-end links */
define( 'WP_IMOVEL_URL', WP_PLUGIN_URL . '/wp-imovel' );

/** Directory path for includes */
define( 'WP_IMOVEL_DIR', WP_PLUGIN_DIR .'/wp-imovel' );

/** Directory path for includes of template files  */
define( 'WP_IMOVEL_TEMPLATES', WP_IMOVEL_DIR . '/templates' );

/** Sets prefix for UD_UI and UD_F classes and their functions */
define( 'UD_UI_PREFIX', 'wp-imovel_' );
define( 'UD_PREFIX', 'wp-imovel_' );

// Global Usability Dynamics / TwinCitiesTech.com, Inc. Functions
include WP_IMOVEL_DIR . '/core/class_ud.php';
	
/** Loads built-in plugin metadata and allows for third-party modification to hook into the filters. 
Has to be included here to run after template functions.php */
include WP_IMOVEL_DIR . '/action_hooks.php';
	
/** Defaults filters and hooks */
include WP_IMOVEL_TEMPLATES . '/default_api.php';

/** Loads general functions used by WP-Imovel */
include WP_IMOVEL_DIR . '/core/class_functions.php';
 
 /** Loads widgets */
include WP_IMOVEL_DIR . '/core/class_widgets.php';

 /** Loads all the metaboxes for the property page */
include WP_IMOVEL_DIR . '/core/ui/wp-imovel-property-metaboxes.php';
 
/** Loads all the metaboxes for the property page */
include WP_IMOVEL_DIR . '/core/class_core.php';

// Register activation hook -> has to be in the main plugin file
register_activation_hook( __FILE__, array( 'WP_IMOVEL_F', 'activation' ));

// Register activation hook -> has to be in the main plugin file
register_deactivation_hook( __FILE__, array( 'WP_IMOVEL_F', 'deactivation' ));

// Setup widgets (they need to be called early)  
add_action( 'widgets_init', create_function( '', 'return register_widget("wp_imovel_featured_properties_widget");' ));
add_action( 'widgets_init', create_function( '', 'return register_widget("wp_imovel_search_properties_widget");' ));
//add_action( 'widgets_init', create_function( '', 'return register_widget("wp_imovel_child_properties_widget");' ));
add_action( 'widgets_init', create_function( '', 'return register_widget("wp_imovel_gallery_properties_widget");' ));

// Initiate the plugin
add_action( 'init', create_function( '', 'new WP_IMOVEL_Core;' ));
?>