/**
 * WP-Imovel Admin Overview Scripts
 *
*/

jQuery(document).ready(function() {

	// Overview table thumbnail
	jQuery(".fancybox").fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'elastic',
		'speedIn'		:	600, 
		'speedOut'		:	200, 
		'overlayShow'	:	false
	});


	// Toggle Featured Setting
	
	jQuery(".wp_imovel_featured_toggle").click(function() {
		
		var button_id 	= jQuery(this).attr("id");
		var post_id 	= button_id.replace( 'wp_imovel_feature_', '' );
		var _wpnonce    = jQuery(this).attr("nonce");
		
		jQuery.post(
			ajaxurl,
			{
				post_id: post_id,
				action: 'wp_imovel_make_featured',
				_wpnonce: _wpnonce				
			},
			function(data) {
			
				var button = jQuery("#wp_imovel_feature_" + data.post_id);
 				if(data.status == 'featured' ) {
					jQuery(button).val(wp_imovel_overview_l10n.Featured);
					jQuery(button).addClass( 'wp_imovel_is_featured' );
 				}
				if(data.status == 'not_featured' ) {
					jQuery(button).val(wp_imovel_overview_l10n.NotFeatured); 
					jQuery(button).removeClass( 'wp_imovel_is_featured' );
 				}
				
			},
			'json'
			);
		
	});
});