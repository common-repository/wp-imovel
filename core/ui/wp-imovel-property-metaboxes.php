<?php
class Walker_Category_Radiolist extends Walker {
	var $tree_type = 'category';
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

	function start_lvl(&$output, $depth, $args) {
		$indent = str_repeat( "\t", $depth );
		$output .= $indent . '<ul class="children">' . "\n";
	}

	function end_lvl(&$output, $depth, $args) {
		$indent = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n</fieldset>";
	}

	function start_el(&$output, $category, $depth, $args) {
		extract($args);
		if ( empty( $taxonomy ) ) {
			$taxonomy = 'category';
		}
		$output .= "\n<li id='{$taxonomy}-{$category->term_id}'>";
		if ( $has_children ) {
			$output .= '<fieldset class="wp-imovel-status widget"><legend>' . esc_html( apply_filters('the_category', $category->name )) .'</legend>';
		} else {
			if ( $taxonomy == 'category' ) {
				$field_name = 'post_category';
			}else {
				$field_name = $taxonomy;
			}
			if ( 0 == $category->parent ) {
				$output .= '<fieldset class="wp-imovel-status widget"><legend>' . esc_html( apply_filters('the_category', $category->name )) .'</legend>';
				$output .=  __( 'Looks like this setting has no options. Please add some options or remove the setting.', 'wp-imovel' );
			} else {
				$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
				$output .= '<label class="selectit"><input value="' . $category->term_id . '" type="radio" name="input-is-parent-' . $field_name . '[' . $category->parent . ']" id="in-'.$taxonomy.'-' . $category->term_id . '"' .  ( in_array( $category->term_id, $selected_cats ) ? ' checked="checked"' : '' ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';
			}
		}			
	}

	function end_el(&$output, $category, $depth, $args) {
		$output .= "</li>\n";
	}
}
function wp_terms_radiolist( $post_id = 0, $args = array() ) { 	
	$defaults = array(
		'descendants_and_self' => 0,
		'selected_cats' => false,
		'popular_cats' => false,
		'walker' => null,
		'taxonomy' => 'category',
		'checked_ontop' => false
	);
	extract( wp_parse_args($args, $defaults), EXTR_SKIP );

	if ( empty( $walker ) || !is_a( $walker, 'Walker' ) ) {
		$walker = new Walker_Category_Radiolist;
	}
	$descendants_and_self = (int) $descendants_and_self;

	$args = array('taxonomy' => $taxonomy);

	$tax = get_taxonomy($taxonomy);
	$args['disabled'] = !current_user_can( $tax->cap->assign_terms );

	if ( is_array( $selected_cats ) )
		$args['selected_cats'] = $selected_cats;
	elseif ( $post_id )
		$args['selected_cats'] = wp_get_object_terms( $post_id, $taxonomy, array_merge( $args, array('fields' => 'ids') ) );
	else 
		$args['selected_cats'] = array();

	if ( is_array( $popular_cats ) )
		$args['popular_cats'] = $popular_cats;
	else
		$args['popular_cats'] = get_terms( $taxonomy, array( 'fields' => 'ids', 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false ) );

	if ( $descendants_and_self ) {
		$categories = (array) get_terms( $taxonomy, array( 'child_of' => $descendants_and_self, 'hierarchical' => true, 'hide_empty' => false ) );
		$self = get_term( $descendants_and_self, $taxonomy );
		array_unshift( $categories, $self );
	} else {
		$categories = (array) get_terms($taxonomy, array('get' => 'all'));
	}

	if ( $checked_ontop ) {
		// Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
		$checked_categories = array();
		$keys = array_keys( $categories );

		foreach( $keys as $k ) {
			if ( in_array( $categories[$k]->term_id, $args['selected_cats'] ) ) {
				$checked_categories[] = $categories[$k];
				unset( $categories[$k] );
			}
		}

		// Put checked cats on top
		echo call_user_func_array( array( &$walker, 'walk' ), array( $checked_categories, 0, $args ) );
	}
	// Then the rest of them
	echo call_user_func_array( array( &$walker, 'walk' ), array( $categories, 0, $args ) );
}		

class WP_IMOVEL_UI {

/*
	Inserts content into the "Publish" metabox on property pages
*/
	function wp_imovel_property_submitbox_misc_actions() {
		global $post, $action;
		if ( $post->post_type == 'wp_imovel_property' ) {
			$featured = ( 'true' == get_post_meta( $post->ID, 'featured', true ) );
			$disable_slideshow = ( 'true' == get_post_meta( $post->ID, 'disable_slideshow', true ) );
			$sold = ( 'sold' == $post->post_status );
?>
<div class="misc-pub-section ">
	<ul>
<?php 
/*
		<li>
<?php 
			_e( 'Menu Sort Order:', 'wp-imovel' );  
			echo UD_UI::input("name=menu_order&special=size=4", $post->menu_order ); 
?>
		</li>
<?php 
*/
//			if ( current_user_can( 'manage_options' ) ) { 
?>
		<li>
			<?php 
				$display_property_text = __( 'Display property in featured listing.', 'wp-imovel' ); 
				echo UD_UI::checkbox( 'name=wp_imovel_data[meta][featured]&label=' . $display_property_text, $featured ); 
			?>
		</li>
		<li>
			<?php 
				$display_property_text = __( 'Mark property as sold?', 'wp-imovel' ); 
				echo UD_UI::checkbox( 'name=wp_imovel_data[meta][sold]&label=' . $display_property_text, $sold ); 
			?>
		</li>
<?php
//			}
?>
		<?php do_action( 'wp_imovel_publish_box_options' ); ?>
	</ul>
</div>
<?php
		}
		return false;
	}

