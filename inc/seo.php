<?php
/**
 * Search and sharing essentials in <head>: titles, meta descriptions, Open Graph,
 * Twitter cards, icons, base schema and robots.txt.
 *
 * If Yoast SEO or Rank Math is switched on, titles, descriptions, Open Graph and schema are left
 * to the plugin so nothing is printed twice. Icons and robots.txt rules stay with the theme.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether an SEO plugin is handling the head.
 *
 * @return bool
 */
function dlscalc_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' );
}

/**
 * Title tag and meta description per page, keyword first.
 *
 * @return array<string, array{title: string, description: string}>
 */
function dlscalc_seo_pages() {
	return array(
		'home'                      => array(
			'title'       => 'DLSCalc: Free Cricket Calculators for Rain Targets, NRR & Run Rate',
			'description' => 'Free DLS calculator for revised targets and par scores in T20, ODI and local matches, plus a net run rate calculator with qualification scenarios. Works on any phone.',
		),
		'dls-calculator'            => array(
			'title'       => 'DLS Calculator: Revised Target for Rain-Hit Matches (ICC) | DLSCalc',
			'description' => 'Work out the DLS revised target and par score for any rain-hit match. Uses the ICC Standard Edition table, handles several interruptions and shows every step.',
		),
		'dls-target-calculator-t20' => array(
			'title'       => 'DLS Target Calculator T20: Rain Target & Par Score | DLSCalc',
			'description' => 'DLS target calculator for T20 matches. Enter the score and overs lost to get the revised target, par score and an over-by-over par table for every wicket.',
		),
		'dls-par-score-table'       => array(
			'title'       => 'DLS Par Score Calculator & Table (5 to 50 Overs) | DLSCalc',
			'description' => 'DLS par score for every over and wicket of the chase. Pick 5 to 50 overs, enter Team 1\'s score and print the par table for the scorers. ICC Standard Edition.',
		),
		'live-dls-par-score'        => array(
			'title'       => 'Live DLS Par Score: Who Is Ahead on DLS Right Now? | DLSCalc',
			'description' => 'Enter the chasing score over by over and see the live DLS par score, so you know who is ahead if rain stops play. Free live DLS calculator for scorers.',
		),
		'net-run-rate-calculator'   => array(
			'title'       => 'Net Run Rate Calculator (NRR Calculator Cricket) | DLSCalc',
			'description' => 'Calculate net run rate across every match, with the all-out rule built in. Then see the win margin or chase overs your team needs to pass a rival. Free.',
		),
		'run-rate-calculator'       => array(
			'title'       => 'Run Rate Calculator: Current & Required Run Rate | DLSCalc',
			'description' => 'Current run rate, required run rate, runs needed per ball and projected score for any cricket innings. Overs counted in balls, so 12.4 overs is handled correctly.',
		),
		'dls-method'                => array(
			'title'       => 'DLS Method in Cricket Explained: Formula, Table & Examples',
			'description' => 'What the DLS method is, how resources work, the formula for revised targets, the resource table and the ICC worked examples, explained step by step.',
		),
		'how-to-calculate-nrr'      => array(
			'title'       => 'How to Calculate NRR in Cricket: Net Run Rate Formula',
			'description' => 'The net run rate formula with a worked two-match example, the all-out rule, DLS matches and no-results, and how a team can improve its NRR.',
		),
		'vjd-method'                => array(
			'title'       => 'VJD Method in Cricket: Jayadevan System vs DLS Explained',
			'description' => 'What the VJD method is, who V. Jayadevan is, how his rain rule sets targets, where the BCCI used it, and how it differs from DLS and the old D/L method.',
		),
		'about'                     => array(
			'title'       => 'About DLSCalc and Ch Alee | DLSCalc',
			'description' => 'DLSCalc is built by Ch Alee, a cricketer and tournament organiser. How the calculators work, the ICC sources they follow and their limits.',
		),
		'privacy-policy'            => array(
			'title'       => 'Privacy Policy | DLSCalc',
			'description' => 'How DLSCalc handles data. The calculators run in your browser and nothing you type is sent to us.',
		),
		'contact'                   => array(
			'title'       => 'Contact | DLSCalc',
			'description' => 'Contact DLSCalc to report a mistake or suggest a new cricket calculator.',
		),
	) + dlscalc_match_seo_pages();
}

