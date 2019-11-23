<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'par_post_has_review' ) ) {
	/**
	 * Check if a post has review.
	 *
	 * @return Boolean.
	 */
	function par_post_has_review( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		} else {
			$post_id = absint( $post_id );
		}

		$has_review = get_post_meta( $post_id, '_par_post_has_review', true );
		if ( isset( $has_review ) && $has_review ) {
			return true;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'par_get_post_review_data' ) ) {

	function par_get_post_review_data( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		} else {
			$post_id = absint( $post_id );
		}

		if ( empty( $post_id ) ) {
			return;
		}

		$review_data = new Pro_Author_Review();
		$review_data = $review_data->get_post_review_data( $post_id );

		return $review_data;
	}
}

if ( ! function_exists( 'par_get_post_review_type' ) ) {
	/**
	 * Check if a post has review.
	 *
	 * @return Boolean.
	 */
	function par_get_post_review_type( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		} else {
			$post_id = absint( $post_id );
		}

		$review_data = get_post_meta( $post_id, '_par_post_review_data', true );
		$review_tpl  = $review_data['tpl'] ?? '';

		if ( ! empty( $review_tpl ) ) {
			$tpl_review_meta = get_post_meta( $review_tpl, '_par_post_review_data', true );
			$review_type     = $tpl_review_meta['type'] ?? '';
		} else {
			$review_type = $review_data['type'] ?? '';
		}

		return $review_type;
	}
}

if ( ! function_exists( 'par_convert_percent_to_point' ) ) {

	/**
	 * Helper function.
	 * convert percent to point
	 * @return $value.
	 */
	function par_convert_percent_to_point( $value ) {
		if ( is_numeric( $value ) ) {
			return round( $value / 10, 1 );
		} else {
			return 0;
		}
	}
}
if ( ! function_exists( 'par_convert_percent_to_star' ) ) {

	/**
	 * Helper function.
	 * convert percent to star
	 * @return $value.
	 */
	function par_convert_percent_to_star( $value ) {
		if ( is_numeric( $value ) ) {
			$review_star = round( $value / 20, 1 );
			return $review_star;
		} else {
			return 0;
		}
	}
}

/**
 * Global Functions
 * - used to access function in class Sama_Author_review
 * - Use  this function in your Theme to easy access class
 */

if ( ! function_exists( 'par_get_average_author_rating' ) ) {

	/**
	 * Get Average Total Review.
	 *
	 * @since   1.0
	 * @param   type $review_items array for review items in single post.
	 * @return  Average total review in single post.
	 */
	function par_get_average_author_rating( $args ) {

		$defaults = array(
			'post_id'     => null,
			'review_type' => 'percent',
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		$meta_key = '_par_post_total_author_review';

		if ( null === $post_id ) {
			global $post;
			$post_id = $post->ID;
		} else {
			$post_id = absint( $post_id );
		}
		$total_author_review = get_post_meta( $post_id, $meta_key, true );
		if ( 'star' === $review_type ) {
			return par_convert_percent_to_star( $total_author_review );
		} elseif ( 'point' === $review_type ) {
			return par_convert_percent_to_point( $total_author_review );
		} else {
			return absint( $total_author_review );
		}
	}
}

if ( ! function_exists( 'par_get_average_users_rate' ) ) {

	/**
	 * Get Average Total Review.
	 *
	 * @since   1.0
	 * @param   type $review_items array for review items in single post.
	 * @return  Average total review in single post.
	 */
	function par_get_average_users_rate( $args ) {

		$defaults = array(
			'post_id'     => null,
			'review_type' => 'percent',
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		$meta_key = '_par_post_users_avg_rating';

		if ( null === $post_id ) {
			global $post;
			$post_id = $post->ID;
		} else {
			$post_id = absint( $post_id );
		}
		$total_author_review = get_post_meta( $post_id, $meta_key, true );
		if ( 'star' === $review_type ) {
			return par_convert_percent_to_star( $total_author_review );
		} elseif ( 'point' === $review_type ) {
			return par_convert_percent_to_point( $total_author_review );
		} else {
			return absint( $total_author_review );
		}
	}
}

if ( ! function_exists( 'par_author_review_get_template' ) ) {
	/**
	 * include template and check if it exists in a theme or child theme
	 *
	 * @since 1.0
	 * @return type string template path.
	 */
	function par_author_review_get_template( $template_name ) {

		$template = locate_template( 'pro-author-review/' . $template_name, false );
		if ( empty( $template ) ) {
			$template = PRO_AR_DIR . 'templates/' . $template_name;
		}

		return $template;
	}
}

if ( ! function_exists( 'par_wp_review_option' ) ) {

	function par_wp_review_option() {
		$defaults = array(
			'post_type'      => array( 'post' ),
			'login_page_url' => '',
			'add_to_content' => true,
			'version'        => PRO_AR_VERSION,
			'db_version'     => PRO_AR_DB_VERSION,
		);

		$options = get_option( 'pro_author_review', array() );

		return wp_parse_args( $options, $defaults );
	}
}
