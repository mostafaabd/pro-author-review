<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Pro_Author_Review_API' ) ) {

	class Pro_Author_Review_API {

		function __construct() {
			add_action( 'rest_api_init', array( $this, 'add_author_review_api' ) );
		}

		function add_author_review_api() {

			$options    = par_wp_review_option();
			$post_types = $options['post_type'] ?? array( 'post' );

			register_rest_field(
				$post_types,
				'par_post_has_review',
				array(
					'get_callback' => array( $this, 'get_post_has_review' ),
				)
			);

			register_rest_field(
				$post_types,
				'par_post_review_data',
				array(
					'get_callback' => array( $this, 'get_post_review_data' ),
				)
			);
		}

		function get_post_has_review( $object ) {
			return par_post_has_review( $object['id'] );
		}

		function get_post_review_data( $object ) {

			$review_data = false;

			if ( par_post_has_review( $object['id'] ) ) {
				return par_get_post_review_data( $object['id'] );
			}

			return $review_data;
		}
	}
	$pra_author_review_api = new Pro_Author_Review_API();
}
