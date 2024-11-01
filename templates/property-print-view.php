<?php
/**
 * Property Default Template for Single Property Print View
 *
 * Overwrite by creating your own in the theme directory called either:
 * property-print-view.php
 * or add the property type to the end to customize further, example:
 * property-building.php or property-floorplan.php, etc.
 *
 * By default the system will look for file with property type suffix first, 
 * if none found, will default to: property-print-view.php
 *
 * Copyright 2010 Andy Potanin <andy.potanin@twincitiestech.com>
 * Copyright 2011 Gabriel Reguly <>
 *
 * @version 1.0
 * @package WP-Imovel
*/


global $wp_imovel;
get_header( 'print-view' );
the_post();
?>
	<div id="content_print_only">
		<div id="maggiore"> <img src="http://imobiliariamaggiore.com.br/wp-content/themes/maggiore/images/maggiore_imoveis.jpg" alt="Maggiore Imóveis" />
			<h2>(54) 3223.7222</h2>
			<p><br />
				www.imobiliariamaggiore.com.br</p>
		</div>
		<div class="property_image"> <img src="<?php echo $post->images['medium'] ?>" alt="<?php echo $post->post_title ?>" /></div>
		<div class="excerpt"><?php echo $post->post_excerpt; ?> </div>
	</div>
	<div id="container" class="<?php echo ( ! empty( $post->property_type) ? $post->property_type . '_container' : '' );?>">
		<div id="content" role="main" class="property_content">
			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="building_title_wrapper">
					<h1 class="property-title entry-title">
						<?php the_title(); ?>
					</h1>
					<h3 class="entry-subtitle"> Código: <?php echo $post->post_name?>&nbsp;&nbsp;<br />
						Faixa de preço: <span><?php echo WP_IMOVEL_F::get_price_range_string( $post->price_range ) ?></span>
					</h3>
				</div>
				<div class="entry-content">
					<?php the_content(); ?>
					<div id="property_stats" class="overview_stats">
						<?php draw_stats("exclude={$wp_imovel['configuration']['address_attribute']}"); ?>
					</div>
					<?php if ( get_features( 'type=wp_imovel_property_feature&format=count' ) ) :  ?>
					<div class="features_list">
						<h3>
							<?php _e( 'Features', 'wp-imovel' ) ?>
						</h3>
						<table>
							<tr>
								<?php 
								$i = 0;
								$features = get_features( 'type=wp_imovel_property_feature&format=array&links=false' ) + get_features( 'type=wp_imovel_property_status&format=array&links=false&parent=true' );
								foreach( $features as $value ) {
									if ( $i && $i%2 == 0 ) { 
										echo '</tr><tr>';
									}
									echo '<td>'. $value . '</td>';
									$i++;
								}
								if ( $i%2 != 0 ) { 
										echo '<td>&nbsp;</td>';
								}
	?>
							</tr>
						</table>
					</div>
					<?php endif; ?>
					<?php if ( get_features( 'type=wp_imovel_community_feature&format=count' )):  ?>
					<div class="features_list">
						<h3>
							<?php _e( 'Community Features', 'wp-imovel' ) ?>
						</h3>
						<table>
							<tr>
								<?php 
								$i = 0;
								$features = get_features( 'type=wp_imovel_community_feature&format=array&links=false' );
								foreach( $features as $value ) {
									if ( $i && $i%2 == 0 ) { 
										echo '</tr><tr>';
									}
									echo '<td>'. $value . '</td>';
									$i++;
								}
								if ( $i%2 != 0 ) { 
										echo '<td>&nbsp;</td>';
								}
	?>
							</tr>
						</table>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
<?php
get_footer(); 
?>