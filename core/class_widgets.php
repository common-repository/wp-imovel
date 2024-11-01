<?php
/**
 Child Properties Widget
*/
 class wp_imovel_child_properties_widget extends WP_Widget {
    /** constructor */
    function wp_imovel_child_properties_widget() {
        parent::WP_Widget( false, $name = __( 'Child Properties', 'wp-imovel' ) );
    }
    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        global $post, $wp_imovel;
		if ( ! isset( $post->ID ) ) {
			return false;
		}
		extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        $image_type = $instance['image_type'];
        $stats = $instance['stats'];
        $address_format = $instance['address_format'];
		$attachments = get_pages('child_of=' . $post->ID . '&post_type=wp_imovel_property');
		// Bail out if no children
		if ( count( $attachments ) < 1 ) {
			return false;
		}
		echo $before_widget;
		echo '<div id="wp_imovel_child_properties_widget">';
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		foreach ( $attachments as $attached ) {
			$this_property = WP_IMOVEL_F::get_property( $attached->ID, 'return_object=true' );
			$image_sizes = WP_IMOVEL_F::get_image_sizes( $image_type );
?>

<div class="apartment_entry clearfix">
	<?php
			if ( ! empty( $this_property->images[$image_type] ) ) {
?>
	<a class="sidebar_property_thumbnail" href="<?php echo $this_property->permalink; ?>"><img width="<?php echo $image_sizes['width']; ?>" height="<?php echo $image_sizes['height']; ?>" src="<?php echo $this_property->images[$image_type];?>"     alt="<?php echo sprintf(__( '%s at %s for %s', 'wp-imovel' ), $this_property->post_title, $this_property->location, $this_property->price ); ?>" /></a>
	<?php
			}
			if ( is_array( $stats )) {
?>
	<ul class="sidebar_floorplan_status">
		<?php 
				foreach( $stats as $stat ) {
					$content = apply_filters( 'wp_imovel_stat_filter_' . $stat, $this_property->$stat, $this_property, $address_format );
					if ( empty( $content ) ) {
						continue;
					}
?>
		<li><span><?php echo $wp_imovel['property_public_meta'][$stat]; ?>:</span>
			<p><?php echo  $content;  ?></p>
		</li>
		<?php
				}				
?>
	</ul>
	<?php
			}
?>
</div>
<?php
				unset($this_property);
		}				
		echo '</div>';
		echo $after_widget;
    }
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        return $new_instance;
    }
    /** @see WP_Widget::form */
    function form( $instance ) {
		global $wp_imovel;
        $title = esc_attr( $instance['title'] );
        $address_format = esc_attr( $instance['address_format'] );
        $image_type = esc_attr( $instance['image_type'] );
		$property_stats = $instance['stats'];
		if ( empty( $address_format ) ) {
			$address_format = "[street_number] [street_name],\n[city], [state]";
		}
?>
<p>
	<?php __( 'The widget will not be displayed if the currently viewed property has no children.', 'wp-imovel' ); ?>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>">
	<?php _e( 'Title:', 'wp-imovel' ); ?>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
	</label>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'image_type' ); ?>">
	<?php _e( 'Image Size:', 'wp-imovel' ); ?>
	<?php WP_IMOVEL_F::draw_image_size_dropdown("name=" . $this->get_field_name( 'image_type' ) . "&selected=" . $image_type); ?>
	</label>
</p>
<p>
	<?php __( 'Select the stats you want to display', 'wp-imovel' ); ?>
</p>
<?php 
		foreach ( $wp_imovel['property_public_meta'] as $stat => $label ) {
?>
<label for="<?php echo $this->get_field_id( 'stats' ); ?>_<?php echo $stat; ?>">
<input id="<?php echo $this->get_field_id( 'stats' ); ?>_<?php echo $stat; ?>" name="<?php echo $this->get_field_name( 'stats' ); ?>[]" type="checkbox" value="<?php echo $stat; ?>"
					<?php 
			if ( is_array( $property_stats) && in_array( $stat, $property_stats)) {
				echo " checked ";
			}
?>>
<?php echo $label;?> </label>
<br />
<?php
		}
