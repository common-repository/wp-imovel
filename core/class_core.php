<?php
/**
 * WP-Imovel Core Framework Class
 *
 * Contains primary functions for setting up the framework of the plugin.
 *
 * @version 0.60
 * @package WP-Imovel
 * @subpackage Main
 */
class WP_IMOVEL_Core {
	/**
	 * Primary function of WP_IMOVEL_Core, gets called by init.
	 *
     *
	 * @todo  Find a way of not having to call $wp_rewrite->flush_rules(); on every load.
	 * @since 0.60
	 * @uses $wp_imovel WP-Imovel configuration array
	 * @uses $wp_rewrite WordPress rewrite object
 	 * @access public
	 *
	 */
	function WP_IMOVEL_Core() {
		global $wp_imovel, $wp_rewrite;
		
		WP_IMOVEL_F::settings_action();
		
		// Load early so plugins can use them as well
		wp_register_style( 'jquery-fancybox-css', WP_IMOVEL_URL. '/third-party/fancybox/jquery.fancybox-1.3.4.css' );
		
		wp_register_script( 'jquery-fancybox', WP_IMOVEL_URL. '/third-party/fancybox/jquery.fancybox-1.3.4.pack.js', array( 'jquery' ) );
		wp_register_script( 'jquery-easing',   WP_IMOVEL_URL. '/third-party/fancybox/jquery.easing-1.3.pack.js',     array( 'jquery' ) );
		wp_register_script( 'jquery-slider',   WP_IMOVEL_URL. '/js/jquery.ui.slider.min.js',                         array( 'jquery' ) );
		wp_register_script( 'jquery-cookie',   WP_IMOVEL_URL. '/js/jquery.cookie.js',                                array( 'jquery' ) );

		wp_register_script( 'wp-imovel-property-admin-overview', WP_IMOVEL_URL. '/js/wp-imovel-property-admin-overview.js', array( 'jquery' ) );
		wp_register_script( 'wp-imovel-media-admin-overview',    WP_IMOVEL_URL. '/js/wp-imovel-media-admin-overview.js',    array( 'jquery' ) );
		wp_register_script( 'wp-imovel-property-global',         WP_IMOVEL_URL. '/js/wp-imovel-property-global.js',         array( 'jquery' ) );
		
		// Find and register stylesheet
		if ( file_exists( STYLESHEETPATH  . '/wp_imovel.css' ) ) {
			wp_register_style( 'wp-imovel-property-frontend', STYLESHEETPATH . '/wp_imovel.css',   array(),'0.63' );
		} elseif ( file_exists( WP_IMOVEL_TEMPLATES . '/wp_imovel.css' ) && $wp_imovel['configuration']['autoload_css'] == 'true' ) {
			wp_register_style( 'wp-imovel-property-frontend', WP_IMOVEL_URL . '/templates/wp_imovel.css',  array(), '0.63' );
		}
		// Find and register MSIE stylesheet
		if ( file_exists( STYLESHEETPATH  . '/wp_imovel-msie.css' ) ) {
			wp_register_style( 'wp-imovel-property-frontend-msie', STYLESHEETPATH . '/wp_imovel-msie.css',   array(),'0.63' );
		} elseif ( file_exists( WP_IMOVEL_TEMPLATES . '/wp_imovel-msie.css' ) && $wp_imovel['configuration']['autoload_css'] == 'true' ) {
			wp_register_style( 'wp-imovel-property-frontend-msie', WP_IMOVEL_URL . '/templates/wp_imovel-msie.css',  array(), '0.63' );
		}
		// Find front-end JavaScript and register the script
		if ( file_exists( STYLESHEETPATH  . '/wp_imovel.js' ) ) {
			wp_register_script( 'wp-imovel-property-frontend', STYLESHEETPATH . '/wp_imovel.js', array(),'0.63' );
 		} elseif ( file_exists( WP_IMOVEL_TEMPLATES . '/wp_imovel.js' ) ) {
			wp_register_script( 'wp-imovel-property-frontend', WP_IMOVEL_URL . '/templates/wp_imovel.js', array(), '0.63' );
 		}
		
		// Add troubleshoot log page
		if ( $wp_imovel['configuration']['show_ud_log'] == 'true' ) {
			UD_F::add_log_page();
		}
		// Init action hook
		do_action( 'wp_imovel_init' );
		
		// Modify admin body class
		add_filter( 'admin_body_class', array( $this, 'wp_imovel_admin_body_class' ) );
		
		//Modify Front-end property body class
		add_filter('body_class',        array( $this, 'wp_imovel_properties_body_class' ) );

	// Ajax functions
		add_action( 'wp_ajax_wp_imovel_ajax_property_query', create_function( '', ' $class = WP_IMOVEL_F::get_property($_REQUEST["property_id"]); if ( $class)  print_r($class); else echo __("No property found.","wp-imovel"); die();' ) );
		
	// Make Property Featured Via AJAX
		if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp_imovel_make_featured_' . $_REQUEST['post_id'] ) ) {
			add_action( 'wp_ajax_wp_imovel_make_featured', create_function( '', ' $post_id = $_REQUEST[post_id]; echo WP_IMOVEL_F::toggle_featured($post_id); die();' ) );
		}				
	// Toggle Property Image Privacy Via AJAX
		if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp_imovel_image_privacy_' . $_REQUEST['post_id'] ) ) {
			add_action( 'wp_ajax_wp_imovel_toggle_image_privacy', create_function( '', ' $post_id = $_REQUEST[post_id]; echo WP_IMOVEL_F::toggle_privacy($post_id); die();' ) );
		}				
	// Plug page actions -> Add Settings Link to plugin overview page
		add_filter( 'plugin_action_links', array( $this, 'wp_imovel_plugin_action_links' ), 10, 2 );

 		//Ajax pagination for property_overview
 		add_action( 'wp_ajax_wp_imovel_property_overview_pagination',        array( $this, 'wp_imovel_ajax_property_overview' ) );
        add_action( 'wp_ajax_nopriv_wp_imovel_property_overview_pagination', array( $this, 'wp_imovel_ajax_property_overview' ) );

	// our shortcodes 
 		add_shortcode( 'property_overview',   array( $this, 'shortcode_property_overview' ) );
 		add_shortcode( 'property_search',     array( $this, 'shortcode_property_search' ) );
 		add_shortcode( 'featured_properties', array( $this, 'shortcode_featured_properties' ) );

	// our image sizes
		foreach( $wp_imovel['image_sizes'] as $image_name => $image_sizes ) {
			add_image_size( $image_name, $image_sizes['width'], $image_sizes['height'], true ); 
		}
		
	// admin listing page for our custom post type
		add_filter( 'manage_edit-wp_imovel_property_columns', array( $this, 'wp_imovel_property_edit_columns' ) ); 
		add_filter( 'display_post_states',                    array( $this, 'wp_imovel_post_states' ) );
		add_action( 'manage_pages_custom_column',             array( $this, 'wp_imovel_custom_columns' ) );
		add_action( 'right_now_content_table_end',            array( $this, 'wp_imovel_property_count' ) );

	// admin listing page for media ( some of our images can be 'private' )
		//add_filter( 'wp_generate_attachment_metadata', array( $this, 'wp_imovel_process_image' ) );
		add_filter( 'manage_media_columns',            array( $this, 'wp_imovel_media_columns' ) );
		add_action( 'manage_media_custom_column',      array( $this, 'wp_imovel_media_custom_column' ), 10, 2 );
		
	// 	the screen layout for editing our custom post type
		add_filter( 'screen_layout_columns', array( $this, 'wp_imovel_stats_columns' ), 10, 2 );
		
	// Called in setup_postdata().  
	// We add property values here to make available in global $post variable on frontend
		add_action( 'the_post',                    array( 'WP_IMOVEL_F', 'wp_imovel_the_property' ) );
		add_action( 'the_content',                 array( $this,         'wp_imovel_the_content' ) );

		add_action( 'template_redirect',           array( $this, 'wp_imovel_template_redirect' ) );

	// Admin interface init
		add_action( 'admin_init',                  array( $this, 'wp_imovel_admin_init' ) );
	    add_action( 'admin_print_styles',          array( $this, 'wp_imovel_admin_css' ) );
		add_action( 'admin_menu',                  array( $this, 'wp_imovel_admin_menu' ) );
		add_action( 'add_meta_boxes',              array( $this, 'wp_imovel_manage_meta_boxes' ) );

 		add_action( 'post_submitbox_misc_actions', array( WP_IMOVEL_UI, 'wp_imovel_property_submitbox_misc_actions' ) );

		add_action( 'save_post',                   array( $this, 'wp_imovel_save_property' ) );
		add_filter( 'post_updated_messages',       array( $this, 'wp_imovel_property_updated_messages' ) );
	// Fix toggable row actions -> get rid of "Quick Edit" on property rows
		add_filter( 'page_row_actions',            array( $this, 'wp_imovel_property_row_actions' ),0 ,2 );
	// Fix 404 errors
		add_filter( 'parse_query',                 array( $this, 'wp_imovel_fix_404' ) );
	// Load admin header scripts
		add_action( 'admin_enqueue_scripts',       array( $this, 'wp_imovel_admin_enqueue_scripts' ) );
    // Add our meta values to search
		add_filter( 'pre_get_posts',               array( $this, 'wp_imovel_enhance_search_filter' ) );
	// fix the posts' SQL request to search our meta values	
		add_filter( 'posts_request',               array( $this, 'wp_imovel_request_filter' ) );
		

	// Register our custom post type wp_imovel_property
		$labels = array(
			'name' => __( 'Properties', 'wp-imovel' ),
			'singular_name' => __( 'Property', 'wp-imovel' ),
			'add_new' => __( 'Add New', 'wp-imovel' ),
			'add_new_item' => __( 'Add New Property', 'wp-imovel' ),
			'edit' => __( 'Edit', 'wp-imovel' ),
			'edit_item' => __( 'Edit Property', 'wp-imovel' ),
			'new_item' => __( 'New Property', 'wp-imovel' ),
			'view' => __( 'View', 'wp-imovel' ),
			'view_item' => __( 'View Property', 'wp-imovel' ),
			'search_items' => __( 'Search Properties', 'wp-imovel' ),
			'not_found' =>  __( 'No properties found', 'wp-imovel' ),
			'not_found_in_trash' => __( 'No properties found in Trash', 'wp-imovel' ),
			'parent_item_colon' => ':'
		);
		$args = array(
			'labels' => $labels,
			'singular_label' => __( 'Property', 'wp-imovel' ),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
 			'menu_position' => 2,
			'hierarchical' => true,
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
			'rewrite' => array( 'slug' => $wp_imovel['configuration']['base_slug'],  'with_front' => false ),
			'query_var' => $wp_imovel['configuration']['base_slug'],
			'menu_icon' => WP_IMOVEL_URL . '/images/wp-imovel-menu-icon.png',
			'description' => __( 'Easily manage your properties here.', 'wp-imovel' ),
			'has_archive' => 'vendido'
		);
		register_post_type( 'wp_imovel_property', $args );
		
		register_post_status( 'sold',  array(
		'label'       => __( 'Sold', 'wp-imovel' ),
		'protected'   => true,
		'show_in_admin_all' => true,
		'show_in_admin_status_list' => true, 
		'show_in_admin_all_list' => true,
		'hierarchical' => true, 
		'public' => true,
		'label_count' => _n_noop('Vendido <span class="count">(%s)</span>', 'Vendidos <span class="count">(%s)</span>' ),
		) );

	// Register our 3 taxonomies
		// taxonomy wp_imovel_property_status
		$labels = array( 
			'name' => __( 'Property Status', 'wp-imovel' ),
			'singular_name' => __( 'Property Status', 'wp-imovel' ),
			'search_items' => __( 'Search Property Status', 'wp-imovel' ),
			'all_items' => __( 'All Property Status', 'wp-imovel' ),
			'parent_item' => __( 'Parent Property Status', 'wp-imovel' ),
			'parent_item_colon' => ':',
			'edit_item' => __( 'Edit Property Status', 'wp-imovel' ),
			'update_item' => __( 'Update Property Status', 'wp-imovel' ),
			'add_new_item' => __( 'Add New Property Status', 'wp-imovel' ),
			'new_item_name' => __( 'New Property Status', 'wp-imovel' )
		);
        register_taxonomy( 'wp_imovel_property_status', 'wp_imovel_property',
			array(
				'hierarchical' => true,
				'labels' => $labels,
				'query_var' => 'wp_imovel_property_status',
				'rewrite' => array( 'slug' => 'wp_imovel_property_status' ),
			)
		);
	// taxonomy wp_imovel_property_feature
		$labels = array( 
			'name' => __( 'Property Features', 'wp-imovel' ),
			'singular_name' => __( 'Property Feature', 'wp-imovel' ),
			'search_items' => __( 'Search Property Features', 'wp-imovel' ),
			'all_items' => __( 'All Property Features', 'wp-imovel' ),
			'parent_item' => __( 'Parent Property Feature', 'wp-imovel' ),
			'parent_item_colon' => ':',
			'edit_item' => __( 'Edit Property Feature', 'wp-imovel' ),
			'update_item' => __( 'Update Property Feature', 'wp-imovel' ),
			'add_new_item' => __( 'Add New Property Feature', 'wp-imovel' ),
			'new_item_name' => __( 'New Property Feature', 'wp-imovel' )
		);
        register_taxonomy( 'wp_imovel_property_feature', 'wp_imovel_property',
			array(
				'hierarchical' => true,
				'labels' => $labels,
				'query_var' => 'wp_imovel_property_feature',
				'rewrite' => array( 'slug' => 'wp_imovel_property_feature' )
			)
		);
	// taxonomy wp_imovel_community_feature
		$labels = array( 
			'name' => __( 'Community Features', 'wp-imovel' ),
			'singular_name' => __( 'Community Feature', 'wp-imovel' ),
			'search_items' => __( 'Search Community Features', 'wp-imovel' ),
			'all_items' => __( 'All Community Features', 'wp-imovel' ),
			'parent_item' => __( 'Parent Community Feature', 'wp-imovel' ),
			'parent_item_colon' => ':',
			'edit_item' => __( 'Edit Community Feature', 'wp-imovel' ),
			'update_item' => __( 'Update Community Feature', 'wp-imovel' ),
			'add_new_item' => __( 'Add New Community Feature', 'wp-imovel' ),
			'new_item_name' => __( 'New Community Feature', 'wp-imovel' )
		);
        register_taxonomy( 'wp_imovel_community_feature', 'wp_imovel_property',
			array(
				'hierarchical' => true,
				'labels' => $labels,
				'query_var' => 'wp_imovel_community_feature',
				'rewrite' => array( 'slug' => 'wp_imovel_community_feature' )
			)
		);
