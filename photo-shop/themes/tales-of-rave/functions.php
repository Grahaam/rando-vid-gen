<?php
/**
 * Tales of Rave theme functions.
 *
 * @package TalesOfRave
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue front-end and editor styles.
 */
function tales_of_rave_enqueue_styles() {
	wp_enqueue_style(
		'tales-of-rave-style',
		get_stylesheet_directory_uri() . '/assets/css/tales-of-rave.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'tales_of_rave_enqueue_styles' );

/**
 * Theme setup.
 */
function tales_of_rave_setup() {
	add_editor_style( 'assets/css/tales-of-rave.css' );

	// Web-friendly size for gallery and shop previews (full-res files are sold, not shown).
	add_image_size( 'tales-of-rave-preview', 2000, 2000, false );
}
add_action( 'after_setup_theme', 'tales_of_rave_setup' );

/**
 * Register block styles used across the site.
 */
function tales_of_rave_register_block_styles() {
	$styles = array(
		'carousel'      => array(
			'label'  => __( 'Carrousel', 'tales-of-rave' ),
			'blocks' => array( 'core/gallery', 'woocommerce/product-template', 'core/post-template' ),
		),
		'noir-et-blanc' => array(
			'label'  => __( 'Noir et blanc', 'tales-of-rave' ),
			'blocks' => array( 'core/image', 'core/gallery', 'core/cover' ),
		),
		'pancarte'      => array(
			'label'  => __( 'Pancarte', 'tales-of-rave' ),
			'blocks' => array( 'core/paragraph' ),
		),
		'souligne'      => array(
			'label'  => __( 'Souligné rouge', 'tales-of-rave' ),
			'blocks' => array( 'core/heading' ),
		),
	);

	foreach ( $styles as $name => $style ) {
		foreach ( $style['blocks'] as $block ) {
			register_block_style(
				$block,
				array(
					'name'  => $name,
					'label' => $style['label'],
				)
			);
		}
	}
}
add_action( 'init', 'tales_of_rave_register_block_styles' );

/**
 * Register the pattern category.
 */
function tales_of_rave_register_pattern_category() {
	register_block_pattern_category(
		'tales-of-rave',
		array( 'label' => __( 'Tales of Rave', 'tales-of-rave' ) )
	);
}
add_action( 'init', 'tales_of_rave_register_pattern_category' );
