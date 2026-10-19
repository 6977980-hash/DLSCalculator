<?php
/**
 * DLSCalc child theme bootstrap.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DLSCALC_VERSION', '0.1.0' );
define( 'DLSCALC_DIR', get_stylesheet_directory() );
define( 'DLSCALC_URI', get_stylesheet_directory_uri() );

require DLSCALC_DIR . '/inc/setup.php';
require DLSCALC_DIR . '/inc/template-tags.php';
require DLSCALC_DIR . '/inc/layout.php';
require DLSCALC_DIR . '/inc/pages.php';
require DLSCALC_DIR . '/inc/seo.php';
