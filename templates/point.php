<?php
/**
 * The template for displaying author review point in a single post.
 *
 * Override this template by copying it to yourtheme/pro-author-review/point.php
 *
 * @since    1.0.0
 */

defined( 'ABSPATH' ) || exit;

extract( $post_review_data ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

$css_classes = 'pro-author-review' . ' review-' . $review_position . ' ' . $review_type;

if ( 'author' === $who_can_review ) {
	$css_classes .= ' author-only';
} elseif ( 'users' === $who_can_review ) {
	$css_classes .= ' users-only';
}
if ( ! empty( $css_class ) ) {
	$css_classes .= ' ' . $css_class;
}
?>

<div class="<?php echo esc_attr( $css_classes ); ?>">

	<?php do_action( 'pra_before_author_review_box' ); ?>

	<div class="inner-review">

		<div class="review-header">

			<?php par_tpl_review_title( $review_title ); ?>

			<?php
			if ( 'users' === $who_can_review || 'both' === $who_can_review ) {
				par_tpl_review_buttons( $post_review_data );
			}
			?>

		</div>

		<?php
		echo '<div class="review-details">';

		if ( 'author' === $who_can_review || 'both' === $who_can_review ) {
			echo '<div class="author-reviews">';
				par_tpl_loop_criteria_fields(
					array(
						'review_type'     => $review_type,
						'criteria_fields' => $author_criteria_fields,
					)
				);
			echo '</div>';
		}

		if ( 'users' === $who_can_review || 'both' === $who_can_review ) {
			// @hook used to add user rate
			do_action( 'pra_add_users_rate_template' );
		}

		echo '</div>';
		par_tpl_review_summry( $post_review_data );
		par_tpl_pros_cons( $post_review_data );
		par_tpl_affiliate( $post_review_data );
		?>
	</div>
</div>
