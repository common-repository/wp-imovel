<?php
/**
 * WP-Imovel General Functions
 *
 * Contains all the general functions used by the plugin.
 *
 * @package WP-Imovel
 * @subpackage Functions
 */
class WP_IMOVEL_F {
	/**
	 * Prevents all columns on the overview page from being enabled if nothing is configured
	 *
 	 *
 	 * @since 0.721
	 *
 	 */
	function fix_screen_options() {	
		global $current_user;
		$user_id = $current_user->data->ID;
		$current_hidden = get_user_meta( $user_id, 'manage-edit-wp_imovel_property-columnshidden', true); 
		if ( ! is_array( $current_hidden ) ) {
			$default_hidden[] = 'type';
			update_user_meta( $user_id, 'manage-edit-wp_imovel_property-columnshidden', $default_hidden );
		}
	}
	/**
	 * Makes a given property featured, usually called via ajax
	 *
 	 *
 	 * @since 0.721
	 *
 	 */
	 function toggle_featured( $post_id = false ) {
		global $current_user;
		if ( ! $post_id ) {
			return false;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$featured = get_post_meta( $post_id, 'featured', true );
		// Check if already featured
		if ( $featured == 'true' ) {
			update_post_meta( $post_id, 'featured', 'false' );
			$status = 'not_featured';
		} else {  
			update_post_meta( $post_id, 'featured', 'true' );
			$status = 'featured';
		}
		echo json_encode( array( 'success' => 'true', 'status' => $status, 'post_id' => $post_id) );
	 }
	/**
	 * Makes a given image private/public, usually called via ajax
	 *
 	 *
	 *
 	 */
	 function toggle_privacy( $id = false ) {
		global $current_user;
		if ( ! $id ) {
			return false;
		}
		$meta = wp_get_attachment_metadata( $id );
		if ( ! current_user_can( 'edit_posts' ) ) {
			echo json_encode( array( 'success' => 'false', 'status' => $meta['wp_imovel_private'] , 'post_id' => $post_id) );		
		}
		// Check if not private
		if ( ! isset( $meta['wp_imovel_private'] ) || empty( $meta['wp_imovel_private'] ) || $meta['wp_imovel_private'] != 'private' ) {
			$meta['wp_imovel_private'] =  'private';
			wp_update_attachment_metadata( $id, $meta );			
			$status = 'private';
		} else {  
			$meta['wp_imovel_private'] =  'public';
			wp_update_attachment_metadata( $id, $meta );			
			$status = 'public';
		}
		echo json_encode( array( 'success' => 'true', 'status' => $status, 'post_id' => $id) );
	 }
	/**
	 * Determines most common property type (used for defaults when needed)
	 *
 	 *
 	 * @since 0.55
	 *
 	 */
	function get_most_common_property_type( $array = false ) {
		global $wpdb;
	
		$top_property_type = $wpdb->get_row("
			SELECT meta_value as property_type, count(meta_value) as count
			FROM {$wpdb->prefix}postmeta 
			WHERE meta_key = 'property_type'
			GROUP BY meta_value
			ORDER BY count DESC
			LIMIT 0,1
			");
		return $top_property_type->property_type;
	}
	/**
	 * Determines if all of the arrays values are numeric
	 *
 	 *
 	 * @since 0.55
	 *
 	 */
	function is_numeric_range( $array = false ) {
		if ( ! is_array( $array ) ) {
			return false;
		}	
		foreach( $array as $value ) {
			if ( ! is_numeric( $value ) ) {
				return false;	
			}
		}
		return true;
	}

	function draw_property_dropdown( $args = '' ) {
		global $wp_imovel, $wpdb;
		$defaults = array( 'id' => 'wp_imovel_properties',  'name' => 'wp_imovel_properties',  'selected' => '' );
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		$all_properties = $wpdb->get_results("
			SELECT id, post_title
			FROM {$wpdb->prefix}posts 
			WHERE post_type = 'wp_imovel_property_type' 
				AND post_status = 'publish'
			");
		if ( ! is_array( $all_properties ) ) { 
			return false;
		}
		$dropdown = '
			<select id="' . $id. '" name="' . $name . '">';
		foreach ( $all_properties as $property ) { 
			$dropdown .= '
				<option value="' . $property->id . '"' . ( $selected == $property->id ? ' selected="selected"'  : '' ) . '>' . $property->post_title . ' (' . $property->post_name . ')</option>';
		}
		$dropdown .= '
			</select>';
		echo $dropdown;
		return false;
	}

	function draw_property_type_dropdown( $args = '' ) {
		global $wp_imovel;
		$defaults = array( 'id' => 'wp_imovel_property_type',  'name' => 'wp_imovel_property_type',  'selected' => '' );
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		if ( ! is_array( $wp_imovel['property_types'] ) ) {
			_e ( 'No property types found. Have a look at your settings.' , 'wp-imovel' );
			return false;
		}
		$dropdown = '
			<select id="' . $id. '" name="' . $name . '">
				<option value=""></option>';
		foreach ( $wp_imovel['property_types'] as $slug => $property_data ) {
			$label = $property_data['title'];
			$dropdown .= '
				<option value="' . $slug . '"' . ( $selected == $slug ? ' selected="selected"'  : '' ) . '>' . $label . '</option>';
		}
		$dropdown .= '
			</select>';
		echo $dropdown;
		return false;
	}
	
	function draw_attribute_dropdown( $args = '' ) {
		global $wp_imovel; 
		$defaults = array( 'id' => 'wp_imovel_attribute',  'name' => 'wp_imovel_attribute',  'selected' => '' );
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		$attributes = $wp_imovel['property_public_meta'];
		if ( ! is_array( $attributes ) ) {
			return false;
		}
		$dropdown = '
			<select id="' . $id. '" name="' . $name . '">';
		foreach ( $attributes as $slug => $label ) { 
			$dropdown .= '
				<option value="' . $slug . '"' . ( $selected == $slug ? ' selected="selected" ' : '' ) . '>' . $label . ' (' . $slug . ')</option>';
		}
		$dropdown .= '
			</select>';
		echo $dropdown;
		return false;
	}
	
	function draw_localization_dropdown( $args = '' ) {
		$languages = array(
			'en' => 'English',
			'cs' => 'Czech',
			'de' => 'German',
			'el' => 'Greek',
			'es' => 'Spanish',
			'fr' => 'French',
			'it' => 'Italian',
			'ja' => 'Japanese',
			'ko' => 'Korean',
			'nl' => 'Dutch',
			'no' => 'Norwegian',
			'pt' => 'Portuguese',
			'ru' => 'Russian',
			'sv' => 'Swedish',
			'uk' => 'Ukranian' );
		$defaults = array( 'id' => 'wp_imovel_google_maps_localization',  'name' => 'wp_imovel_google_maps_localization',  'selected' => '' );
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		$dropdown = '
			<select id="' . $id . '" name="' . $name . '">';
		foreach ( $languages as $slug => $label ) {
			$dropdown .= '
				<option value="' . $slug . '"' . ( $selected == $slug ? ' selected="selected" ' : '' ) . '>' . $label . ' (' . $slug . ')</option>';
		}
		$dropdown .= '
			</select>';
		echo $dropdown;
		return false;
	} 
	
	
	function get_property_type_price_ranges( $slug = '' )  { 
		global $wp_imovel, $wpdb; 
		if ( empty( $slug ) ) {
			return false;
		}
		$usage = '';
		$price_ranges = $wp_imovel['price_ranges'];
		ksort( $price_ranges );
		foreach(  $price_ranges as $key => $range ) { 
			$count = $wpdb->get_col("
				SELECT COUNT( p.ID )
    			FROM {$wpdb->prefix}posts AS p,
					{$wpdb->prefix}postmeta AS pm,
					{$wpdb->prefix}postmeta AS pm1
    			WHERE p.ID = pm.post_id
					AND p.ID = pm1.post_id
					AND p.post_type = 'wp_imovel_property'
                    AND p.post_status = 'publish'
					AND pm.meta_key = 'property_type'
					AND pm.meta_value = '$slug'
					AND pm1.meta_key = 'price_range'
					AND pm1.meta_value = '$key'
				");
				
			if ( $count[0] > 0 ) { 				
				$usage .=  '<dt>' . WP_IMOVEL_F::get_price_range_string( $key ) . '</dt><dd>' . $count[0] . '</dd>';
			}
		}
		return '<dl>' . $usage . '</dl>' ;
	}
	
	function get_property_price_range_count( $property_types = array() ) { 
		global $wp_imovel, $wpdb; 
		if ( 0 == sizeof( $property_types ) ) {
			$property_types = $wp_imovel['searchable_property_types'];
		}
		$usage = array();
		foreach ( $property_types as $slug  ) {
			$count = $wpdb->get_col("
				SELECT COUNT( p.ID )
    			FROM {$wpdb->prefix}posts AS p,
					{$wpdb->prefix}postmeta AS pm
    			WHERE p.ID = pm.post_id
					AND p.post_type = 'wp_imovel_property'
                    AND p.post_status = 'publish'
					AND pm.meta_key = 'property_type'
					AND pm.meta_value = '$slug'
				");
				
			$usage[$slug]= array( $count[0], $wp_imovel['property_types'][$slug]['title'] );
		}
		return $usage;
	}

	function get_property_type_count( $property_types = array() ) { 
		global $wp_imovel, $wpdb; 
		if ( 0 == sizeof( $property_types ) ) {
			$property_types = $wp_imovel['searchable_property_types'];
		}
		$usage = array();
		foreach ( $property_types as $slug  ) {
			$count = $wpdb->get_col("
				SELECT COUNT( p.ID )
    			FROM {$wpdb->prefix}posts AS p,
					{$wpdb->prefix}postmeta AS pm
    			WHERE p.ID = pm.post_id
					AND p.post_type = 'wp_imovel_property'
                    AND p.post_status = 'publish'
					AND pm.meta_key = 'property_type'
					AND pm.meta_value = '$slug'
				");
				
			$usage[$slug]= array( $count[0], $wp_imovel['property_types'][$slug]['title'] );
		}
		return $usage;
	}
	
	
	
	
	function get_property_type_strings( $property_types = array() ) { 
		global $wp_imovel, $wpdb; 
		$strings = array();
		foreach ( $property_types as $slug  ) {
			$count = $wpdb->get_col("
				SELECT COUNT( p.ID )
    			FROM {$wpdb->prefix}posts AS p,
					{$wpdb->prefix}postmeta AS pm
    			WHERE p.ID = pm.post_id
					AND p.post_type = 'wp_imovel_property'
                    AND p.post_status = 'publish'
					AND pm.meta_key = 'property_type'
					AND pm.meta_value = '$slug'
				");
				
			if ( $count[0] ) {
				$strings[$slug]= $wp_imovel['property_types'][$slug]['title'];
			}
		}
		ksort( $strings );
		return $strings;
	}

	function get_price_range_string( $price_range = 0 ) {
		global $wp_imovel; 
		if ( empty( $wp_imovel['price_ranges'][$price_range]['start'] ) ) {
			$label = $wp_imovel['price_ranges'][$price_range]['end'];
		} else {
			$label =  $wp_imovel['price_ranges'][$price_range]['start'] . ' - ' . $wp_imovel['price_ranges'][$price_range]['end'];
		}
		return $label;
	}

	function get_price_range_strings( $price_ranges = array() ) {
		global $wp_imovel; 
		$strings = array();
		foreach ( $price_ranges as $slug  ) {
			if ( empty( $wp_imovel['price_ranges'][$slug]['start'] ) ) {
				$label = $wp_imovel['price_ranges'][$slug]['end'];
			} else {
				$label =  $wp_imovel['price_ranges'][$slug]['start'] . ' - ' . $wp_imovel['price_ranges'][$slug]['end'];
			}
			$strings[$slug]= $label;
		}
		ksort( $strings );
		return $strings;
	}
	
	function draw_price_range_dropdown( $args = '' ) {
		global $wp_imovel; 
		$defaults = array( 'id' => 'wp_imovel_price_range',  'name' => 'wp_imovel_settings[price_ranges]',  'selected' => '' );
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		$price_ranges = $wp_imovel['price_ranges'];
		if ( ! is_array( $price_ranges ) ) {
			return false;
		}
		ksort( $price_ranges );
		$dropdown = '
			<select id="' . $id. '" name="' . $name . '">';
		foreach ( $price_ranges as $slug => $values ) {
			 if ( empty( $values['start'] ) ) {
			 	$label = $values['end'];
			} else {
				$label =  $values['start'] . ' - ' . $values['end'];
			}
			$dropdown .= '
				<option value="' . $slug . '"' . ( $selected == $slug ? ' selected="selected" ' : '' ) . '>' . $label . '</option>';
		}
		$dropdown .= '
			</select>';
		echo $dropdown;
		return false;
	}
	/**
	 * Displays dropdown of available property size images
	 *
 	 *
 	 * @since 0.54
	 *
 	 */
	function draw_image_size_dropdown( $args = "" ) {
		$defaults = array( 'name' => 'wp_imovel_image_sizes',  'selected' => 'none' );
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		if ( empty( $id ) && ! empty( $name ) ) {
			$id = $name;
		}
		$image_array = get_intermediate_image_sizes();
		$dropdown = '
			<select id="' . id . '" name="' . $name . '" >';
		foreach ( $image_array as $name ) {
			$sizes = WP_IMOVEL_F::get_image_sizes( $name );
			if ( ! $sizes ) {
				continue;
			}
			$dropdown .= '
				<option value="' . $name .'"' . ( $selected == $name ? ' selected="selected"' : '' ) . '>' . $name . ': ' . $sizes['width'] . ' x ' . $sizes['height'] . '</option>';
		}
		$dropdown .= '
			</select>';
		echo $dropdown;
		return false;
	}

	function get_image_sizes( $type = false, $args = "" ) {
		global $_wp_additional_image_sizes;
		$defaults = array( 'return_all' => false);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		if ( ! $type ) {
			return false;
		}
		if ( is_array( $_wp_additional_image_sizes[$type] ) ) {
			$image_sizes = $_wp_additional_image_sizes[$type];
		} else {
			if ( $type == 'thumbnail' || $type == 'thumb' ) {
				$image_sizes = array( 
					'width' => intval( get_option( 'thumbnail_size_w' )), 
					'height' => intval( get_option( 'thumbnail_size_h' ))
					);
			}
			if ( $type == 'medium' ) {
				$image_sizes = array( 
					'width' => intval( get_option( 'medium_size_w' )), 
					'height' => intval( get_option( 'medium_size_h' ))
					);
			}
			if ( $type == 'large' ) {
				$image_sizes = array( 
					'width' => intval( get_option( 'large_size_w' )), 
					'height' => intval( get_option( 'large_size_h' ))
					);
			}
		}
		if ( ! is_array( $image_sizes ) ) {
			return false;
		}

		if ( ! $return_all ) {
			// Empty dimensions means they are deleted
			if ( empty( $image_sizes['width'] ) || empty( $image_sizes['height'] ) ) {
				return false;
			}
			// Zeroed out dimensions means they are deleted
			if ( $image_sizes['width'] == '0' || $image_sizes['height'] == '0' ) {
				return false;
			}
		}
		// Return dimensions
		return $image_sizes;
	}

	/**
	 * Saves settings, applies filters, and loads settings into global variable
	 *
	 * Attached to do_action_ref_array( 'wp_imovel_the_property', array( &$post)); in setup_postdata()
	 *
	 * @return array|$wp_imovel
	 * @since 0.54
	 *
 	 */
	function settings_action( $force_db = false ) {
		global $wp_imovel, $wp_rewrite, $wp_imovel_messages;
		global $current_user;
		if ( isset( $_REQUEST['wp_imovel_settings'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp_imovel_settings_save' ) ) {
		// Process saving settings
//		UD_F::log( 'update settings <pre>' . print_r( $_REQUEST['wp_imovel_settings'], true )  . '</pre>' );
//		UD_F::log( 'current settings <pre>' . print_r( get_option( 'wp_imovel_settings' ), true )  . '</pre>' );
			$wp_imovel_updated_settings = array_merge( get_option( 'wp_imovel_settings' ), $_REQUEST['wp_imovel_settings'] );
//		UD_F::log( 'merged settings, to be saved <pre>' . print_r( $wp_imovel_updated_settings, true )  . '</pre>' );
 			update_option( 'wp_imovel_settings', $wp_imovel_updated_settings );
			if ( isset( $_REQUEST['save_hidden_columns'] ) ) {
				update_user_meta( $current_user->data->ID, 'manage-edit-wp_imovel_property-columnshidden', (array) $_REQUEST['hidden_columns'] );
			}
//			$wp_rewrite->flush_rules();
			// Load settings out of database to overwrite defaults from action_hooks.
			$wp_imovel_db = get_option( 'wp_imovel_settings' );
			// Overwrite $wp_imovel with database setting
			$wp_imovel = array_merge( $wp_imovel, $wp_imovel_db );
			$wp_imovel_messages['notice'][] =   __( 'Settings saved.', 'wp-imovel' );
		}
		
		if ( $force_db ) {
			// Load settings out of database to overwrite defaults from action_hooks.
			$wp_imovel_db = get_option( 'wp_imovel_settings' );
			// Overwrite $wp_imovel with database setting
			$wp_imovel = array_merge( $wp_imovel, $wp_imovel_db );
		}
		add_filter( 'wp_imovel_image_sizes', array( 'WP_IMOVEL_F', 'remove_deleted_image_sizes' ) );
		// Filters are applied
/*
		$wp_imovel['configuration'] 					= apply_filters( 'wp_imovel_configuration', $wp_imovel['configuration']);
		$wp_imovel['location_matters'] 					= apply_filters( 'wp_imovel_location_matters', $wp_imovel['location_matters']);
		$wp_imovel['hidden_attributes'] 				= apply_filters( 'wp_imovel_hidden_attributes', $wp_imovel['hidden_attributes']);
		$wp_imovel['descriptions'] 						= apply_filters( 'wp_imovel_label_descriptions' , $wp_imovel['descriptions']);
		$wp_imovel['image_sizes'] 						= apply_filters( 'wp_imovel_image_sizes' , $wp_imovel['image_sizes']);
		$wp_imovel['price_ranges'] 						= apply_filters( 'wp_imovel_price_ranges' , $wp_imovel['price_ranges']);
		$wp_imovel['search_conversions'] 				= apply_filters( 'wp_imovel_search_conversions' , $wp_imovel['search_conversions']);
		$wp_imovel['searchable_attributes'] 			= apply_filters( 'wp_imovel_searchable_attributes' , $wp_imovel['searchable_attributes']);
		$wp_imovel['searchable_property_types'] 		= apply_filters( 'wp_imovel_searchable_property_types' , $wp_imovel['searchable_property_types']);
		$wp_imovel['property_inheritance'] 				= apply_filters( 'wp_imovel_property_inheritance' , $wp_imovel['property_inheritance']);
		$wp_imovel['property_private_meta'] 			= apply_filters( 'wp_imovel_property_private_meta' , $wp_imovel['property_private_meta']);
		$wp_imovel['property_public_meta'] 				= apply_filters( 'wp_imovel_property_public_meta' , $wp_imovel['property_public_meta']);
		$wp_imovel['property_types'] 					= apply_filters( 'wp_imovel_property_types' , $wp_imovel['property_types']);
*/
		return $wp_imovel;

	}

	function remove_deleted_image_sizes( $sizes ) {
		global $wp_imovel;
		foreach ( $sizes as $slug => $size ) {
			if ( $size['width'] == '0' || $size['height'] == '0' ) {
				unset( $sizes[$slug] );
			}
		}
		return $sizes;
	}
	/**
	 * Loads property values into global $post variables
	 *
	 * Attached to do_action_ref_array( 'wp_imovel_the_property', array( &$post)); in setup_postdata()
	 *
	 * @todo There may be a better place to load property variables
	 * @since 0.54
	 *
 	 */
	function wp_imovel_the_property( $post ) {
		global $post;
		if ( $post->post_type == 'wp_imovel_property' ) {
			$post = WP_IMOVEL_F::get_property( $post->ID, "return_object=true" );
		}
 	}

	function activation() {
		if ( ! get_option( 'wp_imovel_settings' ) ) { 
			add_option( 'wp_imovel_settings', '' );
		}
		
		$wp_imovel_settings = get_option( 'wp_imovel_settings' );
		
		if ( empty( $wp_imovel_settings['configuration']['display_address_format'] ) ) {
			$wp_imovel_settings['configuration']['display_address_format'] =  "[street_number] [street_name], [city], [state]";
		}
		if ( empty( $wp_imovel_settings['configuration']['google_maps_localization'] ) ) {
			$wp_imovel_settings['configuration']['google_maps_localization'] =  "en";
		}
		update_option( 'wp_imovel_settings', $wp_imovel_settings );
	}

	function deactivation() {
		global $wp_rewrite;
/*
		$wp_imovel_options = array( 
			'wp_imovel_settings', 
			'wp_imovel_property_feature_children', 
			'wp_imovel_community_feature_children' 
		);
*/
/*

todo - offer some backup option before removing info


		$wp_imovel_options = array( 'wp_imovel_settings' );
		foreach ( $wp_imovel_options as $option ) {
			if ( get_option( $option ) ) {
				 delete_option( $option );
			}
		}
		unregister_widget( 'wp_imovel_featured_properties_widget' );
		unregister_widget( 'wp_imovel_child_properties_widget' );
		unregister_widget( 'wp_imovel_search_properties_widget' );
		unregister_widget( 'wp_imovel_gallery_properties_widget' );
		$wp_rewrite->flush_rules();
*/
	}
	/**
	 * Returns array of searchable property IDs
	 *
	 *
	 * @return array|$wp_imovel
	 * @since 0.621
	 *
 	 */
	function get_searchable_properties() {
		global $wp_imovel;
		if ( ! is_array( $wp_imovel['searchable_property_types'] ) ) {
			return false;
		}
		// Get IDs of all property types
		$searchable_properties = array();
		foreach ( $wp_imovel['searchable_property_types'] as $property_type ) {
			$this_type_properties = WP_IMOVEL_F::get_properties( 'property_type=' . $property_type );
			if ( is_array( $this_type_properties) && is_array( $searchable_properties ) ) {
				$searchable_properties = array_merge( $searchable_properties, $this_type_properties );
			}
		}
		if ( is_array( $searchable_properties ) ) {
			return $searchable_properties;
		} else {
			return false;
		}
	}
	/**
	 * Returns array of searchable attributes and their ranges
	 *
	 *
	 * @todo Should cache values
	 * @return array|$wp_imovel
	 * @since 0.57
	 *
 	 */
	function get_search_values( $search_attributes, $searchable_property_types, $cache = false ) {
		global $wpdb, $wp_imovel;
		$cachefile = WP_IMOVEL_DIR . '/cache/searchwidget.values.res';

		$result = false;
		if ( $cache && is_file( $cachefile ) && ( time() - filemtime( $cachefile ) < 3600 ) ) {
			$result = unserialize( file_get_contents( $cachefile ) );
		}
		if ( ! $result ) {
    		$query_types = "";
    		if ( is_array( $searchable_property_types ) ) {
    		   $query_types = "'" . implode('\',\'', $searchable_property_types ) . "'";
			 }
    		
    		$matching_ids = $wpdb->get_col("
				SELECT post_id
    			FROM {$wpdb->prefix}posts AS p,
					{$wpdb->prefix}postmeta as pm
    			WHERE p.ID = pm.post_id
                    AND p.post_status = 'publish'
					AND pm.meta_value IN ({$query_types})
				");
//			UD_F::log( 'get_search_values() 1st query ' . $wpdb->last_query. "<br /><pre>" . print_r( $matching_ids, true ) . '</pre>' );    		
			
    		if ( empty( $matching_ids ) ) {
    		    return false;
			}
    		$matching_ids = "'" . implode('\',\'', $matching_ids ) . "'";

    		$query_attributes = "";
    		if ( is_array( $search_attributes ) ) {
    		    $query_attributes = "'" . implode( '\',\'', $search_attributes ) . "'";
			}

    		$results = $wpdb->get_results("
				SELECT post_id, meta_key, meta_value 
    			FROM {$wpdb->prefix}postmeta
    			WHERE post_id IN ({$matching_ids}) 
					AND meta_key IN ({$query_attributes})
				", ARRAY_A);
				
//			UD_F::log( 'get_search_values() 2nd query ' . $wpdb->last_query. "<br /><pre>" . print_r( $results, true ) . '</pre>' );    		

    		if ( empty( $results ) ) {
    		    return false;
    		}
    		$searchable_properties = array();
    		foreach ( $results as $value ) {
                $searchable_properties[$value['post_id']][$value['meta_key']] = $value['meta_value'];
    		}
//			UD_F::log( 'get_search_values() $searchable_properties <pre>' . print_r( $searchable_properties, true ) . '</pre>' );    		
    		
			$existing_attributes = array();
    		foreach ( $searchable_properties as $searchable_property_attributes ) {
    			foreach ( $searchable_property_attributes as $searchable_attribute  => $searchable_value ) {
    				if ( ! empty( $searchable_value ) && ! in_array( $searchable_attribute, $existing_attributes ) ) {
						$existing_attributes[] = $searchable_attribute;
					}
				}
			}
//			UD_F::log( '$existing_attributes <pre>' . print_r( $existing_attributes, true ) . '</pre>' );    		
			
			$result = array();
     		// Cycle through all searchable properties all searchable data into one array
    		foreach ( $searchable_properties as $searchable_property ) {
    			foreach ( $existing_attributes as $searchable_attribute ) {
//					UD_F::log( '$searchable_attribute <pre>' . print_r( $searchable_attribute, true ) . '</pre>' );    		
     				// Clean up values if a conversion exists
//    				$search_value = WP_IMOVEL_F::do_search_conversion( $searchable_attribute, trim( $searchable_property[$searchable_attribute] ) );
    				$search_value = $searchable_property[$searchable_attribute];
    				if ( ! empty( $search_value ) ) {
	    				if ( strpos( $search_value, '-' ) ) {
		    				// Fix ranges
							$split = explode( '-', $search_value );
							foreach ( $split as $new_search_value ) {
								if ( ! empty( $new_search_value ) ) {
									$result[$searchable_attribute][] = trim( $new_search_value );
								}
							}
						} else {
							$result[$searchable_attribute][$search_value] = $search_value;
//			UD_F::log( '$result <pre>' . print_r( $result, true ) . '</pre>' );    		
							$result[$searchable_attribute]   = array_unique( $result[$searchable_attribute] );
//			UD_F::log( 'unique $result <pre>' . print_r( $result, true ) . '</pre>' );    		
//							sort( $result[$searchable_attribute], SORT_NUMERIC );
						}
					}
    			}
    		}
			if ( $cache ) {
				$cachedir = dirname( $cachefile );
				if ( ! is_dir( $cachedir ) ) {
					wp_mkdir_p( $cachedir );
				}
				@file_put_contents($cachefile, serialize( $result ) );
			}
		}
		return $result;
	}
	/*
	 * Returns searchable values
	 *
	 */
function draw_search_options( $slug ) {
	global $wpdb, $wp_imovel;

		$results = $wpdb->get_results(
			$wpdb->prepare("
				SELECT DISTINCT pm.meta_value 
				FROM {$wpdb->prefix}posts AS p,
					{$wpdb->prefix}postmeta as pm
				WHERE p.ID = pm.post_id
					AND p.post_status = 'publish'
					AND pm.meta_key = %s 
				", $slug ),
			 ARRAY_N );
//		UD_F::log( 'draw_search_options() 0th query ' . $wpdb->last_query. "<br /><pre>" . print_r( $results, true ) . '</pre>' );    		
		if ( empty( $results ) ) {
			return false;
		} else {
			$result = array();
			foreach( $results as $result_array ) {
				array_push( $result,  $result_array[0] );
			}
			sort( $result );
//		UD_F::log( 'draw_search_options() result <br /><pre>' . print_r( $result, true ) . '</pre>' );    		
			return $result;
		}
			
		$matching_ids = $wpdb->get_col("
			SELECT post_id
			FROM {$wpdb->prefix}posts AS p,
				{$wpdb->prefix}postmeta as pm
			WHERE p.ID = pm.post_id
				AND p.post_status = 'publish'
				AND pm.meta_key ='$slug' 
			");
			UD_F::log( 'draw_search_options() 1st query ' . $wpdb->last_query. "<br /><pre>" . print_r( $matching_ids, true ) . '</pre>' );    		
		
		if ( empty( $matching_ids ) ) {
			return false;
		}
		$matching_ids = "'" . implode('\',\'', $matching_ids ) . "'";

		$results = $wpdb->get_results("
			SELECT post_id, meta_key, meta_value 
			FROM {$wpdb->prefix}postmeta
			WHERE post_id IN ({$matching_ids}) 
				AND meta_key = '$slug'
			", ARRAY_A);
			
			UD_F::log( 'draw_search_options() 2nd query ' . $wpdb->last_query. "<br /><pre>" . print_r( $results, true ) . '</pre>' );    		

		if ( empty( $results ) ) {
			return false;
		}
		$searchable_properties = array();
		foreach ( $results as $value ) {
			$searchable_properties[$value['post_id']][$value['meta_key']] = $value['meta_value'];
		}
			UD_F::log( 'draw_search_options() $searchable_properties <pre>' . print_r( $searchable_properties, true ) . '</pre>' );    		
		
		$existing_attributes = array();
		foreach ( $searchable_properties as $searchable_property_attributes ) {
			foreach ( $searchable_property_attributes as $searchable_attribute  => $searchable_value ) {
				if ( ! empty( $searchable_value ) && ! in_array( $searchable_attribute, $existing_attributes ) ) {
					$existing_attributes[] = $searchable_attribute;
				}
			}
		}
			UD_F::log( 'draw_search_options $existing_attributes <pre>' . print_r( $existing_attributes, true ) . '</pre>' );    		
		
		$result = array();
		// Cycle through all searchable properties all searchable data into one array
		foreach ( $searchable_properties as $searchable_property ) {
			foreach ( $existing_attributes as $searchable_attribute ) {
					UD_F::log( '$searchable_attribute <pre>' . print_r( $searchable_attribute, true ) . '</pre>' );    		
				// Clean up values if a conversion exists
//    				$search_value = WP_IMOVEL_F::do_search_conversion( $searchable_attribute, trim( $searchable_property[$searchable_attribute] ) );
				$search_value = $searchable_property[$searchable_attribute];
				if ( ! empty( $search_value ) ) {
					if ( strpos( $search_value, '-' ) ) {
						// Fix ranges
						$split = explode( '-', $search_value );
						foreach ( $split as $new_search_value ) {
							if ( ! empty( $new_search_value ) ) {
								$result[$searchable_attribute][] = trim( $new_search_value );
							}
						}
					} else {
						$result[$searchable_attribute][$search_value] = $search_value;
			UD_F::log( 'draw_search_options $result <pre>' . print_r( $result, true ) . '</pre>' );    		
						$result[$searchable_attribute]   = array_unique( $result[$searchable_attribute] );
			UD_F::log( 'draw_search_options unique $result <pre>' . print_r( $result, true ) . '</pre>' );    		
						sort( $result[$searchable_attribute] );
					}
				}
			}
		}
		return $result;
	}

/*
	check if a search converstion exists for a attributes value
*/
	function do_search_conversion( $attribute, $value, $reverse = false )  {
		global $wp_imovel;
		// First, check if any conversions exists for this attribute, if not, return value
		if ( count( $wp_imovel['search_conversions'][$attribute] ) < 1 ) {
			return $value;
		}
		// If reverse is set to true, means we are trying to convert a value to integer (most likely),
		// For instance: in "bedrooms", $value = 0 would be converted to "Studio"
		if ( $reverse ) {
			$flipped_conversion = array_flip( $wp_imovel['search_conversions'][$attribute] );
			// Debug:
			//echo "reverse conversion: $attribute - $value; -" .$flipped_conversion['search_conversions'][$attribute][$value]. "<br />";
			if ( ! empty( $flipped_conversion[$value] ) ) {
				return $flipped_conversion[$value];
			}
		}
		// Debug:
		//echo "doing conversion: $attribute - $value; -" .$wp_imovel['search_conversions'][$attribute][$value]. "<br />";
		// Search conversion does exist, make sure its not an empty value.
		// Need to $conversion == '0' or else studios will not work, since they have 0 bedrooms
		$conversion = $wp_imovel['search_conversions'][$attribute][$value];
		if ( $conversion == '0' || ! empty( $conversion ) ) {
			return $conversion;
		}
		// Return value in case something messed up
		return $value;
	}
	/**
	 * Primary function for queries properties  based on type and attributes
	 *
 	 *
 	 * @since 0.55
	 *
 	 */
	function get_properties( $args = '' ) {
		global $wpdb;
		$limit_query    = '';
		$sql_sort_by    = '';
		$sql_sort_order = '';
		$defaults       = array( 'property_type' => 'all' );
		if ( is_array( $maybe_array = unserialize( $args ) ) ) {
			$query = $maybe_array;
		} else {
			$query = wp_parse_args( $args, $defaults );
		}
        if ( substr_count( $query['pagi'], '--' ) ) {
            $pagi = explode( '--', $query['pagi'] );
            if ( count( $pagi ) == 2  && is_numeric( $pagi[0] ) && is_numeric( $pagi[1] ) ) {
                $limit_query = "LIMIT $pagi[0], $pagi[1];";
			} 
        }
		unset( $query['pagi'] );
		unset( $query['pagination'] );
		if ( $query['sort_by'] ) {
            $sql_sort_by = $query['sort_by'];
            $sql_sort_order = ( $query['sort_order'] ) ? strtoupper( $query['sort_order'] ) : 'ASC';
            //$sql_order = "ORDER BY `$sql_sort_by` $sql_sort_order";
        }
        unset( $query['sort_by'] ); 
		unset( $query['sort_order'] );
		
//		UD_F::log( 'WP_IMOVEL_F::get_properties() query:<pre>' . print_r( $query, true )  . '</pre>' );	
		
		if ( array_key_exists( 'id', $query  ) ) {
			// search for a specific ID, or something like the ID
				$criteria = $query['id'];
				$matching_ids_query = "
						SELECT p.ID
						FROM {$wpdb->prefix}posts AS p
						WHERE p.post_name like '%$criteria%'
							AND p.post_status = 'publish'";
				$matching_ids = $wpdb->get_col( $matching_ids_query );
		} else {
			// Go down the array list narrowing down matching properties
			foreach ( $query as $meta_key => $criteria ) {
	//			UD_F::log( "processing : $meta_key =&gt; $criteria " );
				if ( isset( $matching_ids ) && empty( $matching_ids ) ) {
	//				UD_F::log("Stop filtering because no IDs left, count: " . count( $matching_ids ) );
					break;
				}
				if ( is_array( $criteria ) ) {
					$min = $criteria['min'];
					$max = $criteria['max'];
					$specific = $criteria['specific'];
				} else {
					if ( substr_count( $criteria, ',' ) || substr_count( $criteria, '-' )) {
						if ( substr_count( $criteria, ',' ) && ! substr_count( $criteria, '-' )) {
							$comma_and = explode( ',', $criteria );
						}
						if ( substr_count( $criteria, '-' ) && ! substr_count($criteria, ',' )) {
							$hyphen_between = explode( '-', $criteria );
						}
					} else {
						$specific = $criteria;
					}
				}
	
	/**
	UD_F::log( 'WP_IMOVEL_F::get_properties() query:<pre>' . print_r( $query, true )  . '
	metakey :'.$meta_key.'
	comma   :"'.print_r($comma_and, true). '"
	hyphen  :"'.print_r($hyphen_between, true).'"
	specific:"'.$specific.'"
	criteria:"'.print_r( $criteria, true ) . '"</pre>' );
	//echo $meta_key. ': comma:"'.print_r($comma_and, true). '"|hyphen:"'.print_r($hyphen_between, true).'"|specific:"'.$specific. '"|criteria:"'.print_r($criteria, true).'"<br>';
	/**/
				switch ( $meta_key ) {
					case 'property_type':
						if ( $specific == 'all' ) {
							// Get all property types
	//						UD_F::log( 'Get all IDs for any property_type' );
							$matching_ids_query = "
									SELECT pm.post_id 
									FROM {$wpdb->prefix}posts AS p, 
										{$wpdb->prefix}postmeta AS pm
									WHERE p.ID = pm.post_id
										AND p.post_status = 'publish'
										AND pm.meta_key = 'property_type'";
							if ( isset( $matching_ids ) ) {
								$matching_id_filter = implode( "' OR pm.post_id ='", $matching_ids );
								$matching_ids_query .= "
										AND ( pm.post_id = '$matching_id_filter' )";
							}
						} else {
							//UD_F::log( "Filtering property_type <em>'$meta_key'</em>" );
							if ( ! is_array( $criteria ) ) {
								$criteria = array( $criteria );
							}
							if ( $comma_and ) {
								$where_string = implode("' OR pm.meta_value ='", $comma_and );
							} else {
								$where_string = $specific;
							}
							$matching_ids_query = "
									SELECT pm.post_id 
									FROM {$wpdb->prefix}posts AS p, 
										{$wpdb->prefix}postmeta AS pm
									WHERE p.ID = pm.post_id
										AND p.post_status = 'publish'
										AND pm.meta_key = 'property_type'
										AND ( pm.meta_value ='$where_string' )";
							if ( isset( $matching_ids ) ) {
								$matching_id_filter = implode( "' OR pm.post_id ='", $matching_ids );
								$matching_ids_query .= "
										AND	( pm.post_id = '$matching_id_filter' ) ";
							}
						}
					break;
					default:
						if ( empty( $min ) && empty( $max ) && empty( $specific ) ) {
							//UD_F::log( "Skipping meta_key '<em>$meta_key</em>' search because no criteria passed.");
							continue;
						}
						if ( WP_IMOVEL_F::is_numeric_range( $criteria ) ) {
							$matching_ids_query = "
									SELECT pm.post_id 
									FROM {$wpdb->prefix}posts AS p, 
										{$wpdb->prefix}postmeta AS pm
									WHERE p.ID = pm.post_id
										AND p.post_status = 'publish'
										AND pm.meta_key = '$meta_key' 
										AND ( pm.meta_value BETWEEN $min AND $max )";
										
							if ( isset( $matching_ids ) ) {
								$matching_id_filter = implode( "' OR pm.post_id ='", $matching_ids );
								$matching_ids_query .= "
										AND ( pm.post_id ='$matching_id_filter' )";
							}
						} else {
							if ( $specific == 'all' && ! $comma_and && ! $hyphen_between ) {
							// Get all properties for that meta_key
								$matching_ids_query = "
										SELECT pm.post_id 
										FROM {$wpdb->prefix}posts AS p, 
											{$wpdb->prefix}postmeta AS pm
										WHERE p.ID = pm.post_id
											AND p.post_status = 'publish'
											AND pm.meta_key = '$meta_key' 
											AND pm.meta_value != ''";
								//UD_F::log("Filtering meta_key '$meta_key', \$specific == 'all' && ! \$comma_and && ! \$hyphen_between");
								if ( isset( $matching_ids ) ) {
									$matching_id_filter = implode( "' OR pm.post_id ='", $matching_ids );
									$matching_ids_query .= "
											AND ( pm.post_id ='$matching_id_filter' ) ";
								}		
							} else {
								//UD_F::log("Filtering meta_key '$meta_key', \$specific == '$specific '");
								if ( $specific == 'all' && $comma_and ) {
								//UD_F::log("Filtering meta_key '$meta_key', \$comma_and == '" . print_r($comma_and, true ) . "'");
									$where_and = "pm.meta_key = '$meta_key' 
											AND ( pm.meta_value ='" . implode( "' OR pm.meta_value ='", $comma_and ) . "' )";
									$specific = $where_and;
								}
								if ( $specific == 'all' && $hyphen_between ) {
								//UD_F::log("Filtering meta_key '$meta_key', \$comma_and == '" . print_r($comma_and, true ) . "'");
									foreach ( ( array ) $hyphen_between as $value ) {
										if ( is_numeric( $value ) ) {
											$hyphen_between_temp[] = $value;
										}
									}
									$hyphen_between = $hyphen_between_temp;
									$where_between = "pm.meta_key = '$meta_key' 
											AND pm.meta_value BETWEEN '" . implode( "' AND '", $hyphen_between ) . "'";
									$specific = $where_between;
								}
								if ( ! substr_count( $specific, 'meta_value' ) ) {
									$specific = "pm.meta_value = '$specific'";
	//								$specific = "pm.meta_value LIKE '%" . ( str_replace(' ', '%', $specific ) ) ."%'";
								}
								$matching_ids_query = "
										SELECT post_id 
										FROM {$wpdb->prefix}postmeta pm
										WHERE pm.meta_key = '$meta_key' 
											AND $specific";
								if ( isset( $matching_ids ) ) {
									$matching_id_filter = implode( "' OR pm.post_id ='", $matching_ids );
									$matching_ids_query .= "
										AND ( pm.post_id = '$matching_id_filter' ) ";
								}
							}
						}
				}
				$matching_ids = $wpdb->get_col( $matching_ids_query );
	//			UD_F::log( '<pre>' . $wpdb->last_query. "\n" . print_r( $matching_ids, true ) . '</pre>' );
				unset( $comma_and );
				unset( $hyphen_between );
				unset( $specific );
			}
		}
		if ( ! empty( $matching_ids ) ) {		
			// remove duplicates
			$matching_ids = array_unique( $matching_ids );
			// calculate total published properties
			$total = $wpdb->get_var("
				SELECT COUNT(DISTINCT ID) 
				FROM {$wpdb->prefix}posts 
				WHERE (ID = '" . implode("' OR ID = '", $matching_ids) . "') 
					AND post_status = 'publish'
				");
			// make sure selected posts are published, and sorted accordin to menu order
			if ( $sql_sort_by && $sql_sort_by != 'menu_order' ) {
				$matching_ids = $wpdb->get_col("
					SELECT p.ID 
					FROM {$wpdb->prefix}posts AS p, 
						{$wpdb->prefix}postmeta AS pm
					WHERE p.ID IN (" . implode(",", $matching_ids) . ")
						AND p.ID = pm.post_id
						AND p.post_status = 'publish'
						AND pm.meta_key = '$sql_sort_by'
					ORDER BY pm.meta_value $sql_sort_order
					$limit_query
					");
			} else {
				$matching_ids = $wpdb->get_col("
					SELECT ID 
					FROM {$wpdb->prefix}posts 
					WHERE ( ID = '" . implode( "' OR ID = '", $matching_ids ) . "' ) 
						AND post_status = 'publish' 
					ORDER BY menu_order ASC
					$limit_query
					");
			}
//			UD_F::log( '<pre>' . $wpdb->last_query . '</pre>' );
		}
		if ( ! empty( $matching_ids ) ) {		
            $matching_ids['total'] = $total;
//			UD_F::log( "Search complete, returning <pre>" . print_r( $matching_ids, true ) . '</pre>' );
			return $matching_ids;
		} else {
//			UD_F::log( " Search complete, nothing found :-( " );
			return false;
		}
	}
	/**
	 * Load property information into an array or an object
	 *
 	 *
 	 * @since 0.55
	 *
 	 */
	function get_property( $id, $args = false ) {
		global $wp_imovel, $wpdb, $wp_imovel_cache;
		//UD_F::log("Loading property id: $id");
		$post = get_post( $id, ARRAY_A );
		if ( $post['post_type'] != 'wp_imovel_property' ) {
			return false;
		}
		$property = array();
 		$defaults = array('get_children' => 'true', 'return_object' => 'false', 'load_gallery' => 'true', 'load_thumbnail' => 'true', 'load_parent' => 'true' );
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		if ( $keys = get_post_custom( $id ) ) {
  			foreach ( $keys as $key => $value ) {
				$key_trimmed = trim( $key );
				if ( '_' == $key_trimmed[0]  ) {
					continue;
				}
				// Fix for boolean values
				switch ( $value[0] ) {
					case 'true':
						$real_value = true;
					break;
					case 'false':
						$real_value = false;
					break;
					default:
						$real_value = $value[0];
					break;
				}
 				// if a property_meta value, we do a nl2br since it will most likely have line breaks
				if ( array_key_exists( $key, $wp_imovel['property_private_meta'] ) ) {
					$real_value = nl2br( $real_value );
				}
				$property[$key] = $real_value;
			}
 		}
		if ( sizeof( $property ) ) {
			$property = array_merge( $property, $post );
		}
		/*
			Figure out what the thumbnail is, and load all sizes
		*/
		if ( $load_thumbnail == 'true' ) {
			$wp_image_sizes = get_intermediate_image_sizes();
			$thumbnail_id = get_post_meta( $id, '_thumbnail_id', true );
			$attachments = get_children( array( 'post_parent' => $id, 'post_type' => 'attachment', 'post_mime_type' => 'image',  'orderby' => 'menu_order ASC, ID', 'order' => 'DESC' ) );
			if ( $thumbnail_id ) {
				foreach ( $wp_image_sizes as $image_name ) {
					$this_url = wp_get_attachment_image_src( $thumbnail_id, $image_name , true );
					$property['images'][$image_name] = $this_url[0];
				}
				$featured_image_id = $thumbnail_id;
			} elseif ($attachments ) {
				foreach ( $attachments as $attachment_id => $attachment ) {
					foreach( $wp_image_sizes as $image_name ) {
						$this_url =  wp_get_attachment_image_src( $attachment_id, $image_name , true );
						$property['images'][$image_name] = $this_url[0];
					}
					$featured_image_id = $attachment_id;
					break;
				}
			}
			if ( $featured_image_id ) {
				$property['featured_image'] = $featured_image_id;
				$image_title = $wpdb->get_var("SELECT post_title  FROM {$wpdb->prefix}posts WHERE ID = '$featured_image_id' ");
				$property['featured_image_title'] = $image_title;
				$property['featured_image_url'] = wp_get_attachment_url($featured_image_id);
			}
		}
		/*
			Load all attached images and their sizes
		*/
		if ( $load_gallery == 'true' ) {
			if ( ! $attachments) {
				$property['gallery'] = false;
			} else {
				// Get gallery images
				foreach ( $attachments as $attachment_id => $attachment ) {
					$property['gallery'][$attachment->post_name]['post_title'] = $attachment->post_title;
					$meta = wp_get_attachment_metadata( $attachment_id );
					if ( ! isset( $meta['wp_imovel_private'] ) || empty( $meta['wp_imovel_private'] ) || $meta['wp_imovel_private'] != 'private' ) {
						$property['gallery'][$attachment->post_name]['image_privacy'] = 'public';
					} else {
						$property['gallery'][$attachment->post_name]['image_privacy'] = 'private';
					}
					foreach ( $wp_image_sizes as $image_name ) {
						$this_url =  wp_get_attachment_image_src( $attachment_id, $image_name , true );
						$property['gallery'][$attachment->post_name][$image_name] = $this_url[0];
					}
				}
			}
		}
		/*
			Load parent if exists.
			Inherit Parent's Properties
		*/
		if ( $load_parent == 'true' && $post['post_parent'] ) {
			$property['is_child'] = true;
			$parent_object = WP_IMOVEL_F::get_property( $post['post_parent'], "get_children=false" );
 			$property['parent_id'] = $post['post_parent'];
			$property['parent_link'] = $parent_object['permalink'];
			$property['parent_title'] = $parent_object['post_title'];
			// Inherit things
			if ( is_array( $wp_imovel['property_inheritance'][$property['property_type']] ) ) {
				foreach ( $wp_imovel['property_inheritance'][$property['property_type']] as $inherit_attrib ) {
					if ( ! empty( $parent_object[$inherit_attrib] ) && empty( $property[$inherit_attrib] ) ) {
						$property[$inherit_attrib] = $parent_object[$inherit_attrib];
					}
				}
			}
		}
		/*
			Load Children and their attributes
		*/
		if ( $get_children == 'true' ) {
			// Calculate variables if based off children if children exist
			$children = $wpdb->get_col("
				SELECT ID 
				FROM {$wpdb->prefix}posts 
				WHERE  post_type = 'wp_imovel_property' 
					AND post_status = 'publish' 
					AND post_parent = '$id' 
				ORDER BY menu_order ASC ");
			//print_r($children);
			if ( count( $children ) > 0 ) {
				$range = array();
				// Cycle through children and get necessary variables
				foreach ( $children as $child_id ) {
					$child_object = WP_IMOVEL_F::get_property( $child_id, "load_parent=false");
					$property['children'][$child_id] = $child_object;
					// Exclude variables from searchable attributes ( to prevent ranges )
					$excluded_attributes = array(
						$wp_imovel['configuration']['address_attribute'],
						'city',
						'country_code',
						'country',
						'state',
						'state_code',
						'state' );
					foreach ( $wp_imovel['searchable_attributes'] as $searchable_attribute ) {
						if ( ! empty( $child_object[$searchable_attribute] ) && ! in_array( $searchable_attribute, $excluded_attributes ) ) {
							$range[$searchable_attribute][]	= $child_object[$searchable_attribute];
						}
					}
				}
				// Cycle through every type of range (i.e. price, deposit, bathroom, etc) and fix-up the respective data arrays
				foreach ( $range as $range_attribute => $range_values ) {
					// Cycle through all values of this range (attribute), and fix any ranges that use dashes
					foreach ( $range_values as $key => $single_value ) {
						// Remove dollar signs
						$single_value = str_replace( "$" , '', $single_value); //FIX-ME: Should use setting, 'R$'
						// Fix ranges
						if ( strpos( $single_value, '-' ) ) {
							$split = explode( '-', $single_value );
							foreach ( $split as $new_single_value ) {
								if ( ! empty( $new_single_value ) ) {
									array_push( $range_values, trim( $new_single_value ) );
								}
							}
							// Unset original value with dash
							unset( $range_values[$key] );
						}
					}
					// Remove duplicate values from this range
					$range[$range_attribute] =  array_unique( $range_values );
					// Sort the values in this particular range
 					sort( $range[$range_attribute] );
 					if ( count($range[$range_attribute] ) < 2 ) {
						$property[$range_attribute] = $range[$range_attribute][0];
					}
					if ( count($range[$range_attribute] ) > 1 ) {
						$property[$range_attribute] = min( $range[$range_attribute] ) . ' - ' .  max( $range[$range_attribute] );
					}
				}
			}
		} /* end get_children */
		// Another name for location
        $property['address'] = $property['location'];
		$property['permalink'] = get_permalink( $id );
		if ( empty( $property['phone_number'] ) && ! empty( $wp_imovel['configuration']['phone_number'] ) ) {
			$property['phone_number'] = $wp_imovel['configuration']['phone_number'];
		}
		ksort( $property );
		$property = apply_filters( 'wp_imovel_get_property', $property );
		// Get rid of all empty values
		foreach ( $property as $key => $item ) {
			if ( empty( $item ) ) {
				unset( $property[$key] );
			}
		}
		// Convert to object
		if ( $return_object == 'true' ) {
			$property = WP_IMOVEL_F::array_to_object( $property );
		}
		// Save to cache
		if ( is_object( $property ) ) {
			$wp_imovel_cache[$id]['object'] = $property;
		}
		if ( is_array( $property ) ) {
			$wp_imovel_cache[$id]['array'] = $property;
		}
		return $property;
	}
/*
	Returns array of all values for a particular attribute/meta_key
*/
	function get_all_attribute_values( $slug ) {
		global $wpdb;
		$prefill_meta_values = $wpdb->get_col("
			SELECT meta_value 
			FROM {$wpdb->prefix}postmeta 
			WHERE meta_key = '$slug'
			");

		
		$prefill_meta_values = apply_filters( 'wp_imovel_prefill_meta', $prefill_meta_values, $slug );
		if ( count( $prefill_meta_values ) < 1 ) {
			return false;
		}
		// Clean up values
		$all_attribute_values = array();
		foreach ( $prefill_meta_values as $value ) {
			if ( empty( $value ) ) {
				continue;
			}
			$all_attribute_values[] = $value;
		}
		// Remove duplicates
		$all_attribute_values = array_unique( $all_attribute_values );
		sort( $all_attribute_values );
		return $all_attribute_values;
	}
/*
	gets a count for a particular attrribute/meta_key
*/
	function get_attribute_count( $value ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare("
			SELECT COUNT(*) 
			FROM {$wpdb->prefix}postmeta  
			WHERE meta_value = %s", $value  ) );
	}

/*
	Update value for a particular attribute/meta_key
*/
	function set_attribute_value( $slug, $old_value, $new_value ) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare("
			UPDATE {$wpdb->prefix}postmeta  
			SET meta_value = %s
			WHERE meta_key = %s
				AND meta_value = %s", $new_value, $slug, $old_value ) );
	}
	

/*
	Gets prefix to an attribute
*/
	function get_attrib_prefix($attrib) {
		if ( $attrib == 'price' ) {
			return "$"; // FIX-ME, ;-) R$ 
		}
		if ( $attrib == 'deposit' ) {
			return "$"; // FIX-ME, ;-) R$ 
		}
		return false;
	}
/*
	Gets annex to an attribute
*/
	function get_attrib_annex($attrib) {
		if ( $attrib == 'area' ) {
			return " sq ft."; // FIX-ME  metro quadrado
		}
		return false;
	}

/*
	Get coordinates for property out of database
*/
	function get_coordinates( $listing_id = false ) {
		global $post;
		if (  ! $listing_id ) {
			$listing_id = $post->ID;
		}
		$latitude = get_post_meta( $listing_id, 'latitude', true );
		$longitude = get_post_meta( $listing_id, 'longitude', true );
		if ( empty( $latitude) || empty( $longitude ) ) {
			// Try parent
			if ( $post->parent_id )  {
				$latitude = get_post_meta( $post->parent_id, 'latitude', true );
				$longitude = get_post_meta($post->parent_id, 'longitude', true );
			}
			// Still nothing ?
			if ( empty( $latitude ) || empty( $longitude ) ) {
				return false;
			}
		}
		return array( 'latitude' => $latitude, 'longitude' => $longitude );
	}
/*
	Validate if a URL is valid.
*/
	function isURL( $url ) {
		return preg_match( '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url );
	}

/*
	Returns an array of a property's stats and their values
	Query is array of variables to use
*/
	function get_stat_values_and_labels( $property_object, $args = false, $meta = 'property_public_meta' ) {
		global $wp_imovel;
		$defaults = array( );
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		if ( $exclude ) {
			$exclude = explode( ', ', $exclude );
		}
		if ( $include ) {
			$include = explode( ', ', $include );
		}
		$stats_values_and_labels = array();
		$property_stats = $wp_imovel[$meta];
		foreach ( $property_stats as $slug => $label ) {
			$value = $property_object->$slug;
			// Exclude passed variables
			if ( is_array( $exclude ) && in_array( $slug, $exclude ) ) {
				continue;
			}
			// Include only passed variables
			if ( is_array( $include ) && in_array( $slug, $include ) ) {
				if ( ! empty( $value ) ) {
					$stats_values_and_labels[$label] = $value;
				}
				continue;
			}
			if ( ! is_array( $include ) ) {
				if (  ! empty( $value ) ) {
					$stats_values_and_labels[$label] = $value;
				}
			}
		}
		if ( count( $stats_values_and_labels ) > 0 ) {
			return $stats_values_and_labels;
		} else {
			return false;
		}
	}

	function array_to_object( $array = array() ) {
		$data = false;
		foreach ( $array as $akey => $aval ) {
			$data->{$akey} = $aval;
		}
		return $data;
	}
	
}
?>