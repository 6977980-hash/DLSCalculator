<?php
/**
 * Guide pages (/dls-method/, /how-to-calculate-nrr/, /vjd-method/) and the trust pages
 * (About, Contact, Privacy Policy): shared layout, styles and Article schema.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The guides: heading, short answer, contents, call to action and related links.
 * Articles live in template-parts/content/<slug>.php; FAQs in dlscalc_faqs().
 *
 * @return array<string, array{h1: string, answer: string, published: string, toc: array<string, string>, cta: array{0: string, 1: string, 2: string}, related: string[]}>
 */
function dlscalc_guides() {
	return array(
		'dls-method'           => array(
			'h1'        => 'DLS Method in Cricket: How Rain Targets Are Calculated',
			'answer'    => 'The DLS method (Duckworth-Lewis-Stern) is the ICC\'s rule for setting a fair target when overs are lost to rain or bad light. It treats the overs left and the wickets in hand as resources, and scales Team 1\'s score by the share of resources Team 2 gets.',
			'published' => '2026-10-19',
			'toc'       => array(
				'full-form'      => 'DLS full form and history',
				'resources'      => 'How resources work',
				'formula'        => 'The DLS formula',
				'resource-table' => 'DLS resource table',
				'examples'       => 'Worked examples',
				'minimum-overs'  => 'Minimum overs and abandoned matches',
				'dl-vs-dls'      => 'D/L vs DLS',
				'editions'       => 'Standard vs Professional Edition',
				'criticism'      => 'Is DLS fair?',
				'faq'            => 'FAQ',
			),
			'cta'       => array( 'dls-calculator', 'Rain in your match right now?', 'Open the DLS Calculator' ),
			'related'   => array( 'dls-calculator', 'dls-target-calculator-t20', 'dls-par-score-table', 'vjd-method', 'famous-dls-matches' ),
		),
		'how-to-calculate-nrr' => array(
			'h1'        => 'How to Calculate NRR in Cricket (Net Run Rate Formula)',
			'answer'    => 'Net run rate (NRR) is a team\'s scoring rate minus its opponents\' scoring rate across a tournament: total runs scored ÷ total overs faced, minus total runs conceded ÷ total overs bowled. It separates teams that finish level on points.',
			'published' => '2026-10-19',
			'toc'       => array(
				'full-form'     => 'NRR full form',
				'formula'       => 'Net run rate formula',
				'step-by-step'  => 'Step by step',
				'example'       => 'Two-match example',
				'special-cases' => 'All out, DLS and no result',
				'tournament'    => 'NRR in a points table',
				'improve'       => 'How to improve NRR',
				'faq'           => 'FAQ',
			),
			'cta'       => array( 'net-run-rate-calculator', 'Work out your team\'s NRR and what it needs to qualify', 'Open the NRR Calculator' ),
			'related'   => array( 'net-run-rate-calculator', 'run-rate-calculator', 'dls-method', 'dls-calculator' ),
		),
		'vjd-method'           => array(
			'h1'        => 'VJD Method in Cricket: Jayadevan\'s Rain Rule vs DLS',
			'answer'    => 'The VJD method is a rain rule for limited-overs cricket created by V. Jayadevan, an engineer from Kerala. Like DLS it resets the target when overs are lost, but it models how scoring speeds up through an innings in its own way. India\'s BCCI uses it in domestic cricket, while the ICC uses DLS.',
			'published' => '2026-10-19',
			'toc'       => array(
				'what-is-vjd'    => 'What VJD is',
				'how-vjd-works'  => 'How it works',
				'example'        => 'Worked example',
				'vjd-vs-dls'     => 'VJD vs DLS',
				'where-used'     => 'Where it is used',
				'famous-matches' => 'Matches decided by VJD',
				'icc-decision'   => 'Why the ICC uses DLS',
				'vjd-calculator' => 'Is there a VJD calculator?',
				'faq'            => 'FAQ',
			),
			'cta'       => array( 'dls-calculator', 'Need a rain target for your match?', 'Open the DLS Calculator' ),
			'related'   => array( 'dls-method', 'dls-calculator', 'dls-target-calculator-t20', 'dls-par-score-table' ),
		),
	) + dlscalc_match_guides();
}

/**
 * Slugs of the trust pages, which use the article styles but carry no byline.
 *
 * @return string[]
 */
function dlscalc_trust_slugs() {
	return array( 'about', 'contact', 'privacy-policy' );
}

/**
 * Whether the current page is a guide.
 *
 * @return bool
 */
