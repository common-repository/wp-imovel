<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("#wp_imovel_settings_tabs").tabs({ cookie: { name: "wp_imovel_property_settings_tabs", expires: 30 } });

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

});
</script>

<div class="wrap">
	<?php screen_icon( 'options-general' ); ?>
	<h2>
		<?php _e( 'Property Type Settings', 'wp-imovel' ); ?>
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
	<form method="post" action="<?php echo admin_url( 'edit.php?post_type=wp_imovel_property&page=wp-imovel-page-property-settings' ); ?>" />
	<?php wp_nonce_field( 'wp_imovel_settings_save' ); ?>
	<div id="wp_imovel_settings_tabs" class="clearfix">
		<ul class="tabs">
			<li><a href="#tab_admin_tools">
				<?php _e( 'Property Types', 'wp-imovel' ) ?>
				</a></li>
			<li><a href="#tab_price_ranges ">
				<?php _e( 'Price Ranges', 'wp-imovel' ); ?>
				</a></li>
			<li><a href="#tab_property_public_fields ">
				<?php _e( 'Property Meta Public', 'wp-imovel' ); ?>
				</a></li>
			<li><a href="#tab_property_private_fields ">
				<?php _e( 'Property Meta Private', 'wp-imovel' ); ?>
				</a></li>
			<li><a href="#tab_property_search_fields ">
				<?php _e( 'Property Search Fields', 'wp-imovel' ); ?>
				</a></li>
