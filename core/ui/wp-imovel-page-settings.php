<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("#wp_imovel_settings_tabs").tabs({ cookie: {  name: "wp_imovel_settings_tabs", expires: 30 } });

	// Show settings array
	jQuery("#wp_imovel_show_settings_array").click(function() {
		jQuery("#wp_imovel_show_settings_array_cancel").show();
		jQuery("#wp_imovel_show_settings_array_result").show();
	});

	// Hide settings array
	jQuery("#wp_imovel_show_settings_array_cancel").click(function() {
		jQuery("#wp_imovel_show_settings_array_result").hide();
		jQuery(this).hide();
	});

	// Hide property query
	jQuery("#wp_imovel_ajax_property_query_cancel").click(function() {
		jQuery("#wp_imovel_ajax_property_result").hide();
		jQuery(this).hide();
	});

	// Show property query
	jQuery("#wp_imovel_ajax_property_query").click(function() {
		var property_id = jQuery("#wp_imovel_property_class_id").val();
		jQuery("#wp_imovel_ajax_property_result").html("");
		jQuery.post(ajaxurl, {
				action: 'wp_imovel_ajax_property_query',
				property_id: property_id,
 			}, function(data) {
				jQuery("#wp_imovel_ajax_property_result").show();
				jQuery("#wp_imovel_ajax_property_result").html(data);
				jQuery("#wp_imovel_ajax_property_query_cancel").show();
			});
	});

});
</script>

<div class="wrap">
	<?php screen_icon( 'options-general' ); ?>
	<h2>
		<?php _e( 'Property Settings', 'wp-imovel' ); ?>
	</h2>
	<?php 
global $wp_imovel_messages;	
if ( $wp_imovel_messages['error'] ) {
 ?>
	<div class="error">
		<?php 
	foreach( $wp_imovel_messages['error'] as $error_message ) { 
?>
		<p><?php echo $error_message; ?></p>
		<?php 
	}
?>
	</div>
	<?php
}
if ( $wp_imovel_messages['notice'] ) {
?>
	<div class="updated fade">
		<?php 
	foreach ( $wp_imovel_messages['notice'] as $warning_message ) {  
?>
		<p><?php echo $warning_message; ?></p>
		<?php 
	} 
?>
	</div>
	<?php
}
?>
	<form method="post" action="<?php echo admin_url( 'edit.php?post_type=wp_imovel_property&page=wp-imovel-page-settings' ); ?>" />
	<?php wp_nonce_field( 'wp_imovel_settings_save' ); ?>
	<div id="wp_imovel_settings_tabs" class="clearfix">
		<ul class="tabs">
			<li><a href="#tab_main">
				<?php _e( 'Main', 'wp-imovel' ); ?>
				</a></li>
			<li><a href="#tab_display">
				<?php _e( 'Display', 'wp-imovel' ); ?>
				</a></li>
			<li><a href="#tab_admin_ui">
				<?php _e( 'Admin UI', 'wp-imovel' ); ?>
				</a></li>
			<li><a href="#tab_troubleshooting">
				<?php _e( 'Troubleshooting', 'wp-imovel' ); ?>
				</a></li>
		</ul>
		<div id="tab_main">
			<table class="form-table">
				<tr>
					<th>
						<?php _e( 'Properties Listing Page', 'wp-imovel' ); ?>
					</th>
					<td>
						<select name="wp_imovel_settings[configuration][base_slug]" id="wp_imovel_settings_base_slug">
							<option <?php if ( $wp_imovel['configuration']['base_slug'] == 'property' ) echo ' selected ="selected"'; ?> value="property">
							<?php _e( 'Property (Default)', 'wp-imovel' ); ?>
							</option>
							<?php 
foreach( get_pages() as $page ) {
	if (  $wp_imovel['configuration']['base_slug'] == $page->post_name)  { 
		$selected = ' selected="selected"';
	} else {
		$selected = '';
	}
?>
							<option <?php echo $selected ?> value="<?php echo $page->post_name; ?>"><?php echo $page->post_title; ?></option>
							<?php
}
?>
						</select>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<?php 
