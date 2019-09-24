<?php
/**
 * The Template for displaying author review widget.
 *
 * Override this template by copying it to yourtheme/pro-author-review/author-review-widget.php
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;
?>

<li>
	<?php

	$thumbnail = apply_filters( 'par_widget_thumbnail', 'par-thumb' );
	if ( has_post_thumbnail() ) {
		?>
		<div class="wrap-thumbnail">

			<a rel="bookmark" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">

				<?php the_post_thumbnail( $thumbnail ); ?>

			</a>

		</div>

	<?php } ?>

		<a rel="bookmark" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>

		<div class="star-review">

		<?php
		if ( 'users' === $who_can_review ) {
			$total_review  = par_get_average_users_rate( array( 'review_type' => 'star' ) );
			$total_percent = par_get_average_users_rate( array( 'review_type' => 'percent' ) );
		} else {
			$total_review  = par_get_average_author_rating( array( 'review_type' => 'star' ) );
			$total_percent = par_get_average_author_rating( array( 'review_type' => 'percent' ) );
		}
		?>
		<?php // translators: %s:Average rating ?>
		<span class="star-rating" title="<?php printf( esc_html__( 'Rated %s out of 5', 'pro-author-review' ), round( $total_review, 1 ) ); ?>">

			<span class="star-over" style="width:<?php echo absint( $total_percent ); ?>%;"></span>

		</span>

	</div>
</li>