<?php
/*
			<li><a href="#tab_troubleshooting">
				<?php _e( 'Troubleshooting', 'wp-imovel' ); ?>
				</a></li>
*/
?>
		</ul>
		<div id="tab_admin_tools">
			<table class="form-table">
				<tr>
					<td>
						<?php _e( '<p>Create new property types using this menu. </p>
				<p>The <b>slug</b> is automatically created from the title and is used in the back-end.  It is also used for template selection, example: floorplan will look for a template called property-floorplan.php in your theme folder, or default to property.php if nothing is found.</p>
				<p>If <b>Searchable</b> is checked then the property will be loaded for search, and available on the property search widget.</p>
				<p>If <b>Location Matters</b> is checked, then an address field will be displayed for the property, and validated against Google Maps API.  Additionally, the property will be displayed on the SuperMap, if the feature is installed.</p>
				<p><b>Hidden Attributes</b> determine which attributes are not applicable to the given property type, and will be grayed out in the back-end.</p>
				<p><b>Inheritance</b> determines which attributes should be automatically inherited from the parent property.</p>', 'wp-imovel' ) ?>
						<table id="wp_imovel_inquiry_property_types" class="ud_ui_dynamic_table widefat">
							<thead>
								<tr>
									<th>
										<?php _e( 'Property Type', 'wp-imovel' ) ?>
									</th>
									<th>
										<?php _e( 'Internal slug', 'wp-imovel' ) ?>
									</th>
									<th>
										<?php _e( 'Property Slug', 'wp-imovel' ) ?>
									</th>
									<th style="text-decoration:line-through;">
										<?php _e( 'Settings', 'wp-imovel' ) ?>
									</th>
									<th style="text-decoration:line-through;">
										<?php _e( 'Hidden Attributes', 'wp-imovel' ) ?>
									</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php
		foreach ( $wp_imovel['property_types'] as $property_slug => $property ) {
			$title = $property['title'];
			$slug  = $property['slug'];
			if ( is_array( $wp_imovel['searchable_property_types'] ) && in_array( $property_slug, $wp_imovel['searchable_property_types'] ) ) {
				$searchable_property_types_checked = ' checked="checked"';
			} else {
				$searchable_property_types_checked = '';
			}
			if ( is_array( $wp_imovel['location_matters'] ) && in_array( $property_slug, $wp_imovel['location_matters'] ) ) {
				$location_matters_checked = ' checked="checked"';
			} else {
				$location_matters_checked = '';
			}
?>
								<tr class="wp_imovel_dynamic_table_row <?php echo $hidden; ?>" slug="<?php echo $property_slug; ?>">
									<td>
										<input class="slug_setter" type="text" name="wp_imovel_settings[property_types][<?php echo $property_slug; ?>][title]" value="<?php echo $title; ?>" />
									</td>
									<td>
										<input type="text" class="slug" value="<?php echo $property_slug; ?>" readonly="readonly" />
									</td>
									<td>
										<input type="text" class="slug_short" name="wp_imovel_settings[property_types][<?php echo $property_slug; ?>][slug]" value="<?php echo $slug; ?>" />
									</td>
									<td>&nbsp;
										<?php /*?>										<ul>
											<li>
												<input class="slug" id="<?php echo $property_slug; ?>_searchable_property_types" <?php echo $searchable_property_types_checked ?> type="checkbox" name="wp_imovel_settings[searchable_property_types][]" value="<?php echo $property_slug; ?>" />
												<label for="<?php echo $property_slug; ?>_searchable_property_types">
												<?php _e( 'Searchable', 'wp-imovel' ) ?>
												</label>
											</li>
											<li>
												<input class="slug" id="<?php echo $property_slug; ?>_location_matters"  <?php $location_matters_checked ?> type="checkbox"  name="wp_imovel_settings[location_matters][]" value="<?php echo $property_slug; ?>" />
												<label for="<?php echo $property_slug; ?>_location_matters">
												<?php _e( 'Location Matters', 'wp-imovel' ) ?>
												</label>
											</li>
										</ul>
<?php */?>
									</td>
									<td>
										<ul class="wp_imovel_hidden_property_attributes">
											<?php 
/*			foreach ( $wp_imovel['property_public_meta'] as $property_stat_slug => $property_stat_label ) { 
?>
											<li>
												<input id="<?php echo $property_slug . "_" .$property_stat_slug;?>_hidden_attributes" <?php if ( is_array( $wp_imovel['hidden_attributes'][$property_slug]) && in_array( $property_stat_slug, $wp_imovel['hidden_attributes'][$property_slug] ) ) echo ' checked="checked"'; ?> type="checkbox" name="wp_imovel_settings[hidden_attributes][<?php echo $property_slug;?>][]" value="<?php echo $property_stat_slug; ?>" />
												<label for="<?php echo $property_slug . "_" .$property_stat_slug;?>_hidden_attributes"> <?php echo $property_stat_label;?> </label>
											</li>
											<?php
			}								
			foreach ( $wp_imovel['property_private_meta'] as $property_meta_slug => $property_meta_label ) { 
?>
											<li>
												<input id="<?php echo $property_slug . "_" . $property_meta_slug;?>_hidden_attributes" <?php if ( is_array( $wp_imovel['hidden_attributes'][$property_slug]) && in_array( $property_meta_slug, $wp_imovel['hidden_attributes'][$property_slug] ) ) echo ' checked="checked"'; ?> type="checkbox" name="wp_imovel_settings[hidden_attributes][<?php echo $property_slug;?>][]" value="<?php echo $property_meta_slug; ?>" />
												<label for="<?php echo $property_slug . "_" . $property_meta_slug;?>_hidden_attributes"> <?php echo $property_meta_label;?> </label>
											</li>
											<?php
			}
/*			
			foreach ( $wp_imovel['property_status'] as $property_meta_slug => $property_meta_label ) { 
?>
											<li>
												<input id="<?php echo $property_slug . "_" . $property_meta_slug;?>_hidden_attributes" <?php if ( is_array( $wp_imovel['hidden_attributes'][$property_slug]) && in_array( $property_meta_slug, $wp_imovel['hidden_attributes'][$property_slug] ) ) echo ' checked="checked"'; ?> type="checkbox" name="wp_imovel_settings[hidden_attributes][<?php echo $property_slug;?>][]" value="<?php echo $property_meta_slug; ?>" />
												<label for="<?php echo $property_slug . "_" . $property_meta_slug;?>_hidden_attributes"> <?php echo $property_meta_label;?> </label>
											</li>
											<?php
			}
*/			
?>
										</ul>
									</td>
									<td> <span class="wp_imovel_delete_row wp_imovel_link">
										<?php _e( 'Delete', 'wp-imovel' ) ?>
										</span> </td>
								</tr>
								<?php
		}
?>
							</tbody>
							<tfoot>
								<tr>
									<td>&nbsp;</td>
									<td colspan="5">
										<input type="button" class="wp_imovel_add_row button-secondary" value="<?php _e( 'Add Property Type', 'wp-imovel' ) ?>" />
									</td>
								</tr>
							</tfoot>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div id="tab_price_ranges">
			<table class="form-table">
				<tr>
					<td>
						<h3>
							<?php _e( 'Price Range Settings', 'wp-imovel' ); ?>
						</h3>
						<p>
							<?php _e( 'Price ranges for properties.', 'wp-imovel' ); ?>
						</p>
						<table id="wp_imovel_price_ranges" class="ud_ui_dynamic_table widefat">
							<thead>
								<tr>
									<th>
										<?php _e( 'Slug', 'wp-imovel' ); ?>
									</th>
									<th>
										<?php _e( 'Starting Price', 'wp-imovel' ); ?>
									</th>
									<th>
										<?php _e( 'Ending Price', 'wp-imovel' ); ?>
									</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php
	$price_ranges = $wp_imovel['price_ranges'];
	ksort($price_ranges);
	foreach(  $price_ranges as $key => $range ) { 
	?>
								<tr class="wp_imovel_dynamic_table_row" slug="<?php echo $key; ?>">
									<td  class="wp_imovel_slug">
										<input class="slug_setter slug"  type="text" value="<?php echo $key ?>" />
									</td>
									<td class="wp_imovel_starting_price">
										<input type="text" name="wp_imovel_settings[price_ranges][<?php echo $key; ?>][start]" value="<?php echo $range['start']; ?>" />
									</td>
									<td  class="wp_imovel_ending_price">
										<input type="text" name="wp_imovel_settings[price_ranges][<?php echo $key; ?>][end]" value="<?php echo $range['end']; ?>" />
									</td>
									<td><span class="wp_imovel_delete_row wp_imovel_link">
										<?php _e( 'Delete', 'wp-imovel' ) ?>
										</span></td>
								</tr>
								<?php 
	}
	?>
							<tfoot>
								<tr>
									<td>&nbsp;</td>
									<td colspan="3">
										<input type="button" class="wp_imovel_add_row button-secondary" value="<?php _e( 'Add Price Range', 'wp-imovel' ) ?>" />
									</td>
								</tr>
							</tfoot>
							</tbody>
							
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div id="tab_property_public_fields">
			<script type="text/javascript">
				jQuery(document).ready(function() {
					// Options ordering 
					jQuery("#wp_imovel_public_metadata_fields tbody").sortable().disableSelection();
					jQuery("#wp_imovel_private_metadata_fields tbody").sortable().disableSelection();
					jQuery("#wp_imovel_public_metadata_fields tbody tr, #wp_imovel_private_metadata_fields tbody tr").live("mouseover", 
						function() {			
							jQuery(this).addClass("wp_imovel_draggable_handle_show");
						});
					jQuery("#wp_imovel_public_metadata_fields tbody tr, #wp_imovel_private_metadata_fields tbody tr").live("mouseout", 
						function() {			
							jQuery(this).removeClass("wp_imovel_draggable_handle_show");
						});
				});	
			</script>
			<table class="form-table">
				<tr>
					<td>
						<h3>
							<?php _e( 'Property Meta Public', 'wp-imovel' ) ?>
						</h3>
						<?php _e( '<p>Property Meta Public are meant to be short entries that can be searchable, on the back-end attributes will be displayed as single-line input boxes. On the front-end they are displayed using a definitions list.</p>
				<p>Making an attribute as "searchable" will list it as one of the searchable options in the <strong>Property Search widget</strong> settings.</p>
				<p>Be advised, attributes added via add_filter() function supercede the settings on this page.</p>', 'wp-imovel' ) ?>
						<table id="wp_imovel_public_metadata_fields" class="ud_ui_dynamic_table widefat">
							<thead>
								<tr>
									<th class="wp_imovel_draggable_handle">&nbsp;</th>
									<th>
										<?php _e( 'Name', 'wp-imovel' ) ?>
									</th>
									<th>
										<?php _e( 'Slug', 'wp-imovel' ) ?>
									</th>
									<th>
										<?php _e( 'Searchable', 'wp-imovel' ) ?>
									</th>
									<th>
										<?php _e ( 'View/Edit values', 'wp-imovel' ) ?>
									</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php 
			foreach ( $wp_imovel['property_public_meta'] as $slug => $label ) { 
 ?>
								<tr class="wp_imovel_dynamic_table_row <?php echo $hidden; ?>" slug="<?php echo $slug; ?>">
									<th class="wp_imovel_draggable_handle">&nbsp;</th>
									<td>
										<input class="slug_setter" type="text" name="wp_imovel_settings[property_public_meta][<?php echo $slug; ?>]" value="<?php echo $label; ?>" />
									</td>
									<td>
										<input type="text" class="slug" value="<?php echo $slug; ?>" />
									</td>
									<td>
										<input <?php if ( is_array( $wp_imovel['searchable_attributes'] ) && in_array( $slug, $wp_imovel['searchable_attributes'] ) ) echo ' checked="checked"'; ?> type="checkbox" class="slug" name="wp_imovel_settings[searchable_attributes][]" id="<?php echo $slug; ?>_searchable_property_stats" value="<?php echo $slug; ?>" />
										<label for="<?php echo $slug; ?>_searchable_property_stats">
										<?php _e( 'Searchable', 'wp-imovel' ) ?>
										</label>
									</td>
									<td><a href="<?php echo WP_IMOVEL_URL ?>/core/ui/wp-imovel-page-meta-values.php?id=<?php echo $slug ?>&label=<?php echo urlencode( $label )?>&TB_iframe=1&width=640&height=400" class="thickbox" title="<?php echo sprintf(__( 'Edit %s', 'wp-imovel' ), $label )?>" ><?php _e( 'Edit', 'wp_imovel' ) ?></a></td>
									<td><span class="wp_imovel_delete_row wp_imovel_link">
										<?php _e( 'Delete', 'wp-imovel' ) ?>
										</span></td>
								</tr>
								<?php
			}
?>
							</tbody>
							<tfoot>
								<tr>
									<td>&nbsp;</td>
									<td colspan="4">
										<input type="button" class="wp_imovel_add_row button-secondary" value="<?php _e( 'Add Property Meta Public', 'wp-imovel' ) ?>" />
									</td>
								</tr>
							</tfoot>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div id="tab_property_private_fields">
			<table class="form-table">
				<tr>
					<td>
						<h3>
							<?php _e( 'Property Meta Private', 'wp-imovel' ) ?>
						</h3>
						<p>
							<?php _e( 'Meta Private is used for descriptions, on the back-end meta fields will be displayed as textareas. On the front-end they will not be displayed.', 'wp-imovel' ) ?>
						</p>
						<table id="wp_imovel_private_metadata_fields" class="ud_ui_dynamic_table widefat">
							<thead>
								<tr>
									<th class="wp_imovel_draggable_handle">&nbsp;</th>
									<th>
										<?php _e( 'Name', 'wp-imovel' ) ?>
									</th>
									<th>
										<?php _e( 'Slug', 'wp-imovel' ) ?>
									</th>
									<th>&nbsp;</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php 
			foreach ( $wp_imovel['property_private_meta'] as $slug => $label ) {  ?>
								<tr class="wp_imovel_dynamic_table_row <?php echo $hidden; ?>" slug="<?php echo $slug; ?>">
									<th class="wp_imovel_draggable_handle">&nbsp;</th>
									<td>
										<input class="slug_setter" type="text" name="wp_imovel_settings[property_private_meta][<?php echo $slug; ?>]" value="<?php echo $label; ?>" />
									</td>
									<td>
										<input type="text" class="slug" value="<?php echo $slug; ?>" id="<?php echo $_slug; ?>_searchable_property_meta" readonly="readonly" />
									</td>
									<td><a href="<?php echo WP_IMOVEL_URL ?>/core/ui/wp-imovel-page-meta-values.php?id=<?php echo $slug ?>&label=<?php echo urlencode( $label )?>&TB_iframe=1&width=640&height=400" class="thickbox" title="<?php echo sprintf(__( 'Edit %s', 'wp-imovel' ), $label )?>" ><?php _e( 'Edit', 'wp_imovel' ) ?></a></td>
									<td><span class="wp_imovel_delete_row wp_imovel_link">
										<?php _e( 'Delete', 'wp-imovel' ) ?>
										</span></td>

								</tr>
								<?php
			}
?>
							</tbody>
							<tfoot>
								<tr>
									<td>&nbsp;</td>
									<td colspan="3">
										<input type="button" class="wp_imovel_add_row button-secondary" value="<?php _e( 'Add Property Meta Private', 'wp-imovel' ) ?>" />
									</td>
								</tr>
							</tfoot>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div id="tab_property_search_fields">
			<table class="form-table">
				<tr>
					<td>
						<h3>
							<?php _e( 'Property Search Fields', 'wp-imovel' ) ?>
						</h3>
						<p>
							<?php _e( 'Search properties using price ranges and the meta fields.', 'wp-imovel' ) ?>
						</p>
						<table id="wp_imovel_search_metadata" class="ud_ui_dynamic_table widefat">
							<thead>
								<tr>
									<th class="wp_imovel_draggable_handle">&nbsp;</th>
									<th>
										<?php _e( 'Name', 'wp-imovel' ) ?>
									</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php 
			$searchables = array( $wp_imovel['property_public_meta'], $wp_imovel['property_private_meta'] ); 
			foreach ( $searchables as $searchable ) {
				foreach ( $searchable as $slug => $label ) {  
				?>
								<tr class="wp_imovel_dynamic_table_row <?php echo $hidden; ?>" slug="<?php echo $slug; ?>">
									<th class="wp_imovel_draggable_handle">&nbsp;</th>
									<td>
										<label for="<?php echo $slug; ?>_searchable_property_meta"><?php echo $label; ?></label>
									</td>
									<td>
										<input <?php if ( is_array( $wp_imovel['searchable_metadata'] ) && in_array( $slug, $wp_imovel['searchable_metadata'] ) ) echo ' checked="checked"'; ?> type="checkbox" class="slug" name="wp_imovel_settings[searchable_metadata][]" id="<?php echo $slug; ?>_searchable_property_meta" value="<?php echo $slug; ?>" />
										<label for="<?php echo $slug; ?>_searchable_property_meta">
										<?php _e( 'Searchable in admin', 'wp-imovel' ) ?>
										</label>
									</td>
								</tr>
								<?php
				}
			}
?>
							</tbody>
						</table>
					</td>
				</tr>
			</table>
		</div>
		
		<?php /*?><div id="tab_troubleshooting">
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
				<pre id="wp_imovel_show_settings_array_result" class="wp_imovel_class_pre hidden"><?php print_r($wp_imovel); ?></pre>
			</div>
		</div><?php */?>
	</div>
	<br class="cb" />
	<p class="wp_imovel_save_changes_row">
		<input type="submit" value="<?php _e( 'Save Changes', 'wp-imovel' );?>" class="button-primary" name="<?php _e( 'Submit', 'wp-imovel' );?>">
	</p>
	</form>
</div>