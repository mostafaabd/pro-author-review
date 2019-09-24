<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'par_review_get_all_blog_categories' ) ) {

	function par_review_get_all_blog_categories() {
		$cats   = array();
		$cats[] = array(
			'label' => 'All Categories',
			'value' => 0,
		);

		$categories = get_terms(
			'category',
			array(
				'orderby'    => 'name',
				'hide_empty' => 0,
			)
		);

		foreach ( $categories as $cat ) {
			$cats[] = array(
				'label' => $cat->name,
				'value' => $cat->term_id,
			);
		}

		return $cats;
	}
}

if ( ! function_exists( 'par_review_get_post_types_list' ) ) {

	function par_review_get_post_types_list() {

		$post_types          = get_post_types( array() );
		$post_types_list     = array();
		$excluded_post_types = array(
			'revision',
			'nav_menu_item',
			'vc_grid_item',
			'attachment',
			'custom_css',
			'customize_changeset',
			'shop_order',
			'shop_order_refund',
			'shop_coupon',
			'shop_webhook',
			'vc4_templates',
			'product_variation',
			'oembed_cache',
			'mc4wp-form',
			'user_request',
			'sidebar',
			'wpcf7_contact_form',
			'parreview',
			'wp_block',
		);
		if ( is_array( $post_types ) && ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				if ( ! in_array( $post_type, $excluded_post_types, true ) ) {
					$label             = ucfirst( $post_type );
					$post_types_list[] = array(
						$post_type,
						$label,
					);
				}
			}
		}

		return $post_types_list;
	}
}
if ( ! function_exists( 'par_review_print_html_widget_fields' ) ) {

	function par_review_print_html_widget_fields( $instance, $fields ) {
		$defaults = array(
			'type'    => 'text',
			'id'      => '',
			'name'    => '',
			'title'   => '',
			'desc'    => '',
			'options' => array(),
			'default' => '',
		);

		foreach ( $fields as $field ) {

			$field = wp_parse_args( (array) $field, $defaults );
			extract( $field ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

			if ( isset( $instance[ $key ] ) ) {
				$value = $instance[ $key ];
			} else {
				$value = $default;
			}
			switch ( $field['type'] ) {

				case 'text':
					?>
					<p>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?> </label><input class="widefat" type="text" name="<?php echo esc_attr( $field['name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>" size="20" />
					</p>
					<?php
					par_eye_review_print_description( $field['desc'] );
					break;
				case 'select':
					?>
					<p>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<select name="<?php echo esc_attr( $field['name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" class="widefat">
						<?php
						$options = $field['options'];
						foreach ( $options as $label => $key ) {
							echo '<option value="' . $key . '" id="' . $key . '"', $value === $key ? ' selected="selected"' : '', '>', $label, '</option>';
						}
						?>
						</select>
					</p>
					<?php
					par_eye_review_print_description( $field['desc'] );
					break;
				case 'select_posttype':
					?>
					<p>
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
						<select name="<?php echo esc_attr( $field['name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" class="widefat">
						<?php
						$options = $field['options'];
						foreach ( $options as $post ) {
							echo '<option value="' . $post[0] . '" id="' . $post[0] . '"', $value === $post[0] ? ' selected="selected"' : '', '>', $post[1], '</option>';
						}
						?>
						</select>
					</p>
					<?php
					par_eye_review_print_description( $field['desc'] );
					break;
			}
		}
	}
}

if ( ! function_exists( 'par_eye_review_print_description' ) ) {

	function par_eye_review_print_description( $desc ) {
		if ( ! empty( $desc ) ) {
			echo '<span>' . wp_kses_post( $desc ) . '</span>';
		}
	}
}
