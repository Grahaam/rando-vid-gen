#!/usr/bin/env bash
# Configure a fresh wp-env site as a photography shop.
# Usage: npm run start && npm run setup
# On a real server, point it at your own WP-CLI: WP_CLI="wp --path=/var/www/html" bin/setup.sh
set -euo pipefail

# Git Bash on Windows rewrites arguments that start with "/" into Windows
# paths (e.g. '/%postname%/' becomes 'C:/Program Files/Git/%postname%/').
export MSYS_NO_PATHCONV=1

WP_CLI="${WP_CLI:-npx wp-env run cli wp}"
wp() { $WP_CLI "$@"; }

echo "==> Identity, language, theme, permalinks"
wp option update blogname 'Tales of Rave'
wp option update blogdescription 'Photographe militante en teuf et en manif'
wp language core install fr_FR --activate
wp language plugin install --all fr_FR || true
wp theme activate tales-of-rave
wp rewrite structure '/%postname%/' --hard
wp option update timezone_string 'Europe/Paris'

echo "==> WooCommerce store settings"
wp option update woocommerce_default_country 'FR'
wp option update woocommerce_currency 'EUR'
wp option update woocommerce_price_num_decimals 2
wp option update woocommerce_price_decimal_sep ','
wp option update woocommerce_price_thousand_sep ' '
wp option update woocommerce_currency_pos 'right_space'
wp option update woocommerce_calc_taxes 'yes'
wp option update woocommerce_enable_myaccount_registration 'yes'
wp option update woocommerce_enable_signup_and_login_from_checkout 'yes'
wp option update woocommerce_downloads_require_login 'yes'
wp option update woocommerce_downloads_grant_access_after_payment 'yes'
# "force" works everywhere; switch to "xsendfile" on a production server that supports it.
wp option update woocommerce_file_download_method 'force'
# New stores start in "coming soon" mode, which hides the shop from visitors.
wp option update woocommerce_coming_soon 'no'
wp option update woocommerce_enable_reviews 'no'
wp option update woocommerce_permalinks '{"product_base":"tirage","category_base":"categorie-produit","tag_base":"etiquette-produit","attribute_base":"","use_verbose_page_rules":false}' --format=json
wp option update woocommerce_onboarding_profile '{"skipped":true}' --format=json

echo "==> Product categories"
for cat in "Tirages photo" "Tirages grand format" "Téléchargements" "Commandes sur mesure"; do
	wp wc product_cat create --name="$cat" --user=admin --porcelain || true
done
cat_id() { wp term list product_cat --name="$1" --field=term_id | tr -d '\r'; }

echo "==> Print size attribute"
size_id=$(wp wc product_attribute create --name="Format" --slug="format" --user=admin --porcelain | tr -d '\r')
for size in "A4" "A3" "A2" "50x70 cm"; do
	wp wc product_attribute_term create "$size_id" --name="$size" --user=admin --porcelain
done

echo "==> Sample products (replace with real photos and texts)"
wp wc product create --user=admin --name="Teknival — tirage A3" \
	--type=simple --regular_price=45 --manage_stock=true --stock_quantity=20 \
	--featured=true --categories="[{\"id\":$(cat_id 'Tirages photo')}]" \
	--short_description="Tirage noir et blanc sur papier mat, signé. Imprimé à la demande."
wp wc product create --user=admin --name="Manif — tirage A4" \
	--type=simple --regular_price=30 --manage_stock=true --stock_quantity=20 \
	--featured=true --categories="[{\"id\":$(cat_id 'Tirages photo')}]" \
	--short_description="Tirage noir et blanc, signé. Imprimé à la demande."
wp wc product create --user=admin --name="Fête libre — tirage d’exposition 50x70" \
	--type=simple --regular_price=120 --manage_stock=true --stock_quantity=3 \
	--categories="[{\"id\":$(cat_id 'Tirages grand format')}]" \
	--short_description="Format de l’exposition « Fête libre et répression »."
wp wc product create --user=admin --name="Fond d’écran — pack numérique" \
	--type=simple --regular_price=5 --virtual=true --downloadable=true \
	--categories="[{\"id\":$(cat_id 'Téléchargements')}]" \
	--short_description="Usage personnel uniquement."
wp wc product create --user=admin --name="Commande sur mesure" \
	--type=simple --regular_price=0 --virtual=true --catalog_visibility=hidden \
	--categories="[{\"id\":$(cat_id 'Commandes sur mesure')}]" \
	--short_description="Utilise le formulaire de contact pour un format spécial ou un reportage."

echo "==> Pages"
home_id=$(wp post create --post_type=page --post_title='Accueil' --post_status=publish --porcelain | tr -d '\r')
wp option update show_on_front 'page'
wp option update page_on_front "$home_id"

form_id=$(wp post list --post_type=wpcf7_contact_form --field=ID --posts_per_page=1 | tr -d '\r')
wp post meta update "$form_id" _form "$(cat <<'FORM'
<label>Ton nom [text* your-name autocomplete:name]</label>

<label>Ton e-mail [email* your-email autocomplete:email]</label>

<label>Sujet [select* your-subject "Tirage sur mesure" "Reportage ou mariage" "Accueillir l’expo" "Retrait ou floutage d’une photo" "Autre"]</label>

<label>Ton message [textarea* your-message]</label>

[submit "Envoyer"]
FORM
)"
wp post update "$form_id" --post_title='Contact'
wp post create --post_type=page --post_title='Contact' --post_name='contact' --post_status=publish \
	--page_template='page-contact' \
	--post_content="<!-- wp:paragraph --><p>Tirage sur mesure, reportage, mariage, accueil de l’expo… ou demande de retrait d’une photo où tu apparais : écris-moi, je réponds vite.</p><!-- /wp:paragraph --><!-- wp:shortcode -->[contact-form-7 id=\"$form_id\"]<!-- /wp:shortcode -->"

# French law requires these for an online shop; fill them in before going live.
wp post create --post_type=page --post_title='Mentions légales' --post_name='mentions-legales' --post_status=draft
terms_id=$(wp post create --post_type=page --post_title='Conditions générales de vente' --post_name='cgv' --post_status=draft --porcelain | tr -d '\r')
wp option update woocommerce_terms_page_id "$terms_id"

# French slugs to match the theme's links, and remove the default sample page.
wp post update "$(wp option get woocommerce_shop_page_id | tr -d '\r')" --post_name=boutique --post_title='Boutique'
wp post update "$(wp option get woocommerce_myaccount_page_id | tr -d '\r')" --post_name=mon-compte --post_title='Mon compte'
wp post update "$(wp option get woocommerce_cart_page_id | tr -d '\r')" --post_name=panier --post_title='Panier'
wp post update "$(wp option get woocommerce_checkout_page_id | tr -d '\r')" --post_name=commande --post_title='Commande'
wp post delete "$(wp post list --post_type=page --name=sample-page --field=ID | tr -d '\r')" --force || true

wp rewrite flush

echo "==> Done: http://localhost:8888 (admin / password)"