/*
 * - name - general name for the taxonomy, usually plural. The same as and overriden by $tax->label. Default is Post Tags/Categories
 * - singular_name - name for one object of this taxonomy. Default is Post Tag/Category
 * - search_items - Default is Search Tags/Search Categories
 * - all_items - Default is All Tags/All Categories
 * - parent_item - This string isn't used on non-hierarchical taxonomies. In hierarchical ones the default is Parent Category
 * - parent_item_colon - The same as <code>parent_item</code>, but with colon <code>:</code> in the end
 * - edit_item - Default is Edit Tag/Edit Category
 * - update_item - Default is Update Tag/Update Category
 * - add_new_item - Default is Add New Tag/Add New Category
 * - new_item_name - Default is New Tag Name/New Category Name
*/
		// Register a sidebar for each property type, so it can be used at single.php
		foreach ( $wp_imovel['property_types'] as $property_slug => $property_data ) {
			$property_title = $property_data['title'];
			$args = array(
				'name'         => sprintf(__( 'Property: %s', 'wp-imovel' ), $property_title ),
				'id'           => 'wp_imovel_sidebar_' . $property_slug,
				'before_title' => '<h3 class="widget-title">',
				'after_title'  => '</h3>',
				) ;
			register_sidebar( $args );
		}
		// Has to be called everytime, or else the custom slug will not work
		//$wp_rewrite->flush_rules();
	}

	/**
	 * Adds "Settings" link to the plugin overview page
	 *
	 */
	 function wp_imovel_plugin_action_links( $links, $file ) {
 		if ( $file == 'wp-imovel/wp-imovel.php' ) {
			$settings_link = '<a href="' . admin_url( 'edit.php?post_type=wp_imovel_property&page=wp-imovel-page-settings') . '">' . __( 'Settings', 'wp-imovel' ) . '</a>';
			array_unshift( $links, $settings_link ); // before other links
		}
		return $links;
	}
	/**
	 * Can enqueue scripts on specific pages, and print content into head
	 *
	 *
	 *	@uses $current_screen global variable
	 *
	 */
	function wp_imovel_admin_enqueue_scripts( $hook ) {
		global $current_screen, $wp_imovel;
//		echo '<h1>' . $current_screen->id . '</h1>';
		switch ( $current_screen->id ) {
			case 'wp_imovel_property_page_wp-imovel-page-settings':
			case 'wp_imovel_property_page_wp-imovel-page-property-settings':
			// Settings pages 
				wp_enqueue_script( 'jquery-cookie' );
				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'jquery-ui-draggable' );
				wp_enqueue_script( 'jquery-ui-droppable' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_script( 'wp-imovel-property-global' );
			break;
			case 'edit-wp_imovel_property':
			// Property Overview Page
				// Get width of overview table thumbnail, and set CSS
				$thumbnail_attribs = WP_IMOVEL_F::get_image_sizes($wp_imovel['configuration']['admin_ui']['overview_table_thumbnail_size']);
				$thumbnail_width = ( ! empty( $thumbnail_attribs['width'] ) ? $thumbnail_attribs['width'] : false );
				if ( $thumbnail_width ) { 
?>
<style type="text/css">
	.wp_imovel_overview .column-thumbnail { width: <?php echo $thumbnail_width + 10; ?>px; }
	.wp_imovel_overview .column-type { width: 90px; }
	.wp_imovel_overview .column-title { width: 230px; }
	.wp_imovel_overview .column-menu_order { width: 50px; }
	.wp_imovel_overview td.column-menu_order { text-align: center; }
	.wp_imovel_overview .column-featured { width: 100px; }
</style>
<?php
			}
				// Enables fancybox js, css and loads overview scripts
				wp_enqueue_script( 'jquery-fancybox' );
				wp_enqueue_script( 'wp-imovel-property-admin-overview' );
				wp_localize_script( 'wp-imovel-property-admin-overview', 'wp_imovel_overview_l10n', WP_IMOVEL_Core::wp_imovel_overview_localize_vars() );
				wp_enqueue_style( 'jquery-fancybox-css' );
			break;
			case 'wp_imovel_property':
				// Edit page
			case 'upload':
				// Media overview page
				wp_enqueue_script( 'wp-imovel-media-admin-overview' );
				wp_localize_script( 'wp-imovel-media-admin-overview', 'wp_imovel_media_l10n', WP_IMOVEL_Core::wp_imovel_media_localize_vars() );
			break;
		}
	}

	function wp_imovel_overview_localize_vars() {
	    return array(
       		'Featured'  => __( 'Featured', 'wp-imovel'),
			'NotFeatured' => __( 'Feature', 'wp-imovel')
	    );
	}
	
	function wp_imovel_media_localize_vars() {
	    return array(
       		'MakePublic'  => __( 'Make public', 'wp-imovel'),
			'MakePrivate' => __( 'Make private', 'wp-imovel')
	    );
	}

	function wp_imovel_admin_menu() {
		global $wp_imovel;
		$settings_page = add_submenu_page( 'edit.php?post_type=wp_imovel_property', __( 'Properties', 'wp-imovel' ), __( 'Property Type Settings', 'wp-imovel' ), 'manage_options', 'wp-imovel-page-property-settings', create_function( '', 'global $wp_imovel; include "ui/wp-imovel-page-property-settings.php";' ));
		$settings_page = add_submenu_page( 'edit.php?post_type=wp_imovel_property', __( 'Properties', 'wp-imovel' ), __( 'Property Settings', 'wp-imovel' ),      'manage_options', 'wp-imovel-page-settings',          create_function( '', 'global $wp_imovel; include "ui/wp-imovel-page-settings.php";' ));
		add_action( 'admin_head-edit.php', array( $this, 'wp_imovel_overview_page_scripts' ) );
	}
	/**
	 * Prints header javascript on admin side
	 *
	 * Called after print_scripts so loaded scripts can be utilized.
	 *
	 * @since 0.54
	 *
	 */
	function wp_imovel_overview_page_scripts() {
		global $current_screen, $wp_imovel;
//		echo '<h1>' . $current_screen->id . '</h1>from wp_imovel_overview_page_scripts';
		if ( 'edit-wp_imovel_property' == $current_screen->id ) {
			// Properties Overview Page
			if ( get_option( 'wp_imovel_settings' ) == '' ) {
				// If settings not configured
				$default_url       =  UD_F::base_url($wp_imovel['configuration']['base_slug']);
				$settings_page     =  admin_url( 'edit.php?post_type=wp_imovel_property&page=wp-imovel-page-settings' );
				$permalink_message = ( get_option( 'permalink_structure' ) == '' )
					? sprintf(__( 'Be advised, since you don\'t have permalinks enabled, you must visit the <a href="%s">Settings Page</a> and set a custom property overview page.', 'wp-imovel' ), $settings_page )
					: sprintf(__( 'By default, your property overview will be displayed on the <a href="%s">$default_url</a> page. You may change the overview page on the <a href="%s">Settings Page</a>', 'wp-imovel' ), $default_url, $settings_page );
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					var message = '<div class="updated fade">' +
						'<p><b><?php _e( 'Thank you for installing WP-Imovel!', 'wp-imovel' ) ?></b> ' +
						'<?php echo $permalink_message; ?></p>' +
						'<?php _e( '<p>You may also visit <a href="http://ppgr.com.br/wordpress/plugins/wp-imovel/">PPGR.com.br</a> for more information.</p>', 'wp-imovel' ) ?>' +
						'</div>';
					jQuery(message).insertAfter(".wp_imovel_overview  h2");
				});
			</script>
<?php
			}
		}
	}
	/**
	 * Modify admin body class on property pages for CSS
	 *
	 * @return string|$request a modified request to query listings
	 * @since 0.5
	 *
	 */
	 function wp_imovel_admin_body_class( $content ) {
		global $current_screen;
		if ( $current_screen->id == 'edit-wp_imovel_property' ) {
			return 'wp_imovel_overview';
		}
		if ( $current_screen->id == 'wp_imovel_property' ) {
			return 'wp_imovel_property_edit';
		}
	 }
	/**
	 * 
	 * Adds wp-property-listing class in search results and property_overview pages
	 * @since 0.7260
	 *
	 */
	function wp_imovel_properties_body_class( $classes ) {
		global  $post;
		if ( strpos( $post->post_content, "property_overview" ) || ( is_search() && isset( $_REQUEST['wp_imovel_search'] ) ) ) {
			$classes[] = 'wp-property-listing';
		}
		return $classes;
	}
	/**
	 * Fixed property pages being seen as 404 pages
	 *
	 * WP handle_404() function decides if current request should be a 404 page
	 * Marking the global variable $wp_query->is_search to true makes the function
	 * assume that the request is a search.
 	 *
	 * @return string|$request a modified request to query listings
	 * @since 0.5
	 *
	 */
	function wp_imovel_fix_404( $query ) {
		global $wp_query, $wp_imovel;
		if ( empty( $wp_imovel['configuration']['base_slug'] ) ) {
			return false;
		}
		if ( $query->query_vars['name'] == $wp_imovel['configuration']['base_slug'] ) {
			$query->is_search = true;
		}
 	}

	function wp_imovel_stats_columns( $columns, $screen ) { // to be removed? - greguly
		if ( $screen == 'wp_imovel_property' ) {
			$columns['wp_imovel_property'] = 2;
		}
		return $columns;
	}

	function wp_imovel_after_setup_theme() {
		add_theme_support( 'post-thumbnails' );
	}
	
	function wp_imovel_manage_meta_boxes() {
		// remove metabox for our wp_imovel_property_status taxonomy 
		// because we are adding our own custom metabox for it.
		remove_meta_box( 'wp_imovel_property_statusdiv', 'wp_imovel_property', 'side' );
	}
	
	function wp_imovel_the_content( $content ) {
		global $post, $wp_imovel;
		if ( empty( $wp_imovel['configuration']['base_slug'] ) ) {
			return $content;
		}
		if ( $wp_imovel['configuration']['base_slug'] == $post->post_name && $wp_imovel['configuration']['automatically_insert_overview'] == 'true' ) {
			return WP_IMOVEL_Core::shortcode_property_overview( );
		} else {
			return $content;
		}
	}

	function wp_imovel_save_property( $post_id ) {
		global $wp_rewrite, $wp_imovel, $wpdb;
//		UD_F::log( 'Gravando ' . $post_id );
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'update-wp_imovel_property_' . $post_id ) ) {
//			UD_F::log( 'Gravando, falha no nonce ' . $post_id );
			return $post_id;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
//			UD_F::log( 'Gravando, estava fazendo autosave ' . $post_id );
			return $post_id;
		}
		
		$update_data = (array) $_REQUEST['wp_imovel_data']['meta'];
