<?php
/**
 * Save meta values.
 *
 * @package wp-imovel
 */
/** Load WordPress Administration Bootstrap */
require_once( '../../../../../wp-admin/admin.php' );
@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title>
<?php bloginfo('name') ?>
</title>
<?php
wp_enqueue_style( 'global' );
wp_enqueue_style( 'wp-admin' );
wp_enqueue_style( 'colors' );
wp_enqueue_style( 'ie' );
do_action('admin_print_styles');
do_action('admin_print_scripts');
do_action('admin_head');
?>
</head>
<body<?php if ( isset($GLOBALS['body_id']) ) echo ' id="' . $GLOBALS['body_id'] . '"'; ?>>
<?php
//echo '<pre>'; print_r($_REQUEST); echo '</pre>';
if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'edit-meta-values' ) ) {
	$slug = $_REQUEST['slug'];
	$label = $_REQUEST['label'];
	$old_values = $_REQUEST['value_old'];
	$maybe_new_values = $_REQUEST['value_new'];
	$new_values = array();
	foreach( $maybe_new_values as $key => $value ) {
		if ( $value != $old_values[$key] ) {
			$new_values[$key] = $value;
		}
	}
	if ( sizeof( $new_values ) ) {
?>
<h2><?php echo esc_attr( $label ) ?> </h2>
<ul>
	<?php	
		foreach( $new_values as $key => $new_value ) {
			$affected_rows = WP_IMOVEL_F::set_attribute_value( $slug, $old_values[$key], $new_value );
			if ( $affected_rows ) {
				echo  '<li>' . $key . ': ' . $old_values[$key] . ' =&gt; ' . $new_value . ', updated ' . $affected_rows . ' rows</li>';
			} else {
				echo  '<li style="text-decoration:line-through;">' . $key . ': ' . $old_values[$key] . ' =&gt; ' . $new_value . '</li>';
			}
		}
?>
</ul>
<?php			
	}	
	?>
<form>
	<input type="button" class="button-secondary" value="<?php esc_attr_e( 'Ok' ); ?>" onclick="self.parent.tb_remove();" />
</form>
<?php	
	
}
do_action('admin_print_footer_scripts');
?>
</body>
</html>