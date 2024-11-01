<?php
/**
 * Property Default Template for Single Property View
 *
 * Overwrite by creating your own in the theme directory called either:
 * property.php
 * or add the property type to the end to customize further, example:
 * property-building.php or property-floorplan.php, etc.
 *
 * By default the system will look for file with property type suffix first, 
 * if none found, will default to: property.php
 *
 * Copyright 2010 Andy Potanin <andy.potanin@twincitiestech.com>
 *
 * @version 1.3
 * @package WP-Imovel
*/


global $wp_imovel, $post;

$map_image_type = $wp_imovel['configuration']['single_property_view']['map_image_type'];

// Uncomment to disable fancybox script being loaded on this page
//wp_deregister_script( 'jquery-fancybox' );
//wp_deregister_script( 'jquery-fancybox-css' );

/*

populate all images with fancybox filter
add_action( 'loop_start', '_wp_imovel_class_filter_add'    );
add_action( 'loop_end',   '_wp_imovel_class_filter_remove' );

function _wp_imovel_class_filter_add( $attr ) {
	add_filter( 'wp_get_attachment_image_attributes', '_wp_imovel_fancybox_filter' );
}
function _wp_imovel_class_filter_remove( $attr ) {
	remove_filter( 'wp_get_attachment_image_attributes', '_wp_imovel_fancybox_filter' );
}
function _wp_imovel_fancybox_filter( $attr ) {
//	$attr['class'] .= ' fancybox';
//	$attr['rel'] = 'wp_imovel_gallery';
	$attr['rel'] = 'property_gallery_widget';
	return $attr;
}
*/
get_header();
the_post();
//	$attr['rel'] = 'wp_imovel_gallery';
//		jQuery("a:has(img)").attr('rel', 'wp_imovel_gallery').fancybox({
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("a.widget-area:has(img)").attr('rel', 'property_gallery_widget').fancybox({
			'transitionIn'	:	'elastic',
			'transitionOut'	:	'elastic',
			'speedIn'		:	600, 
			'speedOut'		:	200, 
			'overlayShow'	:	false
		});
		initialize();
	});
	function initialize() {
<?php 
if ( $coords = WP_IMOVEL_F::get_coordinates()) {
?>
		var myLatlng = new google.maps.LatLng(<?php echo $coords[latitude]; ?>,<?php echo $coords[longitude]; ?>);
		var myOptions = {
		  zoom: <?php echo (!empty( $wp_imovel['configuration'][gm_zoom_level]) ? $wp_imovel['configuration'][gm_zoom_level] : 13); ?>,
		  center: myLatlng,
		  mapTypeId: google.maps.MapTypeId.ROADMAP
		}

		var map = new google.maps.Map(document.getElementById("property_map"), myOptions);
		
		var infowindow = new google.maps.InfoWindow({
			content: '<table cellpadding=0 cellspacing=0><tr><td width="110"><img style="margin:0;padding:0;" src="<?php echo addslashes($post->images[$map_image_type]);?>" alt="<?php echo addslashes($post->post_title);?>" /></td><td width="200" valign="top"><b><?php echo addslashes($post->{$wp_imovel['configuration']['address_attribute']}); ?></b><br /><br /><a target="_blank" href="http://maps.google.com/maps?gl=us&daddr=<?php echo str_replace( ' ', '+', $post->{$wp_imovel['configuration']['address_attribute']}); ?>"><?php _e( 'Get Directions', 'wp-imovel' ) ?></a></td></tr></table>'
		});

	
	   var marker = new google.maps.Marker({
			position: myLatlng,
			map: map,
			title: '<?php echo addslashes($post->post_title); ?>'
		});
 			infowindow.open(map,marker);
 
<?php
}
?>
	}
</script>

<div id="content_print_only">
	<?php
//		echo '<pre>'; print_r($post); echo '</pre>';
?>
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
					<?php
if ( function_exists( 'wp_email' ) ) { 
	email_link(); 
}
if ( function_exists( 'wp_print' ) ) { 
	print_link(); 
}
?>
				</h3>
			</div>
			<?php //property_slideshow(); ?>
			<div class="entry-content">
				<?php the_content(); ?>
				<div id="property_stats" class="overview_stats">
					<?php
/*
					<dt class="wp_imovel_stat_dt_location"><?php echo $wp_imovel['property_public_meta'][$wp_imovel['configuration']['address_attribute']]; ?></dt>
					<dd class="wp_imovel_stat_dd_location"><?php echo $post->display_address; ?>&nbsp;</dd>
*/
?>
					<?php draw_stats("exclude={$wp_imovel['configuration']['address_attribute']}"); ?>
				</div>
				<?php 