//		UD_F::log( 'update data <pre>' . print_r( $update_data, true )  . '</pre>' );
		if ( $post_status = get_post_status( $post_id ) ) {
			if ( ! in_array( $post_status, array( 'draft', 'pending', 'auto-draft' ) ) ) {
				$update_array = array ( 'post_name' => $wp_imovel['property_types'][$update_data['property_type']]['slug'] . '-' . str_pad( $post_id, 5, '0', STR_PAD_LEFT ) );
				if ( $update_data['sold'] == 'true' ) {
					if ( $post_status != 'sold' ) {
						$update_array['post_status'] = 'sold';
//						UD_F::log( 'Gravando post status, era <em>' . $post_status . '</em> e virou <em>sold</em> ' . $post_name );
					}
				}
				$wpdb->update( $wpdb->posts, $update_array, array( 'ID' => $post_id ) );
//				UD_F::log( 'Gravando slug ' . $post_name );
			}
		}
		unset( $update_data['sold'] );
		
		$user_tax_input = (array) $_REQUEST['input-is-parent-wp_imovel_property_status'];
//		UD_F::log( 'Ajustando taxonomia <pre>' . print_r( $user_tax_input, true )  . '</pre>' );
		$tax_input['wp_imovel_property_status'][0] = 0;
		foreach ( $user_tax_input as $key => $value ) {
			array_push( $tax_input['wp_imovel_property_status'], $value );
		}
		foreach ( $tax_input as $taxonomy => $tags ) {
			$taxonomy_obj = get_taxonomy( $taxonomy );
			if ( is_array( $tags ) ) { // array = hierarchical, string = non-hierarchical.
				$tags = array_filter( $tags );
			}
			if ( current_user_can( $taxonomy_obj->cap->assign_terms ) ) {
				wp_set_post_terms( $post_id, $tags, $taxonomy );
//				UD_F::log( 'Ajustando taxonomia <pre>wp_set_post_terms( ' . $post_id.  ', ' . print_r( $tags, true ) . ', ' . $taxonomy . ' )' . '</pre>' );
			}
		}

