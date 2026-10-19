<?php
/**
 * Reusable template pieces: logo, menus, breadcrumbs, author box, dates.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site pages the theme links to before menus are set up in WP admin.
 * Keyed by slug so menus, cards and footers stay in sync.
 *
 * @return array<string, array{label: string, title: string, blurb: string}>
 */
function dlscalc_tools() {
	return array(
		'dls-calculator'            => array(
			'label' => 'DLS Calculator',
			'title' => 'DLS Calculator',
			'blurb' => 'Revised target and par score for any format, with interruptions in either innings.',
		),
		'dls-target-calculator-t20' => array(
			'label' => 'T20 Target',
			'title' => 'DLS Target Calculator T20',
			'blurb' => 'Built for 20-over matches, with an over-by-over par score table for every wicket.',
		),
		'net-run-rate-calculator'   => array(
			'label' => 'Net Run Rate',
			'title' => 'Net Run Rate Calculator',
			'blurb' => 'Net run rate from your points table, plus the margin you need to qualify. Free, no app needed.',
		),
		'run-rate-calculator'       => array(
			'label' => 'Run Rate',
			'title' => 'Run Rate Calculator',
			'blurb' => 'Current and required run rate, runs needed per ball, and projected scores.',
		),
	);
}

/**
 * URL for a site page by slug.
 *
 * @param string $slug Page slug.
 * @return string
 */
function dlscalc_page_url( $slug ) {
	return home_url( '/' . trim( $slug, '/' ) . '/' );
}

/**
 * Cricket-ball logo and wordmark.
 */
function dlscalc_logo() {
	?>
	<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<svg width="30" height="30" viewBox="0 0 30 30" aria-hidden="true" focusable="false"><circle cx="15" cy="15" r="13" fill="#C8372D" stroke="#F5F3EC" stroke-width="2"/><path d="M9 5c3 6 3 14 0 20M21 5c-3 6-3 14 0 20" fill="none" stroke="#F5F3EC" stroke-width="1.5" stroke-dasharray="2 2"/></svg>
		<span class="site-logo__text">DLS<span>Calc</span></span>
	</a>
	<?php
}

/**
 * A menu location, or the built-in links when no menu is assigned yet.
 *
 * @param string $location Menu location.
 * @param string $label    Accessible label for the nav element.
 * @param string $class    CSS class for the list.
 */
function dlscalc_menu( $location, $label, $class ) {
	echo '<nav class="' . esc_attr( $class ) . '" aria-label="' . esc_attr( $label ) . '">';
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'depth'          => 1,
				'fallback_cb'    => false,
			)
		);
	} else {
		$links = 'primary' === $location ? dlscalc_default_primary_links() : dlscalc_default_footer_links();
		echo '<ul class="menu">';
		foreach ( $links as $slug => $text ) {
			if ( ! dlscalc_page_is_live( $slug ) ) {
				continue;
			}
			$url     = dlscalc_page_url( $slug );
			$current = dlscalc_is_current_url( $url ) ? ' aria-current="page"' : '';
			printf( '<li><a href="%s"%s>%s</a></li>', esc_url( $url ), $current, esc_html( $text ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $current is a fixed string.
		}
		echo '</ul>';
	}
	echo '</nav>';
}

/**
 * Header links used until a menu is assigned in WP admin.
 *
 * @return array<string, string> Slug => label.
 */
function dlscalc_default_primary_links() {
	$links = wp_list_pluck( dlscalc_tools(), 'label' );
	$links['dls-method'] = 'Guides';
	return $links;
}

/**
 * Footer links used until a menu is assigned in WP admin.
 *
 * @return array<string, string> Slug => label.
 */
function dlscalc_default_footer_links() {
	return array(
		'about'                => 'About',
		'dls-method'           => 'DLS method guide',
		'how-to-calculate-nrr' => 'NRR guide',
		'privacy-policy'       => 'Privacy Policy',
		'contact'              => 'Contact',
	);
}

/**
 * Whether a published page exists at this slug, so menus never link to a 404.
 *
 * @param string $slug Page slug.
 * @return bool
 */
function dlscalc_page_is_live( $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	return $page && 'publish' === $page->post_status;
}

/**
 * Whether a URL is the page being viewed.
 *
 * @param string $url Absolute URL.
 * @return bool
 */
function dlscalc_is_current_url( $url ) {
	if ( is_404() || is_search() ) {
		return false;
	}
	$here = untrailingslashit( strtok( home_url( add_query_arg( array() ) ), '?' ) );
	return untrailingslashit( $url ) === $here;
}

/**
 * Breadcrumb trail: Home / Parent / Current.
 */
function dlscalc_breadcrumbs() {
	if ( is_front_page() || ! is_singular() ) {
		return;
	}
	$crumbs   = array();
	$crumbs[] = array( home_url( '/' ), 'Home' );
	foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
		$crumbs[] = array( get_permalink( $ancestor ), get_the_title( $ancestor ) );
	}
	echo '<nav class="breadcrumbs" aria-label="Breadcrumb"><ol>';
	foreach ( $crumbs as $crumb ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $crumb[0] ), esc_html( $crumb[1] ) );
	}
	printf( '<li aria-current="page">%s</li>', esc_html( get_the_title() ) );
	echo '</ol></nav>';
}

/**
 * Author name, role and short bio used site-wide.
 *
 * @return array{name: string, role: string, initials: string, bio: string}
 */
function dlscalc_author() {
	return array(
		'name'     => 'Ch Alee',
		'role'     => 'Cricketer and tournament organiser',
		'initials' => 'CA',
		'bio'      => 'I play cricket and organise tournaments, and I built these calculators because I needed accurate rain targets during live matches. Every tool follows the ICC playing conditions and shows its working.',
	);
}

/**
 * "By Ch Alee · Last updated …" line under the page title.
 */
function dlscalc_byline() {
	$author = dlscalc_author();
	printf(
		'<p class="byline">By <a href="%1$s">%2$s</a> · Last updated <time datetime="%3$s">%4$s</time></p>',
		esc_url( dlscalc_page_url( 'about' ) ),
		esc_html( $author['name'] ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date( 'j F Y' ) )
	);
}

/**
 * Author box shown at the end of tools and guides.
 */
function dlscalc_author_box() {
	$author = dlscalc_author();
	?>
	<section class="author-box" aria-label="About the author">
		<div class="author-box__badge" aria-hidden="true"><?php echo esc_html( $author['initials'] ); ?></div>
		<div class="author-box__body">
			<div class="author-box__name">By <a href="<?php echo esc_url( dlscalc_page_url( 'about' ) ); ?>"><?php echo esc_html( $author['name'] ); ?></a></div>
			<div class="author-box__role"><?php echo esc_html( $author['role'] ); ?></div>
			<p><?php echo esc_html( $author['bio'] ); ?></p>
		</div>
	</section>
	<?php
}