	function metabox_public_meta( $object ) {
		global $wp_imovel, $wpdb;
		$property = WP_IMOVEL_F::get_property( $object->ID );
		$property_stats = $wp_imovel['property_public_meta'];
		
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		// Done with PHP but in case of page reloads
		wp_imovel_toggle_attributes();
		/*
			Display prefill values.
			Hide "Show common values" link.
			Display "Cancel" button
		*/
		jQuery(".wp_imovel_show_prefill_values").click(function() {
			jQuery(this).hide();
			var parent_cell = jQuery(this).parents( '.wp_imovel_attribute_cell' );
			jQuery(this).parent().children( '.wp_imovel_prefill_attribute' ).show();
			jQuery( '.wp_imovel_show_prefill_values_cancel', parent_cell).show();
		});			
		/*
			Cancel displaying prefill values.
			Hide "Cancel" button
			Hide all pre-filled values
			Show "Show common values" link.
		*/			
		jQuery(".wp_imovel_show_prefill_values_cancel").click(function() {	
			jQuery(this).hide();
			var parent_cell = jQuery(this).parents( '.wp_imovel_attribute_cell' );
			jQuery( '.wp_imovel_prefill_attribute', parent_cell).hide();
			jQuery( '.wp_imovel_show_prefill_values', parent_cell).show();
			
		});
		
		jQuery(".wp_imovel_prefill_attribute").click(function() {
			var value = jQuery(this).text();
			var parent_cell = jQuery(this).parents( '.wp_imovel_attribute_cell' );
			jQuery( '.text-input', parent_cell).val(value);
			jQuery( '.wp_imovel_prefill_attribute', parent_cell).hide();
			jQuery( '.wp_imovel_show_prefill_values_cancel', parent_cell).hide();
			jQuery( '.wp_imovel_show_prefill_values', parent_cell).show();
		});
		 
		 
		// Setup toggling settings
		jQuery("#wp_imovel_meta_property_type").change(function() {			
			wp_imovel_toggle_attributes();				
		});
	
		function wp_imovel_toggle_attributes() {
<?php 
if ( count( $wp_imovel['hidden_attributes'] ) < 1 ) {
	echo 'return;';
} else {
	?>
			var property_type = jQuery("#wp_imovel_meta_property_type option:selected").val();
			if ( property_type == "" ) {
				return;
			}
			// Show all fields
			jQuery(".wp_imovel_attribute_row").removeClass( 'disabled_row' );
	<?php 
	if ( is_array( $wp_imovel['hidden_attributes'] ) ) {
?>
			switch( property_type ) {
<?php
		foreach ( $wp_imovel['hidden_attributes'] as $property_type => $hidden_values ) {
			if ( is_array( $hidden_values ) ) {
?>
				case "<?php echo $property_type; ?>":
<?php 
				foreach ( $hidden_values as $value ) {
?>
					jQuery(".wp_imovel_attribute_row_<?php echo $value; ?>").addClass( 'disabled_row' );
<?php 
				}
?>
				break;
<?php
			}
		}
?>
			}
<?php		
	}
}
?>
		}
});
</script>
<table class="widefat">
	<tr>
		<th>
			<?php _e( 'Property Type', 'wp-imovel' ); ?>
		</th>
		<td> <?php WP_IMOVEL_F::draw_property_type_dropdown('name=wp_imovel_data[meta][property_type]&selected=' . $property['property_type'] ); ?> </td>
	</tr>
	<tr>
		<th>
			<?php _e( 'Price Range', 'wp-imovel' ); ?>
		</th>
		<td> <?php WP_IMOVEL_F::draw_price_range_dropdown('name=wp_imovel_data[meta][price_range]&selected=' . $property['price_range']); ?> </td>
	</tr>
	<?php
		foreach ( $property_stats as $slug => $label ) {
			if ( is_array( $wp_imovel['hidden_attributes'][$property['property_type']] ) && in_array( 'parent', $wp_imovel['hidden_attributes'][$property['property_type']] ) ) {
				$disabled = 'disabled_row'; 
			} else {
				$disabled =  '';
			}
	 ?>
	<tr class="wp_imovel_attribute_row wp_imovel_attribute_row_<?php echo $slug; ?> <?php echo $disabled ?>">
		<th><?php echo $label; ?></th>
		<td class="wp_imovel_attribute_cell"> <span class="disabled_message"><?php echo sprintf(__( 'Editing %s is disabled, it may be inherited.', 'wp-imovel' ), $label ); ?></span>
			<?php
			
			$formVar =  '<input type="text" id="wp_imovel_meta_' . $slug . '" name="wp_imovel_data[meta][' . $slug . ']"  class="text-input" value="' . get_post_meta( $object->ID, $slug, true ) . '" />';
			echo apply_filters( 'wp_imovel_property_public_meta_input_' . $slug, $formVar, $slug, $object );
			
			// Get pre-filed meta data
			$common_values = WP_IMOVEL_F::get_all_attribute_values( $slug ); 
			if ( $common_values ) {
			 ?>
			<span class="wp_imovel_prefill_values clearfix"> <span class="wp_imovel_show_prefill_values wp_imovel_link">
			<?php _e( 'Show common values.', 'wp-imovel' ) ?>
			</span>
			<?php 
				foreach ( $common_values as $meta ) { 
			 ?>
			<span class="wp_imovel_prefill_attribute"><?php echo $meta; ?></span>
			<?php 
				}
			 ?>
			<span class="wp_imovel_show_prefill_values_cancel wp_imovel_subtle_link hidden">
			<?php _e( 'Cancel.', 'wp-imovel' ) ?>
			</span> </span>
			<?php
			} 
			if ( ! empty( $wp_imovel['descriptions'][$slug] ) ) {
			?>
			<span class="description"><?php echo $wp_imovel['descriptions'][$slug]; ?></span>
			<?php 
			}
			//do_action( 'wp_imovel_ui_after_attribute_' . $slug, $object->ID ); 
			?>
		</td>
	</tr>
	<?php
		}
	?>
</table>
<?php
	}

	function metabox_private_meta( $object ) {
		global $wp_imovel, $wpdb;
		$property = WP_IMOVEL_F::get_property( $object->ID );
		$property_meta = $wp_imovel['property_private_meta'];
		
?>
<table class="widefat">
	<?php
		foreach ( $property_meta as $slug => $label ) {
			if ( is_array( $wp_imovel['hidden_attributes'][$property['property_type']] ) && in_array( 'parent', $wp_imovel['hidden_attributes'][$property['property_type']] ) ) {
				$disabled = 'disabled_row'; 
			} else {
				$disabled =  '';
			}
	 ?>
	<tr class="wp_imovel_attribute_row wp_imovel_attribute_row_<?php echo $slug; ?> <?php echo $disabled ?>">
		<th><?php echo $label; ?></th>
		<td class="wp_imovel_attribute_cell"> <span class="disabled_message"><?php echo sprintf(__( 'Editing %s is disabled, it may be inherited.', 'wp-imovel' ), $label); ?></span>
			<?php
/*
			$formVar =  '<input type="text" id="wp_imovel_meta_' . $slug . '" name="wp_imovel_data[meta][' . $slug . ']"  class="text-input" value="' . get_post_meta( $object->ID, $slug, true ) . '" />';
			echo apply_filters( 'wp_imovel_property_private_meta_input_' . $slug, $formVar, $slug, $object );
/*/
?>
			<textarea name="wp_imovel_data[meta][<?php echo $slug; ?>]" class="text-input"><?php echo get_post_meta($object->ID, $slug, true); ?></textarea>
			<?php 
/**/
			// Get pre-filed meta data
			$common_values = WP_IMOVEL_F::get_all_attribute_values( $slug ); 
			if ( $common_values ) {
			 ?>
			<span class="wp_imovel_prefill_values clearfix"> <span class="wp_imovel_show_prefill_values wp_imovel_link">
			<?php _e( 'Show common values.', 'wp-imovel' ) ?>
			</span>
			<?php 
				foreach ( $common_values as $meta ) { 
			 ?>
			<span class="wp_imovel_prefill_attribute"><?php echo $meta; ?></span>
			<?php 
				}
			 ?>
			<span class="wp_imovel_show_prefill_values_cancel wp_imovel_subtle_link hidden">
			<?php _e( 'Cancel.', 'wp-imovel' ) ?>
			</span> </span>
			<?php
			}

			if ( ! empty( $wp_imovel['descriptions'][$slug] ) ) {  
?>
			<span class="description"><?php echo $wp_imovel['descriptions'][$slug]; ?></span>
			<?php 
			} 
			?>
		</td>
	</tr>
	<?php
		}
?>
</table>
<?php
	}

	function metabox_status( $object ) {
		global $wp_imovel;
		$property = WP_IMOVEL_F::get_property( $object->ID );
		$taxonomy_ids = get_terms( 'wp_imovel_property_status', array( 'hide_empty' => false, 'fields' => 'ids', 'parent' => 0 ) );
?>
<div class="categorydiv tabs-panel">
	<?php
		foreach ( $taxonomy_ids as $taxonomy_id ) {
?>
	<ul id="wp_imovel_property_statuschecklist" class="list:wp_imovel_property_status categorychecklist form-no-clear">
		<?php wp_terms_radiolist( $property['ID'], array( 'descendants_and_self' => $taxonomy_id, 'taxonomy' => 'wp_imovel_property_status' ) ); ?>
	</ul>
	<?php
		}
/*
		$terms = array();
		$taxonomies = get_object_taxonomies( $object );
		foreach ( $taxonomies as $taxonomy ) {
			$taxonomy_ids = get_terms( $taxonomy, array( 'hide_empty' => false, 'fields' => 'ids', 'parent' => 0 ) ) ;
			foreach ( $taxonomy_ids as $taxonomy_id ) {
?>
			<ul id="<?php echo $taxonomy; ?>checklist" class="list:<?php echo $taxonomy?> categorychecklist form-no-clear">
				<?php wp_terms_radiolist( $post->ID, array( 'descendants_and_self' => $taxonomy_id, 'taxonomy' => $taxonomy ) ); ?>
				<?php //wp_terms_radiolist( $post->ID, array( 'taxonomy' => $taxonomy ) ); ?>
			</ul>
<?php
			
			}
?>
<?php

/*
			echo '<pre>terms from ' . $taxonomy . '
		'; print_r(get_terms( $taxonomy, array( 'hide_empty' => false, 'fields' => 'names', 'parent' => 0 ) )); echo '</pre>';
//			$terms = array_merge( $terms, get_terms( $taxonomy, array( 'hide_empty' => false, 'fields' => 'names' ) ) );

		}
/*
		foreach ( $taxonomies as $taxonomy ) {
			wp_dropdown_categories( array(
				'taxonomy' => $taxonomy, 'hide_empty' => 0, 'name' => 'new'.$taxonomy.'_parent', 'orderby' => 'name',
				'hierarchical' => 1, 'show_option_none' => '&mdash; '.$taxonomy->labels->parent_item.' &mdash;'
			) );
		}
		
/*
		echo '<pre>all terms
		'; print_r($terms); echo '</pre>';
		
		$terms =  wp_get_object_terms( $object->ID, $taxonomies );
		echo '<pre>terms
		'; print_r($terms); echo '</pre>';
*/


?>
</div>
<?php
	}
	
	function metabox_image_privacy( $object ) {
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => $object->ID
		);
		$attachments = get_posts( $args );
		if ( ! $attachments ) {
			_e( 'No property images yet.', 'wp-imovel' );
		} else {
			foreach ( $attachments as $attachment ) {
				$id = $attachment->ID;
				if ( substr( $attachment->post_mime_type, 0, 5 ) != 'image' )  {
					$is_image = false;
					$class = ' wp-imovel-private-image';
				} else {
					$is_image = true;
					$meta = wp_get_attachment_metadata( $id );
					if ( ! isset( $meta['wp_imovel_private'] ) || empty( $meta['wp_imovel_private'] ) || $meta['wp_imovel_private'] != 'private' ) {
						$is_private = false;
						$class = '';
					} else {
						$is_private = true;
						$class = ' wp-imovel-private-image';
					}
				}
				echo '
				<div class="center">
					<fieldset class="wp-imovel-image-privacy widget' . $class .'" id="image_privacy_metabox_' . $id . '">
						<legend>&nbsp;' . esc_html( apply_filters('the_title', $attachment->post_title ) ) . '&nbsp;</legend>';
				if ( ! $is_image ) {
					echo '<img src="' . includes_url() . '/images/crystal/document.png" width="46" height="60" /><br />';
					echo wp_get_attachment_link( $id );
				} else {
					echo wp_get_attachment_image( $id ) . '<br />';
					if ( ! $is_private ) {
						echo '<img id="wp_imovel_image_privacy_icon_' . $id . '" src="' . WP_IMOVEL_URL . '/images/yes.png'  . '" alt="" /><br />';
						echo '<input type="button" id="wp_imovel_image_privacy_' . $id . '" nonce="' . wp_create_nonce( 'wp_imovel_image_privacy_' . $id ) . '" value="' . __('Make private', 'wp-imovel') . '" class="wp_imovel_toggle_image_privacy" />';
					} else {
						echo '<img id="wp_imovel_image_privacy_icon_' . $id . '" src="' . WP_IMOVEL_URL . '/images/no.png'  . '" alt="" /><br />';
						echo '<input type="button" id="wp_imovel_image_privacy_' . $id . '" nonce="' . wp_create_nonce( 'wp_imovel_image_privacy_' . $id ) . '" value="' . __('Make public', 'wp-imovel')  . '" class="wp_imovel_toggle_image_privacy wp_imovel_image_is_private" />';
					}
					echo '
							<img class="waiting_' . $id . '" src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" alt="" style="display:none" />';
				} 	
				echo '
					</fieldset>
				</div>';
			}
		}
	}
	
	function metabox_overview( $object ) {
		global $wp_imovel, $wpdb;
		$num_properties_per_type = WP_IMOVEL_F::get_property_type_count();


		echo "\n\t".'<div class="table table_content">';
		echo "\n\t".'<p class="sub">' . __('Content') . '</p>'."\n\t".'<table>';

		// Break down by property type
			foreach ( $num_properties_per_type as $slug => $usage ) {
				if ( 0 == $usage[0] ) {
					continue;
				}
				$num = number_format_i18n( $usage[0] );
				$text = $usage[1];
				if ( current_user_can( 'edit_posts' ) ) {
					$num  = '<a href="edit.php?post_type=wp_imovel_property&s=' . $slug . '">' . $num . '</a>';
					$text = '<a href="edit.php?post_type=wp_imovel_property&s=' . $slug . '">' . $text . '</a>';
				}
				echo "\n\t".'<tr class="first">';
				echo '<td class="t posts">' . $text . '</td>';
				echo '<td class="first b b-posts">' . $num . '</td>';
				echo '</tr>';
/*				echo '
					<tr>
						<td class="b b-posts">&nbsp;</td>
						<td class="t posts">' . $usage[1] . ' ' . $usage[0] . ' ' .  _n( 'unit', 'units', intval( $usage[0] ), 'wp-imovel' ). '<br />' 
						. WP_IMOVEL_F::get_property_type_price_ranges( $slug ) . '</td>
					</tr>';
*/
			}
		echo "\n\t</table>\n\t</div>";
		echo "\n\t".'<div class="table table_discussion">';
		echo "\n\t".'<p class="sub">' . __('Details') . '</p>'."\n\t".'<table>';
		
		if ( array_key_exists( 'tem-placa', $wp_imovel['property_private_meta'] ) ) {
			$text = $wp_imovel['property_private_meta']['tem-placa'];
			$num = 	WP_IMOVEL_F::get_attribute_count( $text );
			if ( current_user_can( 'edit_posts' ) ) {
				$slug = urlencode( $text );
				$num  = '<a href="edit.php?post_type=wp_imovel_property&s=' . $slug . '">' . $num . '</a>';
				$text = '<a href="edit.php?post_type=wp_imovel_property&s=' . $slug . '">' . $text . '</a>';
			}
			echo "\n\t".'<tr class="first">';
			echo '<td class="t posts">' . $text . '</td>';
			echo '<td class="first b b-posts">' . $num . '</td>';
			echo '</tr>';
		}
		echo "\n\t</table>\n\t</div>";
		echo '<br class="clear" />';
		
/*
		echo "\n\t".'<div class="any">';
		echo "\n\t".'<p class="sub">' . __('Search') . '</p>'."\n\t".'<table>';
			echo "\n\t".'<tr class="first">';
			echo '<td class="first posts">' . __( 'Price Range', 'wp-imovel' );
			WP_IMOVEL_F::draw_price_range_dropdown();
			echo '</td>';
			echo "</tr>";
		
		$searchables = $wp_imovel['searchable_metadata'];
		foreach ( $searchables as $searchable ) {
			echo "\n\t".'<tr class="first">';
			echo '<td class="first posts">' . $searchable;
			$search_values = WP_IMOVEL_F::draw_search_options( $searchable );
//			echo '<pre>'; print_r($search_values); echo '</pre>';
?>			<select id="wp_imovel_search_input_field_<?php echo $searchable; ?>" class="wp_imovel_search_select_field wp_imovel_search_select_field_<?php echo $searchable; ?>" name="wp_imovel_search[specific][<?php echo $searchable; ?>]" >
				<option value="">
				<?php _e( 'Any', 'wp-imovel' ) ?>
				</option>
				<?php 	
						$search_values_and_keys = $search_values;
						foreach ( $search_values_and_keys as $key => $value ) { 
							if ( empty( $value ) ) {
								$value = '-';
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
			echo '</td>';
			echo "</tr>";
		}
		echo "\n\t</table>\n\t</div>";
*/
	}
}
?>