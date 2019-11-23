<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Pro_Users_Rate' ) ) {

	class Pro_Users_Rate {

		/*
		 * Metabox Hold totla users rating a post in percent
		 *
		 * used to calculate avg_rate by ( TOTAL_RATING / TOTAL_USERS )
		 */
		const TOTAL_RATING = '_par_post_total_users_rating';

		// Metabox Hold numbers of users those rating a post.
		const TOTAL_USERS = '_par_post_total_users';


		// Metabox holds average users rating for post in percent 100
		const AVG_RATING = '_par_post_users_avg_rating';

		/*
		 * Metabox holds individual criteria rate By User save as array
		 *
		 * to get each a criteria value you must Divide it for num user rating
		 */
		const CRITERIA_FIELDS = '_par_post_users_criteria_fields';

		function __construct() {
			add_action( 'init', array( $this, 'init' ) );
		}

		function init() {

			add_action( 'wp_ajax_user_rate_post', array( $this, 'ajax_user_rate_post' ) );
			add_action( 'pra_add_users_rate_template', array( $this, 'add_user_rate_to_author_review_box' ) );
			add_action( 'admin_init', array( $this, 'add_action_when_post_delete' ) );
		}

		/**
		 * Response to ajax in frontend
		 * @since 1.0
		 * @return string die if check ajax false.
		 */
		function ajax_user_rate_post() {

			$post_id = absint( $_POST['post_id'] );
			if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-user-rate-post-nonce-' . $post_id ) || ! is_numeric( $_POST['post_id'] ) ) {
				$args = array(
					'success' => false,
					'message' => esc_html__( 'Your vote could not be added', 'pro-author-review' ),
				);
				echo json_encode( $args );
				wp_die();
			}

			$check_id = get_post( $post_id );

			if ( ! $check_id ) {

				// If Post ID Not found.
				$args = array(
					'success' => false,
					'message' => esc_html__( 'Your vote could not be added', 'pro-author-review' ),
				);
				echo json_encode( $args );
				wp_die();
			}

			if ( ! $this->check_user_rate_post_before( $post_id ) ) {

				$user_individual_criteria = $this->convert_object_to_array( (array) json_decode( stripslashes( $_POST['args'] ) ) );

				if ( ! is_array( $user_individual_criteria ) ) {
					$args = array(
						'success' => false,
						'message' => esc_html__( 'Your vote could not be added', 'pro-author-review' ),
					);
					echo json_encode( $args );
					wp_die();
				}

				$args = $this->add_user_rate_to_post( $post_id, $user_individual_criteria );

				if ( true === $args['success'] ) {
					//
					/*
					 *	make array hold each criteria in post with score and width to update
					 *  Users Rate in frontend using Ajax
					 */
					$criteria_meta = $args['users_criteria'];

					$users_criteria = array();
					foreach ( $criteria_meta as $criteria ) {
						$each_criteria = array(
							'name'  => esc_attr( $criteria['name'] ),
							'slug'  => esc_attr( $criteria['slug'] ),
							'value' => round( $criteria['value'], 1 ),
							'width' => absint( $criteria['value'] / $args['num_of_users'] ),
						);

						if ( 'star' === $args['review_type'] ) {
							$each_criteria['score'] = par_convert_percent_to_star( $criteria['value'] / $args['num_of_users'] );
						} elseif ( 'point' === $args['review_type'] ) {
							$each_criteria['score'] = round( ( $criteria['value'] / $args['num_of_users'] ) / 10, 1 );
						} else {
							$each_criteria['score'] = absint( $criteria['value'] / $args['num_of_users'] ) . '%';
						}
						$users_criteria[] = $each_criteria;
					}

					$args['users_criteria'] = $users_criteria;
					// translators: %s: Number of users rating
					$args['text_users_num'] = sprintf( _n( '%s vote', '%s votes', $args['num_of_users'], 'pro-author-review' ), $args['num_of_users'] );
				}
			} else {
				// if user rate this post before
				$args = array(
					'success' => false,
					'message' => esc_html__( 'You rate this post before.', 'pro-author-review' ),
				);
			}
			if ( function_exists( 'w3tc_flush_post' ) ) {
				w3tc_flush_post( $post_id );
			}

			echo json_encode( $args );
			wp_die();
		}

		/**
		 * Add user rate post
		 *
		 * @since 1.0
		 * @param type int post id.
		 * @param type float user rate.
		 * @return array for ajax response.
		 */
		function add_user_rate_to_post( $post_id, $user_individual_criteria ) {
			global $wpdb;
			/*
			* this $args array return when user add rate success or false
			*/
			$args = array(
				'success'        => false,
				'num_of_users'   => '',
				'avg_rating'     => '',
				'users_criteria' => array(),
				'message'        => '',
				'review_type'    => par_get_post_review_type( $post_id ),
			);

			$avg_rating   = get_post_meta( $post_id, self::AVG_RATING, true );
			$num_of_users = get_post_meta( $post_id, self::TOTAL_USERS, true );
			$rating       = get_post_meta( $post_id, self::TOTAL_RATING, true );

			if ( $num_of_users ) {
				$num_of_users++;
			} else {
				$num_of_users = 1;
				$rating       = 0;
			}

			$criteria_get_slugs_names = $this->get_post_criteria_slug_name( $post_id );
			$criteria_slugs           = $criteria_get_slugs_names['slugs'];
			$criteria_slugs_names     = $criteria_get_slugs_names['names'];
			$user_criteria_rate       = array(); // hold individual criteria rated by user
			$num_of_criteria          = 0;
			$total_criteria_value     = 0;
			$criteria_fields          = get_post_meta( $post_id, self::CRITERIA_FIELDS, true );
			$new_criteria             = array(); // hold slug and value for old criteria and new rate by user

			foreach ( $user_individual_criteria as $criteria ) {

				// Used to check if criteria slugs exists in post criteria fields
				if ( in_array( $criteria['slug'], $criteria_slugs, true ) ) {

					if ( 'percent' === $args['review_type'] ) {
						if ( $criteria['value'] < 0 || $criteria['value'] > 100 ) {
							$criteria['value'] = 50;
						}
						$criteria['value'] = $criteria['value'];
					} elseif ( 'point' === $args['review_type'] ) {
						if ( $criteria['value'] < 0 || $criteria['value'] > 10 ) {
							$criteria['value'] = 5;
						}
						$criteria['value'] = $criteria['value'] * 10;
					} else {
						if ( $criteria['value'] < 0 || $criteria['value'] > 5 ) {
							$criteria['value'] = 2.5;
						}
						$criteria['value'] = $criteria['value'] * 20;
					}

					$user_criteria_rate[] = array(
						'name'  => esc_attr( $this->get_name_of_slug( $criteria_slugs_names, $criteria['slug'] ) ),
						'slug'  => esc_attr( $criteria['slug'] ),
						'value' => absint( $criteria['value'] ),
					);
				}
			}

			if ( $criteria_fields ) {
				foreach ( $criteria_fields as $single_criteria ) {
					foreach ( $user_criteria_rate as $user_criteria ) {
						if ( $single_criteria['slug'] === $user_criteria['slug'] ) {
							$new_criteria[] = array(
								'name'  => esc_attr( $single_criteria['name'] ),
								'slug'  => esc_attr( $single_criteria['slug'] ),
								'value' => absint( $single_criteria['value'] + $user_criteria['value'] ),
							);

							$total_criteria_value += $user_criteria['value'];
							$num_of_criteria++;
						}
					}
				}
			} else {
				error_log( 'here' );
				$new_criteria = $user_criteria_rate;
				foreach ( $new_criteria as $user_criteria ) {
						$total_criteria_value += $user_criteria['value'];
						$num_of_criteria++;
				}
			}

			$user_avg_rate = $total_criteria_value / $num_of_criteria;
			$rating        = $rating + $user_avg_rate;
			$avg_rating    = $rating / $num_of_users;

			if ( is_user_logged_in() ) {
				$user_rate_it = $this->check_user_rate_post_before( $post_id );
				$table_name   = $wpdb->prefix . PRO_AR_USERS_RATE_TABLE;
				$now          = current_time( 'mysql' );
				if ( $user_rate_it ) {
					// update user rate
					$wpdb->update(
						$table_name,
						array(
							'post_id'       => absint( $post_id ),
							'user_id'       => absint( get_current_user_id() ),
							'criteria_rate' => maybe_serialize( $user_criteria_rate ),
							'avg_rate'      => round( $user_avg_rate, 2 ),
							'rate_modified' => $now,
						),
						array(
							'post_id' => absint( $post_id ),
							'user_id' => absint( get_current_user_id() ),
						)
					);

				} else {
					// add new user rate
					$wpdb->insert(
						$table_name,
						array(
							'post_id'       => absint( $post_id ),
							'user_id'       => absint( get_current_user_id() ),
							'criteria_rate' => maybe_serialize( $user_criteria_rate ),
							'avg_rate'      => round( $user_avg_rate, 2 ),
							'rate_date'     => $now,
							'rate_modified' => $now,
						),
						array(
							'%d',
							'%d',
							'%s',
							'%d',
							'%s',
							'%s',
						)
					);
				}

				update_post_meta( $post_id, self::TOTAL_RATING, round( $rating, 2 ) );
				update_post_meta( $post_id, self::TOTAL_USERS, absint( $num_of_users ) );
				update_post_meta( $post_id, self::AVG_RATING, round( $avg_rating, 2 ) );
				update_post_meta( $post_id, self::CRITERIA_FIELDS, $new_criteria );

				if ( 'percent' === $args['review_type'] ) {
					$avg_rating = absint( $avg_rating );
				} elseif ( 'point' === $args['review_type'] ) {
					$avg_rating = par_convert_percent_to_point( $avg_rating );
				} elseif ( 'star' === $args['review_type'] ) {
					$avg_rating = par_convert_percent_to_star( $avg_rating );
				}

				$args['success']        = true;
				$args['num_of_users']   = absint( $num_of_users );
				$args['avg_rating']     = $avg_rating;
				$args['users_criteria'] = $new_criteria;
				$args['message']        = esc_html__( 'Thank you to rate this post', 'pro-author-review' );
			} else {
				// if only registered users can rate this post.
				$args['success'] = false;
				$args['message'] = esc_html__( 'Only logged in user can rate this post', 'pro-author-review' );
			}

			return $args;
		}

		/**
		 * check if user rate this post before
		 *
		 * @since 1.0
		 * @param type int post id.
		 * @return boolean true if user rate post before.
		 */
		private function check_user_rate_post_before( $post_id ) {
			global $wpdb;

			if ( is_user_logged_in() ) {

				$table_name   = $wpdb->prefix . PRO_AR_USERS_RATE_TABLE;
				$user_rate_it = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM `$table_name` WHERE `post_id` = %d AND `user_id` = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						absint( $post_id ),
						get_current_user_id()
					)
				);

				if ( $user_rate_it ) {
					return true;
				} else {
					return false;
				}
			}

			return false;
		}

		/**
		 * Convert Object to arry.
		 * used when user rate individual criteria.
		 *
		 * @since 1.1
		 * @return type array.
		 */
		function convert_object_to_array( $obj ) {
			$criteria = array();
			foreach ( $obj as $item ) {
				$criteria[] = array(
					'slug'  => $item->slug,
					'value' => $item->value,
				);
			}

			return $criteria;
		}

		/**
		 * Convert Object to arry.
		 * used when user rate individual criteria.
		 *
		 * @since 1.1
		 * @return type array all slug of criteria to rate by user .
		 */
		function get_post_criteria_slug_name( $post_id ) {

			$slugs           = array();
			$names           = array();
			$criteria_fields = get_post_meta( $post_id, Pro_Author_Review::CRITERIA_FIELDS, true );
			foreach ( $criteria_fields as $review ) {
					$slugs[] = $review['slug'];
					$names[] = array(
						'slug' => $review['slug'],
						'name' => $review['name'],
					);
			}
			$names_slugs = array(
				'slugs' => $slugs,
				'names' => $names,
			);

			return $names_slugs;
		}

		/**
		 * @since 1.1
		 *
		 * @return type string label for slug.
		 */
		function get_name_of_slug( $names_slugs, $slug ) {

			if ( $names_slugs && is_array( $names_slugs ) ) {
				foreach ( $names_slugs as  $review ) {

					if ( $review['slug'] === $slug ) {
						return $review['name'];
					}
				}
			}

			return false;
		}

		/**
		 * @since 1.0
		 *
		 * @return type string HTML for rate.
		 */
		function add_user_rate_to_author_review_box() {
			if ( is_singular() ) {
				$tpl = $this->get_users_rate_template();
				echo $tpl;
			}

			return false;
		}
		/**
		 * display star rate in post
		 *
		 * @since 1.0
		 * @param type int Post id
		 * @return type string HTML for rate.
		 */
		function get_users_rate_template() {
			$located = par_author_review_get_template( 'users-rate-template.php' );
			ob_start();
			require( $located );
			$content = ob_get_clean();

			return $content;
		}

		function add_action_when_post_delete() {
			add_action( 'delete_post', array( $this, 'delete_users_rate' ), 10 );
		}

		function delete_users_rate( $post_id ) {
			global $wpdb;

			$table_name = $wpdb->prefix . PRO_AR_USERS_RATE_TABLE;
			$wpdb->delete(
				$table_name,
				array(
					'post_id' => absint( $post_id ),
				)
			);
		}

	} // EOF Class

	global $par_users_rate;
	$par_users_rate = new Pro_Users_Rate();
}
