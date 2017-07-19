<?php

namespace wpscholar\WordPress;

/**
 * Class Utilities
 *
 * @package wpscholar\WordPress
 */
class Utilities {

	/**
	 * Reliably generate a unique cache key, but something that could be rebuilt if necessary.
	 *
	 * @param string $name A unique name for the cached data.
	 * @param array $context An array representing values that may change and will require a unique cache key.
	 *
	 * @return string
	 */
	public static function generateCacheKey( $name, array $context = [] ) {
		if ( ! empty( $context ) ) {
			if ( array_values( $context ) === $context ) {
				asort( $context );
			} else {
				ksort( $context );
			}
			$name = $name . '?' . http_build_query( $context, null, '&' );
		}

		return md5( $name );
	}

	/**
	 * Returns a nav menu object by location name.
	 *
	 * @param string $location Nav menu location name
	 *
	 * @return false|\WP_Term Returns WP_Term object if menu exists, false otherwise.
	 */
	public static function getNavMenuByLocation( $location ) {

		global $_wp_registered_nav_menus;

		$nav_menu = false;

		if ( has_nav_menu( $location ) ) {
			$nav_menu = wp_get_nav_menu_object( $_wp_registered_nav_menus[ $location ] );
		}

		return $nav_menu;
	}

	/**
	 * Returns a collection of nav menu items.
	 *
	 * @param string $location Nav menu location name.
	 * @param array $args Optional. Arguments to pass to get_posts().
	 *
	 * @return array
	 */
	public static function getNavMenuItemsByLocation( $location, array $args = [] ) {

		global $_wp_registered_nav_menus;

		$nav_menu_items = [];
		if ( has_nav_menu( $location ) ) {
			$nav_menu_items = wp_get_nav_menu_items( $_wp_registered_nav_menus[ $location ], $args );
		}

		return $nav_menu_items;
	}

	/**
	 * Get a post's featured image URL for a specific image size.
	 *
	 * @param \WP_Post|int $post
	 * @param string $size
	 *
	 * @return string Returns a URL, or an empty string if there is no featured image.
	 */
	public static function getPostThumbnailUrl( $post, $size = 'full' ) {
		$url = '';
		if ( has_post_thumbnail( $post ) ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), $size );
			if ( isset( $image[0] ) ) {
				$url = $image[0];
			}
		}

		return $url;
	}

	/**
	 * Load a template with context.
	 *
	 * @param string|array $template Template name(s)
	 * @param array $context Array containing variable name and value pairs.
	 */
	public static function loadTemplateWithContext( $template, $context = [] ) {
		extract( $context, EXTR_SKIP );
		include locate_template( $template );
	}

	/**
	 * A PHP generator that can be used to iterate through a collection of posts.
	 * Essentially, the WordPress loop can be replaced with a simple foreach.
	 *
	 * @param \WP_Query|\WP_Post[] $iterable
	 *
	 * @return \Generator
	 */
	public static function loop( $iterable ) {

		$posts = [];
		if ( is_array( $iterable ) ) {
			$posts = $iterable;
		} else if ( property_exists( $iterable, 'posts' ) ) {
			$posts = $iterable->posts;
		}

		global $post;
		// Save a copy of current global post
		$save_post = $post;
		try {
			foreach ( $posts as $post ) {
				// Setup post data (also sets global context)
				setup_postdata( $post );
				yield $post;
			}
		} finally { // Once loop is done, or we break out
			wp_reset_postdata();
			// Restore global post
			$post = $save_post;

		}

	}

}
