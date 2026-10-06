<?php
/**
 * Custom "Shipped" and "Delivered" order statuses.
 *
 * @package PhotoShopCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Status slugs (without the "wc-" prefix) and their labels.
 *
 * @return array<string, string>
 */
function photo_shop_core_statuses() {
	return array(
		'shipped'   => _x( 'Shipped', 'Order status', 'photo-shop-core' ),
		'delivered' => _x( 'Delivered', 'Order status', 'photo-shop-core' ),
	);
}

/**
 * Add the statuses to WooCommerce, right after "Processing".
 *
 * Using the woocommerce_register_shop_order_post_statuses filter works with
 * both HPOS and legacy post storage.
 *
 * @param array $statuses Registered post statuses.
 * @return array
 */
function photo_shop_core_register_post_statuses( $statuses ) {
	$label_counts = array(
		/* translators: %s: number of orders */
		'shipped'   => _n_noop( 'Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>', 'photo-shop-core' ),
		/* translators: %s: number of orders */
		'delivered' => _n_noop( 'Delivered <span class="count">(%s)</span>', 'Delivered <span class="count">(%s)</span>', 'photo-shop-core' ),
	);

	foreach ( photo_shop_core_statuses() as $slug => $label ) {
		$statuses[ 'wc-' . $slug ] = array(
			'label'                     => $label,
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => $label_counts[ $slug ],
		);
	}
	return $statuses;
}
add_filter( 'woocommerce_register_shop_order_post_statuses', 'photo_shop_core_register_post_statuses' );

/**
 * Show the statuses in the order status dropdown.
 *
 * @param array $order_statuses Order statuses.
 * @return array
 */
function photo_shop_core_order_statuses( $order_statuses ) {
	$result = array();
	foreach ( $order_statuses as $key => $label ) {
		$result[ $key ] = $label;
		if ( 'wc-processing' === $key ) {
			foreach ( photo_shop_core_statuses() as $slug => $status_label ) {
				$result[ 'wc-' . $slug ] = $status_label;
			}
		}
	}
	return $result;
}
add_filter( 'wc_order_statuses', 'photo_shop_core_order_statuses' );

/**
 * Treat the new statuses as paid, so downloads and reports keep working.
 *
 * @param string[] $statuses Paid statuses.
 * @return string[]
 */
function photo_shop_core_paid_statuses( $statuses ) {
	return array_merge( $statuses, array_keys( photo_shop_core_statuses() ) );
}
add_filter( 'woocommerce_order_is_paid_statuses', 'photo_shop_core_paid_statuses' );
add_filter( 'woocommerce_reports_order_statuses', 'photo_shop_core_paid_statuses' );

/**
 * Add "Change status to shipped/delivered" bulk actions (HPOS and legacy screens).
 *
 * @param array $actions Bulk actions.
 * @return array
 */
function photo_shop_core_bulk_actions( $actions ) {
	foreach ( photo_shop_core_statuses() as $slug => $label ) {
		/* translators: %s: order status label */
		$actions[ 'mark_' . $slug ] = sprintf( __( 'Change status to %s', 'photo-shop-core' ), strtolower( $label ) );
	}
	return $actions;
}
add_filter( 'bulk_actions-woocommerce_page_wc-orders', 'photo_shop_core_bulk_actions' );
add_filter( 'bulk_actions-edit-shop_order', 'photo_shop_core_bulk_actions' );

/**
 * Register the customer emails with WooCommerce.
 *
 * @param array $emails Email classes.
 * @return array
 */
function photo_shop_core_register_emails( $emails ) {
	require_once PHOTO_SHOP_CORE_DIR . 'includes/class-photo-shop-status-email.php';

	$emails['Photo_Shop_Shipped_Email']   = new Photo_Shop_Status_Email(
		'shipped',
		__( 'Order shipped', 'photo-shop-core' ),
		__( 'Your {site_title} order is on its way', 'photo-shop-core' ),
		__( 'Your order has shipped', 'photo-shop-core' ),
		__( 'Good news: your prints have left the studio and are on their way to you.', 'photo-shop-core' )
	);
	$emails['Photo_Shop_Delivered_Email'] = new Photo_Shop_Status_Email(
		'delivered',
		__( 'Order delivered', 'photo-shop-core' ),
		__( 'Your {site_title} order has been delivered', 'photo-shop-core' ),
		__( 'Your order has arrived', 'photo-shop-core' ),
		__( 'Your order has been delivered. I hope you enjoy your prints!', 'photo-shop-core' )
	);
	return $emails;
}
add_filter( 'woocommerce_email_classes', 'photo_shop_core_register_emails' );

/**
 * Let WooCommerce load its mailer when our status transitions fire.
 *
 * @param string[] $actions Actions that trigger transactional emails.
 * @return string[]
 */
function photo_shop_core_email_actions( $actions ) {
	foreach ( array_keys( photo_shop_core_statuses() ) as $slug ) {
		$actions[] = 'woocommerce_order_status_' . $slug;
	}
	return $actions;
}
add_filter( 'woocommerce_email_actions', 'photo_shop_core_email_actions' );
