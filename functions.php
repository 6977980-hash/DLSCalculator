<?php
/**
 * DLSCalc child theme bootstrap.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DLSCALC_VERSION', '0.6.0' );
define( 'DLSCALC_DIR', get_stylesheet_directory() );
define( 'DLSCALC_URI', get_stylesheet_directory_uri() );

require DLSCALC_DIR . '/inc/setup.php';
require DLSCALC_DIR . '/inc/template-tags.php';
require DLSCALC_DIR . '/inc/layout.php';
require DLSCALC_DIR . '/inc/pages.php';
require DLSCALC_DIR . '/inc/seo.php';
require DLSCALC_DIR . '/inc/content.php';
require DLSCALC_DIR . '/inc/dls.php';
require DLSCALC_DIR . '/inc/dls-ui.php';
require DLSCALC_DIR . '/inc/nrr.php';
require DLSCALC_DIR . '/inc/nrr-ui.php';
require DLSCALC_DIR . '/inc/guides.php';
require DLSCALC_DIR . '/inc/ads.php';
require DLSCALC_DIR . '/inc/llms.php';
require DLSCALC_DIR . '/inc/indexnow.php';
require DLSCALC_DIR . '/inc/matches.php';
require DLSCALC_DIR . '/inc/matches-ui.php';
require DLSCALC_DIR . '/inc/embed.php';
require DLSCALC_DIR . '/inc/pwa.php';
require DLSCALC_DIR . '/inc/security.php';
