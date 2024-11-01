<?php
/**
 * Property Default Template for Single Property Email View
 *
 * Overwrite by creating your own in the theme directory called either:
 * property-email-view.php
 * or add the property type to the end to customize further, example:
 * property-building.php or property-floorplan.php, etc.
 *
 * By default the system will look for file with property type suffix first, 
 * if none found, will default to: property-email-view.php
 *
 * Copyright 2010 Andy Potanin <andy.potanin@twincitiestech.com>
 * Copyright 2011 Gabriel Reguly <>
 *
 * @version 1.0
 * @package WP-Imovel
*/


global $wp_imovel;
get_header( 'email-view' );
the_post();

$thumbnail_size = $wp_imovel['configuration']['property_overview']['thumbnail_size'];
$thumbnail_sizes = WP_IMOVEL_F::get_image_sizes( $thumbnail_size );
?>
			<div id="container" class="<?php echo ( ! empty( $post->property_type) ? $post->property_type . '_container' : '' );?>">
				<div id="content" role="main">
					<div class="wp_imovel_row_view">
						<?php 
		// Get property array/object and run it through prepare_property_for_display(), which runs all filters 
			$property = get_property( $post->ID );
			$link_class = '';
			$thumbnail_link = $property['permalink'];
	  ?>
						<style type="text/css">
	.wp_imovel_overview_left_column { width: <?php echo ($thumbnail_sizes['width'] + 10); ?>px;}
	.wp_imovel_overview_right_column { margin-left: -<?php echo ($thumbnail_sizes['width'] + 10); ?>px;}
	.wp_imovel_overview_data { margin-left: <?php echo ($thumbnail_sizes['width'] + 20); ?>px; } 
	</style>
						<div class="property_div <?php echo $property['post_type']; ?> clearfix">
							<div class="wp_imovel_overview_left_column">
								<div class="property_image">
									<?php
			if ( $property['images'][$thumbnail_size] ) {
	?>
									<a href="<?php echo $thumbnail_link; ?>" title="<?php echo $property['post_title'] . ($property['parent_title'] ? " of " . $property['parent_title'] : '');?>" class="property_overview_thumb property_overview_thumb_<?php echo $thumbnail_size . $link_class; ?>" rel="properties" > <img src="<?php echo $property['images'][$thumbnail_size]; ?>" alt="<?php echo $property['post_title'];?>" /> </a>
									<?php
			} else {
	/*
					 <a href="<?php echo $thumbnail_link; ?>" title="<?php echo $property['post_title'] . ($property['parent_title'] ? " of " . $property['parent_title'] : '');?>" class="property_overview_thumb property_overview_thumb_<?php echo $thumbnail_size ?>" > <img src="http://imobiliariamaggiore.com.br/wp-content/themes/maggiore/images/maggiore_imoveis.jpg<?php //echo $wp_imovel['default_thumbnail'][$thumbnail_size] ?>" alt="<?php echo $property['post_title'];?>" /> </a> 
	*/
		?>
									<a title="<?php echo $property['post_title'] . ($property['parent_title'] ? " of " . $property['parent_title'] : '');?>" class="property_overview_thumb property_overview_thumb_<?php echo $thumbnail_size ?>"> <img src="http://imobiliariamaggiore.com.br/wp-content/themes/maggiore/images/maggiore_imoveis.jpg<?php //echo $wp_imovel['default_thumbnail'][$thumbnail_size] ?>" alt="<?php echo $property['post_title'];?>" /> </a>
									<?php
			}
			if ( $property['post_excerpt'] ) { 
	?>
									<span class="property_resume"><?php echo $property['post_excerpt']; ?></span>
									<?php
			}
	?>
								</div>
							</div>
							<div class="wp_imovel_overview_right_column">
								<ul class="wp_imovel_overview_data">
									<li class="property_title"> <a href="<?php echo $property['permalink']; ?>" title="<?php _e( 'Click here to view more details', 'wp-imovel' ); ?>"><?php echo $property['post_title']; ?></a>
										<?php if ( $property[is_child]): ?>
										of <a href="<?php echo $property['parent_link']; ?>"><?php echo $property['parent_title']; ?></a>
										<?php endif; ?>
									</li>
									<?php
	/*
					<li class="property_type"><?php echo $wp_imovel['property_types'][$property['property_type']]['title']?></li>
	*/
	?>
									<li class="property_slug">Código: <span><?php echo $property['post_name']?></span></li>
									<li class="property_price_range">Faixa de preço: <span><?php echo WP_IMOVEL_F::get_price_range_string( $property['price_range'] ) ?></span></li>
									<?php
					foreach ( $wp_imovel['property_public_meta'] as $key => $value ) {
						if ( $property[$key] ) {
	?>
									<li class="property_<?php echo $key ?>"><?php echo $value?>: <span><?php echo $property[$key] ?></span></li>
									<?php 
						}
					}
	/*?>				
					<li class="property"><pre>property<?php print_r( $property ) ?></pre></li>
					<li class="property"><pre>$wp_imovel['property_public_meta']<?php print_r( $wp_imovel['property_public_meta'] ) ?></pre></li>
					
					<?php 
	*/
					if ( $show_children && $property['children']): ?>
									<li class="child_properties">
										<div class="wpd_floorplans_title"><?php echo $child_properties_title; ?></div>
										<table class="wp_imovel_overview_child_properties_table">
											<?php foreach($property['children'] as $child): ?>
											<tr class="property_child_row">
												<th class="property_child_title"><a href="<?php echo $child[permalink]; ?>"><?php echo $child[post_title]; ?></a></th>
												<td class="property_child_price"><?php echo $child[price]; ?></td>
											</tr>
											<?php endforeach; ?>
										</table>
									</li>
									<?php endif; ?>
									<li class="link_details"><a href="<?php echo $property['permalink']; ?>">
										<?php _e( 'Click here to view more details', 'wp-imovel' ); ?>
										</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>