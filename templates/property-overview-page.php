<?php
/**
 * The default page for property overview page.
 *
 * Used when no WordPress page is setup to display overview via shortcode.
 * Will be rendered as a 404 not-found, but still can display properties.
 *
 * @package WP-Imovel
 */
get_header(); 
?>
<div id="container">
	<div id="content" role="main">
		<div id="wp_imovel_default_overview_page" >
			<h1 class="entry-title"><?php _e( 'Properties', 'wp-imovel' ) ?></h1>
			<div class="entry-content">
<?php 
if (  false && is_404() ) { // $query->is_search = true; 
?>
				<p><?php _e( 'Sorry, we could not find what you were looking for.  Since you are here, take a look at some of our properties.', 'wp-imovel' ) ?></p>
<?php 
}
echo WP_IMOVEL_Core::shortcode_property_overview(); 
?>
			</div>
		</div>
	</div>
</div>
<?php 
get_sidebar(); 
get_footer(); 
?>