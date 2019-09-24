<?php
/**
 * The template for displaying users rating in a single post.
 *
 * Override this template by copying it to yourtheme/pro-author-review/users-rate-template.php
 *
 * @since    1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $par_users_rate, $par_post_review_data;

extract( $par_post_review_data ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

?>

<div class="users-rate">
<?php if ( ! $num_of_users ) { ?>
	<p class="not-rated-before">
		<?php esc_html_e( 'Be the first and rate this post', 'pro-author-review' ); ?>
		&#160;<a href="javascript:void(0)" class="rate-it-btn"><?php esc_html_e( 'Rate It', 'pro-author-review' ); ?></a>
	</p>
<?php } ?>

<?php
if ( $num_of_users ) {
	if ( 'star' === $review_type ) {
		par_tpl_loop_stars_criteria_fields( array( 'criteria_fields' => $users_criteria_fields ) );
	} else {
		par_tpl_loop_criteria_fields(
			array(
				'review_type'     => $review_type,
				'criteria_fields' => $users_criteria_fields,
			)
		);
	}
} else {
	if ( 'star' === $review_type ) {
		foreach ( $author_criteria_fields as $criteria ) {
			?>
			<div class="review-item hidden">
				<div class="details">
					<h4><?php echo esc_attr( $criteria['name'] ); ?></h4>
				</div>
				<div class="star-review"><div class="star-wrap">
					<span class="star-rating" title="">
						<span class="star-over" style="width:0%;"></span>
					</span>
				</div></div>
			</div>
			<?php
		}
	} else {
		foreach ( $author_criteria_fields as $criteria ) {
			?>
			<div class="review-item hidden">
				<div class="details">
					<h4><?php echo esc_attr( $criteria['name'] ); ?><span class="score">0</span></h4>
				</div>
				<div class="progress">
					<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="1" aria-valuemax="100"></div>
				</div>
			</div>
			<?php
		}
	}
}
?>
</div>

<?php
if ( ! $par_users_rate->check_user_rate_post_before( $post_id ) ) {
	if ( is_user_logged_in() ) {
		if ( 'percent' === $review_type ) {
			$value = 50;
			$min   = 10;
			$max   = 100;
			$step  = 10;
		} elseif ( 'point' === $review_type ) {
			$value = 5;
			$min   = 1;
			$max   = 10;
			$step  = 1;
		} else {
			$value = 3;
			$min   = 1;
			$max   = 5;
			$step  = 1;
		}
		?>
		<div class="rate-it rate-criteria" data-post_id="<?php echo $post_id; ?>">

		<?php foreach ( $users_criteria_fields as $criteria ) { ?>

			<div class="criteria-rate">

				<label for="<?php echo esc_attr( $criteria['slug'] ); ?>"><?php echo esc_html( $criteria['name'] ); ?></label>
				<div class="slider addslider" class="slider" data-value="<?php echo $value; ?>" data-min="<?php echo $min; ?>" data-max="<?php echo $max; ?>" data-step="<?php echo $step; ?>" data-slug="<?php echo esc_attr( $criteria['slug'] ); ?>" id="<?php echo esc_attr( $criteria['slug'] ); ?>"></div>
				<span class="slider-value"><?php echo $value; ?></span>
				<input type="text" class="value hidden" value="<?php echo $value; ?>">
			</div>

		<?php } ?>
			<p>
				<a href="javascript:void(0)" class="add-user-rate"><?php esc_html_e( 'Rate It', 'pro-author-review' ); ?></a>
			</p>
		</div>

		<?php
	} else { // Only Login user can rate this post
		?>
		<div class="rate-it rate-criteria" data-post_id="<?php echo absint( $post_id ); ?>">
			<p>
			<?php
			esc_html_e( 'Only logged in users can rate this post.', 'pro-author-review' );
			if ( ! empty( $login_page_url ) ) {
				echo ' ';
				printf(
					// translators: %s: The message display when a user doesn't login
					esc_html__( 'You must be %1$slogged in%2$s to rate this post.', 'pro-author-review' ),
					'<a href="' . esc_url( $login_page_url ) . '">',
					'</a>'
				);
			}
			?>
			</p>
		</div>
	<?php } ?>

	<?php } else { ?>
		<div class="rate-it rate-criteria" data-post_id="<?php echo $post_id; ?>">
				<p><?php esc_html_e( 'You rated this post before.', 'pro-author-review' ); ?></p>
		</div>			
<?php } ?>
