<?php

defined( 'ABSPATH' ) || exit;

register_activation_hook( __FILE__, 'par_author_review_activation' );
//register_deactivation_hook( __FILE__, 'eye_author_review_deactivation' );

if ( ! function_exists( 'par_author_review_activation' ) ) {
	function par_author_review_activation() {

		par_author_review_create_db();

		$plugin_options = par_wp_review_option( 'pra_author_review' );
		update_option( 'pra_author_review', $plugin_options );
	}
}

if ( ! function_exists( 'eye_author_review_deactivation' ) ) {
	function eye_author_review_deactivation() {
		// Welp, I've been deactivated - are there some things I should clean up?
	}
}

// Uses to check the plugin version
if ( ! function_exists( 'par_author_review_current_version' ) ) {
	function par_author_review_current_version() {
		$plugin_options = par_wp_review_option( 'pra_author_review' );
		$version        = $plugin_options['version'];
		return version_compare( $version, PRO_AR_VERSION, '=' ) ? true : false;
	}
}

// Uses to check the plugin version
if ( ! function_exists( 'par_author_review_database_current_version' ) ) {
	function par_author_review_database_current_version() {
		$plugin_options = par_wp_review_option( 'pra_author_review' );
		$version        = $plugin_options['db_version'];
		return version_compare( $version, PRO_AR_DB_VERSION, '=' ) ? true : false;
	}
}

if ( ! function_exists( 'par_author_review_create_db' ) ) {
	function par_author_review_create_db() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . PRO_AR_USERS_RATE_TABLE;

		$sql = "CREATE TABLE $table_name (
				ID bigint(20) NOT NULL AUTO_INCREMENT,
				post_id bigint(20) NOT NULL,
				user_id bigint(20) NOT NULL,
				criteria_rate longtext NOT NULL,
				avg_rate tinyint(2) NOT NULL,
				rate_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				rate_modified datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				PRIMARY KEY  (ID)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$plugin_options               = get_option( 'pra_author_review_ver' );
		$plugin_options['db_version'] = PRO_AR_DB_VERSION;

		update_option( 'pra_author_review_ver', $plugin_options );
	}
}

// Check Database update
if ( ! par_author_review_database_current_version() ) {
	par_author_review_create_db();
}