//		UD_F::log( 'Gravando metadados ' . $post_id  . '<pre>' . print_r( $update_data, true ) . '</pre>' );
		foreach( $update_data as $meta_key => $meta_value ) {
			// Only admins can make featured
			if ( $meta_key == 'featured' && ! current_user_can( 'manage_options' ) ) {
				continue;
			}
			//Remomve certain characters
/*
			if ( $meta_key == 'price' || $meta_key == 'deposit' ) {
				 $meta_value = str_replace( '$' , '', $meta_value );
			}
*/
//			UD_F::log( 'Gravando metadado ' . $meta_key . '=' .  $meta_value  );
			update_post_meta( $post_id, $meta_key, $meta_value );
 		}
		// Update Coordinates
/*		if ( ! empty( $update_data[$wp_imovel['configuration']['address_attribute']] ) ) {
			$geo_data = UD_F::geo_locate_address($update_data[$wp_imovel['configuration']['address_attribute']], $wp_imovel['configuration']['google_maps_localization']);
			if ( ! $geo_data ) {
                update_post_meta($post_id, 'address_is_formatted', false);
			} else {
				update_post_meta($post_id, 'address_is_formatted', true);
				if ( ! empty( $wp_imovel['configuration']['address_attribute'] ) ) {
					update_post_meta($post_id, $wp_imovel['configuration']['address_attribute'], $geo_data->formatted_address);
				}
				update_post_meta($post_id, 'latitude', $geo_data->latitude);
				update_post_meta($post_id, 'longitude', $geo_data->longitude);
				update_post_meta($post_id, 'street_number', $geo_data->street_number);
				update_post_meta($post_id, 'route', $geo_data->route);
				update_post_meta($post_id, 'city', $geo_data->city);
				update_post_meta($post_id, 'county', $geo_data->county);
				update_post_meta($post_id, 'state', $geo_data->state);
				update_post_meta($post_id, 'state_code', $geo_data->state_code);
				update_post_meta($post_id, 'country', $geo_data->country);
				update_post_meta($post_id, 'country_code', $geo_data->country_code);
				update_post_meta($post_id, 'postal_code', $geo_data->postal_code);
            }
		}
*/		
		// Check if property has children
		$children = get_children( 'post_parent=' . $post_id . '&post_type=wp_imovel_property' );
		// Write any data to children properties that are supposed to inherit things
		if ( count( $children ) > 0 ) {
			//1) Go through all children
			foreach ( $children as $child_id => $child_data ) {
				// Determine child property_type
				$child_property_type = get_post_meta( $child_id, 'property_type', true );
				// Check if child's property type has inheritence rules, and if meta_key exists in inheritance array
				if ( is_array( $wp_imovel['property_inheritance'][$child_property_type] ) ) {
					foreach ( $wp_imovel['property_inheritance'][$child_property_type] as $i_meta_key ) {
						$parent_meta_value = get_post_meta( $post_id, $i_meta_key, true );
						// inheritance rule exists for this property_type for this meta_key
						update_post_meta( $child_id, $i_meta_key, $parent_meta_value );
//						UD_F::log("Updating inherited child meta_data: $i_meta_key - $parent_meta_value for $child_id");
					}
				}
			}
		}
		// Update Counts
