#!/usr/bin/env bash
# Configure a fresh wp-env site as a photography shop.
# Usage: npm run start && npm run setup
# On a real server, point it at your own WP-CLI: WP_CLI="wp --path=/var/www/html" bin/setup.sh
set -euo pipefail

WP_CLI="${WP_CLI:-npx wp-env run cli wp}"
wp() { $WP_CLI "$@"; }

echo "==> Theme, permalinks, language"
wp theme activate lumiere
wp rewrite structure '/%postname%/' --hard
wp option update timezone_string 'Europe/Paris'

echo "==> WooCommerce store settings"
wp option update woocommerce_default_country 'FR'
wp option update woocommerce_currency 'EUR'
wp option update woocommerce_price_num_decimals 2
wp option update woocommerce_calc_taxes 'yes'
wp option update woocommerce_enable_myaccount_registration 'yes'
wp option update woocommerce_enable_signup_and_login_from_checkout 'yes'
wp option update woocommerce_downloads_require_login 'yes'
wp option update woocommerce_downloads_grant_access_after_payment 'yes'
# "force" works everywhere; switch to "xsendfile" on a production server that supports it.
wp option update woocommerce_file_download_method 'force'
wp option update woocommerce_onboarding_profile '{"skipped":true}' --format=json

echo "==> Product categories"
for cat in "Prints" "Digital downloads" "Canvas prints" "Custom orders"; do
	wp wc product_cat create --name="$cat" --user=admin --porcelain || true
done
cat_id() { wp term list product_cat --name="$1" --field=term_id | tr -d '\r'; }

echo "==> Print size attribute"
size_id=$(wp wc product_attribute create --name="Size" --slug="size" --user=admin --porcelain | tr -d '\r')
for size in "A4" "A3" "A2" "50x70 cm"; do
	wp wc product_attribute_term create "$size_id" --name="$size" --user=admin --porcelain
done

echo "==> Sample products (replace with your own photos)"
wp wc product create --user=admin --name="Morning Fog — Digital Download" \
	--type=simple --regular_price=25 --virtual=true --downloadable=true \
	--featured=true --categories="[{\"id\":$(cat_id 'Digital downloads')}]" \
	--short_description="High-resolution JPEG, personal licence."
wp wc product create --user=admin --name="Harbour at Dusk — Fine-Art Print" \
	--type=simple --regular_price=90 --manage_stock=true --stock_quantity=20 \
	--featured=true --categories="[{\"id\":$(cat_id 'Prints')}]" \
	--short_description="Archival pigment print on Hahnemühle paper, signed."
wp wc product create --user=admin --name="Mountain Light — Canvas" \
	--type=simple --regular_price=180 --manage_stock=true --stock_quantity=5 \
	--categories="[{\"id\":$(cat_id 'Canvas prints')}]" \
	--short_description="Gallery-wrapped canvas, ready to hang."
wp wc product create --user=admin --name="Custom Order" \
	--type=simple --regular_price=0 --virtual=true --catalog_visibility=hidden \
	--categories="[{\"id\":$(cat_id 'Custom orders')}]" \
	--short_description="Use the contact form to request a custom size or commission."

echo "==> Pages"
home_id=$(wp post create --post_type=page --post_title='Home' --post_status=publish --porcelain | tr -d '\r')
wp option update show_on_front 'page'
wp option update page_on_front "$home_id"

form_id=$(wp post list --post_type=wpcf7_contact_form --field=ID --posts_per_page=1 | tr -d '\r')
wp post create --post_type=page --post_title='Contact' --post_name='contact' --post_status=publish \
	--page_template='page-contact' \
	--post_content="<!-- wp:paragraph --><p>Custom sizes, commissions and photo sessions: send me a message.</p><!-- /wp:paragraph --><!-- wp:shortcode -->[contact-form-7 id=\"$form_id\"]<!-- /wp:shortcode -->"

# French law requires these for an online shop; fill them in before going live.
wp post create --post_type=page --post_title='Mentions légales' --post_status=draft
terms_id=$(wp post create --post_type=page --post_title='Conditions générales de vente' --post_name='cgv' --post_status=draft --porcelain | tr -d '\r')
wp option update woocommerce_terms_page_id "$terms_id"

echo "==> Done: http://localhost:8888 (admin / password)"
