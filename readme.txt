=== WP-Imovel - Plugin para gerenciar imóveis com WordPress  ===
Contributors: Gabriel Reguly
Tags: property management, real estate, properties, property
Requires at least: 3.0
Tested up to: 3.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin para gerenciar imóveis com WordPress

== Description ==

WP-Imovel is a fork from WP-Property, with fixes for Multisite


== Installation ==

1. Download and activate the plugin through the 'Plugins' menu in WordPress.
2. Visit Property -> Property Settings -> Main tab and set "Property Page" to one of your pages.
3. Check "Automatically insert property overview into property page content." or copy and paste [property_overview] into the body of your main property page.
4. Visit Appearance -> Widgets and set up the widgets you want to show on your different property type pags.


== Screenshots ==

1. Tipos de imóveis ( Brazilian Portuguese )

== Frequently Asked Questions ==

= How do stylesheets work? =

The plugin uses your theme’s stylesheet, but also has its own. Inside the plugin folder (wp-content/plugins/wp-imovel/templates) there is a file called "wp_imovel.css". Copy that file to your template directory, and the plugin will automatically switch to using the settings in that file, and will not load the default one anymore. That way when you upgrade the plugin, your custom CSS will not be overwritten. Same goes for all the other template files. 

= How do I configure the plugin? =

The easy way is to go to the Settings -> Properties page, many settings can be configured there.  

= How do I upload property images? =

You would do it the same way as if you were editing a post or a page.  On the property editing page, click the Image icon above the content area, and upload images into the media library.  If you want the images to show up on the front-end, you may want to visit Appearance -> Widgets and setup the Property Gallery widget to show up on the property page.


== Changelog ==

= 1.0.1 =

* No code changes, small repository fixes

= 1.0 =

* Fork from WP-Property 0.724

== Upgrade Notice ==

= 1.0.1 =

* No code changes

= 1.0 =
* Enjoy it