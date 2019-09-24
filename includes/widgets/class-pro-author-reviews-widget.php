<?php

defined( 'ABSPATH' ) || exit;

/*
 * Pro Author Review Widget
 *
 * @category 	Widgets
 * @extends 	WP_Widget
 * @version 1.0
 */

add_action( 'widgets_init', 'Pro_Author_Reviews_Widget::register_this_widget' );

class Pro_Author_Reviews_Widget extends WP_Widget {

	function __construct() {

		$widget_ops = array(
			'classname'   => 'pro_author_reviews',
			'description' => esc_html__( 'Author reviews', 'pro-author-review' ),
		);

		parent::__construct( 'pro_author_reviews', esc_html__( 'Author reviews', 'pro-author-review' ), $widget_ops );
	}

	static function register_this_widget() {
		register_widget( __class__ );
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	function widget( $args, $instance ) {

		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		$title          = apply_filters( 'widget_title', $instance['title'] );
		$posts_per_page = absint( $instance['posts_per_page'] );
		$post_type      = esc_attr( $instance['post_type'] );
		$who_can_review = esc_attr( $instance['who_can_review'] );
		$cat_ids        = esc_attr( $instance['cat_ids'] );

		if ( ! isset( $post_type ) && ! empty( $post_type ) ) {
			$post_type = 'post';
		}
		$query_args = array(
			'post_type'           => $post_type,
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => $posts_per_page,
			'order'               => 'DESC',
			'orderby'             => 'meta_value_num',
		);

		if ( ! empty( $cat_ids ) ) {
			$query_args['cat'] = esc_attr( $cat_ids );
		}

		if ( 'users' === $who_can_review ) {
			$query_args['meta_key']   = '_par_post_users_avg_rating';
			$query_args['meta_query'] = array(
				'relation'   => 'AND',
				'has_review' => array(
					'key'   => '_par_post_has_review',
					'value' => true,
				),
				'total'      => array(
					'key'     => '_par_post_users_avg_rating',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
			);
		} else {
			$query_args['meta_key']   = '_par_post_total_author_review';
			$query_args['meta_query'] = array(
				'relation'   => 'AND',
				'has_review' => array(
					'key'   => '_par_post_has_review',
					'value' => true,
				),
				'total'      => array(
					'key'     => '_par_post_total_author_review',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
			);
		}

		ob_start();
		$review_query = new WP_Query( $query_args );
		if ( $review_query->have_posts() ) {

			echo $before_widget;

			if ( ! empty( $title ) ) {
				echo $before_title . $title . $after_title;
			}
			echo '<ul class="author-review">';

			while ( $review_query->have_posts() ) {

				$review_query->the_post();
				$tpl = par_author_review_get_template( 'review-widget.php' );
				include( $tpl );
			}
			echo '<ul>';
			echo $after_widget;
		}
		wp_reset_postdata();

		$content = ob_get_clean();

		echo $content;
	}

	/**
	 * update function.
	 *
	 * @see WP_Widget->update
	 * @access public
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {

		$instance                   = $old_instance;
		$instance['title']          = esc_attr( $new_instance['title'] );
		$instance['posts_per_page'] = absint( $new_instance['posts_per_page'] );
		$instance['who_can_review'] = esc_attr( $new_instance['who_can_review'] );
		$instance['post_type']      = esc_attr( $new_instance['post_type'] );
		$instance['cat_ids']        = esc_attr( $new_instance['cat_ids'] );

		return $instance;
	}

	/**
	 * form function.
	 *
	 * @see WP_Widget->form
	 * @access public
	 * @param array $instance
	 * @return void
	 */
	function form( $instance ) {

		$fields = array(
			array(
				'type'  => 'text',
				'key'   => 'title',
				'id'    => $this->get_field_id( 'title' ),
				'name'  => $this->get_field_name( 'title' ),
				'title' => esc_html__( 'title:', 'pro-author-review' ),
			),
			array(
				'type'    => 'text',
				'key'     => 'posts_per_page',
				'id'      => $this->get_field_id( 'posts_per_page' ),
				'name'    => $this->get_field_name( 'posts_per_page' ),
				'title'   => esc_html__( 'Number of posts:', 'pro-author-review' ),
				'default' => 5,
			),
			array(
				'type'    => 'select',
				'key'     => 'who_can_review',
				'id'      => $this->get_field_id( 'who_can_review' ),
				'name'    => $this->get_field_name( 'who_can_review' ),
				'title'   => esc_html__( 'Who can review posts:', 'pro-author-review' ),
				'default' => 'author',
				'options' => array(
					esc_html__( 'Author review', 'pro-author-review' ) => 'author',
					esc_html__( 'Users rate', 'pro-author-review' )    => 'users',
				),
			),
			array(
				'type'    => 'select_posttype',
				'key'     => 'post_type',
				'id'      => $this->get_field_id( 'post_type' ),
				'name'    => $this->get_field_name( 'post_type' ),
				'title'   => esc_html__( 'Post type:', 'pro-author-review' ),
				'default' => 'post',
				'options' => par_review_get_post_types_list(),
			),
			array(
				'type'    => 'text',
				'key'     => 'cat_ids',
				'id'      => $this->get_field_id( 'cat_ids' ),
				'name'    => $this->get_field_name( 'cat_ids' ),
				'title'   => esc_html__( 'Filter by category:', 'pro-author-review' ),
				'desc'    => esc_html__( 'You can select one or more categories', 'pro-author-review' ),
				'options' => par_review_get_all_blog_categories(),
			),
		);

		par_review_print_html_widget_fields( $instance, $fields );
	}
}
