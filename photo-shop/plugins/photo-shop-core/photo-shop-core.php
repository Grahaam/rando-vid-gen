<?php
/**
 * Plugin Name:       Photo Shop Core
 * Description:       Shop logic for the photography store: "Shipped" and "Delivered" order statuses with customer emails, and GPS removal from uploaded photos.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Requires Plugins:  woocommerce
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       photo-shop-core
 *
 * @package PhotoShopCore
 */

defined( 'ABSPATH' ) || exit;

define( 'PHOTO_SHOP_CORE_DIR', plugin_dir_path( __FILE__ ) );

require_once PHOTO_SHOP_CORE_DIR . 'includes/order-statuses.php';
require_once PHOTO_SHOP_CORE_DIR . 'includes/privacy.php';

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage.
 */
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
