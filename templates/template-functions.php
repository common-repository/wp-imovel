<?php
/**
 * Functions to be used in templates.  Overrided by anything in template functions.php
 *
 * Copyright 2010 Andy Potanin <andy.potanin@twincitiestech.com>
 *
 * @package WP-Imovel
*/
if ( ! function_exists( 'prepare_property_for_display' ) ) {
	/**
	 * Runs all filters through property variables
	 *
	 * @since 1.4
	 *
 	 */
	 function prepare_property_for_display( $property ) {
		if ( empty( $property ) ) {
			return false;
		}
		foreach( $property as $meta_key => $value ) {
 			$property[$meta_key] = apply_filters( 'wp_imovel_stat_filter_' . $meta_key, $value );
		}
		// Go through children properties
		if ( is_array( $property['children'] ) ) {
			foreach ( $property['children'] as $child => $child_data ) {
				$property['children'][$child] = prepare_property_for_display( $child_data );
			}
		}
		return $property;	
	}
}	
if ( ! function_exists( 'property_slideshow' ) ) {
	/**
	 * Returns property slideshow images, or single image if plugin not installed
	 *
	 * @since 1.0
	 *
 	 */
	 function property_slideshow( $args = "" ) {
		global $wp_imovel, $post;
		$defaults = array( 'force_single' => false, 'return' => false );
		$args = wp_parse_args( $args, $defaults );
		if ( $wp_imovel['configuration']['property_overview']['display_slideshow'] == 'false' ) {
			return false;
		}
		ob_start();
			// Display slideshow if premium plugin exists and the property isn't set to hide slideshow
			if ( $wp_imovel['plugins']['slideshow']['status'] == 'enabled' 
				&& ! $post->disable_slideshow ) {
 				wp_imovel_slideshow::display_property_slideshow( wp_imovel_slideshow::get_property_slideshow_images( $post->ID ) );
			} else {
				// Get slideshow image type for featured image
				if ( ! empty( $post->slideshow ) ) {
					echo '<a href="' . $post->featured_image_url . '" class="fancybox_image" rel="slideshow">';
					echo '<img src="' . $post->slideshow . '" alt="' . $post->featured_image_title . '" />';
					echo '</a>';
				} else {
					echo '<h1>no slideshow</h1>';
				}
			}
		$content = ob_get_contents();
		ob_end_clean();
		if ( empty( $content ) ) {
			return false;
		}
		if ( $return ) {
			return $content;
		}
		echo $content;
	}
}
/*
	Extends get_post by dumping all metadata into array
*/
if ( ! function_exists( 'get_property' ) ) {
	function get_property( $id, $args = "" ) {
		if (  $id ) {
			return WP_IMOVEL_F::get_property( $id, $args );
		}
	}
}
if ( ! function_exists( 'the_tagline' ) ) {
 	function the_tagline($before = '', $after = '', $echo = true ) {
		global $post;
		$content = $post->tagline;
		if ( strlen($content) == 0 ) {
			return false;
		}
		$content = $before . $content . $after;
		if ( $echo ) {
			echo $content;
		} else {
			return $content;
		}
	}
}
if ( ! function_exists( 'get_features' ) ) {
	function get_features($args = '' ) {
		global $post;
		$defaults = array( 'type' => 'wp_imovel_property_feature', 'format' => 'comma', 'links' => true, 'parent' => false );
		$args = wp_parse_args( $args, $defaults );
		$features = get_the_terms( $post->ID, $args['type'] );
		if ( $features ) {
			if (  $args['format'] == 'count' ) {
				return ( count( $features ) > 0 ? count( $features ) : false );
			}
			$features_html = array();
			foreach ( $features as $feature ) {
				if ( $args['parent'] ) {
					$parent_term = get_term_by( 'id', $feature->parent, $args['type'] );
					$name = $parent_term->name .  ': ';
				} else {
					$name = '';
				}
				$name = $name . $feature->name;

				if ( $links ) {
					array_push( $features_html, '<a href="' . get_term_link( $feature->slug, $args['type'] ) . '">' . $name . '</a>' );
				} else {
					array_push( $features_html, $name );
				}
			}
			if ( $args['format'] == 'list' ) {
				echo '<li>' . implode( $features_html, '</li><li>' ) . '</li>';
			}
			if ( $args['format'] == 'comma' ) {
				echo implode( $features_html, ', ' );
			}
			if ( $args['format'] == 'array' ) {
				return $features_html;
			}
		}
	}
}
if ( ! function_exists( 'draw_stats' ) ) {
	function draw_stats( $args = false, $meta = 'property_public_meta' ) {
		global $wp_imovel, $post;
		$defaults = array( );
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		$stats = WP_IMOVEL_F::get_stat_values_and_labels( $post, $args, $meta );
		if ( ! $stats ) {
			return false;
		}
		foreach ( $stats as $label => $value ) {
			if ( empty( $value ) ) {
				return false;
			}			
			$labels_to_keys = array_flip( $wp_imovel[$meta] );
			$tag = $labels_to_keys[$label];
?>
<dl>
	<dt class="wp_imovel_stat_dt_<?php echo $tag; ?>"><?php echo $label; ?></dt>
	<dd class="wp_imovel_stat_dd_<?php echo $tag; ?>"><?php echo apply_filters( 'wp_imovel_stat_filter_' . $tag, $value, $post); ?>&nbsp;</dd>
</dl>
<?php
		}
	}
}	
if ( ! function_exists( 'draw_featured_properties' ) ) {
	function draw_featured_properties() {
		$featured_array = WP_IMOVEL_F::get_properties("featured=true&property_type=all");
	    if ( is_array( $featured_array ) ) {
			foreach ( $featured_array as $featured ) {
				unset($this_property);
				$this_property = WP_IMOVEL_F::get_property( $featured->ID );
?>
<div class="apartment_entry clearfix" style="clear:both;margin-bottom:15px;"> <a href="<?php echo $this_property['permalink']; ?>"> <img src="<?php echo $this_property['sidebar_gallery_thumb'];?>" alt="<?php echo sprintf(__( '%s at %s for %s', 'wp-imovel' ), $this_property['post_title'], $this_property['location'], $this_property['price']); ?>" /> </a>
	<ul class="sidebar_properties">
		<li><span>
			<?php _e( 'Price:', 'wp-imovel' ); ?>
			</span> <?php echo  wp_imovel_add_dollar_sign( $this_property['price'] )  ?></li>
		<li><span>
			<?php _e( 'Bed(s):', 'wp-imovel' ); ?>
			</span> <?php echo  $this_property['bedrooms']; ?></li>
		<li><span>
			<?php _e( 'Bath(s):', 'wp-imovel' ); ?>
			</span> <?php echo  $this_property['bathrooms']; ?></li>
		<li><span>
			<?php _e( 'Square Ft:', 'wp-imovel' ); ?>
			</span> <?php echo  $this_property['area']; ?></li>
	</ul>
</div>
<?php
			}
		}
	}
}
	/**
	 * Draws search form
	 *
	 *
	 * @return array|$wp_imovel
	 * @since 0.57
	 *
 	 */
