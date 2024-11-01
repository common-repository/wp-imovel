jQuery(document).ready(function() {
	jQuery(".wp_imovel_toggle_image_privacy").click(function() {
															 	
		var button_id 	= jQuery(this).attr("id");
		var post_id 	= button_id.replace( 'wp_imovel_image_privacy_', '' );
		var _wpnonce    = jQuery(this).attr("nonce");
		var loading     = jQuery(".waiting_" + post_id);
		
		loading.show();
		
		jQuery.post(
			ajaxurl,
			{
				post_id: post_id,
				action: 'wp_imovel_toggle_image_privacy',
				_wpnonce: _wpnonce				
			},
			function(data) {
			
				var button = jQuery("#wp_imovel_image_privacy_" + data.post_id);
				var loading = jQuery(".waiting_" + data.post_id);
				var metabox = jQuery("#image_privacy_metabox_" + data.post_id);
				var my_image = jQuery("#wp_imovel_image_privacy_icon_" + data.post_id);
 				if ( data.status == 'private' ) {
					jQuery(button).val(wp_imovel_media_l10n.MakePublic);
					jQuery(button).addClass( 'wp_imovel_image_is_private' );
					jQuery(metabox).addClass( 'wp-imovel-private-image' );
					jQuery(my_image).attr("src", jQuery(my_image).attr("src").replace( 'yes.png', 'no.png' ) );
 				}
				if ( data.status == 'public' ) {
					jQuery(button).val(wp_imovel_media_l10n.MakePrivate);
					jQuery(button).removeClass( 'wp_imovel_image_is_private' );
					jQuery(metabox).removeClass( 'wp-imovel-private-image' );
					jQuery(my_image).attr("src", jQuery(my_image).attr("src").replace( 'no.png', 'yes.png' ) );
 				}
				loading.hide();
				
			},
			'json'
			);
			
		
	});

});