$insert_property_text = __( 'Automatically insert property overview into property page content.', 'wp-imovel' );
echo UD_UI::checkbox("name=wp_imovel_settings[configuration][automatically_insert_overview]&label=$insert_property_text", $wp_imovel['configuration']['automatically_insert_overview']); ?>
						<br />
						<p>
							<?php _e( 'If unchecked, you will have to copy and paste one of the shortcodes into the content to display property information on the page.', 'wp-imovel' ); ?>
						</p>
						<p>
							<?php _e( 'Available shortcodes:', 'wp-imovel' ); ?>
						</p>
						<ul>
							<li>
								<?php _e( '[property_overview] - Property Overview', 'wp-imovel' ); ?>
								<ul>
									<li>
										<?php _e( '[property_overview type=floorplan] - Property Overview, floorplans only', 'wp-imovel' ); ?>
									</li>
									<li>
										<?php _e( '[property_overview pagination=on per_page=5 sorter=on] - Property Overview, pagination and sort order', 'wp-imovel' ); ?>
									</li>
									<li>
										<?php _e( '[property_overview [property_overview for_sale=true] - Property Overview, filter by custom attribute', 'wp-imovel' ); ?>
									</li>
								</ul>
							</li>
							<li>
								<?php _e( '[featured_properties] - Featured Properties', 'wp-imovel' ); ?>
							<li>
						</ul>
						<p>
							<?php _e( 'Copy and paste the shortcodes into the page content.', 'wp-imovel' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Default Phone Number', 'wp-imovel' ); ?>
					</th>
					<td>
						<?php 
$phone_number_text = __( 'Phone number to use when a property-specific phone number is not specified.', 'wp-imovel' );
echo UD_UI::input("name=phone_number&label=$phone_number_text&group=wp_imovel_settings[configuration]&style=width: 200px;", $wp_imovel['configuration']['phone_number'] ); 
?>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Address Attribute', 'wp-imovel' ); ?>
					</th>
					<td>
						<?php _e( 'Attribute to use for address:', 'wp-imovel' ); ?>
						<?php  WP_IMOVEL_F::draw_attribute_dropdown('name=wp_imovel_settings[configuration][address_attribute]&selected=' . $wp_imovel['configuration']['address_attribute']); ?>
						<?php _e( 'and localize for:', 'wp-imovel' ); ?>
						<?php  WP_IMOVEL_F::draw_localization_dropdown("name=wp_imovel_settings[configuration][google_maps_localization]&selected={$wp_imovel['configuration'][google_maps_localization]}"); ?>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Currency', 'wp-imovel' ); ?>
					</th>
					<td>
						<?php 
$currency_symbol_test = __( 'Currency symbol.', 'wp-imovel' );
echo UD_UI::input('name=currency_symbol&label=' . $currency_symbol_test . '&group=wp_imovel_settings[configuration]&style=width: 50px;', $wp_imovel['configuration']['currency_symbol'] ); 
?>
					</td>
				</tr>
			</table>
		</div>
		<div id="tab_display">
			<table class="form-table">
				<tr>
					<th>
						<?php _e( 'General Settings', 'wp-imovel' ); ?>
					</th>
					<td>
						<p>
							<?php _e( 'These are the general display settings', 'wp-imovel' ); ?>
						</p>
						<ul>
							<li>
								<?php 
$default_css_text = __( 'Automatically include default CSS.', 'wp-imovel' );
echo UD_UI::checkbox("name=wp_imovel_settings[configuration][autoload_css]&label=$default_css_text", $wp_imovel['configuration']['autoload_css']); 
?>
							</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Image Sizes', 'wp-imovel' ); ?>
					</th>
					<td>
						<p>
							<?php _e( 'Image sizes used throughout the plugin.', 'wp-imovel' ); ?>
							<br />
							<?php if ( class_exists("RegenerateThumbnails")): ?>
							<?php echo sprintf(__( 'After adding/removing image size, be sure to <a href="%s">regenerate thumbnails</a> using the Regenerate Thumbnails plugin.', 'wp-imovel' ), admin_url("tools.php?page=regenerate-thumbnails")); ?><br />
							<?php endif; ?>
							<?php if ( ! class_exists( 'RegenerateThumbnails' )): ?>
							<?php _e( 'We strongly recommend to <a href="http://wordpress.org/extend/plugins/regenerate-thumbnails/">Regenerate Thumbnails</a> plugin by Viper007Bond.', 'wp-imovel' ); ?>
							<?php endif; ?>
						</p>
						<table id="wp_imovel_image_sizes" class="ud_ui_dynamic_table widefat">
							<thead>
								<tr>
									<th>
										<?php _e( 'Slug', 'wp-imovel' ); ?>
									</th>
									<th>
										<?php _e( 'Width', 'wp-imovel' ); ?>
									</th>
									<th>
										<?php _e( 'Height', 'wp-imovel' ); ?>
									</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php
$wp_imovel_image_sizes = $wp_imovel['image_sizes'];
foreach( get_intermediate_image_sizes() as $slug ) { 
	$slug = trim( $slug);
	// We return all, including images with zero sizes, to avoid default data overriding what we save
	$image_dimensions = WP_IMOVEL_F::get_image_sizes($slug, "return_all=true");
	// Skip images w/o dimensions
	if (  ! $image_dimensions ) { 
		continue;
	}
	// Disable if WP not a WPP image size
	if ( ! is_array( $wp_imovel_image_sizes[$slug] ) ) {
		$disabled = true;
	} else { 
		$disabled = false;
	}
	if ( ! $disabled ) {
?>
								<tr class="wp_imovel_dynamic_table_row" slug="<?php echo $slug; ?>">
									<td  class="wp_imovel_slug">
										<input class="slug_setter slug"  type="text" value="<?php echo $slug; ?>" />
									</td>
									<td class="wp_imovel_width">
										<input type="text" name="wp_imovel_settings[image_sizes][<?php echo $slug; ?>][width]" value="<?php echo $image_dimensions['width']; ?>" />
									</td>
									<td  class="wp_imovel_height">
										<input type="text" name="wp_imovel_settings[image_sizes][<?php echo $slug; ?>][height]" value="<?php echo $image_dimensions['height']; ?>" />
									</td>
									<td><span class="wp_imovel_delete_row wp_imovel_link">
										<?php _e( 'Delete', 'wp-imovel' ) ?>
										</span></td>
								</tr>
								<?php 
	} else { 
?>
								<tr>
									<td>
										<div class="wp_imovel_permanent_image"><?php echo $slug; ?></div>
									</td>
									<td>
										<div class="wp_imovel_permanent_image"><?php echo $image_dimensions['width']; ?></div>
									</td>
									<td>
										<div class="wp_imovel_permanent_image"><?php echo $image_dimensions['height']; ?></div>
									</td>
									<td>&nbsp;</td>
								</tr>
								<?php 
	}
}
?>
							<tfoot>
								<tr>
									<td>&nbsp;</td>
									<td colspan="3">
										<input type="button" class="wp_imovel_add_row button-secondary" value="<?php _e( 'Add Image Size', 'wp-imovel' ) ?>" />
									</td>
								</tr>
							</tfoot>
							</tbody>
							
						</table>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Overview Shortcode', 'wp-imovel' ) ?>
					</th>
					<td>
						<p>
							<?php _e( 'These are the settings for the [property_overview] shortcode.  The shortcode displays a list of all building / root properties.<br />
				The display settings may be edited further by customizing the <b>wp-content/plugins/wp-properties/templates/property-overview.php</b> file.  To avoid losing your changes during updates, create a <b>property-overview.php</b> file in your template directory, which will be automatically loaded.', 'wp-imovel' ) ?>
						</p>
						<ul>
							<li>
								<?php _e( 'Thumbnail size:', 'wp-imovel' ) ?>
								<?php WP_IMOVEL_F::draw_image_size_dropdown("name=wp_imovel_settings[configuration][property_overview][thumbnail_size]&selected=" . $wp_imovel['configuration']['property_overview']['thumbnail_size']); ?>
							</li>
							<li>
								<?php 
                  $show_children_text = __( 'Show children properties.', 'wp-imovel' );
                  echo UD_UI::checkbox("name=wp_imovel_settings[configuration][property_overview][show_children]&label=$show_children_text", $wp_imovel['configuration']['property_overview']['show_children']); ?>
							</li>
							<li>
								<?php
                  $show_larger_img_text = __( 'Show larger image of property when image is clicked using fancybox.', 'wp-imovel' );
                  echo UD_UI::checkbox("name=wp_imovel_settings[configuration][property_overview][fancybox_preview]&label=$show_larger_img_text", $wp_imovel['configuration']['property_overview']['fancybox_preview']); ?>
							</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Property Page', 'wp-imovel' ) ?>
					</th>
					<td>
						<ul>
							<li>
								<?php 
                  $display_larger_img_text = __( 'Display larger image, or slideshow, at the top of the property listing.', 'wp-imovel' );
                  echo UD_UI::checkbox("name=wp_imovel_settings[configuration][property_overview][display_slideshow]&label=$display_larger_img_text", $wp_imovel['configuration']['property_overview']['display_slideshow']); ?>
							</li>
						</ul>
						<p>
							<?php _e( 'The display settings may be edited further by customizing the <b>wp-content/plugins/wp-properties/templates/property.php</b> file.  To avoid losing your changes during updates, create a <b>property.php</b> file in your template directory, which will be automatically loaded.', 'wp-imovel' ) ?>
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Google Maps', 'wp-imovel' ) ?>
					</th>
					<td>
						<ul>
							<li>
								<?php _e( 'Map Thumbnail Size:', 'wp-imovel' ) ?>
								<?php WP_IMOVEL_F::draw_image_size_dropdown("name=wp_imovel_settings[configuration][single_property_view][map_image_type]&selected=" . $wp_imovel['configuration']['single_property_view']['map_image_type'] ); ?>
							</li>
							<li>
								<?php _e( 'Map Zoom Level:', 'wp-imovel' ) ?>
								<?php echo UD_UI::input("name=gm_zoom_level&group=wp_imovel_settings[configuration][single_property_view]&style=width: 30px;&value={$wp_imovel['configuration'][single_property_view][gm_zoom_level]}" ); ?></li>
						</ul>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Address Display', 'wp-imovel' ) ?>
					</th>
					<td>
						<textarea name="wp_imovel_settings[configuration][display_address_format]" style="width: 70%;"><?php echo $wp_imovel['configuration']['display_address_format']; ?></textarea>
						<br />
						<span class="description">
						<?php _e( 'Available tags:', 'wp-imovel' ) ?>
						[street_number] [street_name], [city], [state], [state_code], [county],  [country], [zip_code]. </span> </td>
				</tr>
			</table>
		</div>
		<div id="tab_admin_ui">
			<table class="form-table">
				<tr>
					<th>
						<?php _e( 'Overview Page', 'wp-imovel' ) ?>
					</th>
					<td>
						<p>
							<?php _e( 'These settings are for the main property page on the back-end.', 'wp-imovel' ) ?>
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Thumbnails', 'wp-imovel' ) ?>
					</th>
					<td>
						<ul>
							<li>
								<?php _e( 'Thumbnail size:', 'wp-imovel' ) ?>
								<?php WP_IMOVEL_F::draw_image_size_dropdown("name=wp_imovel_settings[configuration][admin_ui][overview_table_thumbnail_size]&selected=" . $wp_imovel['configuration']['admin_ui']['overview_table_thumbnail_size'] ); ?>
							</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Hide / Show columns', 'wp-imovel' ) ?>
					</th>
					<td>
						<input type="hidden" name="save_hidden_columns" value="1" />
						<table id="wp_imovel_property_hidden_columns" class="ud_ui_dynamic_table widefat">
							<thead>
								<tr>
									<th>
										<?php _e( 'Column Name', 'wp-imovel' ) ?>
									</th>
									<th>
										<?php _e( 'Hide', 'wp-imovel' ) ?>
									</th>
								</tr>
							</thead>
							<tbody>
								<?php  
global $current_user;
$current_hidden_columns = get_user_meta( $current_user->data->ID, 'manage-edit-wp_imovel_property-columnshidden', true); 
if ( ! $current_hidden_columns ) {
	$current_hidden_columns = array();
}
$columns = array();
// copy custom values from function wp_imovel_property_edit_columns(), at file wp-imovel/core/class_core.php
$columns['id'] = __( 'Property Id', 'wp-imovel' );
$columns['type'] = __( 'Type', 'wp-imovel' );

$columns += $wp_imovel['property_public_meta'] 
?>
								<?php 
foreach ( $columns as $slug => $label ) {
	if ( in_array( $slug, $current_hidden_columns ) ) {
		$checked = ' checked="checked"';
	} else {
		$checked = '';
	}
?>
								<tr class="wp_imovel_dynamic_table_row" slug="<?php echo $slug; ?>">
									<td> <?php echo $label; ?> </td>
									<td>
										<input type="checkbox" class="slug" name="hidden_columns[]" id="<?php echo $slug; ?>_hidden" value="<?php echo $slug; ?>" <?php echo $checked ?> />
										<label for="<?php echo $slug; ?>_hidden">
										<?php _e( 'Hide Column', 'wp-imovel' ) ?>
										</label>
									</td>
								</tr>
								<?php
}
?>
							</tbody>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div id="tab_troubleshooting">
			<table class="form-table">
				<tr>
					<th>
						<?php _e( 'Log', 'wp-imovel' ); ?>
					</th>
					<td>
						<?php 
						$show_log_text = __( 'Show Log.', 'wp-imovel' );
						echo UD_UI::checkbox("name=wp_imovel_settings[configuration][show_ud_log]&label=$show_log_text", $wp_imovel['configuration']['show_ud_log']); 
						?>
						<br />
						<span class="description">
						<?php _e( 'The log is always active, but the UI is hidden.  If enabled, it will be visible in the admin sidebar.', 'wp-imovel' ); ?>
						</span> </td>
				</tr>
			</table>
			<div class="wp_imovel_inner_tab">
				<p>
					<?php _e( 'Enter in the ID of the property you want to look up, and the class will be displayed below.', 'wp-imovel' ) ?>
					<input type="text" id="wp_imovel_property_class_id" />
					<input type="button" value="<?php _e( 'Lookup', 'wp-imovel' ) ?>" id="wp_imovel_ajax_property_query">
					<span id="wp_imovel_ajax_property_query_cancel" class="wp_imovel_link hidden">
					<?php _e( 'Cancel', 'wp-imovel' ) ?>
					</span> </p>
				<pre id="wp_imovel_ajax_property_result" class="wp_imovel_class_pre hidden"></pre>
				<p>
					<?php _e( 'Look up the <b>$wp_imovel</b> global settings array.  This array stores all the default settings, which are overwritten by database settings, and custom filters.', 'wp-imovel' ) ?>
					<input type="button" value="<?php printf( __('Show %s', 'wp-imovel' ),  '$wp_imovel' ) ?>" id="wp_imovel_show_settings_array">
					<span id="wp_imovel_show_settings_array_cancel" class="wp_imovel_link hidden">
					<?php _e( 'Cancel', 'wp-imovel' ) ?>
					</span> </p>
				<pre id="wp_imovel_show_settings_array_result" class="wp_imovel_class_pre hidden"><?php print_r( $wp_imovel ); ?></pre>
			</div>
		</div>
	</div>
	<br class="cb" />
	<p class="wp_imovel_save_changes_row">
		<input type="submit" value="<?php _e( 'Save Changes', 'wp-imovel' );?>" class="button-primary" name="<?php _e( 'Submit', 'wp-imovel' );?>">
	</p>
	</form>
</div>