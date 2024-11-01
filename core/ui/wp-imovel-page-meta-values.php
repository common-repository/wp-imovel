<?php
/**
 * Manage meta values.
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
$slug = $_REQUEST['id'];
$label = urldecode( $_REQUEST['label'] );
$common_values = WP_IMOVEL_F::get_all_attribute_values( $slug ); 
if ( ! $common_values ) {
?>
<h2>
	<?php _e( 'Sorry, could not find any values to edit.', 'wp-imovel' ); ?>
</h2>
<?php
} else {

?>
<div class="wp_imovel_box_content">
	<div class="wp_imovel_box">
		<div class="wp_imovel_box_header strong">
			<h2>
				<?php _e( 'Click at the values you want to edit.', 'wp-imovel' ); ?>
			</h2>
		</div>
		<form action="<?php echo WP_IMOVEL_URL ?>/core/ui/wp-imovel-page-meta-values_save.php" method="post">
			<?php wp_nonce_field('edit-meta-values'); ?>
			<input type="hidden" value="<?php echo $slug ?>" name="slug" />
			<input type="hidden" value="<?php echo $label?>" name="label" />
			<div class="wp_imovel_edit_values clearfix">
				<?php
		foreach ( $common_values as $key => $value ) { 
?>
				<input type="hidden" name="value_old[<?php echo $key ?>]" value="<?php echo $value ?>" />
				<input type="hidden" name="value_new[<?php echo $key ?>] " value="<?php echo $value ?>" id="meta_<?php echo $key ?>" />
				<span class="wp_imovel_prefill_attribute edit" id="<?php echo $key ?>" style="display:inline"><?php echo $value; ?></span>
				<?php 
		}
?>
			</div>
			<div class="wp_imovel_box_footer">
				<input type="submit" id="submit-button" class="button" name="save" value="<?php esc_attr_e( 'Save all changes' ); ?>" disabled="disabled" />
				<input type="button" class="button-secondary" value="<?php esc_attr_e( 'Cancel' ); ?>" onclick="self.parent.tb_remove();" />
			</div>
		</form>
	</div>
</div>
<?php		
} 
do_action('admin_print_footer_scripts');
/*
 *
 *  jeditable.min.js (modified to use jQuery instead of $) 
 *  from http://www.appelsiini.net/projects/jeditable
 *
 */