//		update_option( 'wp_imovel_counts', WP_IMOVEL_F::get_search_values() );
//		$wp_rewrite->flush_rules();
//		UD_F::log( 'Gravou tudo ' . $post_id );
		return true;
 	}

	/**
	 * Removes "quick edit" link on property type objects
	 *
	 * Called in via page_row_actions filter
	 *
	 * @since 0.5
	 * @uses $wp_imovel WP-Imovel configuration array
	 * @uses $wp_rewrite WordPress rewrite object
 	 * @access public
	 *
	 */
    function wp_imovel_property_row_actions( $actions, $post ) {
		if ( $post->post_type == 'wp_imovel_property' ) {
			unset( $actions['inline'] );
		}
        return $actions;
    }

	function wp_imovel_admin_css() {
		global $current_screen;
        if ( file_exists( WP_IMOVEL_DIR . '/css/wp-imovel-admin.css' ) ) {
            wp_register_style( 'myStyleSheets', WP_IMOVEL_URL . '/css/wp-imovel-admin.css' );
            wp_enqueue_style( 'myStyleSheets' );
        } else {
			
		}

	}
	
	function wp_imovel_property_count() {
		$num_properties = wp_count_posts( 'wp_imovel_property' );
		$num = number_format_i18n( $num_properties->publish );
		$text = _n( 'Property', 'Properties', intval( $num_properties->publish ), 'wp-imovel' );
		if ( current_user_can( 'edit_posts' ) ) {
			$num  = '<a href="edit.php?post_type=wp_imovel_property">' . $num . '</a>';
			$text = '<a href="edit.php?post_type=wp_imovel_property">' . $text . '</a>';
		}
		echo '<td class="first b b-posts">' . $num . '</td>';
		echo '<td class="t posts">' . $text . '</td>';
		echo '</tr>';
	}
