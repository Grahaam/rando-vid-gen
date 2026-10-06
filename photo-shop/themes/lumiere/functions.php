<?php
/**
 * Lumière theme functions.
 *
 * @package Lumiere
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue front-end and editor styles.
 */
function lumiere_enqueue_styles() {
	wp_enqueue_style(
		'lumiere-style',
		get_stylesheet_directory_uri() . '/assets/css/lumiere.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'lumiere_enqueue_styles' );

/**
 * Theme setup.
 */
function lumiere_setup() {
	add_editor_style( 'assets/css/lumiere.css' );

	// Web-friendly size for gallery and shop previews (full-res files are sold, not shown).
	add_image_size( 'lumiere-preview', 2000, 2000, false );
}
add_action( 'after_setup_theme', 'lumiere_setup' );

/**
 * Register the "Carousel" block style on blocks used for featured work.
 */
function lumiere_register_block_styles() {
	foreach ( array( 'core/gallery', 'woocommerce/product-template', 'core/post-template' ) as $block ) {
		register_block_style(
			$block,
			array(
				'name'  => 'carousel',
				'label' => __( 'Carousel', 'lumiere' ),
			)
		);
	}
}
add_action( 'init', 'lumiere_register_block_styles' );

/**
 * Register the pattern category.
 */
function lumiere_register_pattern_category() {
	register_block_pattern_category(
		'lumiere',
		array( 'label' => __( 'Photography shop', 'lumiere' ) )
	);
}
add_action( 'init', 'lumiere_register_pattern_category' );
