<?php
/**
 * WP-Imovel Actions and Hooks File
 *
 * Do not modify arrays found in these files, use the filters to modify them in your functions.php file
 * Sets up default settings and loads a few actions.
 *
 * Documentation: http://twincitiestech.com/plugins/wp-property/api-documentation/
 *
 * Copyright 2010 Andy Potanin <andy.potanin@twincitiestech.com>
 *
 * @link http://twincitiestech.com/plugins/wp-property/api-documentation/
 * @version 1.1
 * @package WP-Imovel
*/


	// Load settings out of database to overwrite defaults from action_hooks.
	$wp_imovel_db = get_option( 'wp_imovel_settings' );

	/**
	 *
	 * System-wide Filters and Settings
	 *
	 */

	// This slug will be used to display properties on the front-end.  
	// Most likely overwriten by get_option( 'wp_imovel_settings' );
	$wp_imovel['configuration'] = array(
		'autoload_css' => 'true',
		'automatically_insert_overview' => 'true',
		'base_slug' => 'imoveis',
		'currency_symbol' => 'R$',
		'gm_zoom_level' => '13'
		);
			
	// Default setings for [property_overview] shortcode
	$wp_imovel['configuration']['property_overview'] = array(
		'thumbnail_size' => 'medium',
		'fancybox_preview' => 'true',
		'display_slideshow' => 'false',
		'show_children' => 'true'
		);
						
	// Default setings for [single_property_view] shortcode
	$wp_imovel['configuration']['single_property_view'] = array(
		'map_image_type' => 'tiny_thumb',
		'gm_zoom_level' => '13'
		);
	
	// Default setings 
	$wp_imovel['configuration']['address_attribute'] = 'location';
	$wp_imovel['configuration']['google_maps_localization'] = 'pt-BR';
	$wp_imovel['configuration']['display_address_format'] =  "[street_number] [street_name], [city], [state]";
	
	
 	// Default setings for admin UI
	$wp_imovel['configuration']['admin_ui'] = array( 
		'overview_table_thumbnail_size' => 'tiny_thumb' 
		);
			
 
	if ( is_array( $wp_imovel_db['property_types'] ) ) {
		$property_types = array_keys( $wp_imovel_db['property_types'] );
	} else {
	// Setup property types to be used.
		$wp_imovel['property_types'] =  array(
			'apartamento' => array( 
				'title' => __( 'Apartamento', 'wp-imovel' ),
				'slug'  => 'ap' ),
			'cobertura' => array( 
				'title' => __( 'Cobertura', 'wp-imovel' ),
				'slug'  => 'co' ),
			'duplex' => array( 
				'title' => __( 'Duplex', 'wp-imovel' ),
				'slug'  => 'du' ),
			'kitinete' => array( 
				'title' => __( 'Kitenete', 'wp-imovel' ),
				'slug'  => 'ki' ),
			'box' => array( 
				'title' => __( 'Box de garagem', 'wp-imovel' ),
				'slug'  => 'bg' ),
			'flat' => array( 
				'title' => __( 'Flat', 'wp-imovel' ),
				'slug'  => 'fl' ),
			'casa' => array( 
				'title' => __( 'Casa', 'wp-imovel' ),
				'slug'  => 'ca' ),
			'sobrado' => array( 
				'title' => __( 'Sobrado', 'wp-imovel' ),
				'slug'  => 'so' ),
			'terreno' => array( 
				'title' => __( 'Terreno', 'wp-imovel' ),
				'slug'  => 'te' ),
			'terreno_condo' => array( 
				'title' => __( 'Terreno em condomínio', 'wp-imovel' ),
				'slug'  => 'tc' ),
			'chacara' => array( 
				'title' => __( 'Chacara', 'wp-imovel' ),
				'slug'  => 'ch' ),
			'fazenda' =>array( 
				'title' =>  __( 'Fazenda', 'wp-imovel' ),
				'slug'  => 'fa' ),
			'sala' => array( 
				'title' => __( 'Sala Comercial', 'wp-imovel' ),
				'slug'  => 'sc' ),
			'loja' => array( 
				'title' => __( 'Loja', 'wp-imovel' ),
				'slug'  => 'lj' ),
			'pavilhao' => array( 
				'title' => __( 'Pavilhão', 'wp-imovel' ),
				'slug'  => 'pv' ),
			);
		$property_types = array_keys( $wp_imovel['property_types'] );
	}
	
	// Default attribute label descriptions for the back-end
	$wp_imovel['descriptions'] = array(
			'tagline' => __( 'Will appear on overview pages and on top of every listing page.', 'wp-imovel' )
	);
 