/*
	Custom messages for properties
*/
	function wp_imovel_property_updated_messages( $messages ) {
	  $messages['wp_imovel_property'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( 'Property updated!!! <a href="%s">View property</a>', 'wp-imovel' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'wp-imovel' ),
		3 => __( 'Custom field deleted.', 'wp-imovel' ),
		4 => __( 'Property updated.', 'wp-imovel' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Property restored to revision from %s', 'wp-imovel' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Property published. <a href="%s">View property</a>', 'wp-imovel' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Property saved.', 'wp-imovel' ),
		8 => sprintf( __( 'Property submitted. <a target="_blank" href="%s">Preview property</a>', 'wp-imovel' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Property scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview property</a>', 'wp-imovel' ),		  date_i18n( __( 'M j, Y @ G:i', 'wp-imovel' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Property draft updated. <a target="_blank" href="%s">Preview property</a>', 'wp-imovel' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	  );
	  return $messages;
	}
	
	function wp_imovel_post_states( $post_states ) {
		global $post;
		if ( isset( $_GET['post_status'] ) ) {
			$post_status = $_GET['post_status'];
		} else {
			$post_status = '';
		}
		if ( 'sold' == $post->post_status && 'sold' != $post_status) {
			$post_states[] = __( 'Sold', 'wp-imovel' );
		}
		return $post_states;
	}
	

	/**
	 * Sets up columns for admin listing of properties and media ( property images )
	 *
	 */
	function wp_imovel_property_edit_columns( $columns ) {
		global $wp_imovel;
		unset( $columns );
		$columns['cb'] = '<input type="checkbox" />';
		$columns['title'] = __( 'Title', 'wp-imovel' );
		$columns['id'] = __( 'Property Id', 'wp-imovel' );
		$columns['type'] = __( 'Type', 'wp-imovel' );
		if ( is_array( $wp_imovel['property_public_meta'] ) ) {
			foreach ( $wp_imovel['property_public_meta'] as $slug => $title ) {
				$columns[$slug] = $title;
			}
		} else {
			$columns = $columns;
		}

	//	$columns['city'] = __( 'City', 'wp-imovel' );
	//	$columns['description'] = __( 'Description', 'wp-imovel' );
	//	$columns['features'] = __( 'Features', 'wp-imovel' );
	//	$columns['overview'] = __( 'Overview', 'wp-imovel' );
		$columns['featured'] = __( 'Featured', 'wp-imovel' );
		//$columns['menu_order'] = __( 'Order', 'wp-imovel' );
		//$columns['date'] = __( 'Date', 'wp-imovel' );
		//$columns['author'] = __( 'Published By', 'wp-imovel' );
		$columns['thumbnail'] = __( 'Thumbnail', 'wp-imovel' );

		//
		return $columns;
	}
	
	function wp_imovel_custom_columns( $column ) {
		global $post, $wp_imovel;
		$post_id = $post->ID;
		switch ( $column ) {
			case 'id' :
				echo $post_id;
			break;
			case 'description' :
				the_excerpt();
			break;
			case 'type' :
				$property_type = $post->property_type;
				echo $wp_imovel['property_types'][$property_type]['title'];
			break;
/*
			case $wp_imovel['configuration']['address_attribute']:
				// Only show this is the property type has an address
				if ( in_array( $post->property_type, $wp_imovel['location_matters'] ) ) {
					echo $post->display_address. '<br />';
					if ( $post->address_is_formatted ) { 
						echo __( 'Validated:', 'wp-imovel' ) . "<a href=\"http://maps.google.com/maps?q=$property[latitude]},+{$property[longitude]}+%28" . str_replace(" ", "+",$post->post_title). "%29&iwloc=A&hl=en\" target=\"_blank\">". __( 'view on map', 'wp-imovel' ) . '</a>.';
					} else {
						_e( 'Address not validated.', 'wp-imovel' );
					}
				}
			break;
*/
			case 'overview' :
				$overview_stats = $wp_imovel['property_public_meta'];
				unset( $overview_stats['phone_number'] );
				// Not the best way of doing it, but better than nothing.
				// We basically take all property stats, then dump everything too long and empty
				foreach ( $overview_stats as $stat => $label ) {
					if ( empty( $post->$stat ) || strlen( $post->$stat ) > 15 ) {
						continue;
					}
					echo $label . ' : ' . apply_filters( 'wp_imovel_stat_filter_' . $stat, $post->$stat ) . '<br />';
				}
			break;
			case 'features' :
				$features = get_the_terms( $post_id, 'wp_imovel_property_feature' );
 				$features_html = array();
				if ( $features ) {
					foreach ( $features as $feature ) {
						array_push( $features_html, '<a href="' . get_term_link( $feature->slug, "wp_imovel_property_feature" ) . '">' . $feature->name . '</a>' );
					}
					echo implode( $features_html, ', ' );
				}
			break;
			case 'thumbnail' :
				$image_thumb_url = $post->images[$wp_imovel['configuration']['admin_ui']['overview_table_thumbnail_size']];
				if ( ! empty( $image_thumb_url ) ) {
					echo '<a href="' . $post->images['large'] . '" title="' . $post->post_title . '"><img src="' .  $image_thumb_url . '" /></a>';
				} else {
					echo ' - ';
				}
			break;
			case 'featured' :
				if ( current_user_can( 'manage_options' ) ) {
					if ( $post->featured ) {
						echo '<input type="button" id="wp_imovel_feature_' . $post_id . '" nonce="' . wp_create_nonce( 'wp_imovel_make_featured_' . $post_id ) . '" value="' . __( 'Featured', 'wp-imovel' ) .'"  class="wp_imovel_featured_toggle wp_imovel_is_featured" />';
					} else {
						echo '<input type="button" id="wp_imovel_feature_' . $post_id . '" nonce="' . wp_create_nonce( 'wp_imovel_make_featured_' . $post_id ) . '" value="' . __( 'Feature', 'wp-imovel' ) . '" class="wp_imovel_featured_toggle" />';
					}
				} else {
					if ( $post->featured ) {
						echo __( 'Featured', 'wp-imovel' );
					} else {
						echo '&nbsp;';
					}
				}
			break;
			case 'menu_order' :
				if ( $post->menu_order ) {
					echo $post->menu_order;
				}
			break;
			default:
				echo ( ! empty( $post->$column ) ? apply_filters( 'wp_imovel_stat_filter_' . $column, $post->$column ) : '' );
			break;
		}
	}
	
	function wp_imovel_media_columns( $default_columns ) {
		$default_columns['wp_imovel_private'] = __('Public/Private Images', 'wp-imovel' );
		return $default_columns;
	}

	function wp_imovel_media_custom_column( $column_name, $id ) {
		if ( $column_name == 'wp_imovel_private' ) {
			$meta = wp_get_attachment_metadata( $id );
			if ( ! isset( $meta['wp_imovel_private'] ) || empty( $meta['wp_imovel_private'] ) || $meta['wp_imovel_private'] != 'private' ) {
				echo '<img id="wp_imovel_image_privacy_icon_' . $id . '" src="' . WP_IMOVEL_URL . '/images/yes.png'  . '" alt="" /><br />';
				echo '<input type="button" id="wp_imovel_image_privacy_' . $id . '" nonce="' . wp_create_nonce( 'wp_imovel_image_privacy_' . $id ) . '" value="' . __('Make private', 'wp-imovel') . '" class="wp_imovel_toggle_image_privacy" />';
			} else {
				echo '<img id="wp_imovel_image_privacy_icon_' . $id . '" src="' . WP_IMOVEL_URL . '/images/no.png'  . '" alt="" /><br />';
				echo '<input type="button" id="wp_imovel_image_privacy_' . $id . '" nonce="' . wp_create_nonce( 'wp_imovel_image_privacy_' . $id ) . '" value="' . __('Make public', 'wp-imovel')  . '" class="wp_imovel_toggle_image_privacy wp_imovel_image_is_private" />';
			}
			echo '<img class="waiting_' . $id . '" src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" alt="" style="display:none" />';
		}
	}
	
	
	function wp_imovel_template_redirect_no_content_output() {
		global $post;
		// Include template functions
		include WP_IMOVEL_TEMPLATES . '/template-functions.php';
		$args = array(
				'return_object' => true,
				'get_children' => false,
				'load_gallery' => false,
				'load_thumbnail' => false,
				'load_parent' => false
			);
		$post = WP_IMOVEL_F::get_property( $post->ID, $args );
	}
	

	/**
	 * Performs front-end pre-header functionality
	 *
	 * This function is not called on admin side
	 *
	 *
	 */
	function wp_imovel_template_redirect() {
		global $post, $property, $wp, $wp_query, $wp_imovel, $wp_styles;
		// Prepare MSIE css to load on MSIE only
		$wp_styles->add_data( 'wp-imovel-property-frontend-msie', 'conditional', 'lte IE 7' );
		// Call on all pages because styles are used in widgets
		wp_enqueue_style( 'wp-imovel-property-frontend' );
		// Loaded only on MSIE
		wp_enqueue_style( 'wp-imovel-property-frontend-msie' );
		// Include template functions
		include WP_IMOVEL_TEMPLATES . '/template-functions.php';
		
/*
		UD_F::log( 'redirect $post <pre>' . print_r( $post, true )  . '</pre>' );
		UD_F::log( 'redirect $wp_query <pre>' . print_r( $wp_query, true )  . '</pre>' );
*/		
		if ( 'print-view' == $wp_query->query_vars['name'] || 'email-view' == $wp_query->query_vars['name'] ) {
			$wp_query->is_404 = false;
			status_header( 200 );			
			$args = array( 
					'p' => $post->ID,
					'post_type' => $post->post_type
					);
			query_posts( $args );
		
			$view = $wp_query->query_vars['name'];
			$post = WP_IMOVEL_F::get_property($post->ID, "return_object=true&load_gallery=true");
  			$type_view = $post->property_type . '-' . $view;
			// 1. Try custom template in theme folder
			if ( file_exists( STYLESHEETPATH  . '/property-' . $type_view . '.php' ) ) {
				load_template( STYLESHEETPATH  . '/property-' . $type_view . '.php' );
				die();
			}
			// 2. Try general template in theme folder
			if ( file_exists( STYLESHEETPATH  . '/property-' . $view . '.php' ) ) {
				load_template( STYLESHEETPATH  . '/property-' . $view . '.php' );
				die();
			}
			// 3. Try custom template in plugin folder
			if ( file_exists( WP_IMOVEL_TEMPLATES . '/property-' . $type_view . '.php' ) ) {
				load_template( WP_IMOVEL_TEMPLATES . '/property-' . $type_view . '.php' );
				die();
			}
			// 4. If all else fails, try the default general template
			if ( file_exists( WP_IMOVEL_TEMPLATES . '/property-' . $view . '.php' ) ) {
				load_template( WP_IMOVEL_TEMPLATES . '/property-' . $view . '.php' );
				die();
			}
		}
		
 		$is_search = ( is_array( $_REQUEST['wp_imovel_search'] ) ? true : false );

		if ( $post->post_type == 'wp_imovel_property' ) {
			$single_page = true;
		} else {
			$single_page = false;
		}
		
		if ( $post->post_name == $wp_imovel['configuration']['base_slug']
				|| $wp->request == $wp_imovel['configuration']['base_slug'] 
				|| $wp->query_string == "p=" . $wp_imovel['configuration']['base_slug'] 
				|| strpos( $post->post_content, 'property_overview' ) ) {
			$overview_page = true;
		} else {
			$overview_page = false;
		}
		
		// Scripts for both types of views
		if ( $single_page || $overview_page ) {
			wp_enqueue_script( 'jquery-ui-slider', WP_IMOVEL_URL . '/js/jquery.ui.slider.min.js', array( 'jquery-ui-core' ), '1.7.2' );
			wp_enqueue_script( 'jquery-fancybox' );
//			wp_enqueue_script( 'jquery-quicksand' );
			wp_enqueue_script( 'wp-imovel-property-frontend' );
			wp_enqueue_style( 'jquery-fancybox-css' );
		}
		
		if ( $single_page )	{
		
//			echo '<h1>single page</h1>';
			// Load Map Scripts
//			wp_enqueue_script( 'google-maps' );
			// Allow plugins to insert header scripts/styles using wp_head_single_property hook
			do_action( 'template_redirect_single_property' ); 			
			add_action( 'wp_head', create_function( '', "do_action( 'wp_head_single_property' ); "));
			$post = WP_IMOVEL_F::get_property($post->ID, "return_object=true&load_gallery=true");
  			$type = $post->property_type;
			// 1. Try custom template in theme folder
			if ( file_exists( STYLESHEETPATH  . '/property-' . $type . '.php' ) ) {
				load_template( STYLESHEETPATH  . '/property-' . $type . '.php' );
				die();
			}
			// 2. Try general template in theme folder
			if ( file_exists( STYLESHEETPATH  . '/property.php' ) ) {
				load_template( STYLESHEETPATH  . '/property.php' );
				die();
			}
			// 3. Try custom template in plugin folder
			if ( file_exists( WP_IMOVEL_TEMPLATES . '/property-' . $type . '.php' ) ) {
				load_template( WP_IMOVEL_TEMPLATES . '/property-' . $type . '.php' );
				die();
			}
			// 4. If all else fails, try the default general template
			if ( file_exists( WP_IMOVEL_TEMPLATES . '/property.php' ) ) {
				load_template( WP_IMOVEL_TEMPLATES . '/property.php' );
				die();
			}
		} elseif ( $overview_page ) {
//			echo '<h1>Overview Page</h1>';
			// Allow plugins to insert header scripts/styles using wp_head_single_property hook
			do_action( 'template_redirect_property_overview' ); 
			add_action( 'wp_head', create_function( '', "do_action( 'wp_head_property_overview' ); "));
			// If the requested page is the slug, but no post exists, we load our template
			if ( $is_search || ! $post ) {
				// 1. Try custom template in theme folder
				if ( file_exists( STYLESHEETPATH  . '/property-overview-page.php' ) ) {
					load_template( STYLESHEETPATH  . '/property-overview-page.php' );
					die();
				}
				// 2. If all else fails, trys the default general template
				if ( file_exists( WP_IMOVEL_TEMPLATES . '/property-overview-page.php' ) ) {
					load_template( WP_IMOVEL_TEMPLATES . '/property-overview-page.php' );
					die();
				}
			}
		}
	}

	function wp_imovel_admin_init() {
		global $wp_rewrite;
		remove_action( 'admin_notices', 'update_nag', 3 );

		//WP_IMOVEL_F::fix_screen_options();
/*
		add_meta_box( $id,                          $title,                                    $callback,                                          $page,               $context, $priority, $callback_args ); 
*/
//	    add_meta_box( 'property_type',          __( 'Type Information',    'wp-imovel' ),   array( 'WP_IMOVEL_UI', 'metabox_type' ),          'wp_imovel_property', 'normal' );
	    add_meta_box( 'property_status',        __( 'Status Information',  'wp-imovel' ),   array( 'WP_IMOVEL_UI', 'metabox_status' ),        'wp_imovel_property', 'side' );
	    add_meta_box( 'property_stats',	        __( 'General Information', 'wp-imovel' ),   array( 'WP_IMOVEL_UI', 'metabox_public_meta' ),   'wp_imovel_property', 'normal' );
	    add_meta_box( 'property_meta',	        __( 'Private Information', 'wp-imovel' ),   array( 'WP_IMOVEL_UI', 'metabox_private_meta' ),  'wp_imovel_property', 'normal' );
	// add a metabox to manage the privacy of the attached images
		add_meta_box( 'property_image_privacy', __( 'Public/Private Images', 'wp-imovel' ), array( 'WP_IMOVEL_UI', 'metabox_image_privacy' ), 'wp_imovel_property', 'side' );
	// add a metabox to show an overview
		add_meta_box( 'dashboard_wp_imovel_overview',     __( 'WP-Imovel Overview', 'wp-imovel' ),    array( 'WP_IMOVEL_UI', 'metabox_overview' ),      'dashboard',          'side' );
 	}
	/**
	 * Displays featured properties
	 *
	 * Performs searching/filtering functions, provides template with $properties file
	 * Returns html content to be displayed after location attribute on property edit page
	 *
	 * @since 0.60
 	 * @param string $listing_id Listing ID must be passed
	 */
	function shortcode_featured_properties( $attributes = '' ) {
		global $wp_imovel;
		$default_property_type = WP_IMOVEL_F::get_most_common_property_type();
		extract( shortcode_atts( 
			array(
				'type' => $default_property_type,
				'class' => 'shortcode_featured_properties',
				'stats' => '',
				'image_type' => 'thumbnail'
			),
			$attributes));
		// Convert shortcode multi-property-type string to array
		if ( strpos($type, ",") ) {
			$type = explode(",", $type);
		}
		// Convert shortcode multi-property-type string to array
		if ( ! empty( $stats ) ) {
			if ( strpos($stats, ",") ) {
				$stats = explode(",", $stats);
			}
			if ( ! is_array( $stats)) {
				$stats = array( $stats );
			}
		}
		$properties = WP_IMOVEL_F::get_properties("featured=true&property_type=$type");
		 // Set value to false if nothing returned.
		 if ( ! is_array( $properties ) ) {
			return;
		}
		ob_start();
	// 1. Try custom template in theme folder				
		if ( file_exists( STYLESHEETPATH  . "/property-featured-shortcode.php") ) { 
			include STYLESHEETPATH  . "/property-featured-shortcode.php";				
	// 2. Try custom template in defaults folder				
		} elseif ( file_exists( WP_IMOVEL_TEMPLATES . "/property-featured-shortcode.php") ) { 
			include WP_IMOVEL_TEMPLATES . "/property-featured-shortcode.php";
		} 
		$result .= ob_get_contents();
		ob_end_clean();
		return $result;
	}
	
	function shortcode_property_search( $args = "" )  {
		global $post, $wp_properties;
		extract( shortcode_atts( array(
 			'searchable_attributes' => '',
			'searchable_property_types' => '',
			'per_page' => '10'
            ), $args));
		if ( empty( $searchable_attributes ) ) {
			$searchable_attributes = $wp_properties[searchable_attributes];
		} else {
			$searchable_attributes = explode( ",", $searchable_attributes );
		}
		$searchable_attributes = array_unique( $searchable_attributes );
		if ( empty($searchable_property_types ) ) {
			$searchable_property_types = $wp_properties[searchable_property_types];
		} else {
			$searchable_property_types = explode( ",", $searchable_property_types );
		}
		$widget_id = $post->ID . '_search';
		ob_start();
		echo '<div class="wp_imovel_shortcode_search">';
		draw_property_search_form( $searchable_attributes, $searchable_property_types, $per_page );
		echo '</div>';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Displays property overview
	 *
	 * Performs searching/filtering functions, provides template with $properties file
	 * Retirms html content to be displayed after location attribute on property edit page
	 *
	 * @since 0.723
 	 * @param string $listing_id Listing ID must be passed
	 */
	function shortcode_property_overview( $args = '' )  {
		global $wp_imovel, $post;
		$to_use = array( 
			'per_page' => 3,
        	'starting_row' => '0'
			);
		// convert all saved attributes into useful array
		foreach ( $wp_imovel['property_public_meta'] as $key => $value ) {
			$to_use[$key] = '';
		}
		if ( empty( $args ) ) {
			$args['type'] = 'all';
		}
		
		// merge with specified by user
		$merged_args = array_merge( $to_use, $args );
		// build the query string from an array with not empty values
		$query = '';
		foreach ( $merged_args as $key => $value ) {
			if ( $value != '' ) {
				if ( $key == 'type' ) {
					 $key = 'property_type'; // need this for better UI and to avoid mistakes
				}
				if ( $key == 'per_page') { 
					$per_page = $value; 
					continue; 
				}
                if ( $key == 'starting_row') { 
					$offset = $value; 
					continue; 
				}
                if ( $key == 'sorter') { 
					$sorter = $value; 
					continue; 
				}
                if ( $key == 'ajax_call') { 
					$ajax_call = $value; 
					continue; 
				}
				$query .= $key . '=' . $value . '&';
			}
		}
		// get rid of possible & char at the end of $query
		if ( substr( $query, -1, 1 ) == '&' ) {
			$query = substr( $query, 0, -1 );
		}

		// default values not used for getting properties
		extract( array(
					'show_children'          => 'true',
					'child_properties_title' => __( 'Floor plans at location:', 'wp-imovel' ),
					'fancybox_preview'       => $wp_imovel['configuration']['property_overview']['fancybox_preview'],
					'thumbnail_size'         => $wp_imovel['configuration']['property_overview']['thumbnail_size'],
					'ajax_call'              => 'false',
		            'sort_by'                => ( $merged_args['sort_by'] ) ? $merged_args['sort_by'] : 'menu_order',
		            'sort_order'             => ( $merged_args['sort_order'] && ( strtoupper( $merged_args['sort_order'] ) == 'ASC' || strtoupper( $merged_args['sort_order']) == 'DESC' ) ) ? strtoupper( $merged_args['sort_order'] ) : 'ASC',
					'order_by'               => 'menu_order',
					'order_dir'              => 'ASC'
				)
			);
 		// Get image sizes for overview/search page
		$thumbnail_sizes = WP_IMOVEL_F::get_image_sizes( $thumbnail_size );
/*
		if ( isset( $_REQUEST['wp_imovel_search'] ) ) {
			$properties = WP_IMOVEL_F::get_properties( serialize( $_REQUEST['wp_imovel_search'] ) );
		} else {
			$properties = WP_IMOVEL_F::get_properties( $query );
		}
*/
		
		if ( $merged_args['ajax_call'] ) {
			$paginate = true;
            $query .= '&pagi=' . $offset . '--' . $per_page;
//			UD_F::log( 'AJAX call <pre>' . $query  . '</pre>' );
            $properties = WP_IMOVEL_F::get_properties( $query );
			$total = $properties['total'];
			unset( $properties['total'] ); // VERY IMPORTANT!!!
		} else {
            if ( ! isset( $_REQUEST['wp_imovel_search'] ) ) {  
				// NOT SEARCH - usual use via shortcode
				$paginate = false;
		        $query .= '&pagi=' . $offset . '--' . $per_page; // get back limits per page
//				UD_F::log( 'Not search <pre>' . $query  . '</pre>' );
            } else { 
				// SEARCH RESULTS
                // get rid of empty data that are not inputed by user in search form
//				UD_F::log( 'Search start <pre>' . "['wp_imovel_search']" . print_r($_REQUEST['wp_imovel_search'], true ) .  ' </pre>' );
				$paginate = true;
                foreach ( $_REQUEST['wp_imovel_search'] as $key => $value ) {
                	if ( $key == 'property_type' && is_array( $value ) ) {
                		$searchable_attributes[$key] = $value;
                	} elseif( '' == $value  ) {
                        continue;
/*
                	} elseif(  $value['min'] == '' && $value['max'] == ''  ) {
                        continue;
*/
					} else {
                		$searchable_attributes[$key] = $value;
					}
                }
//echo '<pre>'. "['searchable_attributes']"; print_r($searchable_attributes); echo '</pre>';
                $query = ''; 
				// now we need to form a query string that will get found properties
                if ( isset( $searchable_attributes ) ) {
                    foreach( $searchable_attributes as $key => $value ) {
						if ( $key == 'property_type' ) {
							$query .= $key . '=';
							foreach ( $value as $property_type_value) {
								$query .= $property_type_value . ',';
							}
							$query = ( substr( $query, -1, 1) == ',') ? substr_replace( $query, '', -1, 1 ) : $query;
/*
						} elseif ( is_array( $value ) ) {
							$query .= $key . '=' . $value['min'] . '-' . $value['max'];
*/
						} elseif ( $key == 'specific' ) {
							foreach ( $value as $specific_key => $specific_value ) {
								if ( ! empty( $specific_value ) ) {
									$query .= $specific_key . '=' . $specific_value . '&';
								}
							}
							$query = ( substr( $query, -1, 1) == '&') ? substr_replace( $query, '', -1, 1 ) : $query;
                        } else {
							if ( $value == '-1' ) {
								 continue;
							}
							if ( $key == 'pagi') {
								$pagi = '&' . $key . '=' . $value;
								$limits = explode( '--', $value );
								$starting_row = $limits[0];
								$per_page = $limits[1];
								continue;
							}
                            $query .= $key . '=' . $value;
						}
					   $query .= '&';
                    }
                }
                $query = substr_replace( $query, '', -1, 1 );
                $query .= $pagi;
//				UD_F::log( 'Search final <pre>' . $query  . '</pre>' );
            }
//echo "<hr><pre>$query</pre><hr>";
//wp_die('bye');				
			$properties = WP_IMOVEL_F::get_properties( $query );
			$total = $properties['total'];
			unset( $properties['total'] ); // VERY IMPORTANT!!!
			
           	$sortable_attrs = array();
			if ( ! empty( $wp_imovel['property_public_meta']) && $wp_imovel['sortable_attributes'] ) {
				foreach ( $wp_imovel['property_public_meta'] as $slug => $label ) {
					if ( in_array( $slug, $wp_imovel['sortable_attributes'] ) ) {
						$sortable_attrs[$slug] = $label;
					}
				}
				// If default ORDER_BY attr doesn't exist in sortable attributes 
				// we set ORDER_BY by first sortable attribute.
				if ( ! empty( $sortable_attrs ) && ! in_array( $sort_by, $wp_imovel['sortable_attributes'] ) ) {
					$sort_by = key( $sortable_attrs );
				}
			}
			
			$pagination = '';
			if ( $paginate ) {
				ob_start();
				// 1. Try custom template in theme folder
					if ( file_exists( STYLESHEETPATH . "/property-pagination-$type.php")) {
						include STYLESHEETPATH . "/property-pagination-$type.php";
				// 2. Try custom template in defaults folder
					} elseif ( file_exists( WP_IMOVEL_TEMPLATES . "/property-pagination-$type.php")) {
						include WP_IMOVEL_TEMPLATES . "/property-pagination-$type.php";
				// 3. Try general template in theme folder
					}elseif ( file_exists( STYLESHEETPATH . "/property-pagination.php")) {
						include STYLESHEETPATH . "/property-pagination.php";
				// 4. If all else fails, try the default general template
					}elseif ( file_exists( WP_IMOVEL_TEMPLATES . "/property-pagination.php")) {
						include WP_IMOVEL_TEMPLATES . "/property-pagination.php";
					}
				$result .= ob_get_contents();
				ob_end_clean();
			}
        }


		 // Set value to false if nothing returned.
		 if ( ! is_array( $properties ) ) {
			$properties = false;
		}
		// Convert variables to booleans
		$ajax_call 			= ( $ajax_call == 'true' ? true : false );
		$show_children 		= ( $show_children == 'true' ? true : false );
		$fancybox_preview 	= ( $fancybox_preview == 'true' ? true : false );
		ob_start();
	// 1. Try custom template in theme folder
		if ( file_exists( STYLESHEETPATH  . "/property-overview-$type.php") ) {
			include STYLESHEETPATH  . "/property-overview-$type.php";
	// 2. Try custom template in defaults folder
		} elseif (  file_exists( WP_IMOVEL_TEMPLATES . "/property-overview-$type.php") ) {
			include WP_IMOVEL_TEMPLATES . "/property-overview-$type.php";
	// 3. Try general template in theme folder
		}elseif (  file_exists( STYLESHEETPATH  . "/property-overview.php") ) {
			include STYLESHEETPATH  . "/property-overview.php";
	// 4. If all else fails, try the default general template
		}elseif (  file_exists( WP_IMOVEL_TEMPLATES . "/property-overview.php") ) {
			include WP_IMOVEL_TEMPLATES . "/property-overview.php";
		}
		$result .= ob_get_contents();
		ob_end_clean();
		return $result;
	}
	/**
	 * 
	 * @since 0.723
	 * 
	 */
	function wp_imovel_ajax_property_overview()  {
        include_once WP_IMOVEL_TEMPLATES . "/template-functions.php";
        $args = $_REQUEST;
        if ( ! empty( $args['action'] ) ) {
        	unset( $args['action'] );
		}
        if ( ! empty($args['pagination'] ) ) {
        	unset( $args['pagination'] );
		}
       // $args['ajax_call'] = true;
		$data = WP_IMOVEL_Core::shortcode_property_overview( $args );
		echo $data;
        die();
	}

	function wp_imovel_enhance_search_filter( $query ) {
		if ( $query->is_search && 'wp_imovel_property' == $query->get( 'post_type' ) ) {
//       	$query->set('meta_key', '');
			$query->set( 'meta_value', $query->get( 's' ) );
//			$query->set('meta_compare', 'LIKE'); // only in WP 3.1
		}
		return $query;
	}
/*

=== For WordPress 3.1 ===
$args = array(
	'post_type' => 'wp_imovel_property',
	'meta_query' => array(
		array(
			'key' => 'color',
			'value' => 'blue',
			'compare' => 'NOT LIKE'
		),
		array(
			'key' => 'price',
			'value' => array( 20, 100 ),
			'type' => 'numeric',
			'compare' => 'BETWEEN'
		)
	)
*/

	function wp_imovel_request_filter( $full_sql_query ) {
		global $wp_query;
		$query = $wp_query;
		if ( is_admin() && $query->is_search && 'wp_imovel_property' == $query->get( 'post_type' ) ) {
			$full_sql_query = str_replace( 
				'posts.post_content', 
				'postmeta.meta_value', 
				$full_sql_query );
			$full_sql_query = str_replace( 
				"postmeta.meta_value = '" . $query->get( 'meta_value' ) . "'", 
				"postmeta.meta_value LIKE '%" . $query->get( 'meta_value' ) . "%'", 
				$full_sql_query );
			$full_sql_query = str_replace( 
				"SELECT ", 
				"SELECT DISTINCT ", 
				$full_sql_query );
//			UD_F::log( 'full query <pre>' . print_r( $full_sql_query, true )  . '</pre>' );
		}
		return $full_sql_query;
	}

}
/*
add_action('admin_head', 'edit_admin_menu');
function edit_admin_menu(){
	global $menu;

	// Change the order of the standard WP menu items
	unset($menu[5]); // Unset Posts
	$menu[30] = $menu[20]; // Copy Pages from position 20 to position 30 (Below Comments)
	unset($menu[20]); // Unset Pages (from original position)
}
*/
?>