/**
 * The slug of the theme-owned page being viewed, or '' elsewhere.
 *
 * @return string
 */
function dlscalc_current_slug() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_page() ) {
		$post = get_queried_object();
		return $post instanceof WP_Post ? $post->post_name : '';
	}
	return '';
}

/**
 * SEO data for the current page, falling back to the post's own title and excerpt.
 *
 * @return array{title: string, description: string, url: string, image: string, type: string}
 */
function dlscalc_seo_current() {
	$slug  = dlscalc_current_slug();
	$pages = dlscalc_seo_pages();
	$data  = $pages[ $slug ] ?? array(
		'title'       => wp_get_document_title(),
		'description' => is_singular() ? wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 28, '…' ) : get_bloginfo( 'description' ),
	);

	$image_file = 'og/' . ( $slug && file_exists( DLSCALC_DIR . '/assets/img/og/' . $slug . '.png' ) ? $slug : 'default' ) . '.png';

	return array(
		'title'       => $data['title'],
		'description' => $data['description'],
		'url'         => is_front_page() ? home_url( '/' ) : ( is_singular() ? get_permalink() : home_url( add_query_arg( array() ) ) ),
		'image'       => DLSCALC_URI . '/assets/img/' . $image_file,
		'type'        => is_front_page() ? 'website' : 'article',
	);
}

add_filter(
	'pre_get_document_title',
	function ( $title ) {
		if ( dlscalc_seo_plugin_active() ) {
			return $title;
		}
		$pages = dlscalc_seo_pages();
		$slug  = dlscalc_current_slug();
		return isset( $pages[ $slug ] ) ? $pages[ $slug ]['title'] : $title;
	}
);

/**
 * Search engine ownership tags, on the home page only (that is where the verifiers look).
 * Google Search Console token added 19 October 2026. Keep it: removing it unverifies the site.
 */
function dlscalc_head_verification() {
	if ( ! is_front_page() ) {
		return;
	}
	echo '<meta name="google-site-verification" content="lOrnOPv4i22b1lzi2qlfnReUSTe7opBvyjy7X_pHxIc">' . "\n";
}
add_action( 'wp_head', 'dlscalc_head_verification', 2 );

/**
 * Icons and web app manifest. WordPress's own Site Icon wins if one is set in the Customizer.
 */
function dlscalc_head_icons() {
	if ( has_site_icon() ) {
		return;
	}
	$img = DLSCALC_URI . '/assets/img/';
	printf( '<link rel="icon" href="%s" sizes="48x48">' . "\n", esc_url( $img . 'favicon.ico' ) );
	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( $img . 'favicon.svg' ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $img . 'apple-touch-icon.png' ) );
	printf( '<link rel="manifest" href="%s">' . "\n", esc_url( DLSCALC_URI . '/assets/site.webmanifest' ) );
}
add_action( 'wp_head', 'dlscalc_head_icons', 2 );

/**
 * Meta description, Open Graph and Twitter card.
 */