?>
<p>
	<label for="<?php echo $this->get_field_id( 'address_format' ); ?>">
	<?php _e( 'Address Format:', 'wp-imovel' ); ?>
	<textarea  style="width: 100%"  id="<?php echo $this->get_field_id( 'address_format' ); ?>" name="<?php echo $this->get_field_name( 'address_format' ); ?>"><?php echo $address_format; ?></textarea>
	</label>
</p>
<?php
    }
}
/**
 Featured Widget
 */
 class wp_imovel_featured_properties_widget extends WP_Widget {
    /** constructor */
    function wp_imovel_featured_properties_widget() {
        parent::WP_Widget( false, $name = __( 'Featured Properties', 'wp-imovel' ) );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        global  $wp_imovel;
		extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        $image_type = $instance['image_type'];
        $stats = $instance['stats'];
		$address_format = $instance['address_format'];
		if ( ! $image_type ) {
			$image_type == '';
		}
		if ( empty( $address_format ) ) {
			$address_format = "[street_number] [street_name],\n[city], [state]";		
		}
		$featured_properties = WP_IMOVEL_F::get_properties( "featured=true&property_type=all");
		// Bail out if no children
		if ( ! $featured_properties ) {
			echo '<!-- no featured properties found -->';
			return false;
		}
		echo $before_widget;
		echo '<div id="wp_imovel_featured_properties_widget">';
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		foreach ( $featured_properties as $featured ) {
			$this_property = WP_IMOVEL_F::get_property( $featured, 'return_object=true' );
  			$image_sizes = WP_IMOVEL_F::get_image_sizes( $image_type );
			if ( ! empty( $this_property->images[$image_type] ) ) {
?>
<div class="apartment_entry clearfix"  style="min-height: <?php echo $image_sizes['height']; ?>px;"> <a class="sidebar_property_thumbnail"  href="<?php echo $this_property->permalink; ?>"><img width="<?php echo $image_sizes['width'] ?>" height="<?php echo $image_sizes['height'] ?>" src="<?php echo $this_property->images[$image_type]?>" alt="<?php echo sprintf(__( '%s at %s for %s', 'wp-imovel' ), $this_property->post_title, $this_property->location, $this_property->price ); ?>" /></a>
	<?php
				if ( is_array( $stats ) && sizeof( $stats ) ) {
?>
	<ul class="sidebar_floorplan_status">
		<?php
				foreach ( $stats as $stat ) {
					$content =  apply_filters( 'wp_imovel_stat_filter_' . $stat, $this_property->$stat, $this_property, $address_format);
						if ( empty( $content ) ) {
							continue;
						}
?>
		<li><span><?php echo $wp_imovel['property_public_meta'][$stat]; ?>:</span>
			<p><?php echo $content;  ?></p>
		</li>
		<?php 
					}
?>
	</ul>
</div>
<?php
				}
			}
			unset( $this_property );
		}
		echo '</div>';
		echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update( $new_instance, $old_instance ) {
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
		global $wp_imovel;
        $title = esc_attr( $instance['title'] );
        $image_type = esc_attr( $instance['image_type'] );
		$property_stats = $instance['stats'];
        $address_format = esc_attr( $instance['address_format'] );
		if ( empty( $address_format ) ) {
			$address_format = "[street_number] [street_name],\n[city], [state]";
		}
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>">
	<?php _e( 'Title:', 'wp-imovel' ); ?>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
	</label>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'image_type' ); ?>">
	<?php _e( 'Image Size:', 'wp-imovel' ); ?>
	<?php WP_IMOVEL_F::draw_image_size_dropdown("name=" . $this->get_field_name( 'image_type' ) . "&selected=" . $image_type); ?>
	</label>
</p>
<?php
/*
<p>
	<?php _e( 'Select the stats you want to display', 'wp-imovel' ) ?>
</p>
<?php 
		foreach ( $wp_imovel['property_public_meta'] as $stat => $label ) {
			if ( is_array( $property_stats ) && in_array( $stat, $property_stats ) ) {
				$checked = ' checked="checked"';
			} else {
				$checked = '';
			}
			$field_id = $this->get_field_id( 'stats' ) . '_' . $stat; 
	 ?>
	<input id="<?php echo $field_id; ?>" name="<?php echo $this->get_field_name( 'stats' ); ?>[]" type="checkbox" value="<?php echo $stat; ?>" <?php echo $checked ?> />
	<label for="<?php echo $field_id; ?>"><?php echo $label;?></label>
	<br />
	<?php 
		}
*/
/*	
?>
<p>
	<label for="<?php echo $this->get_field_id( 'address_format' ); ?>">
	<?php _e( 'Address Format:', 'wp-imovel' ); ?>
	<textarea style="width: 100%" id="<?php echo $this->get_field_id( 'address_format' ); ?>" name="<?php echo $this->get_field_name( 'address_format' ); ?>"><?php echo $address_format; ?></textarea>
	</label>
</p>
<?php
*/
    }

}
/**
 Property Search Widget
 */
 class wp_imovel_search_properties_widget extends WP_Widget {
    /** constructor */
    function wp_imovel_search_properties_widget() {
        parent::WP_Widget(false, $name = __( 'Property Search', 'wp-imovel' ));
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        global  $wp_imovel;
		if ( ! function_exists( 'draw_property_search_form' ) ) {
			return false;
		}
		extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
 		$searchable_attributes = $instance['searchable_attributes'];
 		$searchable_property_types = $instance['searchable_property_types'];
        if ( isset( $instance['use_pagi'] ) && $instance['use_pagi'] == 'on' ) {
            $per_page = $instance['per_page'];
		} else {
			$per_page = false;
		}

		if ( ! is_array( $searchable_attributes ) ) {
			return false;
		}
		echo $before_widget;
		echo '<div id="wp_imovel_search_properties_widget">';
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		draw_property_search_form( $searchable_attributes, $searchable_property_types, $per_page );
		echo '</div>';
		echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update( $new_instance, $old_instance ) {
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form( $instance ) {
		global $wp_imovel;
        $title = esc_attr( $instance['title'] );
		$all_searchable_attributes = array_unique( $wp_imovel['searchable_attributes'] );
 		$searchable_attributes = (array) $instance['searchable_attributes'];
		$all_searchable_property_types = array_unique( $wp_imovel['searchable_property_types'] );
		$searchable_property_types = (array) $instance['searchable_property_types'];
        $use_pagi = $instance['use_pagi'];
        $per_page = $instance['per_page'];
		
//echo '<pre>$instance'; print_r($instance); echo '</pre>';
//echo '<pre>all_searchable_attributes - '; print_r($all_searchable_attributes); echo '</pre>';		
//echo '<pre>searchable_property_types - '; print_r($searchable_property_types); echo '</pre>';		
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>">
	<?php _e( 'Title:', 'wp-imovel' ); ?>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
	</label>
</p>
<fieldset class="widget"><legend>
	<?php _e( 'Pagination options for search results', 'wp-imovel' ); ?></legend>
<ul>
	<li>
		<label for="<?php echo $this->get_field_id('use_pagi'); ?>">
		<?php _e( 'Do you want to use pagination?', 'wp-imovel' ); ?>
		<input id="<?php echo $this->get_field_id('use_pagi'); ?>" name="<?php echo $this->get_field_name('use_pagi'); ?>" type="checkbox" value="on" <?php if ( $use_pagi == 'on' ) echo ' checked="checked"'; ?> />
		</label>
	</li>
	<li>
		<label for="<?php echo $this->get_field_id('per_page'); ?>">
		<?php _e( 'Items per page', 'wp-imovel' ); ?>
		<input id="<?php echo $this->get_field_id( 'per_page' ); ?>" name="<?php echo $this->get_field_name('per_page'); ?>" type="text" value="<?php echo $per_page; ?>" size="4" maxlength="3" />
		</label>
	</li>
</ul></fieldset>
<?php
		if ( is_array( $all_searchable_attributes ) ) {
?>
<p>
	<?php _e( 'Select the attributes you want to search.', 'wp-imovel' ); ?>
</p>
<p>
<ul>
	<?php
		$attribute = 'property_type';
		if ( in_array( $attribute, $searchable_attributes ) ) { 
			$checked = ' checked="checked"';
		} else {
			$checked = '';
		}
?>
	<li>
		<label for="<?php echo $this->get_field_id( 'searchable_attributes' ); ?>_property-type">
		<input id="<?php echo $this->get_field_id( 'searchable_attributes' ); ?>_property-type" name="<?php echo $this->get_field_name( 'searchable_attributes' ); ?>[]" type="checkbox" value="<?php echo $attribute; ?>" <?php echo $checked ?>  />
		<?php _e( 'Property Type', 'wp-imovel' ); ?>
		</label>
	</li>
	<?php
		$attribute = 'price_range';
		if ( in_array( $attribute, $searchable_attributes ) ) { 
			$checked = ' checked="checked"';
		} else {
			$checked = '';
		}
?>
	<li>
		<label for="<?php echo $this->get_field_id( 'searchable_attributes' ); ?>_price-range">
		<input id="<?php echo $this->get_field_id( 'searchable_attributes' ); ?>_price-range" name="<?php echo $this->get_field_name( 'searchable_attributes' ); ?>[]" type="checkbox" value="<?php echo $attribute; ?>" <?php echo $checked ?>  />
		<?php _e( 'Price Range', 'wp-imovel' ); ?>
		</label>
	</li>
	<?php
			foreach ( $all_searchable_attributes as $attribute ) {
				if ( in_array( $attribute, $searchable_attributes ) ) { 
					$checked = ' checked="checked"';
				} else {
					$checked = '';
				}
			
?>
	<li>
		<label for="<?php echo $this->get_field_id( 'searchable_attributes' ); ?>_<?php echo $attribute; ?>">
		<input id="<?php echo $this->get_field_id( 'searchable_attributes' ); ?>_<?php echo $attribute; ?>" name="<?php echo $this->get_field_name( 'searchable_attributes' ); ?>[]" type="checkbox" value="<?php echo $attribute; ?>" <?php echo $checked ?>  />
		<?php echo (!empty( $wp_imovel['property_public_meta'][$attribute]) ? $wp_imovel['property_public_meta'][$attribute] : ucwords($attribute))  ;?> </label>
	</li>
	<?php
			}
 ?>
</ul>
</p>
<br />
<?php
		if ( ! is_array( $all_searchable_property_types)) { 
?>
<p>
	<?php _e( 'No searchable property types were found.', 'wp-imovel' ); ?>
</p>
<?php 
		}
		if ( is_array( $all_searchable_property_types ) ) {
?>
<p>
	<?php _e( 'Property types to search:', 'wp-imovel' ); ?>
</p>
<p>
<ul>
	<?php
			foreach( $all_searchable_property_types as $property_type )  { 
				if ( is_array( $searchable_property_types ) && in_array( $property_type, $searchable_property_types ) ) {
					$checked = ' checked="checked"';
				} else {
					$checked = '';
				}
				$field_id = $this->get_field_id( 'searchable_property_types' ) . '_ ' . $property_type;
?>
	<li>
		<label for="<?php echo $field_id ?>">
		<input id="<?php echo $field_id ?>" name="<?php echo $this->get_field_name( 'searchable_property_types' ); ?>[]" type="checkbox" value="<?php echo $property_type; ?>" <?php echo $checked ?> />
		<?php echo ( ! empty( $wp_imovel['property_types'][$property_type]['title'] ) ? $wp_imovel['property_types'][$property_type]['title'] : ucwords( $property_type ) ) ?> </label>
	</li>
	<?php
			}
?>
</ul>
</p>
<?php
		}
?>
<?php /*?><p>

	<?php _e( 'City is an automatically created attribute once the address is validated.', 'wp-imovel' ); ?>
</p>
<?php */?>
<?php
    	}
	}
}
/**
 Property Gallery Widget
 */
 class wp_imovel_gallery_properties_widget extends WP_Widget {
    /** constructor */
    function wp_imovel_gallery_properties_widget() {
        parent::WP_Widget(false, $name = __( 'Property Gallery', 'wp-imovel' ));
    }
    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        global  $wp_imovel, $post;
		extract( $args );
        $title = apply_filters( 'widget_title', $instance['title']);
		$image_type = esc_attr($instance['image_type']);
		$big_image_type = esc_attr($instance['big_image_type']);
		$gallery_count = esc_attr($instance['gallery_count']);
		if ( empty( $big_image_type ) ) {
			$big_image_type = 'large';
		}
		$thumbnail_dimensions = WP_IMOVEL_F::get_image_sizes($image_type);
		echo $before_widget;
		echo '<div id="wp_imovel_gallery_widget">';
		if ( $post->gallery ) {
			if ( $title ) {
				echo $before_title . $title . $after_title;
			}
	//		echo '<pre>'; print_r($post->gallery); echo '</pre>';
			$real_count = 0;
			foreach( $post->gallery as $image ) {
				if ( 'private' == $image['image_privacy'] ) {
					continue;
				}
?>
<div class="sidebar_gallery_item"> <a href="<?php echo $image[$big_image_type]; ?>"  class="fancybox_image" rel="property_gallery_widget"> <img src="<?php echo $image[$image_type]; ?>" alt="<?php echo $image['post_title']; ?>" class="size-thumbnail"  width="<?php echo $thumbnail_dimensions['width']; ?>" height="<?php echo $thumbnail_dimensions['height']; ?>" /> </a> </div>
<?php
				$real_count++;
				if ( ! empty( $gallery_count ) && $gallery_count == $real_count) {
					break;
				}
			}
		}
		echo '</div>';
		echo $after_widget;
    }
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        return $new_instance;
    }
    /** @see WP_Widget::form */
    function form($instance) {
		global $wp_imovel;
        $title = esc_attr($instance['title']);
		$image_type = $instance['image_type'];
		$big_image_type = $instance['big_image_type'];
		$gallery_count = $instance['gallery_count'];
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>">
	<?php _e( 'Title:' ); ?>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
	</label>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'image_type' ); ?>">
	<?php _e( 'Thumbnail Size:' ); ?>
	<?php WP_IMOVEL_F::draw_image_size_dropdown("name=" . $this->get_field_name( 'image_type' ) . "&selected=" . $image_type); ?>
	</label>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'big_image_type' ); ?>">
	<?php _e( 'Popup Image Size:' ); ?>
	<?php WP_IMOVEL_F::draw_image_size_dropdown("name=" . $this->get_field_name( 'big_image_type' ) . "&selected=" . $big_image_type); ?>
	</label>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'gallery_count' ) ?>">
	<?php $number_of_images = '<input size="3" type="text" id="'. $this->get_field_id( 'gallery_count' ) .'" name="'. $this->get_field_name( 'gallery_count' ).'" value="'. $gallery_count.'" />'; ?>
	<?php echo sprintf(__( 'Show %s Images', 'wp-imovel' ), $number_of_images); ?> </label>
</p>
<?php
    }
}
?>