if ( ! function_exists( 'draw_property_search_form' ) ) {
	function draw_property_search_form( $search_attributes = false, $searchable_property_types = false, $per_page = false ) {
		global $wp_imovel;
 		if ( ! $search_attributes ) {
			return false;
		}
		if ( is_array( $searchable_property_types ) ) { 
?>
<form action="<?php echo UD_F::base_url($wp_imovel['configuration']['base_slug']); ?>" method="post">
	<?php 
			foreach ( $searchable_property_types as $searchable_property_type ) {
 ?>
	<input type="hidden" name="wp_imovel_search[property_type][]" value="<?php echo $searchable_property_type; ?>" />
	<?php
			}
?>
	<ul class="wp_imovel_search_elements">
		<li class="seach_attribute_id">
			<label class="wp_imovel_search_label wp_imovel_search_label_id" for="wp_imovel_search_input_field_id">
			<?php _e( 'Property code', 'wp-imovel' ) ?>
			:</label>
			<input id="wp_imovel_search_input_field_id" class="wp_imovel_search_input_field_id" name="wp_imovel_search[specific][id]" type="text" value="<?php echo $_REQUEST['wp_imovel_search']['specific']['id'] ?>"  />
		</li>
		<?php 
		
			if ( is_array( $search_attributes ) ) {
				$search_values = WP_IMOVEL_F::get_search_values( $search_attributes, $searchable_property_types );
//echo '<pre>'; print_r( $search_attributes ); echo '</pre>'; 
//echo '<pre>'; print_r( $searchable_property_types ); echo '</pre>'; 
//echo '<pre>'; print_r( $search_values ); echo '</pre>'; 
				foreach( $search_attributes as $search_attribute ) { 
					// Don't display search attributes that have no values
					if ( ! isset( $search_values[$search_attribute] ) ) {
						continue;
					}
					switch ( $search_attribute ) {
						case 'price_range':
							$search_title  = __( 'Price Range', 'wp-imovel' );
							$search_values['price_range'] = WP_IMOVEL_F::get_price_range_strings( $search_values['price_range'] ); 
						break;
						case 'property_type':
							$search_title = __ ( 'Property Type', 'wp-imovel' );
							$search_values['property_type'] = WP_IMOVEL_F::get_property_type_strings( $searchable_property_types ); 
						break;
						default:
							$search_title = (empty( $wp_imovel['property_public_meta'][$search_attribute]) ? ucwords($search_attribute) : $wp_imovel['property_public_meta'][$search_attribute]);
							ksort( $search_values[$search_attribute] );
					}
				?>
		<li class="seach_attribute_<?php echo $search_attribute; ?>">
			<label class="wp_imovel_search_label wp_imovel_search_label_<?php echo $search_attribute; ?>" for="wp_imovel_search_input_field_<?php echo $search_attribute; ?>"><?php echo $search_title ?>:</label>
			<?php 
					// Determine if attribute is a numeric range
					if ( false && WP_IMOVEL_F::is_numeric_range( $search_values[$search_attribute] ) ) { 
			?>
			<input  id="wp_imovel_search_input_field_<?php echo $search_attribute; ?>"  class="wp_imovel_search_input_field_min wp_imovel_search_input_field_<?php echo $search_attribute; ?>" type="text" name="wp_imovel_search[<?php echo $search_attribute; ?>][min]" value="<?php echo $_REQUEST['wp_imovel_search'][$search_attribute]['min']; ?>" />
			-
			<input class="wp_imovel_search_input_field_max wp_imovel_search_input_field_<?php echo $search_attribute; ?>"  type="text" name="wp_imovel_search[<?php echo $search_attribute; ?>][max]" value="<?php echo $_REQUEST['wp_imovel_search'][$search_attribute]['max']; ?>" />
			<?php
					} else { /* Not a numeric range */
						$selectedKeys = array( $_REQUEST['wp_imovel_search']['specific'] ); 
			?>
			<select id="wp_imovel_search_input_field_<?php echo $search_attribute; ?>" class="wp_imovel_search_select_field wp_imovel_search_select_field_<?php echo $search_attribute; ?>" name="wp_imovel_search[specific][<?php echo $search_attribute; ?>]" >
				<option value="">
				<?php _e( 'Any', 'wp-imovel' ) ?>
				</option>
				<?php 	
						$search_values_and_keys = $search_values[$search_attribute];
						foreach ( $search_values_and_keys as $key => $value ) { 
							if ( empty( $value ) ) {
								$value = $key;
							}
							if ( array_key_exists( $search_attribute, $selectedKeys ) && $key == $selectedKeys[$search_attribute] ) {
								$selected = ' selected="selected"';
							} else {
								$selected = '';
							}
							
				?>
				<option value="<?php echo $key; ?>"<?php echo $selected ?>> <?php echo apply_filters("wp_imovel_stat_filter_$search_attribute", $value); ?> </option>
				<?php 
						}
				 ?>
			</select>
			<?php
			 		} 
			 ?>
		</li>
		<?php
				}
		 	}
		  ?>
		<li>
			<input type="submit" class="wp_imovel_search_button submit" value="<?php _e( 'Search', 'wp-imovel' ) ?>" />
		</li>
	</ul>
<?php 
	if ( $per_page ) {
		echo '<input type="hidden" name="wp_imovel_search[pagi]" value="0--' . $per_page . '" />'; 
	}
?>
	
</form>
<?php
		}
	}
}		
?>
