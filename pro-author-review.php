<?php
/*------------------------------------------------------------------------------
Plugin Name: Pro Author Review
Description: Allows a post author or users to rate posts.
Version:     1.1
Author:      Mostafa Abdallah
Author URI:  https://mostafaa.net/
License:     GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
text domain: pro-author-review
Domain Path: /languages

Pro Author Review is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Pro Author Review is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Pro Author Review. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
------------------------------------------------------------------------------*/

// exit if file is called directly
defined( 'ABSPATH' ) || exit;

const PRO_AR_VERSION          = '1.1';
const PRO_AR_DB_VERSION       = '1.0';
const PRO_AR_USERS_RATE_TABLE = 'pro_user_rates';
define( 'PRO_AR_URI', plugins_url( '', __FILE__ ) );
define( 'PRO_AR_DIR', plugin_dir_path( __FILE__ ) );

require_once( 'includes/core-functions.php' );
require_once( 'includes/class-pro-author-review.php' );
require_once( 'includes/class-pro-users-rate.php' );
require_once( 'includes/class-pro-author-review-api.php' );
require_once( 'includes/widgets/widget-fields.php' );
require_once( 'includes/widgets/class-pro-author-reviews-widget.php' );

if ( is_admin() ) {
	require_once( 'includes/admin/pro-admin-functions.php' );
	require_once( 'includes/admin/class-pro-author-review-metabox.php' );
	require_once( 'includes/admin/class-pro-author-review-options.php' );
} else {
	require_once( 'includes/functions-template.php' );
}
