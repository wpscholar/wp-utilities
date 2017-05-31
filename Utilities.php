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