function dlscalc_head_meta() {
	if ( dlscalc_seo_plugin_active() || is_404() ) {
		return;
	}
	$seo  = dlscalc_seo_current();
	$tags = array(
		array( 'name', 'description', $seo['description'] ),
		array( 'property', 'og:type', $seo['type'] ),
		array( 'property', 'og:site_name', 'DLSCalc' ),
		array( 'property', 'og:locale', 'en_US' ),
		array( 'property', 'og:title', $seo['title'] ),
		array( 'property', 'og:description', $seo['description'] ),
		array( 'property', 'og:url', $seo['url'] ),
		array( 'property', 'og:image', $seo['image'] ),
		array( 'property', 'og:image:width', '1200' ),
		array( 'property', 'og:image:height', '630' ),
		array( 'property', 'og:image:alt', $seo['title'] ),
		array( 'name', 'twitter:card', 'summary_large_image' ),
		array( 'name', 'twitter:title', $seo['title'] ),
		array( 'name', 'twitter:description', $seo['description'] ),
		array( 'name', 'twitter:image', $seo['image'] ),
		array( 'name', 'twitter:image:alt', $seo['title'] ),
	);
	if ( is_singular() ) {
		$tags[] = array( 'property', 'article:modified_time', dlscalc_updated_date( 'c' ) );
	}
	foreach ( $tags as $tag ) {
		printf( '<meta %s="%s" content="%s">' . "\n", $tag[0], esc_attr( $tag[1] ), esc_attr( $tag[2] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attribute name is a fixed string.
	}
}
add_action( 'wp_head', 'dlscalc_head_meta', 3 );

/**
 * Profiles elsewhere for the schema's sameAs, so search and AI engines tie the brand and author to them.
 * Add the brand's own profiles (Facebook page, X, YouTube) to 'organization' when they exist.
 *
 * @param string $who 'organization' or 'person'.
 * @return string[]
 */
function dlscalc_same_as( $who ) {
	$profiles = array(
		'organization' => array(),
		'person'       => array(
			'https://www.linkedin.com/in/ali-ahmad-chaudhry-12777486/',
			'https://www.facebook.com/aac7980',
		),
	);
	return $profiles[ $who ] ?? array();
}

/**
 * Base schema: Organization and WebSite on every page, BreadcrumbList and WebPage on inner pages.
 * Tool pages add WebApplication and FAQPage; guides add Article and FAQPage.
 */
function dlscalc_head_schema() {
	if ( dlscalc_seo_plugin_active() || is_404() ) {
		return;
	}
	$home   = home_url( '/' );
	$author = dlscalc_author();
	$graph  = array(
		array(
			'@type'    => 'Organization',
			'@id'      => $home . '#organization',
			'name'     => 'DLSCalc',
			'url'      => $home,
			'logo'     => array(
				'@type'  => 'ImageObject',
				'url'    => DLSCALC_URI . '/assets/img/icon-512.png',
				'width'  => 512,
				'height' => 512,
			),
			'founder'  => array( '@id' => $home . '#author' ),
			'sameAs'   => dlscalc_same_as( 'organization' ),
		),
		array(
			'@type'       => 'Person',
			'@id'         => $home . '#author',
			'name'        => $author['name'],
			'jobTitle'    => $author['role'],
			'description' => $author['bio'],
			'url'         => dlscalc_page_url( 'about' ),
			'sameAs'      => dlscalc_same_as( 'person' ),
		),
		array(
			'@type'         => 'WebSite',
			'@id'           => $home . '#website',
			'name'          => 'DLSCalc',
			'alternateName' => 'DLS Calculator',
			'url'           => $home,
			'inLanguage'    => 'en',
			'publisher'     => array( '@id' => $home . '#organization' ),
		),
	);
	foreach ( $graph as $i => $node ) {
		if ( isset( $node['sameAs'] ) && ! $node['sameAs'] ) {
			unset( $graph[ $i ]['sameAs'] );
		}
	}

	if ( is_singular() && ! is_front_page() ) {
		$seo   = dlscalc_seo_current();
		$items = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => 'Home',
				'item'     => $home,
			),
		);
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => count( $items ) + 1,
				'name'     => get_the_title( $ancestor ),
				'item'     => get_permalink( $ancestor ),
			);
		}
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => count( $items ) + 1,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => get_permalink() . '#breadcrumb',
			'itemListElement' => $items,
		);
		$graph[] = array(
			'@type'        => dlscalc_webpage_type(),
			'@id'          => get_permalink() . '#webpage',
			'url'          => get_permalink(),
			'name'         => $seo['title'],
			'description'  => $seo['description'],
			'isPartOf'     => array( '@id' => $home . '#website' ),
			'breadcrumb'   => array( '@id' => get_permalink() . '#breadcrumb' ),
			'dateModified' => dlscalc_updated_date( 'c' ),
			'inLanguage'   => 'en',
		);
		$graph   = array_merge( $graph, dlscalc_tool_schema( $seo ), dlscalc_guide_schema( $seo ) );
	}

	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	) . "</script>\n";
}
add_action( 'wp_head', 'dlscalc_head_schema', 30 );

/**
 * WebApplication and FAQPage nodes for tool pages.
 *
 * @param array $seo Current page SEO data.
 * @return array[]
 */