function dlscalc_is_guide_page() {
	return isset( dlscalc_guides()[ dlscalc_current_slug() ] );
}

/**
 * Guides share one template.
 *
 * @param string $template Template WordPress picked.
 * @return string
 */
function dlscalc_guide_template( $template ) {
	if ( dlscalc_is_guide_page() ) {
		$guide = locate_template( 'page-guide.php' );
		return $guide ? $guide : $template;
	}
	return $template;
}
add_filter( 'template_include', 'dlscalc_guide_template' );

/**
 * Article styles on guides and trust pages. Tool pages print them from dlscalc_dls_styles().
 */
function dlscalc_guide_styles() {
	if ( dlscalc_is_guide_page() || in_array( dlscalc_current_slug(), dlscalc_trust_slugs(), true ) ) {
		dlscalc_print_inline_css( 'assets/css/prose.css', 'dlscalc-prose-css' );
	}
}
add_action( 'wp_head', 'dlscalc_guide_styles', 6 );

/**
 * Same clean head as the tool pages: these templates use no blocks.
 */
function dlscalc_guide_dequeue() {
	if ( dlscalc_is_guide_page() || in_array( dlscalc_current_slug(), dlscalc_trust_slugs(), true ) ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'dlscalc_guide_dequeue', 100 );

/**
 * Minutes to read, at 230 words a minute.
 *
 * @param string $html Article HTML.
 * @return int
 */
function dlscalc_read_minutes( $html ) {
	return max( 1, (int) round( str_word_count( wp_strip_all_tags( $html ) ) / 230 ) );
}

/**
 * Article schema for guides, and AboutPage / ContactPage types for the trust pages.
 *
 * @param array $seo Current page SEO data.
 * @return array[]
 */
function dlscalc_guide_schema( $seo ) {
	$slug   = dlscalc_current_slug();
	$guides = dlscalc_guides();
	if ( ! isset( $guides[ $slug ] ) ) {
		return array();
	}
	$home = home_url( '/' );
	$url  = get_permalink();
	return array(
		array(
			'@type'            => 'Article',
			'@id'              => $url . '#article',
			'headline'         => $guides[ $slug ]['h1'],
			'description'      => $seo['description'],
			'image'            => $seo['image'],
			'datePublished'    => $guides[ $slug ]['published'] . 'T12:00:00+00:00',
			'dateModified'     => dlscalc_updated_date( 'c' ),
			'author'           => array( '@id' => $home . '#author' ),
			'publisher'        => array( '@id' => $home . '#organization' ),
			'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
			'isPartOf'         => array( '@id' => $url . '#webpage' ),
			'inLanguage'       => 'en',
		),
	);
}

/**
 * Schema type of the WebPage node.
 *
 * @return string
 */
function dlscalc_webpage_type() {
	$types = array(
		'about'   => 'AboutPage',
		'contact' => 'ContactPage',
	);
	return $types[ dlscalc_current_slug() ] ?? 'WebPage';
}

/**
 * Contact email shown on the About and Contact pages.
 *
 * @return string
 */
function dlscalc_contact_email() {
	return 'contact@dlscalculator.com';
}

/**
 * Opening of a trust page (About, Contact, Privacy Policy): breadcrumbs and heading.
 * Pairs with dlscalc_trust_page_close().
 *
 * @param string $h1  Page heading.
 * @param string $sub Optional line under the heading.
 */
function dlscalc_trust_page_open( $h1, $sub = '' ) {
	the_post();
	?>
	<div class="content-area" id="primary">
		<main class="site-main" id="main">
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<?php dlscalc_breadcrumbs(); ?>
					<h1 class="entry-title"><?php echo esc_html( $h1 ); ?></h1>
					<?php if ( $sub ) : ?>
						<p class="byline"><?php echo esc_html( $sub ); ?></p>
					<?php endif; ?>
				</header>
				<div class="entry-content tool-prose">
	<?php
}

/**
 * Closing of a trust page.
 */
function dlscalc_trust_page_close() {
	?>
				</div>
			</article>
		</main>
	</div>
	<?php
}

/**
 * The contact email as a mailto link.
 *
 * @param string $subject Optional email subject.
 * @return string
 */
function dlscalc_contact_link( $subject = '' ) {
	$email = dlscalc_contact_email();
	$href  = 'mailto:' . $email . ( $subject ? '?subject=' . rawurlencode( $subject ) : '' );
	return '<a href="' . esc_url( $href, array( 'mailto' ) ) . '">' . esc_html( $email ) . '</a>';
}
