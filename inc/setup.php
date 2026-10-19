<?php
/**
 * Theme setup, assets and front-end cleanup.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menus and theme supports.
 */
function dlscalc_setup() {
	register_nav_menus(
		array(
			'primary' => __( 'Main menu', 'dlscalc' ),
			'footer'  => __( 'Footer menu', 'dlscalc' ),
		)
	);
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
}
add_action( 'after_setup_theme', 'dlscalc_setup', 20 );

/**
 * Styles: our stylesheet is small (under 10 KB), so it is printed inline in <head>.
 * That leaves no render-blocking CSS request at all, which is the main lever on mobile LCP.
 * GeneratePress's own CSS and menu script are dropped: the theme renders its own header,
 * footer and layout, and over 90% of the parent stylesheet went unused.
 */
function dlscalc_enqueue_assets() {
	wp_dequeue_style( 'generate-style' );
	wp_dequeue_style( 'generate-child' );
	wp_dequeue_script( 'generate-menu' );
	wp_dequeue_script( 'generate-a11y' );
	wp_dequeue_script( 'generate-dropdown-click' );
	wp_dequeue_script( 'generate-back-to-top' );
}
add_action( 'wp_enqueue_scripts', 'dlscalc_enqueue_assets', 50 );

/**
 * Print a theme stylesheet inline, minified. Font URLs are rewritten to absolute theme URLs.
 *
 * @param string $relative Path relative to the theme folder.
 * @param string $id       Style element id.
 */
function dlscalc_print_inline_css( $relative, $id ) {
	$css = file_get_contents( DLSCALC_DIR . '/' . $relative ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	if ( false === $css ) {
		return;
	}
	$css = str_replace( '../fonts/', DLSCALC_URI . '/assets/fonts/', $css );
	$css = preg_replace( '#/\*.*?\*/#s', '', $css );
	$css = preg_replace( '/\s+/', ' ', $css );
	$css = preg_replace( '/\s*([{};:,>])\s*/', '$1', $css );
	echo '<style id="' . esc_attr( $id ) . '">' . trim( $css ) . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme's own stylesheet.
}

/**
 * Print the main stylesheet inline.
 */
function dlscalc_inline_styles() {
	dlscalc_print_inline_css( 'assets/css/main.css', 'dlscalc-css' );
}
add_action( 'wp_head', 'dlscalc_inline_styles', 5 );

/**
 * Cache-busting version: file modification time, so every deploy refreshes caches.
 *
 * @param string $relative Path relative to the theme folder.
 * @return string
 */
function dlscalc_asset_version( $relative ) {
	$path = DLSCALC_DIR . '/' . $relative;
	return file_exists( $path ) ? DLSCALC_VERSION . '.' . filemtime( $path ) : DLSCALC_VERSION;
}

/**
 * Preload the two self-hosted fonts so text renders in the right face without a layout shift.
 */
function dlscalc_preload_fonts() {
	$fonts = array( 'barlow-condensed-700.woff2', 'source-sans-3-var.woff2' );
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( DLSCALC_URI . '/assets/fonts/' . $font )
		);
	}
	echo '<meta name="theme-color" content="#13452F">' . "\n";
}
add_action( 'wp_head', 'dlscalc_preload_fonts', 1 );

/**
 * Full-width layout everywhere: tool pages need the room, and there is no sidebar content.
 */
add_filter( 'generate_sidebar_layout', fn() => 'no-sidebar' );

/**
 * Drop front-end extras that cost requests and give nothing back on this site.
 */
function dlscalc_cleanup() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
}
add_action( 'init', 'dlscalc_cleanup' );

/**
 * The block library stylesheet is only needed where blocks are used; the home page is a template.
 */
function dlscalc_trim_block_css() {
	if ( is_front_page() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'dlscalc_trim_block_css', 100 );