function dlscalc_tool_schema( $seo ) {
	$slug  = dlscalc_current_slug();
	$home  = home_url( '/' );
	$url   = get_permalink();
	$nodes = array();
	if ( isset( dlscalc_dls_tools()[ $slug ] ) || isset( dlscalc_nrr_tools()[ $slug ] ) ) {
		$nodes[] = array(
			'@type'               => 'WebApplication',
			'@id'                 => $url . '#app',
			'name'                => get_the_title(),
			'url'                 => $url,
			'description'         => $seo['description'],
			'applicationCategory' => 'SportsApplication',
			'operatingSystem'     => 'Any',
			'browserRequirements' => 'Requires a modern web browser',
			'isAccessibleForFree' => true,
			'inLanguage'          => 'en',
			'offers'              => array(
				'@type'         => 'Offer',
				'price'         => '0',
				'priceCurrency' => 'USD',
			),
			'author'              => array( '@id' => $home . '#author' ),
			'publisher'           => array( '@id' => $home . '#organization' ),
			'isPartOf'            => array( '@id' => $url . '#webpage' ),
		);
	}
	$faqs = dlscalc_faqs( $slug );
	if ( $faqs ) {
		$nodes[] = array(
			'@type'      => 'FAQPage',
			'@id'        => $url . '#faq',
			'isPartOf'   => array( '@id' => $url . '#webpage' ),
			'mainEntity' => array_map(
				function ( $faq ) {
					return array(
						'@type'          => 'Question',
						'name'           => $faq[0],
						'acceptedAnswer' => array(
							'@type' => 'Answer',
							'text'  => wp_strip_all_tags( $faq[1] ),
						),
					);
				},
				$faqs
			),
		);
	}
	return $nodes;
}

/**
 * robots.txt: welcome search and AI answer crawlers, keep admin out, point to the sitemap.
 * Only takes effect while WordPress serves robots.txt (no physical robots.txt in public_html).
 *
 * @param string $output Core output.
 * @param bool   $public Whether "discourage search engines" is off.
 * @return string
 */
function dlscalc_robots_txt( $output, $public ) {
	if ( ! $public ) {
		return $output;
	}
	$lines = array( 'User-agent: *', 'Disallow: /wp-admin/', 'Allow: /wp-admin/admin-ajax.php', 'Disallow: /?s=', 'Disallow: /search/', '' );
	foreach ( array( 'Googlebot', 'Bingbot', 'GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'PerplexityBot', 'ClaudeBot', 'Claude-SearchBot', 'Google-Extended', 'Applebot' ) as $bot ) {
		$lines[] = 'User-agent: ' . $bot;
		$lines[] = 'Allow: /';
		$lines[] = '';
	}
	$lines[] = 'Sitemap: ' . home_url( '/wp-sitemap.xml' );
	return implode( "\n", $lines ) . "\n";
}
add_filter( 'robots_txt', 'dlscalc_robots_txt', 20, 2 );

/**
 * Keep the core sitemap to pages: no users, no empty post archives.
 */
add_filter(
	'wp_sitemaps_add_provider',
	function ( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);
add_filter(
	'wp_sitemaps_taxonomies',
	function () {
		return array();
	}
);

/**
 * The site has no blog: list only the theme's own pages, so WordPress's sample post and page stay out.
 */
add_filter(
	'wp_sitemaps_post_types',
	function ( $types ) {
		return array_intersect_key( $types, array( 'page' => true ) );
	}
);
add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'page' === $post_type ) {
			$args['post_name__in'] = array_keys( dlscalc_site_pages() );
		}
		return $args;
	},
	10,
	2
);

/**
 * Author, date, category, tag and attachment archives are thin duplicate pages on a site like this;
 * send them home (attachments to their parent). Runs before WordPress's own canonical redirect,
 * which would otherwise send ?author=1 to /author/<username>/ and give the login name away.
 */
function dlscalc_redirect_thin_archives() {
	if ( is_author() || is_date() || is_category() || is_tag() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
	if ( is_attachment() ) {
		$parent = wp_get_post_parent_id( get_queried_object_id() );
		wp_safe_redirect( $parent ? get_permalink( $parent ) : home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'dlscalc_redirect_thin_archives', 1 );