/*
	// Setup property types to be used.
	if ( ! is_array( $wp_imovel_db['property_inheritance'] ) ) {
		$wp_imovel['property_inheritance'] =  array(
			'floorplan' => array("street_number", "route", 'state', 'postal_code', 'location', 'display_address', 'address_is_formatted' ));
	}	
*/
	// Property stats. Can be searchable, displayed as input boxes on editing page.
	if ( ! is_array( $wp_imovel_db['property_public_meta'] ) ) {
		$wp_imovel['property_public_meta'] =  array(
			'bairro'        => __( 'Bairro', 'wp-imovel' ),
			'imediacoes'    => __( 'Imediações', 'wp-imovel' ),
			'dormitorios'   => __( 'Dormitórios', 'wp-imovel' ),
			'banheiros'     => __( 'Banheiros', 'wp-imovel' ),
			'vagas-garagem' => __( 'Vagas de garagem', 'wp-imovel' ),
			'area'          => __( 'Área', 'wp-imovel' ),
		);
	}
	
	
	// Property Private meta.  Typically not searchable.
	if ( ! is_array( $wp_imovel_db['property_private_meta'] ) ) {
		$wp_imovel['property_private_meta'] =  array(
			'proprietario'          => __( 'Nome do proprietário', 'wp-imovel' ),
			'telefone_proprietario' => __( 'Telefone do proprietário', 'wp-imovel' ),
			'endereco'              => __( 'Endereço', 'wp-imovel' ),
			'preco'                 => __( 'Preço', 'wp-imovel' ),
			'agenciador'            => __( 'Agenciador','wp-imovel' ),
			'chaves'                => __( 'Chaves', 'wp-imovel' ),
			'nome_condominio'       => __( 'Nome do condomínio', 'wp-imovel' ),
			'ano_construcao'        => __( 'Ano de construção', 'wp-imovel' ),
			'quadra-lote'           => __( 'Quadra / Lote ', 'wp-imovel' ),
			'matricula'             => __( 'Matrícula', 'wp-imovel' ),
			'frente'                => __( 'Frente', 'wp-imovel' ),
			'fundos'                => __( 'Fundos', 'wp-imovel' ),
			'esquerda'              => __( 'Esquerda', 'wp-imovel' ),
			'direita'               => __( 'Direta', 'wp-imovel' ),
			'area-privativa'        => __( 'Área privativa', 'wp-imovel' ),
			'area-de-uso-comum'     => __( 'Área de uso comum', 'wp-imovel' ),
			'observacoes'           => __( 'Observações', 'wp-imovel' ),
		);
	}

	if ( ! is_array( $wp_imovel_db['price_ranges'] ) ) {
		$wp_imovel['price_ranges'] =  array(
			'10' => array( 'start' => '', end => 'até 100 mil' ),
			'20' => array( 'start' => '101', end => '200 mil' ),
			'30' => array( 'start' => '201', end => '300 mil' ),
			'40' => array( 'start' => '301', end => '400 mil' ),
			'50' => array( 'start' => '401', end => '500 mil' ),
			'60' => array( 'start' => '501', end => '700 mil' ),
			'70' => array( 'start' => '701', end => '900 mil' ),
			'80' => array( 'start' => '901', end => '1100 mil' ),
			'90' => array( 'start' => '', end => 'acima de 1.100 mil' )
		);
	}

	
/*
	// On property editing page - determines which fields to hide for a particular property type 
	if ( ! is_array( $wp_imovel_db['hidden_attributes'] ) ) {
		$wp_imovel['hidden_attributes'] = array(
			'floorplan' => array( 'location', 'parking', 'school' ), /*  Floorplans inherit location. Parking and school are generally same for all floorplans in a building *
			'building' => array( 'price', 'bedrooms', 'bathrooms', 'area', 'deposit' ),
			'single_family_home' => array( 'deposit', 'lease_terms', 'pet_policy' )
			);
	}	
	
 	// Determines property types that have addresses. 
	if ( ! is_array( $wp_imovel_db['location_matters'] ) ) {
		if ( is_array( $wp_imovel['property_types'] ) ) {
			$wp_imovel['location_matters'] = array( array_keys( $wp_imovel['property_types'] ) );
		} else {
			$wp_imovel['location_matters'] = array( array_keys( $wp_imovel_db['property_types'] ) );
		}
	}
	/**
	 *
	 * Searching and Filtering
	 *
	 */

	// Determine which property types should actually be searchable. 
	if ( ! is_array( $wp_imovel_db['searchable_property_types'] ) ) {
		$wp_imovel['searchable_property_types'] = $property_types;
	}


	// Attributes to use in searching.
	if ( ! is_array( $wp_imovel_db['searchable_attributes'] ) ) {
		$wp_imovel['searchable_attributes'] =  array(
			'bairro',
			'dormitorios',
		);
	}

/*	
	// Convert phrases to searchable values.  Converts string stats into numeric values for searching and filtering.
	if ( ! is_array( $wp_imovel_db['search_conversions'] ) ) {
		$wp_imovel['search_conversions'] =array(
			'bedrooms' => array(
				__( 'Studio', 'wp-imovel' ) => '0.5'
		));
	}
*/
 
	if ( ! is_array( $wp_imovel_db['image_sizes'] ) )
		$wp_imovel['image_sizes'] = array(
			'map_thumb'    => array( 'width'=> '75',  'height' => '75' ),
			'tiny_thumb'   => array( 'width'=> '100', 'height' => '100' ),
			'sidebar_wide' => array( 'width'=> '195', 'height' => '130' ),
			'slideshow'    => array( 'width'=> '640', 'height' => '235' )
		);
	

	// Image URLs.
	$wp_imovel['images']['map_icon_shadow'] = WP_IMOVEL_URL . '/images/map_icon_shadow.png';
	
	// Overwrite $wp_imovel with database setting
	$wp_imovel = UD_F::array_merge_recursive_distinct( $wp_imovel, $wp_imovel_db );
?>