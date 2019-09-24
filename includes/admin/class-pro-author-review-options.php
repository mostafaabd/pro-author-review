<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Pro_Author_Review_Options' ) ) {

	class Pro_Author_Review_Options {

		const PAGE_SLUG   = 'pro-author-review';
		const OPTION_NAME = 'pro_author_review';

		function __construct() {
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		}

		function init() {

			add_action( 'admin_menu', array( $this, 'add_author_review_options_page' ) );
			add_action( 'admin_init', array( $this, 'author_review_register_settings' ) );
			add_action( 'admin_notices', array( $this, 'author_review_admin_messages' ) );
		}

		// Add Plugin option page
		function add_author_review_options_page() {
			add_submenu_page(
				'edit.php?post_type=parreview',
				esc_html__( 'Author review settings', 'pro-author-review' ),
				esc_html__( 'Settings', 'pro-author-review' ),
				'manage_options',
				self::PAGE_SLUG,
				array( $this, 'auhtor_review_settings_page_fn' )
			);
		}

		// Out HTML for Plugin option page
		function auhtor_review_settings_page_fn() {

			?>
			<div class="wrap">
				<div class="icon32" id="icon-options-general"></div>

				<h2><?php esc_html_e( 'Author Review Options', 'pro-author-review' ); ?></h2>

				<form method="post" action="options.php">

					<?php settings_fields( self::OPTION_NAME ); ?>

					<?php do_settings_sections( self::PAGE_SLUG ); ?>

					<?php submit_button(); ?>

				</form>
			</div>

			<?php
		}

		function author_review_register_settings() {

			if ( false === get_option( self::OPTION_NAME ) ) {
				add_option( self::OPTION_NAME );
			}

			register_setting( self::OPTION_NAME, self::OPTION_NAME, array( $this, 'register_setting_validate_options' ) );
			add_settings_section( 'pro-author-review-section', '', array( $this, 'author_review_sections_fn' ), self::PAGE_SLUG );

			$options = $this->options_page_fields();

			if ( ! empty( $options ) ) {
				foreach ( $options as $option ) {
					$this->create_settings_field( $option );
				}
			}
		}

		function author_review_sections_fn() {
			// out section description
		}

		/**
		 * Helper function for registering our form field settings
		 *
		 * src: http://alisothegeek.com/2011/01/wordpress-settings-api-tutorial-1/
		 * @param (array) $args The array of arguments to be used in creating the field
		 * @return function call
		 */
		function create_settings_field( $args = array() ) {

			$defaults = array(
				'id'      => '',
				'title'   => '',
				'desc'    => '',
				'std'     => '',
				'type'    => 'text',
				'section' => 'pro-author-review-section',
				'choices' => array(),
				'class'   => '',
			);

			extract( wp_parse_args( $args, $defaults ) ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

			// additional arguments for use in form field output in the function eye_form_field_fn!
			$field_args = array(
				'title'   => $title,
				'type'    => $type,
				'id'      => $id,
				'desc'    => $desc,
				'std'     => $std,
				'choices' => $choices,
			);

			add_settings_field( $id, $title, array( $this, 'auhotr_review_form_field_fn' ), self::PAGE_SLUG, $section, $field_args );

		}

		/**
		 * Define our form fields (settings)
		 *
		 * @return array
		 */
		function options_page_fields() {

			$options[] = array(
				'section' => 'pro-author-review-section',
				'id'      => 'post_type',
				'title'   => esc_html__( 'Post type', 'pro-author-review' ),
				'desc'    => esc_html__( 'Choose post type to display author review metabox', 'pro-author-review' ),
				'type'    => 'post-types-multi-checkbox',
				'std'     => '',
				'choices' => $this->get_post_types(),
			);
			$options[] = array(
				'section' => 'pro-author-review-section',
				'id'      => 'login_page_url',
				'title'   => esc_html__( 'Login page URL', 'pro-author-review' ),
				'type'    => 'text',
				'std'     => '',
			);

			return $options;
		}

		/*
		 * Form Fields HTML
		 *
		 * All form field types share the same function!!
		 * @return echoes output
		 */
		function auhotr_review_form_field_fn( $args = array() ) {
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

			//var_dump( $args );
			$option_name = self::OPTION_NAME;
			$options     = get_option( $option_name );

			if ( ! isset( $options[ $id ] ) ) {
				$options        = array();
				$options[ $id ] = $args['std'];
			}

			// switch html display based on the setting type.
			switch ( $type ) {

				case 'text':
					//echo "<input class='regular-text' type='text' id='$id' name='" . $option_name . "[$id]' value='$options[$id]' />";
					echo '<input class="regular-text" type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $option_name ) . '[' . esc_attr( $id ) . ']" value="' . esc_attr( $options[ $id ] ) . '"/>';
					echo ( '' !== $desc ) ? '<br/><span class="description">' . esc_attr( $desc ) . '</span>' : '';
					break;

				case 'checkbox':
					echo '<input class="checkbox" type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $option_name ) . '[' . $id . ']" value="1"' . checked( 1, esc_attr( $options[ $id ] ), false ) . '/>';
					echo '<label>' . esc_attr( $desc ) . '</label>';
					break;

				case 'post-types-multi-checkbox':
					foreach ( $choices as $item ) {

						$key     = $item['key'];
						$label   = $item['label'];
						$checked = '';

						if ( isset( $options[ $id ][ $key ] ) ) {
							if ( $options[ $id ][ $key ] === $key ) {
								$checked = 'checked="checked"';
							}
						}

						echo '<input class="checkbox" type="checkbox" id="' . esc_attr( $id ) . '|' . esc_attr( $key ) . '" name="' . esc_attr( $option_name ) . '[' . esc_attr( $id ) . '][' . esc_attr( $key ) . ']" value="1" ' . $checked . '/>' . esc_attr( $label ) . '&#160;&#160;&#160;&#160;&#160;';
					}
					echo ( '' !== $desc ) ? '<br/><span class="description">' . esc_attr( $desc ) . '</span>' : '';
					break;
			}
		}

		function register_setting_validate_options( $input ) {

			$valid_input = array();
			$options     = $this->options_page_fields();

			foreach ( $options as $option ) {

				switch ( $option['type'] ) {
					case 'hidden':
					case 'text':
					case 'textarea':
						$valid_input[ $option['id'] ] = esc_attr( $input[ $option['id'] ] );
						break;

					case 'checkbox':
						// if it's not set, default to null!
						if ( ! isset( $input[ $option['id'] ] ) ) {
							$input[ $option['id'] ] = 'off';
						}
						// Our checkbox value is either 0 or 1
						$valid_input[ $option['id'] ] = ( 1 === $input[ $option['id'] ] ? 1 : 0 );
						break;

					case 'post-types-multi-checkbox':
						$check_values  = array();
						$checkboxarray = array();
						foreach ( $option['choices'] as $item ) {
							$check_values[] = $item['key'];
						}
						foreach ( $check_values as $v ) {

							// Check that the option isn't null
							if ( ! empty( $input[ $option['id'] ][ $v ] ) ) {
								$checkboxarray[ $v ] = $v;
							}
						}
						$valid_input[ $option['id'] ] = $checkboxarray;
						break;
				}
			}

			return $valid_input;
		}

		function get_post_types() {
			$post_types     = array();
			$get_post_types = get_post_types( array( 'public' => true ), 'objects' );

			foreach ( $get_post_types as $post_type ) {
				if ( 'attachment' !== $post_type->name ) {
					$post_types[] = array(
						'key'   => $post_type->name,
						'label' => $post_type->labels->singular_name,
					);
				}
			}

			return apply_filters( 'pra_author_review_post_type', $post_types );
		}

		/**
		 * Helper function for creating admin messages
		 * src: http://www.wprecipes.com/how-to-show-an-urgent-message-in-the-wordpress-admin-area
		 *
		 * @param (string) $message The message to echo
		 * @param (string) $msgclass The message class
		 * @return echoes the message
		 */
		function show_msg( $set_errors = array() ) {
			echo '<div id="setting-error-settings_' . $set_errors['type'] . '" class="' . $set_errors['type'] . ' settings-error"><p title="' . $set_errors['setting'] . '">' . $set_errors['message'] . '</p></div>';
		}

		function author_review_admin_messages() {
			if ( isset( $_GET['page'] ) && strpos( false !== $_GET['page'], self::PAGE_SLUG ) ) {
				$settings_pg = strpos( $_GET['page'], self::PAGE_SLUG );
				$set_errors  = get_settings_errors();

				// display admin message only for the admin to see, only on our settings page and only when setting errors/notices are returned!
				if ( current_user_can( 'manage_options' ) && false !== $settings_pg && ! empty( $set_errors ) ) {

					if ( 'settings_updated' === $set_errors[0]['code'] && isset( $_GET['settings-updated'] ) ) {
						$this->show_msg( $set_errors[0] );
					} else {
						foreach ( $set_errors as $set_error ) {
							$this->show_msg( $set_error );
						}
					}
				}
			}
		}
	}

	new Pro_Author_Review_Options();
}
