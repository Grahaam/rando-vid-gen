<?php
/**
 * Title: Exposition « Fête libre et répression »
 * Slug: tales-of-rave/expo
 * Categories: tales-of-rave
 * Inserter: true
 *
 * @package TalesOfRave
 */

?>
<!-- wp:group {"anchor":"expo","align":"full","layout":{"type":"constrained"},"backgroundColor":"accent-2","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}}} -->
<div id="expo" class="wp-block-group alignfull has-accent-2-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:paragraph {"className":"is-style-pancarte"} -->
<p class="is-style-pancarte"><?php esc_html_e( 'Exposition itinérante', 'tales-of-rave' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Fête libre et répression', 'tales-of-rave' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Des photos et des témoignages recueillis en teuf, pour informer et sensibiliser. Déjà présentée dans 9 villes de France, l’expo continue sa route — en camion.', 'tales-of-rave' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Vous êtes un lieu, un collectif, un festival ou une asso et vous voulez l’accueillir ?', 'tales-of-rave' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Accueillir l’expo', 'tales-of-rave' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
