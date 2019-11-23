<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'PRO_Author_Review_Metabox' ) ) {

	class PRO_Author_Review_Metabox {

		function __construct() {
			add_action( 'init', array( $this, 'init' ) );
		}

		function init() {
			add_action( 'add_meta_boxes', array( $this, 'add_review_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_review_meta_data' ), 10, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_styles' ) );
			add_action( 'wp_ajax_get_review_template_using_ajax', array( $this, 'get_review_template_using_ajax' ) );
		}

		function admin_enqueue_scripts_styles() {
			global $pagenow, $post;
			$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$options      = par_wp_review_option();
			$posts_type   = $options['post_type'] ?? array( 'post' );
			$posts_type[] = 'parreview';
			if ( ! empty( $pagenow ) && 'post-new.php' === $pagenow || 'post.php' === $pagenow ) {
				if ( in_array( $post->post_type, $posts_type, true ) ) {
					wp_enqueue_style( 'select2', PRO_AR_URI . '/assets/css/admin/select2' . $suffix . '.css', '', '4.0.3' );
					wp_enqueue_style( 'reviewadmin', PRO_AR_URI . '/assets/css/admin/review' . $suffix . '.css', '', PRO_AR_VERSION );
					wp_enqueue_script( 'select2', PRO_AR_URI . '/assets/js/admin/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.3', true );
					wp_enqueue_script( 'jquery-ui-slider' );
					if ( is_rtl() ) {
						wp_enqueue_script( 'jquery.ui.slider-rtl', PRO_AR_URI . '/assets/js/rtl/jquery.ui.slider-rtl' . $suffix . '.js', '', PRO_AR_VERSION, true );
					}
					wp_enqueue_script( 'reviewadmin', PRO_AR_URI . '/assets/js/admin/author-review-admin' . $suffix . '.js', array( 'select2' ), PRO_AR_VERSION, true );
					wp_localize_script(
						'reviewadmin',
						'get_review_template',
						array(
							'ajaxurl' => admin_url( 'admin-ajax.php' ),
						)
					);
				}
			}
		}

		function add_review_meta_box() {
			$options    = par_wp_review_option();
			$posts_type = $options['post_type'] ?? array( 'post' );

			// Add Metabox for custom post type review
			add_meta_box(
				'pro_author_review_meta',
				esc_html__( 'Review templates', 'pro-author-review' ),
				array( $this, 'review_template_meta_box' ),
				'parreview',
				'normal',
				'high'
			);

			// Add Metabox for post & page & ....
			foreach ( $posts_type as $posttype ) {
				add_meta_box(
					'pro_author_review_meta',
					esc_html__( 'Review options', 'pro-author-review' ),
					array( $this, 'review_meta_box' ),
					$posttype,
					'normal',
					'high'
				);
			}
		}

		function review_template_meta_box() {
			global $post;

			$pra_author_review    = new Pro_Author_Review();
			$get_review_types     = $pra_author_review->review_type();
			$get_review_positions = $pra_author_review->review_position_type();
			$get_who_can_review   = $pra_author_review->who_can_review();

			$wp_review_meta  = get_post_meta( $post->ID, Pro_Author_Review::REVIEW_KEY, true );
			$criteria_fields = get_post_meta( $post->ID, Pro_Author_Review::CRITERIA_FIELDS, true );
			$total           = get_post_meta( $post->ID, Pro_Author_Review::TOTAL_REVIEW_KEY, true );

			$who_can_review   = $wp_review_meta['who_can_review'] ?? 'both';
			$review_type      = $wp_review_meta['type'] ?? 'star';
			$currentposition  = $wp_review_meta['position'] ?? '';
			$css_class        = $wp_review_meta['css_class'] ?? '';
			$review_title     = $wp_review_meta['title'] ?? '';
			$text_under_total = $wp_review_meta['text_under_total'] ?? '';
			$total            = absint( $total ) ?? 0;
			// Output HTML MetaBox
			$output  = '<div id="pro-author-review-box" class="pro-author-review-box">';
			$output .= wp_nonce_field( 'par_post_review_tpl_nonce_action', 'par_post_review_tpl_nonce_name', true, false );
			$output .= '<div id="review-wrapper">';
			$output .= '<div class="review-field">';
			$output .= self::who_can_review_title_field();
			$output .= '<span class="second"><select name="_par_post_review_data[who_can_review]" id="who_can_review">';
			foreach ( $get_who_can_review as $item ) {
				$selected = '';
				if ( $who_can_review === $item['value'] ) {
					$selected = 'selected';
				}
				$output .= '<option value="' . esc_attr( $item['value'] ) . '"' . $selected . '>' . esc_attr( $item['label'] ) . '</option>';
			}
			$output .= '</select></span></div>';
			$output .= '<div class="review-field">';
			$output .= self::get_review_type_title();
			$output .= '<span class="second"><select name="_par_post_review_data[type]" id="review_type">';
			foreach ( $get_review_types as $item ) {
				$selected = '';
				if ( $review_type === $item['value'] ) {
					$selected = 'selected';
				}
				$output .= '<option value="' . esc_attr( $item['value'] ) . '"' . $selected . '>' . esc_attr( $item['label'] ) . '</option>';
			}
			$output .= '</select></span></div>';
			$output .= '<div class="review-field">';
			$output .= self::get_review_position_title();
			$output .= '<span class="second"><select name="_par_post_review_data[position]" id="review-position">';
			foreach ( $get_review_positions as $position ) {
				$selected = '';
				if ( $currentposition === $position['value'] ) {
					$selected = 'selected';
				}
				$output .= '<option value="' . esc_attr( $position['value'] ) . '"' . $selected . '>' . esc_attr( $position['label'] ) . '</option>';
			}
			$output .= '</select></span></div>';
			$output .= '<div class="review-field">';
			$output .= '<span class="first"><label for="review-css-class">' . esc_html__( 'CSS class', 'pro-author-review' ) . '</label></span>';
			$output .= '<span class="second"><input type="text" name="_par_post_review_data[css_class]" id="review-css-class" value="' . esc_attr( $css_class ) . '"/></span>';
			$output .= '</div>';
			$output .= self::get_review_title_field( $review_title );
			$output .= self::get_text_under_total_field( $text_under_total );
			$output .= '<div class="rating-review-item criteria-fields">';
			$output .= '<div class="info">';
			$output .= '<p><strong>' . esc_html__( 'Important', 'pro-author-review' ) . '&#160;!</strong>&#160;' . esc_html__( 'Slug name must be in English only and Unique in this template, feel free to repeat it in other template', 'pro-author-review' ) . '.</p>';
			$output .= '</div>';
			$output .= '<ul id="criteria-items" class="custom_repeatable">';
			if ( $criteria_fields ) {
				foreach ( $criteria_fields as $row ) {
					$output .= '<li class="ui-state-default hndle"><span class="">|||</span>';
					$output .= self::get_criteria_text_field();
					$output .= '<input type="text" name="criteria_name[]" value="' . esc_attr( $row['name'] ) . '" size="30" />';
					$output .= self::get_criteria_slug_field();
					$output .= '<input type="text" name="criteria_slug[]" value="' . esc_attr( $row['slug'] ) . '" size="30" />';
					$output .= '<a class="repeatable-remove button remove-row" href="#">-</a></li>';
				}
			} else {
				$output .= self::get_empty_criteria_field( false );
			}

			$output .= self::get_empty_criteria_field( true );
			$output .= '</ul></div><p><a id="add-row" class="button" href="#">Add criteria</a></p></div>';

			echo $output;
		}

		function review_meta_box() {
			global $post;

			$who_can_review  = '';
			$review_type     = '';
			$currentposition = '';
			$has_review      = get_post_meta( $post->ID, Pro_Author_Review::HAS_REVIEW, true );
			$wp_review_meta  = get_post_meta( $post->ID, Pro_Author_Review::REVIEW_KEY, true );
			$criteria_fields = get_post_meta( $post->ID, Pro_Author_Review::CRITERIA_FIELDS, true );
			$total           = get_post_meta( $post->ID, Pro_Author_Review::TOTAL_REVIEW_KEY, true );
			$current_tpl     = $wp_review_meta['tpl'] ?? '';

			// get this values from template
			if ( ! empty( $current_tpl ) ) {
				$tpl_review_meta = get_post_meta( $current_tpl, Pro_Author_Review::REVIEW_KEY, true );
				$who_can_review  = $tpl_review_meta['who_can_review'] ?? 'authoronly';
				$review_type     = $tpl_review_meta['type'] ?? 'percent';
				$currentposition = $tpl_review_meta['position'] ?? '';

				if ( 'author' === $who_can_review ) {
					$who_can_review_title = esc_html__( 'Author only', 'pro-author-review' );
				} elseif ( 'users' === $who_can_review ) {
					$who_can_review_title = esc_html__( 'Users rate', 'pro-author-review' );
				} else {
					$who_can_review_title = esc_html__( 'Author review and users rate', 'pro-author-review' );
				}
			}

			$has_review       = $has_review ?? false;
			$review_title     = $wp_review_meta['title'] ?? '';
			$text_under_total = $wp_review_meta['text_under_total'] ?? '';
			$total            = ( $total ) ? absint( $total ) : '';
			$description      = $wp_review_meta['description'] ?? '';
			$pros             = $wp_review_meta['pros'] ?? '';
			$cons             = $wp_review_meta['cons'] ?? '';
			$aff_txt          = $wp_review_meta['aff_txt'] ?? '';
			$aff_url          = $wp_review_meta['aff_url'] ?? '';

			// get templates
			$query_args = array(
				'post_type'           => 'parreview',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => -1,
			);

			$templates   = array();
			$reviews_tpl = get_posts( $query_args );

			if ( $reviews_tpl ) {
				foreach ( $reviews_tpl as $tpl ) {
					//setup_postdata( $tpl );
					$template         = array();
					$template['id']   = $tpl->ID;
					$template['name'] = $tpl->post_title;
					$templates[]      = $template;
				}
			} else {
				$template         = array();
				$template['id']   = 0;
				$template['name'] = 'Please add review template';
				$templates[]      = $template;
			}

			$output  = '<div id="pro-author-review-box" class="pro-author-review-box ' . sanitize_html_class( $who_can_review ) . '">';
			$output .= wp_nonce_field( 'par_post_review_nonce_action', 'par_post_review_nonce_name', true, false );
			$output .= '<div class="review-wrapper">';
			$output .= '<div class="review-field">';
			$output .= '<span class="first"><label for="has-review">' . esc_html__( 'Review this post', 'pro-author-review' ) . '</label></span>';
			$checked = '';
			if ( $has_review ) {
				$checked = 'checked="checked"';
			}
			$output .= '<span class="second"><input type="checkbox" name="_par_post_has_review" id="has-review" value="1" ' . $checked . '/></span>';
			$output .= '<p class="alert when-review-unchecked pro-hidden">' . esc_html__( 'Important: if you leave has review field unchecked the review data deleted when you save post.', 'pro-author-review' ) . '</p>';
			$output .= '</div>';
			$output .= '<div class="review-field wrap-select-review-template review-field-active">';
			$output .= '<span class="first"><label for="select-review-template">' . esc_html__( 'Review template', 'pro-author-review' ) . '</label></span>';
			$output .= '<span class="second">';
			$output .= '<select name="_par_post_review_data[tpl]" class="select-review-template" id="select-review-template">';
			$output .= '<option value ="-1" >' . esc_html__( 'Select template ...', 'pro-author-review' ) . '</option>';
			foreach ( $templates as $tpl ) {
				$selected = '';
				if ( $current_tpl === $tpl['id'] ) {
					$selected = 'selected';
				}
				$output .= '<option value="' . absint( $tpl['id'] ) . '"' . $selected . '>' . esc_attr( $tpl['name'] ) . '</option>';
			}
			$output .= '</select></span></div>';

			if ( ! empty( $current_tpl ) ) {
				$output .= '<div class="ajax-fields-wrapper criteria-fields review-field-active tpl-selected">';
				$output .= self::who_can_review_field( $who_can_review_title );
				$output .= self::get_review_type_field( $review_type );
				$output .= self::get_review_position_field( $currentposition );
				$output .= self::get_review_title_field( $review_title );
				$output .= self::get_text_under_total_field( $text_under_total );
				$output .= self::get_affiliate_text( $aff_txt );
				$output .= self::get_affiliate_url( $aff_url );
				$output .= self::get_review_description_field( $description );
				$output .= self::get_review_pros_field( $pros );
				$output .= self::get_review_cons_field( $cons );
				$output .= '<div class="rating-review-item">';
				$output .= self::total_certeria_field( $total );
				$output .= self::get_loop_criteria_fields( $criteria_fields, $who_can_review );
				$output .= '</div></div>';
			} else {
				$output .= '<div class="ajax-fields-wrapper review-field-active"></div>';
			}

			$output .= '</div></div>';

			echo $output;
		}

		function get_review_template_using_ajax() {

			//check_ajax_referer( 'par_review_post_nonce', 'security' );
			check_admin_referer( 'par_post_review_nonce_action', 'security' );

			$tpl_id = absint( $_POST['tpl_id'] );
			if ( empty( $tpl_id ) ) {
				wp_die();
			}

			$template              = array();
			$template['id']        = $tpl_id;
			$template['reviewtpl'] = esc_html__( 'Please add review template', 'pro-author-review' );

			// using ajax
			$args = array(
				'post_type' => 'parreview',
				'p'         => $tpl_id,
			);

			$query = new WP_Query( $args );
			if ( $query->have_posts() ) {

				while ( $query->have_posts() ) {
					$query->the_post();

					if ( ! empty( $tpl_id ) ) {
						$tpl_review_meta = get_post_meta( $tpl_id, Pro_Author_Review::REVIEW_KEY, true );
						$who_can_review  = $tpl_review_meta['who_can_review'] ?? 'authoronly';
						$review_type     = $tpl_review_meta['type'] ?? 'percent';
						$currentposition = $tpl_review_meta['position'] ?? '';

						if ( 'both' === $who_can_review ) {
							$who_can_review_title = esc_html__( 'Author review and users rate', 'pro-author-review' );
						} elseif ( 'author' === $who_can_review ) {
							$who_can_review_title = esc_html__( 'Author only', 'pro-author-review' );
						} elseif ( 'users' === $who_can_review ) {
							$who_can_review_title = esc_html__( 'Users rate', 'pro-author-review' );
						}
					}

					$wp_review_meta   = get_post_meta( get_the_ID(), Pro_Author_Review::REVIEW_KEY, true );
					$criteria_fields  = get_post_meta( get_the_ID(), Pro_Author_Review::CRITERIA_FIELDS, true );
					$review_title     = $wp_review_meta['title'] ?? '';
					$text_under_total = $wp_review_meta['text_under_total'] ?? '';
					$review_desc      = $wp_review_meta['review_desc'] ?? '';

					$output  = self::who_can_review_field( $who_can_review_title, $css = $who_can_review );
					$output .= self::get_review_type_field( $review_type );
					$output .= self::get_review_position_field( $currentposition );
					$output .= self::get_review_title_field( $review_title );
					$output .= self::get_text_under_total_field( $text_under_total );
					$output .= self::get_affiliate_text( '' );
					$output .= self::get_affiliate_url( '' );
					$output .= self::get_review_description_field( $review_desc );
					$output .= self::get_review_pros_field( '' );
					$output .= self::get_review_cons_field( '' );
					$output .= '<div class="rating-review-item">';
					$output .= self::total_certeria_field( 0 );
					$output .= self::get_loop_criteria_fields( $criteria_fields, $who_can_review );
					$output .= '</div></div>';

					$template['id']        = $tpl_id;
					$template['reviewtpl'] = $output;
				}
			} else {
					$template['id']        = 0;
					$template['reviewtpl'] = esc_html__( 'Please add review template', 'pro-author-review' );
			}
			wp_reset_postdata();

			echo json_encode(
				array(
					'id'        => $template['id'],
					'reviewtpl' => $template['reviewtpl'],
				)
			);
			wp_die();
		}

		function save_review_meta_data( $post_id, $post ) {

			$is_review_template = true;

			// verify nonce
			if ( 'parreview' === $post->post_type ) {
				if ( ! isset( $_POST['par_post_review_tpl_nonce_name'] ) || ! wp_verify_nonce( $_POST['par_post_review_tpl_nonce_name'], 'par_post_review_tpl_nonce_action' ) ) {
					return;
				}
			} else {
				if ( ! isset( $_POST['par_post_review_nonce_name'] ) || ! wp_verify_nonce( $_POST['par_post_review_nonce_name'], 'par_post_review_nonce_action' ) ) {
					return;
				}
				$is_review_template = false;
			}

			// check autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// check permissions
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			// metabox Data Validation before save in database
			if ( ! $is_review_template ) {
				$has_review = $_POST[ Pro_Author_Review::HAS_REVIEW ];
				$has_review = ( isset( $has_review ) && '1' === $has_review ) ? true : false;
			}
			$new                     = $_POST[ Pro_Author_Review::REVIEW_KEY ];
			$new['title']            = ( isset( $new['title'] ) ) ? sanitize_text_field( $new['title'] ) : '';
			$new['text_under_total'] = ( isset( $new['text_under_total'] ) ) ? sanitize_text_field( $new['text_under_total'] ) : '';

			if ( $is_review_template ) {
				$new['who_can_review'] = ( isset( $new['who_can_review'] ) ) ? sanitize_text_field( $new['who_can_review'] ) : 'both';
				$new['type']           = ( isset( $new['type'] ) ) ? sanitize_text_field( $new['type'] ) : 'star';
				$new['css_class']      = ( isset( $new['css_class'] ) ) ? sanitize_html_class( $new['css_class'] ) : '';
			}
			if ( ! $is_review_template ) {
				$new['tpl']         = ( isset( $new['tpl'] ) ) ? absint( $new['tpl'] ) : '';
				$new['pros']        = ( isset( $new['pros'] ) ) ? sanitize_textarea_field( $new['pros'] ) : '';
				$new['cons']        = ( isset( $new['cons'] ) ) ? sanitize_textarea_field( $new['cons'] ) : '';
				$new['aff_txt']     = ( isset( $new['aff_txt'] ) ) ? sanitize_text_field( $new['aff_txt'] ) : '';
				$new['aff_url']     = ( isset( $new['aff_url'] ) ) ? esc_url( $new['aff_url'] ) : '';
				$new['description'] = ( isset( $new['description'] ) ) ? sanitize_textarea_field( $new['description'] ) : '';
			}

			$items_review = array();

			$names = array_map( 'sanitize_text_field', $_POST['criteria_name'] );
			$slugs = array_map( 'sanitize_title', $_POST['criteria_slug'] );
			if ( ! $is_review_template ) {
				$values = array_map( 'absint', $_POST['criteria_value'] );
			}

			$count = count( $names );

			for ( $i = 0; $i < $count; $i++ ) {
				if ( '' !== $names[ $i ] ) {
					$review         = array();
					$review['name'] = $names[ $i ];
					if ( $is_review_template ) {
						$review['value'] = 0;
					} else {
						$review['value'] = $values[ $i ];
					}

					if ( empty( $slugs[ $i ] ) ) {
						$review['slug'] = sanitize_title( $names[ $i ] );
					} else {
						$review['slug'] = $slugs[ $i ];
					}
					$items_review[ $i ] = $review;
				}
			}

			$total_author_review = $this->calculate_total_author_review( $items_review );
			update_post_meta( $post_id, Pro_Author_Review::CRITERIA_FIELDS, $items_review );
			update_post_meta( $post_id, Pro_Author_Review::TOTAL_REVIEW_KEY, $total_author_review );
			update_post_meta( $post_id, Pro_Author_Review::REVIEW_KEY, $new );

			if ( ! $is_review_template && $has_review ) {
				update_post_meta( $post_id, Pro_Author_Review::HAS_REVIEW, $has_review );
			} elseif ( ! $is_review_template && ! $has_review ) {
				// need to users rate post from database
				delete_post_meta( $post_id, Pro_Author_Review::HAS_REVIEW );
				delete_post_meta( $post_id, Pro_Author_Review::CRITERIA_FIELDS, $items_review );
				delete_post_meta( $post_id, Pro_Author_Review::TOTAL_REVIEW_KEY, $total_author_review );
				delete_post_meta( $post_id, Pro_Author_Review::REVIEW_KEY, $new );
			}

		}

		/**
		 * calculates the average of Item Review rating.
		 *
		 * @since  1.0
		 * @param  type $items_review array for review items in single post.
		 * @return Average total author review in single post in percent.
		 */
		function calculate_total_author_review( $items_review ) {

			if ( null === $items_review || empty( $items_review ) || ! is_array( $items_review ) ) {
				return false;
			}
			$total_author_review = 0;
			$number_of_items     = 0;
			foreach ( $items_review as $key => $review ) {
				$total_author_review += $review['value'];
				$number_of_items++;
			}
			$total_author_review = absint( $total_author_review / $number_of_items );

			return $total_author_review;
		}

		private static function total_certeria_field( $total ) {
			return '
				<div class="wrap-total-criteria">
					<div class="criteria-title">
						<h3>' . esc_html__( 'Criteria fields', 'pro-author-review' ) . '</h3>
					</div>
					<div class="total-value">
						<div class="total-review"><strong>' . esc_html__( 'Total review', 'pro-author-review' ) . '</strong></div>
						<div class="wrap-total">
							<span class="total-review">' . esc_html__( 'Percent', 'pro-author-review' ) . '</span>
							<span class="percent-value"><strong>' . absint( $total ) . '</strong>%</span>
						</div>
						<div class="wrap-total">
							<span class="total-review">' . esc_html__( 'Point', 'pro-author-review' ) . '</span>
							<span class="point-value"><strong>' . par_convert_percent_to_point( $total ) . '</strong>/10</span>
						</div>
						<div class="wrap-total">
							<span class="total-review">' . esc_html__( 'Star', 'pro-author-review' ) . '</span>
							<span class="star-value"><strong>' . par_convert_percent_to_star( $total ) . '</strong>/5</span>
						</div>	
					</div>
				</div>';
		}

		private static function who_can_review_field( $review_title, $css = '' ) {
			if ( ! empty( $css ) ) {
				$css = ' ' . $css;
			}

			return '<div class="review-field' . esc_attr( $css ) . '">' . self::who_can_review_title_field() . '<span class="second">' . esc_attr( $review_title ) . '</span></div>';
		}

		private static function who_can_review_title_field() {
			return '<span class="first"><label for="who-can-review">' . esc_html__( 'Who can review ?', 'pro-author-review' ) . '</label></span>';
		}

		private static function get_review_position_title() {
			return '<span class="first"><label for="review-position">' . esc_html__( 'Review position', 'pro-author-review' ) . '</label></span>';
		}

		private static function get_review_position_field( $currentposition ) {
			$output  = '<div class="review-field">';
			$output .= self::get_review_position_title();
			$output .= '<span class="second">' . esc_attr( $currentposition ) . '</span></div>';

			return $output;
		}

		private static function get_review_type_title() {
			return '<span class="first"><label for="review-type">' . esc_html__( 'Review type', 'pro-author-review' ) . '</label></span>';
		}

		private static function get_review_type_field( $review_type ) {
			$output  = '<div class="review-field">';
			$output .= self::get_review_type_title();
			$output .= '<span class="second">' . esc_attr( $review_type ) . '</span></div>';

			return $output;
		}

		private static function get_review_title_field( $review_title ) {
			$output  = '<div class="review-field">';
			$output .= '<span class="first"><label for="review-title">' . esc_html__( 'Review title', 'pro-author-review' ) . '</label></span>';
			$output .= '<span class="second"><input type="text" name="_par_post_review_data[title]" id="review-title" value="' . esc_attr( $review_title ) . '"/></span>';
			$output .= '</div>';

			return $output;
		}

		private static function get_text_under_total_field( $text_under_total ) {
			$output  = '<div class="review-field">';
			$output .= '<span class="first"><label for="review-total-text">' . esc_html__( 'Text under total review', 'pro-author-review' ) . '</label></span>';
			$output .= '<span class="second"><input type="text" name="_par_post_review_data[text_under_total]" id="review-title" value="' . esc_attr( $text_under_total ) . '"/></span>';
			$output .= '</div>';

			return $output;
		}

		private static function get_affiliate_text( $aff_txt ) {
			$output  = '<div class="review-field">';
			$output .= '<span class="first"><label for="review-total-text">' . esc_html__( 'Affiliate text', 'pro-author-review' ) . '</label></span>';
			$output .= '<span class="second"><input type="text" name="_par_post_review_data[aff_txt]" id="review-title" value="' . esc_attr( $aff_txt ) . '"/></span>';
			$output .= '</div>';

			return $output;
		}

		private static function get_affiliate_url( $aff_url ) {
			$output  = '<div class="review-field">';
			$output .= '<span class="first"><label for="review-total-text">' . esc_html__( 'Affiliate link', 'pro-author-review' ) . '</label></span>';
			$output .= '<span class="second"><input type="text" name="_par_post_review_data[aff_url]" id="review-title" value="' . esc_url( $aff_url ) . '"/></span>';
			$output .= '</div>';

			return $output;
		}

		private static function get_review_description_field( $review_desc ) {
			$output  = '<div class="review-field">';
			$output .= '<span class="first"><label for="review-description">' . esc_html__( 'Review description', 'pro-author-review' ) . '</label></span>';
			$output .= '<span class="second"><textarea name="_par_post_review_data[description]" id="review-description" rows="3" cols="70">' . esc_textarea( $review_desc ) . '</textarea></span>';
			$output .= '</div>';

			return $output;
		}

		private static function get_review_pros_field( $pros = '' ) {
			$output  = '<div class="review-field">';
			$output .= '<span class="first"><label for="pros-description">' . esc_html__( 'Pros', 'pro-author-review' ) . '</label></span>';
			$output .= '<span class="second"><textarea name="_par_post_review_data[pros]" id="review-pros" rows="3" cols="70">' . esc_textarea( $pros ) . '</textarea></span>';
			$output .= '</div>';

			return $output;
		}

		private static function get_review_cons_field( $cons = '' ) {
			$output  = '<div class="review-field">';
			$output .= '<span class="first"><label for="cons-description">' . esc_html__( 'Cons', 'pro-author-review' ) . '</label></span>';
			$output .= '<span class="second"><textarea name="_par_post_review_data[cons]" id="review-cons" rows="3" cols="70">' . esc_textarea( $cons ) . '</textarea></span>';
			$output .= '</div>';

			return $output;
		}

		private static function get_loop_criteria_fields( $criteria_fields, $who_can_review ) {
			$output = '<ul id="criteria-items" class="custom_repeatable">';
			if ( $criteria_fields ) {
				$i = 1;
				foreach ( $criteria_fields as $row ) {
					$output .= '<li class="ui-state-default hndle"><span class="">|||</span>
							<span class="label-criteria"><strong>' . esc_attr( $row['name'] ) . '</strong></span>
							<input type="text" name="criteria_name[]" value="' . esc_attr( $row['name'] ) . '" size="30" class="pro-hidden"/>
							<input type="text" name="criteria_slug[]" value="' . esc_attr( $row['slug'] ) . '" size="30" class="pro-hidden"/>';
					if ( 'users' !== $who_can_review ) {
						$output .= '<span class="label-type">' . esc_html__( 'Percent', 'pro-author-review' ) . '</span><input type="number" name="criteria_value[]" value="' . absint( $row['value'] ) . '" size="40" class="percent-value criteria-value itemvalue" min="10" max="100"/><span class="label-type">' . esc_html__( 'Point', 'pro-author-review' ) . '</span><input type="text" class="point-value criteria-value" value="" disabled><span class="label-type">' . esc_html__( 'Star', 'pro-author-review' ) . '</span><input type="text" class="star-value criteria-value" value="" disabled=""><span id="slider' . $i . '" class="slider addslider"></span>';
					} else {
						$output .= '<span class="label-type pro-hidden">' . esc_html__( 'Percent', 'pro-author-review' ) . '</span><input type="text" name="criteria_value[]" value="' . absint( $row['value'] ) . '" size="30" class="percent-value criteria-value pro-hidden" /><span class="label-type pro-hidden">' . esc_html__( 'Point', 'pro-author-review' ) . '</span><input type="text" class="point-value criteria-value pro-hidden" value="" disabled><span class="label-type pro-hidden">' . esc_html__( 'Star', 'pro-author-review' ) . '</span><input type="text" class="star-value criteria-value pro-hidden" value="" disabled>
									<span id="slider' . $i . '" class="slider addslider pro-hidden"></span>';
					}
					$output .= '</li>';

					$i++;
				}
			}
			$output .= '</ul>';

			return $output;
		}

		private static function get_criteria_text_field() {
			$output = '<span class="label-criteria"><strong>' . esc_html__( 'Criteria', 'pro-author-review' ) . '</strong></span>';

			return $output;
		}

		private static function get_criteria_slug_field() {
			$output = '<span class="label-criteria"><strong>' . esc_html__( 'Slug', 'pro-author-review' ) . '</strong></span>';

			return $output;
		}

		private static function get_empty_criteria_field( $hidden = false ) {

			if ( $hidden ) {
				$css = 'ui-state-default hndle pro-hidden empty-row screen-reader-text empty-criteria';
			} else {
				$css = 'ui-state-default hndle';
			}
			$output  = '<li class="' . esc_attr( $css ) . '"><span class="">|||</span>';
			$output .= self::get_criteria_text_field();
			$output .= '<input type="text" name="criteria_name[]" value="" size="30" />';
			$output .= self::get_criteria_slug_field();
			$output .= '<input type="text" name="criteria_slug[]" value="" size="30" />';
			$output .= '<a class="repeatable-remove button remove-row" href="#">-</a></li>';

			return $output;
		}

	}

	new PRO_Author_Review_Metabox();
}
