<?php
/**
 * Title: Hero
 * Slug: lumiere/hero
 * Categories: lumiere
 * Inserter: true
 *
 * @package Lumiere
 */

?>
<!-- wp:cover {"dimRatio":60,"overlayColor":"base","minHeight":70,"minHeightUnit":"vh","align":"full"} -->
<div class="wp-block-cover alignfull" style="min-height:70vh"><span aria-hidden="true" class="wp-block-cover__background has-base-background-color has-background-dim-60 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","level":1,"fontSize":"xx-large"} -->
<h1 class="wp-block-heading has-text-align-center has-xx-large-font-size"><?php esc_html_e( 'Light, caught and printed.', 'lumiere' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><?php esc_html_e( 'Fine-art prints, canvas and digital downloads.', 'lumiere' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'Browse the shop', 'lumiere' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->
