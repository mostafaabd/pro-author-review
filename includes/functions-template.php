<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'par_tpl_review_title' ) ) {

	function par_tpl_review_title( $review_title ) {

		if ( ! empty( $review_title ) ) {
			echo '<h3 class="review-title">' . esc_attr( $review_title ) . '</h3>';
		}
	}
}

if ( ! function_exists( 'par_tpl_review_buttons' ) ) {

	function par_tpl_review_buttons( $args ) {

		$defaults = array(
			'review_type'    => 'percent',
			'who_can_review' => '',
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		echo '<div class="buttons">';
		$css_active = ' active';
		if ( 'both' === $who_can_review ) {
			echo '<a href="javascript:void(0)" class="author-reviews-btn' . $css_active . '">' . esc_html__( 'Author Review', 'pro-author-review' ) . '</a>';
			$css_active = '';
		}
		echo '<a href="javascript:void(0)" class="users-rate-btn' . $css_active . '">' . esc_html__( 'Users rate', 'pro-author-review' ) . '</a>';
		echo '<a href="javascript:void(0)" class="rate-it-btn">' . esc_html__( 'Rate It', 'pro-author-review' ) . '</a>';
		echo '</div>';

	}
}

if ( ! function_exists( 'par_tpl_loop_criteria_fields' ) ) {

	function par_tpl_loop_criteria_fields( $args ) {

		$defaults = array(
			'review_type'     => 'percent',
			'criteria_fields' => array(),
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		$symbol = '';
		if ( 'percent' === $review_type ) {
			$symbol = '%';
		}

		foreach ( $criteria_fields as $item ) {
			echo '<div class="review-item">';
			echo '<div class="details">';
			echo '<h4>' . esc_attr( $item['name'] ) . '<span class="score">';
			if ( 'point' === $review_type ) {
				echo round( $item['value'], 1 );
				// used for progress width
				$item['value'] *= 10;
			} else {
				echo absint( $item['value'] ) . $symbol;
			}
			echo '</span></h4></div>';
			echo '<div class="progress">';
			echo '<div class="progress-bar" role="progressbar" aria-valuenow="' . absint( $item['value'] ) . '" aria-valuemin="0" aria-valuemax="100"></div>';
			echo '</div></div>';
		}
	}
}

if ( ! function_exists( 'par_tpl_loop_stars_criteria_fields' ) ) {

	function par_tpl_loop_stars_criteria_fields( $args ) {

		$defaults = array(
			'review_type'     => 'star',
			'criteria_fields' => array(),
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		foreach ( $criteria_fields as $item ) {
			echo '<div class="review-item">';
			echo '<div class="details">';
			echo '<h4>' . esc_attr( $item['name'] ) . '</h4></div>';
			echo '<div class="star-review"><div class="star-wrap">';
			// translators: %s: Average star rating value.
			echo '<span class="star-rating" title="' . sprintf( esc_html__( 'Rated %s out of 5', 'pro-author-review' ), round( $item['value'], 1 ) ) . '">';
			echo '<span class="star-over" style="width:' . absint( $item['value'] * 20 ) . '%"></span>';
			echo '</span>';
			echo '</div></div></div>';
		}
	}
}

if ( ! function_exists( 'par_tpl_review_summry' ) ) {

	function par_tpl_review_summry( $args ) {
		$defaults = array(
			'review_type'       => 'percent',
			'who_can_review'    => '',
			'author_avg_rating' => 0,
			'txt_under_total'   => '',
			'users_avg_rating'  => 0,
			'num_of_users'      => 0,
			'description'       => '',
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		echo '<div class="review-summary">';
		if ( 'author' === $who_can_review || 'both' === $who_can_review ) {

			echo '<div class="final-score">';
			echo '<div class="final-score-inner">';
			if ( 'point' === $review_type ) {
				par_tpl_title_point_rating( $author_avg_rating );
			} elseif ( 'star' === $review_type ) {
				par_tpl_title_star_rating( $author_avg_rating );
			} else {
				par_tpl_title_precent_rating( $author_avg_rating );
			}

			echo '<span>' . esc_attr( $txt_under_total ) . '</span>';
			if ( 'both' === $who_can_review ) {
				echo '<span>' . esc_html__( 'Author Review', 'pro-author-review' ) . '</span>';
			}
			echo '</div></div>';
		}

		if ( 'users' === $who_can_review || 'both' === $who_can_review ) {
			echo '<div class="total-users-rating">';
			echo '<div class="total-users-rating-inner">';

			if ( 'point' === $review_type ) {
				par_tpl_title_point_rating( $users_avg_rating );
			} elseif ( 'star' === $review_type ) {
				par_tpl_title_star_rating( $users_avg_rating );
			} else {
				par_tpl_title_precent_rating( $users_avg_rating );
			}

			echo '<span class="votes">';
			if ( $num_of_users > 0 ) {
				// translators: %s: Number of users rating
				printf( _n( '%d vote', '%d votes', $num_of_users, 'pro-author-review' ), absint( $num_of_users ) );
			} else {
				esc_html_e( 'Not rated yet.', 'pro-author-review' );
			}
			echo '</span>';
			if ( 'both' === $who_can_review ) {
				echo '<span>' . esc_html__( 'Users Rate', 'pro-author-review' ) . '</span>';
			}
			echo '</div></div>';
		}

		if ( ! empty( $description ) ) {
			echo '<div class="short-summary">';
			echo '<p><strong>' . esc_html__( 'Summary:', 'pro-author-review' ) . '</strong>' . esc_attr( $description ) . '</p>';
			echo '</div>';
		}

		echo '</div>';
	}
}

if ( ! function_exists( 'par_tpl_pros_cons' ) ) {

	function par_tpl_pros_cons( $args ) {
		$defaults = array(
			'pros' => array(),
			'cons' => array(),
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		if ( ! empty( $pros ) || ! empty( $cons ) ) {
			echo '<div class="pros-cons">';
			if ( ! empty( $pros ) ) {
				echo '<div class="pros"><ul>';
				foreach ( $pros as $item ) {
					echo '<li>' . esc_attr( $item ) . '</li>';
				}
				echo '</ul></div>';
			}

			if ( ! empty( $cons ) ) {
				echo '<div class="cons"><ul>';
				foreach ( $cons as $item ) {
					echo '<li>' . esc_attr( $item ) . '</li>';
				}
				echo '</ul></div>';
			}

			echo '</div>';
		}

	}
}

if ( ! function_exists( 'par_tpl_affiliate' ) ) {

	function par_tpl_affiliate( $args ) {
		if ( ! empty( $args['aff_url'] ) ) {
			echo '<div class="affiliate"><p>';
			echo '<a href="' . esc_url( $args['aff_url'] ) . '" title="' . esc_attr( $args['aff_text'] ) . '" target="_blank">';
			if ( ! empty( $args['aff_text'] ) ) {
				echo esc_attr( $args['aff_text'] );
			}
			echo '</a>';
			echo '</p></div>';
		}
	}
}

if ( ! function_exists( 'par_tpl_title_star_rating' ) ) {

	function par_tpl_title_star_rating( $avg_rating ) {
		$avg_rating = round( $avg_rating, 1 );
		// translators: %s: Average rating value.
		echo '<h3 title="' . sprintf( esc_html__( 'Rated %d out of 5', 'pro-author-review' ), $avg_rating ) . '"><span class="value">' . $avg_rating . '</span></h3>';
	}
}

if ( ! function_exists( 'par_tpl_title_point_rating' ) ) {

	function par_tpl_title_point_rating( $avg_rating ) {
		$avg_rating = round( $avg_rating, 1 );
		// translators: %s: Average rating value.
		echo '<h3 title="' . sprintf( esc_html__( 'Rated %s out of 10', 'pro-author-review' ), $avg_rating ) . '"><span class="value">' . $avg_rating . '</span></h3>';
	}
}

if ( ! function_exists( 'par_tpl_title_precent_rating' ) ) {

	function par_tpl_title_precent_rating( $avg_rating ) {
		$avg_rating = absint( $avg_rating );
		// translators: %s: Average rating value.
		echo '<h3 title="' . sprintf( esc_html__( 'Rated %d', 'pro-author-review' ), $avg_rating ) . '%"><span class="value">' . $avg_rating . '</span><span class="percent-icon">%</span></h3>';
	}
}
