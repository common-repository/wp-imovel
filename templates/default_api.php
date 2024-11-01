<?php
	// Add post-thumbnails support
	add_action( 'after_setup_theme', array( 'WP_IMOVEL_Core', 'wp_imovel_after_setup_theme'));	
	// Widget address format
	add_filter( 'wp_imovel_stat_filter_' . $wp_imovel['configuration']['address_attribute'], 'wp_imovel_format_address_attribute', 0 ,3 );
	// Add some default actions
//	add_filter( 'wp_imovel_stat_filter_price', 'wp_imovel_add_dollar_sign' );
//	add_filter( 'wp_imovel_stat_filter_deposit', 'wp_imovel_add_dollar_sign' );
//	add_filter( 'wp_imovel_stat_filter_area', 'wp_imovel_add_square_foot' );
	add_filter( 'wp_imovel_stat_filter_phone_number', 'wp_imovel_format_phone_number' );
//	add_filter( 'wp_imovel_get_property', 'wp_imovel_add_display_address' );
//	add_filter( 'wp_imovel_property_inheritance', 'wp_imovel_add_city_to_inheritance' );
//	add_filter( 'wp_imovel_searchable_attributes', 'wp_imovel_add_city_to_searchable' );
/*
	// Add sold/rented options
	add_filter( 'wp_imovel_property_public_meta', 'wp_imovel_property_public_meta_add_sold_or_rented' );
	add_filter( 'wp_imovel_property_public_meta_input_for_rent', 'wp_imovel_property_public_meta_input_for_rent_make_checkbox', 0, 3);
	add_filter( 'wp_imovel_property_public_meta_input_for_sale', 'wp_imovel_property_public_meta_input_for_sale_make_checkbox', 0, 3 );
	add_filter( 'wp_imovel_stat_filter_for_rent', 'wp_imovel_stat_filter_for_rent_fix' );
	add_filter( 'wp_imovel_stat_filter_for_sale', 'wp_imovel_stat_filter_for_sale_fix' );
	add_action( 'wp_imovel_ui_after_attribute_' . $wp_imovel['configuration']['address_attribute'], 'wp_imovel_show_coords' );
	add_action( 'wp_imovel_ui_after_attribute_price', 'wp_imovel_show_week_month_selection' );
*/	
	function wp_imovel_format_address_attribute( $data, $property = false, $format = "[street_number] [street_name],\n[city], [state]") {
		if ( ! is_object( $property ) ) {
			return $data;
		}
		$street_number  = $property->street_number;
		$route  = $property->route;
		$city  = $property->city;
		$state  = $property->state;
		$state_code  = $property->state_code;
		$county  = $property->county;
		$country  = $property->country;
		$postal_code  = $property->postal_code;				
		$display_address = $format;
		$display_address = str_replace("[street_number]", $street_number,$display_address );
		$display_address = str_replace("[street_name]", $route, $display_address );
		$display_address = str_replace("[city]", "$city", $display_address );
		$display_address = str_replace("[state]", "$state", $display_address );
		$display_address = str_replace("[state_code]", "$state_code", $display_address );
		$display_address = str_replace("[county]", "$county" ,$display_address );
		$display_address = str_replace("[country]", "$country", $display_address );
		$display_address = str_replace("[zip_code]", "$postal_code" ,$display_address );
		$display_address = str_replace("[zip]", "$postal_code", $display_address );
		$display_address = str_replace("[postal_code]", "$postal_code", $display_address );
		$display_address = preg_replace( '/^\n+|^[\t\s]*\n+/m', "", $display_address );
		$display_address = nl2br( $display_address );
		return $display_address;
	}

	
	function wpimovel_property_stats_add_sold_or_rented($property_stats) {
		$property_stats['for_sale']= __("For Sale",'wp-imovel' );
		$property_stats['for_rent']= __("For Rent",'wp-imovel' );
		return $property_stats;
	}
	
	function wp_imovel_property_public_meta_input_for_rent_make_checkbox($content, $slug, $object) {
		$checked = ( get_post_meta( $object->ID, $slug, true ) == 'true' ? ' checked="true" ': '' );
		return '
			<input type="hidden" name="wp_imovel_data[meta][' . $slug . ']"  value="false"  />
			<input type="checkbox" id="wp_imovel_meta_' . $slug . '" name="wp_imovel_data[meta][' . $slug . ']"  value="true" ' . $checked . ' /> 
			<label for="wp_imovel_meta_' . $slug . '">' . __( 'This is a rental property.', 'wp-imovel' ) . '</label>
		';
	}
		
	function wp_imovel_property_public_meta_input_for_sale_make_checkbox($content, $slug, $object) {
		$checked = (get_post_meta($object->ID, $slug, true) == 'true' ? ' checked="true" ': false);
		return '
			<input type="hidden" name="wp_imovel_data[meta][' . $slug . ']"  value="false"  />
			<input type="checkbox" id="wp_imovel_meta_' . $slug . '" name="wp_imovel_data[meta][' . $slug . ']"  value="true" ' . $checked . ' /> 
			<label for="wp_imovel_meta_' . $slug . '">' . __( 'This property is for sale.', 'wp-imovel' ) . '</label>
		';
	}
	
	function wp_imovel_stat_filter_for_rent_fix($value) {
		if ( $value == '1' ) {
			return __( 'Yes', 'wp-imovel' );
		}
	}
		
	function wp_imovel_stat_filter_for_sale_fix($value) {
		if ( $value == '1' ) {
			return __( 'Yes', 'wp-imovel' );
		}
	}
	
	/**	
	 * Formats phone number for display
	 *
 	 *
	 * @since 1.0
 	 * @param string $phone_number 
 	 * @return string $phone_number 
	 */
	function wp_imovel_format_phone_number( $phone_number ) {
		wp_die('filtering fone');
		$phone_number = ereg_replace( "[^0-9]", '', $phone_number );
		if ( strlen($phone_number) != 10 ) { 
			return false;
		}
		$sArea = substr( $phone_number, 0, 2 );
		$sPrefix = substr( $phone_number, 2, 4 );
		$sNumber = substr( $phone_number, 6, 4 );
		$phone_number = '( ' . $sArea . ' ) ' . $sPrefix . '-' . $sNumber; 
		return $phone_number;
	}
	
	/**	
	 * Add "city" as an inheritable attribute for city property_type
	 *
	 * Modifies $wp_imovel['property_inheritance'] in WP_IMOVEL_F::settings_action(), overriding database settings
	 *
	 * @since 1.0
 	 * @param array $property_inheritance 
 	 * @return array $property_inheritance 
	 */
	function wp_imovel_add_city_to_inheritance( $property_inheritance ) {
		$property_inheritance['floorplan'][] = 'city';
		return $property_inheritance;
	}
	
	/**	
	 * Adds city to searchable
	 *
 	 * Modifies $wp_imovel['searchable_attributes'] in WP_IMOVEL_F::settings_action(), overriding database settings
 	 *
	 * @since 1.0
 	 * @param string $area 
 	 * @return string $area 
	 */	
	function wp_imovel_add_city_to_searchable( $array ) {
		array_push( $array, 'city' );
		return $array;
	}
	
	/**	
	 * Adds "m2" to the end of all area attributes
	 *
 	 *
	 * @since 1.0
 	 * @param string $area 
 	 * @return string $area 
	 */	
	function wp_imovel_add_square_foot( $area ) {
		return $area . 'm&sup2;';
	}
	
	/**	
	 * Demonstrates how to add a new attribute to the property class
	 *
	 * @since 1.0
	 */
	function wp_imovel_add_display_address( $property ) {
		global $wp_imovel;
		$display_address = $wp_imovel['configuration']['display_address_format'];
		if ( empty( $display_address) ) {
			$display_address =  "[street_number] [street_name],\n[city], [state]";
		}
		$display_address_code = $display_address;
		
		// Check if property is supposed to inehrit the address		
		if ( isset( $property['parent_id'] ) 
			&& is_array( $wp_imovel['property_inheritance'][$property['property_type']]) 
			&& in_array( $wp_imovel['configuration']['address_attribute'], $wp_imovel['property_inheritance'][$property['property_type']] ) ) {
		
			if ( get_post_meta($property['parent_id'], 'address_is_formatted', true) ) {
				$street_number = get_post_meta($property['parent_id'],'street_number', true);
				$route = get_post_meta($property['parent_id'],'route', true);
				$city = get_post_meta($property['parent_id'],'city', true);
				$state = get_post_meta($property['parent_id'],'state', true);
				$state_code = get_post_meta($property['parent_id'],'state_code', true);
				$postal_code = get_post_meta($property['parent_id'],'postal_code', true);
				$county = get_post_meta($property['parent_id'],'county', true);
				$country = get_post_meta($property['parent_id'],'country', true);
					
				$display_address = str_replace("[street_number]", $street_number,$display_address);
				$display_address = str_replace("[street_name]", $route, $display_address);
				$display_address = str_replace("[city]", "$city",$display_address);
				$display_address = str_replace("[state]", "$state",$display_address);
				$display_address = str_replace("[state_code]", "$state_code",$display_address);
				$display_address = str_replace("[country]", "$country",$display_address);
				$display_address = str_replace("[county]", "$county",$display_address);
				$display_address = str_replace("[zip_code]", "$postal_code",$display_address);
				$display_address = str_replace("[zip]", "$postal_code",$display_address);
				$display_address = str_replace("[postal_code]", "$postal_code",$display_address);
				$display_address =	preg_replace( '/^\n+|^[\t\s]*\n+/m', "", $display_address);
				$display_address = nl2br($display_address);
			}
		} else {
			// Verify that address has been converted via Google Maps API
			if ( $property['address_is_formatted'] ) {
					$street_number  = $property['street_number'];
					$route  = $property['route'];
					$city  = $property['city'];
					$state  = $property['state'];
					$state_code  = $property['state_code'];
					$country  = $property['country'];
					$postal_code  = $property['postal_code'];				
					$county  = $property['county'];				
					
					$display_address = str_replace("[street_number]", $street_number,$display_address);
					$display_address = str_replace("[street_name]", $route, $display_address);
					$display_address = str_replace("[city]", "$city",$display_address);
					$display_address = str_replace("[state]", "$state",$display_address);
					$display_address = str_replace("[state_code]", "$state_code",$display_address);
					$display_address = str_replace("[country]", "$country",$display_address);
					$display_address = str_replace("[county]", "$county",$display_address);
					$display_address = str_replace("[zip_code]", "$postal_code",$display_address);
					$display_address = str_replace("[zip]", "$postal_code",$display_address);
					$display_address = str_replace("[postal_code]", "$postal_code",$display_address);
					$display_address =	preg_replace( '/^\n+|^[\t\s]*\n+/m', "", $display_address);
					$display_address = nl2br($display_address);
			}
		}
		
 
		// If somebody is smart enough to do the following with regular expressions, let us know!
		
		$comma_killer = explode(",", $display_address);
		
		if ( is_array( $comma_killer))
			foreach($comma_killer as $key => $addy_line)
				if ( isset($addy_line))
					if ( trim( $addy_line) == "")
						unset($comma_killer[$key]);
					
		$display_address  = implode(", ", $comma_killer);
					
		$empty_line_killer = explode("<br />", $display_address);		
		
		if ( is_array( $empty_line_killer))
			foreach($empty_line_killer as $key => $addy_line)
				if ( isset($addy_line))
					if ( trim( $addy_line) == "")
						unset($empty_line_killer[$key]);
					

		if ( is_array( $empty_line_killer ) ) {
			$display_address  = implode( '<br />', $empty_line_killer );
		}

	
		$property['display_address'] = $display_address;	
		
		// Don't return if result matches the 
		if ( str_replace( array( " ", ",", "\n" ), '', $display_address_code ) == str_replace( array( " ", ",", "\n" ), '', $display_address ) ) {
			$property['display_address'] = '';
		}
			
		return $property;
	}
	
	/**	
	 * Demonstrates how to add dollar signs before all prices and deposits
	 *
	 * @since 1.0
	 */	 
	function wp_imovel_add_dollar_sign( $content ) {
		global $wp_imovel;		
		if (  ! is_numeric( $content ) ) { 
			return $content;
		}		
		$currency_symbol = ( ! empty( $wp_imovel['configuration']['currency_symbol']) ? $wp_imovel['configuration']['currency_symbol'] : 'R$' );
		return $currency_symbol . @number_format($content, 2);

	}	

	/**
	 * Display latitude and longitude on listing edit page below address field
	 *
	 * Echos html content to be displayed after location attribute on property edit page
	 *
	 * @since 1.0
	 * @uses WP_IMOVEL_F::get_coordinates() Creates an array from string $args.
	 * @param string $listing_id Listing ID must be passed
	 */
		function wp_imovel_show_coords( $listing_id = false ) {
			if ( ! $listing_id ) {
				return false;
			}
			// If latitude and logitude meta isn't set, returns false
			$coords = WP_IMOVEL_F::get_coordinates($listing_id);
			if ( $coords ) {
				_e( '<span class="description">Address was validated by Google Maps.</span>', 'wp-imovel' );
			} else {
				_e( '<span class="description">Address has not yet been validated, should be formatted as: street, city, state, postal code, country. Locations are validated through Google Maps.</span>', 'wp-imovel' );
			}

		}
	/**
	 * Add week/month dropdown after price
	 *
	 * Displays a hidden field on property edit page setting the property price frequency
	 *
	 * @since 1.0
 	 * @param string $listing_id Listing ID must be passed
	 */
	function wp_imovel_show_week_month_selection( $listing_id = false ) {
		if ( ! $listing_id ) {
			return false;
		}
		echo '<input type="hidden" name="wp_imovel_data[meta][price_per]" value="month" />';
		/*
		Uncomment the following to allow the editor to select if price is monthly and weekly.
		Or add your own frequencies.

			<select id="wp_imovel_meta_price_per" name="wp_imovel_data[meta][price_per]">
				<option value=""></option>
				<option <?php if ( get_post_meta($listing_id, 'price_per', true) == 'week' ) echo "SELECTED"; ?> value="week">week</option>
				<option <?php if ( get_post_meta($listing_id, 'price_per', true) == 'month' ) echo "SELECTED"; ?> value="month">month</option>
			</select>.
		*/
	}
?>