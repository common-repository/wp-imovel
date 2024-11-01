/**
 * WP-Imovel Global Admin Scripts
 *
 * This file is included on all back-end pages, so extra care needs be taken to avoid conflicts
 *
*/

jQuery(document).ready(function() {

	// Add row to UD UI Dynamic Table
	jQuery(".wp_imovel_add_row").live("click" , function() {

		var table = jQuery(this).parents( '.ud_ui_dynamic_table' );
		var table_id = jQuery(table).attr("id");

		// Clone last row
		var cloned = jQuery(".wp_imovel_dynamic_table_row:last", table).clone();
		
		// Insert new row after last one
		jQuery(cloned).appendTo(table);

		// Get Last row to update names to match slug
		var added_row = jQuery(".wp_imovel_dynamic_table_row:last", table);

		// Display row ust in case
		jQuery(added_row).show();

		// Blank out all values
		jQuery("input[type=text]", added_row).val( '' );
		jQuery("input[type=checkbox]", added_row).attr( 'checked', false);
		
		jQuery("input[type=text]:first", added_row).focus();

	});

	// When the .slug_setter input field is modified, we update names of other elements in row
	jQuery(".wp_imovel_dynamic_table_row input.slug_setter").live("change", function() {

		//console.log( 'Name changed.' );
	
		var this_row = jQuery(this).parents( 'tr.wp_imovel_dynamic_table_row' );

		// Slug of row in question
		var old_slug = jQuery(this_row).attr( 'slug' );

		// Get data from input.slug_setter
		var new_slug = jQuery(this).val();

		// Conver into slug
		var new_slug = wp_imovel_create_slug(new_slug);
		//console.log("New slug: "  + new_slug);

		// Don't allow to blank out slugs
		if(new_slug == "")
			return;

		// If slug input.slug exists in row, we modify it
		jQuery(".slug"      , this_row).val( new_slug );
		jQuery(".slug_short", this_row).val( new_slug.substring( 0, 2 ) );

		// Update row slug
		jQuery(this_row).attr( 'slug', new_slug);
		
		// Cycle through all child elements and fix names
		jQuery( 'input', this_row).each(function(element) {
			var old_name = jQuery(this).attr( 'name' );
			var new_name =  old_name.replace( old_slug, new_slug );

			var old_id = jQuery(this).attr( 'id' );
			var new_id =  old_id.replace(old_slug, new_slug );
			
			// Update to new name
			jQuery(this).attr( 'name', new_name) ;
			jQuery(this).attr( 'id', new_id );
			

		});
		
		// Cycle through labels too
			jQuery( 'label', this_row).each(function(element) {
			var old_for = jQuery(this).attr( 'for' );
			var new_for =  old_for.replace(old_slug,new_slug);
			
			// Update to new name
			jQuery(this).attr( 'for', new_for);
			

		});
				
		/*
		jQuery( '.wp_imovel_width input', this_row).attr("name", "wp_imovel_settings[image_sizes][" + new_slug + "][width]");
		jQuery( '.wp_imovel_height input', this_row).attr("name", "wp_imovel_settings[image_sizes][" + new_slug + "][height]");
		*/
	});

	// Delete row
	jQuery(".wp_imovel_delete_row").live("click", function() {

		var parent = jQuery(this).parents( 'tr.wp_imovel_dynamic_table_row' );
		var row_count = jQuery(".wp_imovel_delete_row:visible").length;
		
		// Blank out all values
		jQuery("input[type=text]", parent).val( '' );
		jQuery("input[type=checkbox]", parent).attr( 'checked', false);
		
		// Don't hide last row
		if(row_count > 1) {
			jQuery(parent).hide();
			jQuery(parent).remove();	
		}
	});

	
});

function wp_imovel_create_slug( str ) {
  str = str.replace(/^\s+|\s+$/g, '' ); // trim
  str = str.toLowerCase();
  
  // remove accents, swap ñ for n, etc
  var from = "àáäâãèéëêìíïîòóöôùúüûñç·/_,:;";
  var to   = "aaaaaeeeeiiiioooouuuunc------";
  for (var i=0, l=from.length ; i<l ; i++) {
    str = str.replace(new RegExp(from.charAt(i), 'g' ), to.charAt(i));
  }

  str = str.replace(/[^a-z0-9 -]/g, '' ) // remove invalid chars
    .replace(/\s+/g, '-' ) // collapse whitespace and replace by -
    .replace(/-+/g, '-' ); // collapse dashes

  return str;
}