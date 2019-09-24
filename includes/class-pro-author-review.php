<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Pro_Author_Review' ) ) {

	class Pro_Author_Review {

		// Metabox uses to check post have a review.
		const HAS_REVIEW = '_par_post_has_review';

		/*
		 * 	Metabox uses to hold all post review informatio in array
		 *	- who can review post
		 *	- Review Title
		 *	- Text under total	ex: excellent , Very Good, Good.
		 *	- Review Template	for review register post type review
		 *	- Review type		[ star - point - percent ]
		 *	- Review position 	[ top - bottom - shortcode or function ]
		 *	Used in meatbox
		 */
		const REVIEW_KEY = '_par_post_review_data';


		// Metabox holds all author review Criteria Fields in array.
		const CRITERIA_FIELDS = '_par_post_review_criteria_fields';

		// Metabox holds Average rating for author review in percent.
		const TOTAL_REVIEW_KEY = '_par_post_total_author_review';

		function __construct() {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'init', array( $this, 'add_thumbnail_size' ) );
			add_action( 'init', array( $this, 'register_cutom_review_post_type' ) );
		}

		function init() {
			load_plugin_textdomain( 'pro-author-review', false, PRO_AR_URI . '/languages/' );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ), 500 );
			add_filter( 'the_content', array( $this, 'add_author_review_to_content' ) );
			add_action( 'wp_footer', array( $this, 'output_rating_schema_js' ) );
		}

		function add_thumbnail_size() {
			add_image_size( 'par-thumb', 72, 72, true );
		}

		/**
		 * custom post type review
		 * @since 1.0
		 * @return void
		 */
		function register_cutom_review_post_type() {

			$labels = array(
				'name'               => esc_html_x( 'Review', 'post type general name', 'pro-author-review' ),
				'singular_name'      => esc_html_x( 'Review', 'post type singular name', 'pro-author-review' ),
				'menu_name'          => esc_html_x( 'Reviews', 'admin menu', 'pro-author-review' ),
				'name_admin_bar'     => esc_html_x( 'Review', 'add new on admin bar', 'pro-author-review' ),
				'add_new'            => esc_html_x( 'Add new', 'Review', 'pro-author-review' ),
				'add_new_item'       => esc_html__( 'Add new review', 'pro-author-review' ),
				'new_item'           => esc_html__( 'New review', 'pro-author-review' ),
				'edit_item'          => esc_html__( 'Edit review', 'pro-author-review' ),
				'view_item'          => esc_html__( 'View review', 'pro-author-review' ),
				'all_items'          => esc_html__( 'All reviews', 'pro-author-review' ),
				'search_items'       => esc_html__( 'Search reviews', 'pro-author-review' ),
				'parent_item_colon'  => esc_html__( 'Parent reviews:', 'pro-author-review' ),
				'not_found'          => esc_html__( 'No reviews found.', 'pro-author-review' ),
				'not_found_in_trash' => esc_html__( 'No reviews found in trash.', 'pro-author-review' ),
			);

			$post_type_args = array(
				'public'              => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_nav_menus'   => true,
				'menu_position'       => 5,
				'menu_icon'           => 'dashicons-star-filled',
				'hierarchical'        => false,
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'rewrite'             => false,
				'query_var'           => false,
				'has_archive'         => false,
				'label'               => 'Review',
				'labels'              => $labels,
			);

			register_post_type( 'parreview', $post_type_args );
		}

		/**
		 * enqueue styles & scripts used in frontend
		 *
		 * @since 1.0
		 */
		function enqueue_scripts_styles() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'pro-author-review', PRO_AR_URI . '/assets/css/front/author-review' . $suffix . '.css', '', PRO_AR_VERSION );
			if ( is_singular() ) {
				$post_id = get_the_ID();
				if ( $this->has_review( $post_id ) ) {

					wp_enqueue_script( 'jquery-ui-slider' );
					if ( is_rtl() ) {
						wp_enqueue_script( 'jquery.ui.slider-rtl', PRO_AR_URI . '/assets/js/rtl/jquery.ui.slider-rtl' . $suffix . '.js', '', PRO_AR_VERSION, true );
					}
					wp_enqueue_script( 'pro-author-review', PRO_AR_URI . '/assets/js/front/author-review' . $suffix . '.js', '', PRO_AR_VERSION, true );
					$ajax_vars = array(
						'url'   => admin_url( 'admin-ajax.php' ),
						'nonce' => wp_create_nonce( 'ajax-user-rate-post-nonce-' . $post_id ),
					);
					// see author-review.php
					wp_localize_script( 'pro-author-review', 'ajax_user_rate', $ajax_vars );
				}
			}
		}

		/**
		 * Hold array of review box position in single post.
		 *
		 * @since  1.0
		 * @return array of review position.
		 */
		function review_position_type() {

			$review_position = array(
				array(
					'label' => esc_html__( 'Top', 'pro-author-review' ),
					'value' => 'top',
				),
				array(
					'label' => esc_html__( 'Bottom', 'pro-author-review' ),
					'value' => 'bottom',
				),
				array(
					'label' => esc_html__( 'Use shortcode or function', 'pro-author-review' ),
					'value' => 'shortcode',
				),
			);

			return apply_filters( 'pra_author_review_position', $review_position );
		}

		/**
		 * Hold array of review type.
		 *
		 * @since 1.0
		 * @return array of review type.
		 */
		function review_type() {

			$review_type = array(
				array(
					'label' => esc_html__( 'Star', 'pro-author-review' ),
					'value' => 'star',
				),
				array(
					'label' => esc_html__( 'Point', 'pro-author-review' ),
					'value' => 'point',
				),
				array(
					'label' => esc_html__( 'percent', 'pro-author-review' ),
					'value' => 'percent',
				),
			);

			return apply_filters( 'pra_author_review_type', $review_type );
		}

		/**
		 * Hold array of Label position only if percent or point.
		 *
		 * @since  1.0
		 * @return array of review position.
		 */
		function who_can_review() {

			$display = array(
				array(
					'label' => esc_html__( 'Author review only', 'pro-author-review' ),
					'value' => 'author',
				),
				array(
					'label' => esc_html__( 'Users rate only', 'pro-author-review' ),
					'value' => 'users',
				),
				array(
					'label' => esc_html__( 'Author review and users rate', 'pro-author-review' ),
					'value' => 'both',
				),
			);

			return apply_filters( 'pra_author_review_who_can_review', $display );
		}

		/**
		 * Check if post has review.
		 *
		 * @since  1.0
		 * @return Boolean.
		 */
		function has_review( $post_id = null ) {

			if ( ! $post_id ) {
				$post_id = get_the_ID();
			} else {
				$post_id = $post_id;
			}

			$has_review = get_post_meta( $post_id, self::HAS_REVIEW, true );
			if ( isset( $has_review ) && $has_review ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Check Post Review Position.
		 *
		 * @since  1.0
		 * @param  string $position.
		 * @return string for success or false.
		 */
		function get_review_position( $post_id = null ) {

			if ( ! $post_id ) {
				$post_id = get_the_ID();
			} else {
				$post_id = $post_id;
			}

			$position    = '';
			$review_meta = get_post_meta( $post_id, self::REVIEW_KEY, true );
			$post_tpl    = isset( $review_meta['tpl'] ) ? $review_meta['tpl'] : '';

			if ( ! empty( $post_tpl ) ) {
				$tpl_review_meta = get_post_meta( $post_tpl, self::REVIEW_KEY, true );
				$position        = isset( $tpl_review_meta['position'] ) ? $tpl_review_meta['position'] : '';
			}

			if ( ! empty( $review_meta ) && ! empty( $position ) ) {
				return $position;
			} else {
				return false;
			}
		}

		/**
		 * Add Author Review Box To post content.
		 *
		 * @since   1.0
		 * @param   string $content post content.
		 * @return  $content.
		 */
		function add_author_review_to_content( $content ) {

			if ( is_single() && $this->has_review() ) {
				if ( 'top' === $this->get_review_position() ) {
					$content = $this->get_html_review_content() . $content;
				} elseif ( 'bottom' === $this->get_review_position() ) {
					$content .= $this->get_html_review_content();
				}
			}

			return $content;
		}

		/**
		 * get post review data.
		 * All reurn data must be validate.
		 *
		 * @since 1.0
		 * @return array.
		 */
		function get_post_review_data( $post_id = null ) {
			global $par_users_rate;

			if ( ! $post_id ) {
				$post_id = get_the_ID();
			} else {
				$post_id = $post_id;
			}

			$author_review_meta     = get_post_meta( $post_id, self::REVIEW_KEY, true );
			$author_criteria_fields = get_post_meta( $post_id, self::CRITERIA_FIELDS, true );
			$post_tpl               = isset( $author_review_meta['tpl'] ) ? $author_review_meta['tpl'] : '';
			$review_title           = isset( $author_review_meta['title'] ) ? $author_review_meta['title'] : '';
			$txt_under_total        = isset( $author_review_meta['text_under_total'] ) ? $author_review_meta['text_under_total'] : '';
			$description            = isset( $author_review_meta['description'] ) ? $author_review_meta['description'] : '';
			$pros                   = isset( $author_review_meta['pros'] ) ? $author_review_meta['pros'] : array();
			$cons                   = isset( $author_review_meta['cons'] ) ? $author_review_meta['cons'] : array();
			$aff_text               = isset( $author_review_meta['aff_txt'] ) ? $author_review_meta['aff_txt'] : '';
			$aff_url                = isset( $author_review_meta['aff_url'] ) ? $author_review_meta['aff_url'] : '';

			if ( ! empty( $post_tpl ) ) {
				$tpl_review_meta = get_post_meta( $post_tpl, self::REVIEW_KEY, true );
				$review_type     = isset( $tpl_review_meta['type'] ) ? ( $tpl_review_meta['type'] ) : 'percent';
				$who_can_review  = isset( $tpl_review_meta['who_can_review'] ) ? $tpl_review_meta['who_can_review'] : 'authoronly';
				$position        = isset( $tpl_review_meta['position'] ) ? $tpl_review_meta['position'] : '';
				$css_class       = isset( $tpl_review_meta['css_class'] ) ? $tpl_review_meta['css_class'] : '';
			} else {
				// Get these values from post if custom post review delete
				$tpl_review_meta = get_post_meta( $post_id, self::REVIEW_KEY, true );
				$review_type     = isset( $tpl_review_meta['type'] ) ? ( $tpl_review_meta['type'] ) : 'percent';
				$who_can_review  = isset( $tpl_review_meta['who_can_review'] ) ? $tpl_review_meta['who_can_review'] : 'author';
				$position        = isset( $tpl_review_meta['position'] ) ? $tpl_review_meta['position'] : '';
				$css_class       = isset( $tpl_review_meta['css_class'] ) ? $tpl_review_meta['css_class'] : '';
			}

			$i = 0;
			foreach ( $author_criteria_fields as $criteria ) {

				if ( 'star' === $review_type ) {
					$author_criteria_fields[ $i ]['value'] = par_convert_percent_to_star( $criteria['value'] );
				} elseif ( 'point' === $review_type ) {
					$author_criteria_fields[ $i ]['value'] = par_convert_percent_to_point( $criteria['value'] );
				} else {
					$author_criteria_fields[ $i ]['value'] = round( ( $criteria['value'] ) );
				}
				$author_criteria_fields[ $i ]['name'] = esc_attr( $criteria['name'] );
				$author_criteria_fields[ $i ]['slug'] = esc_attr( $criteria['slug'] );
				$i++;
			}

			if ( ! empty( $pros ) ) {
				$pros = preg_split( '/\r\n|\r|\n/', $pros );
			} else {
				$pros = array();
			}
			if ( ! empty( $cons ) ) {
				$cons = preg_split( '/\r\n|\r|\n/', $cons );
			} else {
				$cons = array();
			}

			$review_data = array(
				'post_id'                => absint( $post_id ),
				'css_class'              => sanitize_html_class( $css_class ),
				'review_title'           => sanitize_title( $review_title ),
				'review_type'            => sanitize_title( $review_type ),
				'who_can_review'         => esc_attr( $who_can_review ),
				'review_position'        => esc_attr( $position ),
				'txt_under_total'        => esc_attr( $txt_under_total ),
				'description'            => esc_textarea( $description ),
				'pros'                   => array_map( 'esc_attr', $pros ),
				'cons'                   => array_map( 'esc_attr', $cons ),
				'aff_text'               => esc_attr( $aff_text ),
				'aff_url'                => esc_url( $aff_url ),
				'author_criteria_fields' => $author_criteria_fields,
				'author_avg_rating'      => floatval(
					par_get_average_author_rating(
						array(
							'post_id'     => $post_id,
							'review_type' => $review_type,
						)
					)
				),
			);

			if ( 'both' === $who_can_review || 'users' === $who_can_review ) {
				$plugin_options        = par_wp_review_option();
				$users_avg_rating      = get_post_meta( get_the_ID(), $par_users_rate::AVG_RATING, true );
				$num_of_users          = get_post_meta( get_the_ID(), $par_users_rate::TOTAL_USERS, true );
				$users_criteria_fields = get_post_meta( get_the_ID(), $par_users_rate::CRITERIA_FIELDS, true );

				if ( 'star' === $review_type ) {
					$users_avg_rating = par_convert_percent_to_star( $users_avg_rating );
				} elseif ( 'point' === $review_type ) {
					$users_avg_rating = par_convert_percent_to_point( $users_avg_rating );
				}

				if ( $num_of_users ) {

					$i = 0;
					foreach ( $users_criteria_fields as $criteria ) {

						if ( 'star' === $review_type ) {
							$users_criteria_fields[ $i ]['value'] = par_convert_percent_to_star( $criteria['value'] / $num_of_users );
						} elseif ( 'point' === $review_type ) {
							$users_criteria_fields[ $i ]['value'] = par_convert_percent_to_point( $criteria['value'] / $num_of_users );
						} else {
							$users_criteria_fields[ $i ]['value'] = round( ( $criteria['value'] / $num_of_users ) );
						}
						$users_criteria_fields[ $i ]['name'] = esc_attr( $criteria['name'] );
						$users_criteria_fields[ $i ]['slug'] = esc_attr( $criteria['slug'] );
						$i++;
					}
				} else {

					$i                     = 0;
					$users_criteria_fields = $author_criteria_fields;
					foreach ( $users_criteria_fields as $criteria ) {
						$users_criteria_fields[ $i ]['value'] = 0;
						$i++;
					}
				}

				$review_data['login_page_url']        = esc_url( $plugin_options['login_page_url'] );
				$review_data['users_avg_rating']      = ( $users_avg_rating ) ? floatval( $users_avg_rating ) : 0;
				$review_data['num_of_users']          = ( $num_of_users ) ? absint( $num_of_users ) : 0;
				$review_data['users_criteria_fields'] = $users_criteria_fields;
			}

			if ( 'users' === $who_can_review ) {
				$review_data['author_avg_rating'] = 0;
			}

			return $review_data;
		}
		/**
		 * Display author review box in single post
		 *
		 * @since 1.0
		 * @return HTML for author review box.
		 */
		function get_html_review_content( $post_id = null ) {

			if ( ! $post_id ) {
				$post_id = get_the_ID();
			} else {
				$post_id = $post_id;
			}

			if ( $this->has_review( $post_id ) ) {

				global $par_post_review_data;
				$par_post_review_data = $this->get_post_review_data( $post_id );

				if ( 'percent' === $par_post_review_data['review_type'] ) {
					return $this->get_template( 'percent.php' );
				} elseif ( 'point' === $par_post_review_data['review_type'] ) {
					return $this->get_template( 'point.php' );
				} else {
					return $this->get_template( 'star.php' );
				}
			}

			return false;
		}

		/**
		 * include review Template
		 *
		 * @since  1.0
		 * @return HTML for author review box.
		 */
		function get_template( $template_name ) {

			if ( empty( $template_name ) ) {
				return;
			}
			$located = par_author_review_get_template( $template_name );
			ob_start();
			require( $located );
			$content = ob_get_clean();

			return $content;
		}

		function output_rating_schema_js() {
			$output = '';
			if ( is_page() || is_attachment() ) {
				return;
			}
			if ( is_singular() ) {
				$id = get_the_ID();
				if ( $this->has_review( $id ) ) {
					$total_author_review = get_post_meta( $id, self::TOTAL_REVIEW_KEY, true );
					$author_review_meta  = get_post_meta( $id, self::REVIEW_KEY, true );
					$description         = $author_review_meta['description'];
					if ( empty( $description ) ) {
						$description = get_the_excerpt( $id );
					}

					$output .= '<script type="application/ld+json">
							{
								"@context": "http://schema.org",
								"@type": "Review",
								"author": {
									"@type":"Person",
									"name":"' . get_the_author() . '"
								},	
								"url": "' . esc_url( get_permalink( $id ) ) . '",
								"datePublished":"' . get_the_time( 'c', $id ) . '",
								"publisher": {
									"@type":"Organization",
									"name":"' . get_bloginfo( 'name' ) . '"
								},
								"description":"' . esc_attr( $description ) . '",
								"itemReviewed": {
									"@type":"Thing",
									"name": "' . get_the_title( $id ) . '",
									"image": "' . get_the_post_thumbnail_url( $id, 'full' ) . '"
								},
								"reviewRating": {
									"@type":"Rating",
									"worstRating":1,
									"bestRating":5,
									"ratingValue":' . par_convert_percent_to_star( $total_author_review ) . '
								}
							}
							</script>';
					echo $output;
				}
			}
		}
	}
	global $pra_author_review;
	$pra_author_review = new Pro_Author_Review();
}