/*
				if ( get_features( 'type=wp_imovel_property_status&format=count' ) ) :  ?>
				<div class="features_list">
					<h2>
						<?php _e( 'Status', 'wp-imovel' ) ?>
					</h2>
					<ul class="clearfix">
						<?php get_features( 'type=wp_imovel_property_status&format=list&links=false&parent=true' ); ?>
					</ul>
				</div>
				<?php endif; 
*/
				?>
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
					<?php
/*
					<ul class="clearfix">
						<?php get_features( 'type=wp_imovel_property_feature&format=list&links=false' ); ?>
						<?php get_features( 'type=wp_imovel_property_status&format=list&links=false&parent=true' ); ?>
					</ul>
*/
?>
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
					<?php
/*					<ul class="clearfix">
						<?php get_features( 'type=wp_imovel_community_feature&format=list&links=false' ); ?>
					</ul>
*/
?>
				</div>
				<?php endif; ?>
				<?php
if ( current_user_can( 'read_private_pages' ) ) {
?>
				<div id="property_private" class="overview_stats">
					<h3>
						<?php _e('Property Meta Private', 'wp-imovel') ?>
					</h3>
					<?php draw_stats( false, 'property_private_meta' ); ?>
					<?php
	// search for non-image attachmentes like .pdf files
	$args = array(
		'post_type' => 'attachment',
		'numberposts' => -1,
		'post_status' => null,
		'post_parent' => $post->ID
	);
	$attachments = get_posts( $args );
	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$id = $attachment->ID;
			if ( substr( $attachment->post_mime_type, 0, 5 ) == 'image' )  {
				continue;
			}
			echo '<div class="document_item">
				<dt>' . esc_html( apply_filters('the_title', $attachment->post_title ) );
			echo '</dt><dd><img src="' . includes_url() . '/images/crystal/document.png" width="46" height="60" /><br />';
			echo wp_get_attachment_link( $id );
			echo '</dd></div>';
		}
	}
	if ( $post->gallery ) {
		$image_size = 'medium';
		$thumbnail_dimensions = WP_IMOVEL_F::get_image_sizes( $image_size );
		foreach( $post->gallery as $image ) {
			if ( 'private' != $image['image_privacy'] ) {
				continue;
			}
?>
					<div class="gallery_item">
						<dt>&nbsp;</dt>
						<dd><a href="<?php echo $image['large']; ?>" class="fancybox_image" rel="property_gallery_private"> <img src="<?php echo $image[$image_size]; ?>" alt="<?php echo $image['post_title']; ?>" class="size-thumbnail"  width="<?php echo $thumbnail_dimensions['width']; ?>" height="<?php echo $thumbnail_dimensions['height']; ?>" /> </a></dd>
					</div>
					<?php
		}
	}
?>
				</div>
				<?php	
}				
/*
?>
				<?php if ( is_array( $wp_imovel[property_meta] ) ): ?>
				<?php foreach($wp_imovel[property_meta] as $meta_slug => $meta_title): 
					if ( empty( $post->$meta_slug) || $meta_slug == 'tagline' )
						continue;
				?>
				<h2><?php echo $meta_title; ?></h2>
				<p><?php echo $post->$meta_slug; ?></p>
				<?php endforeach; ?>
				<?php endif; 
*/
?>
				<?php if ( false && WP_IMOVEL_F::get_coordinates()): ?>
				<div id="property_map" style="width:100%; height:450px"></div>
				<?php endif; ?>
				<?php if ( class_exists( 'WP_IMOVEL_Inquiry' )): ?>
				<h2>
					<?php _e( 'Interested?', 'wp-imovel' ) ?>
				</h2>
				<?php WP_IMOVEL_Inquiry::contact_form(); ?>
				<?php endif; ?>
				<?php if ( $post->post_parent): ?>
				<a href="<?php echo $post->parent_link; ?>">
				<?php _e( 'Return to building page.', 'wp-imovel' ) ?>
				</a>
				<?php endif; ?>
			</div>
		</div>
		<?php edit_post_link( __( 'Edit', 'wp-imovel' ), '<span class="edit-link">', '</span>' ); ?>
	</div>
</div>
<?php
	// Primary property-type sidebar.
	if ( is_active_sidebar( "wp_imovel_sidebar_" . $post->property_type ) ) { 
?>
<div id="primary" class="widget-area <?php echo "wp_imovel_sidebar_" . $post->property_type; ?>" role="complementary">
	<ul class="xoxo">
		<?php dynamic_sidebar( "wp_imovel_sidebar_" . $post->property_type ) ?>
	</ul>
</div>
<?php
	} 
get_footer(); 
?>
