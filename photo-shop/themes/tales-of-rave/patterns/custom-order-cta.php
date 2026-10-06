<?php
/**
 * Title: Commandes, reportages et mariages
 * Slug: tales-of-rave/custom-order-cta
 * Categories: tales-of-rave
 * Inserter: true
 *
 * @package TalesOfRave
 */

?>
<!-- wp:group {"align":"full","layout":{"type":"constrained"},"backgroundColor":"accent-1","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}}} -->
<div class="wp-block-group alignfull has-accent-1-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Reportages, mariages & commandes', 'tales-of-rave' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><?php esc_html_e( 'Un tirage dans un format spécial, un reportage pour ton collectif, ton mariage ou ton événement ? Parlons-en.', 'tales-of-rave' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Me contacter', 'tales-of-rave' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