?>
<script type="text/javascript">
(function(jQuery){jQuery.fn.editable=function(target,options){if('disable'==target){jQuery(this).data('disabled.editable',true);return;}
if('enable'==target){jQuery(this).data('disabled.editable',false);return;}
if('destroy'==target){jQuery(this).unbind(jQuery(this).data('event.editable')).removeData('disabled.editable').removeData('event.editable');return;}
var settings=jQuery.extend({},jQuery.fn.editable.defaults,{target:target},options);var plugin=jQuery.editable.types[settings.type].plugin||function(){};var submit=jQuery.editable.types[settings.type].submit||function(){};var buttons=jQuery.editable.types[settings.type].buttons||jQuery.editable.types['defaults'].buttons;var content=jQuery.editable.types[settings.type].content||jQuery.editable.types['defaults'].content;var element=jQuery.editable.types[settings.type].element||jQuery.editable.types['defaults'].element;var reset=jQuery.editable.types[settings.type].reset||jQuery.editable.types['defaults'].reset;var callback=settings.callback||function(){};var onedit=settings.onedit||function(){};var onsubmit=settings.onsubmit||function(){};var onreset=settings.onreset||function(){};var onerror=settings.onerror||reset;if(settings.tooltip){jQuery(this).attr('title',settings.tooltip);}
settings.autowidth='auto'==settings.width;settings.autoheight='auto'==settings.height;return this.each(function(){var self=this;var savedwidth=jQuery(self).width();var savedheight=jQuery(self).height();jQuery(this).data('event.editable',settings.event);if(!jQuery.trim(jQuery(this).html())){jQuery(this).html(settings.placeholder);}
jQuery(this).bind(settings.event,function(e){if(true===jQuery(this).data('disabled.editable')){return;}
if(self.editing){return;}
if(false===onedit.apply(this,[settings,self])){return;}
e.preventDefault();e.stopPropagation();if(settings.tooltip){jQuery(self).removeAttr('title');}
if(0==jQuery(self).width()){settings.width=savedwidth;settings.height=savedheight;}else{if(settings.width!='none'){settings.width=settings.autowidth?jQuery(self).width():settings.width;}
if(settings.height!='none'){settings.height=settings.autoheight?jQuery(self).height():settings.height;}}
if(jQuery(this).html().toLowerCase().replace(/(;|")/g,'')==settings.placeholder.toLowerCase().replace(/(;|")/g,'')){jQuery(this).html('');}
self.editing=true;self.revert=jQuery(self).html();jQuery(self).html('');var form=jQuery('<form />');if(settings.cssclass){if('inherit'==settings.cssclass){form.attr('class',jQuery(self).attr('class'));}else{form.attr('class',settings.cssclass);}}
if(settings.style){if('inherit'==settings.style){form.attr('style',jQuery(self).attr('style'));form.css('display',jQuery(self).css('display'));}else{form.attr('style',settings.style);}}
var input=element.apply(form,[settings,self]);var input_content;if(settings.loadurl){var t=setTimeout(function(){input.disabled=true;content.apply(form,[settings.loadtext,settings,self]);},100);var loaddata={};loaddata[settings.id]=self.id;if(jQuery.isFunction(settings.loaddata)){jQuery.extend(loaddata,settings.loaddata.apply(self,[self.revert,settings]));}else{jQuery.extend(loaddata,settings.loaddata);}
jQuery.ajax({type:settings.loadtype,url:settings.loadurl,data:loaddata,async:false,success:function(result){window.clearTimeout(t);input_content=result;input.disabled=false;}});}else if(settings.data){input_content=settings.data;if(jQuery.isFunction(settings.data)){input_content=settings.data.apply(self,[self.revert,settings]);}}else{input_content=self.revert;}
content.apply(form,[input_content,settings,self]);input.attr('name',settings.name);buttons.apply(form,[settings,self]);jQuery(self).append(form);plugin.apply(form,[settings,self]);jQuery(':input:visible:enabled:first',form).focus();if(settings.select){input.select();}
input.keydown(function(e){if(e.keyCode==27){e.preventDefault();reset.apply(form,[settings,self]);}});var t;if('cancel'==settings.onblur){input.blur(function(e){t=setTimeout(function(){reset.apply(form,[settings,self]);},500);});}else if('submit'==settings.onblur){input.blur(function(e){t=setTimeout(function(){form.submit();},200);});}else if(jQuery.isFunction(settings.onblur)){input.blur(function(e){settings.onblur.apply(self,[input.val(),settings]);});}else{input.blur(function(e){});}
form.submit(function(e){if(t){clearTimeout(t);}
e.preventDefault();if(false!==onsubmit.apply(form,[settings,self])){if(false!==submit.apply(form,[settings,self])){if(jQuery.isFunction(settings.target)){var str=settings.target.apply(self,[input.val(),settings]);jQuery(self).html(str);self.editing=false;callback.apply(self,[self.innerHTML,settings]);if(!jQuery.trim(jQuery(self).html())){jQuery(self).html(settings.placeholder);}}else{var submitdata={};submitdata[settings.name]=input.val();submitdata[settings.id]=self.id;if(jQuery.isFunction(settings.submitdata)){jQuery.extend(submitdata,settings.submitdata.apply(self,[self.revert,settings]));}else{jQuery.extend(submitdata,settings.submitdata);}
if('PUT'==settings.method){submitdata['_method']='put';}
jQuery(self).html(settings.indicator);var ajaxoptions={type:'POST',data:submitdata,dataType:'html',url:settings.target,success:function(result,status){if(ajaxoptions.dataType=='html'){jQuery(self).html(result);}
self.editing=false;callback.apply(self,[result,settings]);if(!jQuery.trim(jQuery(self).html())){jQuery(self).html(settings.placeholder);}},error:function(xhr,status,error){onerror.apply(form,[settings,self,xhr]);}};jQuery.extend(ajaxoptions,settings.ajaxoptions);jQuery.ajax(ajaxoptions);}}}
jQuery(self).attr('title',settings.tooltip);return false;});});this.reset=function(form){if(this.editing){if(false!==onreset.apply(form,[settings,self])){jQuery(self).html(self.revert);self.editing=false;if(!jQuery.trim(jQuery(self).html())){jQuery(self).html(settings.placeholder);}
if(settings.tooltip){jQuery(self).attr('title',settings.tooltip);}}}};});};jQuery.editable={types:{defaults:{element:function(settings,original){var input=jQuery('<input type="hidden"></input>');jQuery(this).append(input);return(input);},content:function(string,settings,original){jQuery(':input:first',this).val(string);},reset:function(settings,original){original.reset(this);},buttons:function(settings,original){var form=this;if(settings.submit){if(settings.submit.match(/>jQuery/)){var submit=jQuery(settings.submit).click(function(){if(submit.attr("type")!="submit"){form.submit();}});}else{var submit=jQuery('<button type="submit" />');submit.html(settings.submit);}
jQuery(this).append(submit);}
if(settings.cancel){if(settings.cancel.match(/>jQuery/)){var cancel=jQuery(settings.cancel);}else{var cancel=jQuery('<button type="cancel" />');cancel.html(settings.cancel);}
jQuery(this).append(cancel);jQuery(cancel).click(function(event){if(jQuery.isFunction(jQuery.editable.types[settings.type].reset)){var reset=jQuery.editable.types[settings.type].reset;}else{var reset=jQuery.editable.types['defaults'].reset;}
reset.apply(form,[settings,original]);return false;});}}},text:{element:function(settings,original){var input=jQuery('<input />');if(settings.width!='none'){input.width(settings.width);}
if(settings.height!='none'){input.height(settings.height);}
input.attr('autocomplete','off');jQuery(this).append(input);return(input);}},textarea:{element:function(settings,original){var textarea=jQuery('<textarea />');if(settings.rows){textarea.attr('rows',settings.rows);}else if(settings.height!="none"){textarea.height(settings.height);}
if(settings.cols){textarea.attr('cols',settings.cols);}else if(settings.width!="none"){textarea.width(settings.width);}
jQuery(this).append(textarea);return(textarea);}},select:{element:function(settings,original){var select=jQuery('<select />');jQuery(this).append(select);return(select);},content:function(data,settings,original){if(String==data.constructor){eval('var json = '+data);}else{var json=data;}
for(var key in json){if(!json.hasOwnProperty(key)){continue;}
if('selected'==key){continue;}
var option=jQuery('<option />').val(key).append(json[key]);jQuery('select',this).append(option);}
jQuery('select',this).children().each(function(){if(jQuery(this).val()==json['selected']||jQuery(this).text()==jQuery.trim(original.revert)){jQuery(this).attr('selected','selected');}});}}},addInputType:function(name,input){jQuery.editable.types[name]=input;}};jQuery.fn.editable.defaults={name:'value',id:'id',type:'text',width:'auto',height:'auto',event:'click.editable',onblur:'cancel',loadtype:'GET',loadtext:'Loading...',placeholder:'Click to edit',loaddata:{},submitdata:{},ajaxoptions:{}};})(jQuery);
</script>

<script type="text/javascript">
jQuery( document ).ready( function() {
	jQuery( '.edit' ).editable(
		function( value, settings ) { 
			var meta = jQuery( '#meta_' + this.id );
			var button = jQuery( '#submit-button' );
			if ( button.attr( 'disabled' ) == true ) {
				button.removeAttr( 'disabled' );
			}
			meta.val( value );
			return( value );
	});
});
</script>
</body>
</html>