<?php
/**
 * Privacy helpers for photo uploads.
 *
 * @package PhotoShopCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Strip EXIF/GPS metadata from JPEG uploads when Imagick is available.
 *
 * Keeps the colour profile so prints still look right. Runs before
 * WordPress creates the resized copies, so every size is clean.
 *
 * @param array $upload Upload data: file, url, type.
 * @return array
 */
function photo_shop_core_strip_metadata( $upload ) {
	if ( ! class_exists( 'Imagick' ) || ! in_array( $upload['type'] ?? '', array( 'image/jpeg', 'image/webp' ), true ) ) {
		return $upload;
	}

	try {
		$image    = new Imagick( $upload['file'] );
		$profiles = $image->getImageProfiles( 'icc', true );
		$image->stripImage();
		if ( ! empty( $profiles['icc'] ) ) {
			$image->profileImage( 'icc', $profiles['icc'] );
		}
		$image->writeImage( $upload['file'] );
		$image->clear();
	} catch ( Exception $e ) {
		// Leave the file untouched if Imagick cannot process it.
		unset( $e );
	}

	return $upload;
}
add_filter( 'wp_handle_upload', 'photo_shop_core_strip_metadata' );
