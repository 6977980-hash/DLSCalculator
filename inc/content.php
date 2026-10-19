<?php
/**
 * Page content shared by templates and schema: FAQs, sources, related links and update dates.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * When each theme-written page's content last changed. The byline and schema use this instead of
 * the WordPress post date, because the text lives in the theme, not in the editor.
 *
 * @return array<string, string> Slug => Y-m-d.
 */
function dlscalc_content_dates() {
	return array(
		'dls-calculator'            => '2026-10-19',
		'dls-target-calculator-t20' => '2026-10-19',
		'dls-par-score-table'       => '2026-10-19',
	);
}

/**
 * Last-updated date for the current page.
 *
 * @param string $format PHP date format.
 * @return string
 */
function dlscalc_updated_date( $format ) {
	$dates = dlscalc_content_dates();
	$slug  = dlscalc_current_slug();
	if ( isset( $dates[ $slug ] ) ) {
		$theme = strtotime( $dates[ $slug ] . ' 12:00:00 UTC' );
		$post  = (int) get_the_modified_date( 'U' );
		return wp_date( $format, max( $theme, $post ) );
	}
	return get_the_modified_date( $format );
}

/**
 * Sources cited on the DLS pages.
 *
 * @return array<int, array{0: string, 1: string}> Label, URL.
 */
function dlscalc_dls_sources() {
	return array(
		array( 'ICC playing conditions: Duckworth-Lewis-Stern method, with the Standard Edition resource table and worked examples (PDF)', 'https://images.icc-cricket.com/image/upload/prd/orlbya4cqyhqaceje3b2.pdf' ),
		array( 'ECB: Duckworth/Lewis/Stern methodology, March 2026 (PDF)', 'https://resources.ecb.co.uk/ecb/document/2026/03/24/0ec6d250-8e00-4150-b3b3-1afb64e4853b/Duckworth-Lewis-Stern-Methodology.pdf' ),
	);
}

/**
 * Sources list.
 */
function dlscalc_sources_html() {
	echo '<ul class="sources">';
	foreach ( dlscalc_dls_sources() as $source ) {
		printf( '<li><a href="%s" rel="noopener" target="_blank">%s</a></li>', esc_url( $source[1] ), esc_html( $source[0] ) );
	}
	echo '</ul>';
}

/**
 * Links to the other tools and guides at the end of a tool page.
 *
 * @param string[] $slugs Page slugs with their link text.
 */
function dlscalc_related_html( $slugs ) {
	$text = array(
		'dls-calculator'            => array( 'DLS Calculator', 'Stoppages in both innings, any format.' ),
		'dls-target-calculator-t20' => array( 'DLS Target Calculator T20', 'Rain slider and par table for 20-over matches.' ),
		'dls-par-score-table'       => array( 'DLS Par Score Table', 'Par for every over and wicket, 5 to 50 overs.' ),
		'dls-method'                => array( 'DLS method explained', 'Resources, the formula and the ICC examples.' ),
		'run-rate-calculator'       => array( 'Run Rate Calculator', 'Required rate and runs per ball for the chase.' ),
		'net-run-rate-calculator'   => array( 'Net Run Rate Calculator', 'How a rain-hit result changes the table.' ),
	);
	echo '<nav class="related noprint" aria-label="Related calculators and guides">';
	foreach ( $slugs as $slug ) {
		printf(
			'<a class="card-link" href="%s"><span class="card-link__title">%s</span><span class="card-link__text">%s</span></a>',
			esc_url( dlscalc_page_url( $slug ) ),
			esc_html( $text[ $slug ][0] ),
			esc_html( $text[ $slug ][1] )
		);
	}
	echo '</nav>';
}

/**
 * FAQ questions and answers per page. Answers may contain links (allowed: a href).
 *
 * @param string $slug Page slug.
 * @return array<int, array{0: string, 1: string}>
 */
function dlscalc_faqs( $slug ) {
	$calc = esc_url( dlscalc_page_url( 'dls-calculator' ) );
	$faqs = array(
		'dls-calculator'            => array(
			array( 'How many overs are needed for a DLS result?', 'Team 2 must be able to face at least 20 overs in a 50-over match and 5 overs in a 20-over match. If fewer are possible, there is no result, unless Team 2 has already passed the target or been bowled out. The calculator warns you when the chase falls below that minimum.' ),
			array( 'Can I add more than one rain break?', 'Yes. Add a row for every stoppage in either innings, up to ten per innings. The resources lost in each stoppage are added together, which is how the ICC playing conditions handle several interruptions.' ),
			array( 'Why can my answer differ from the official target?', 'International matches and big franchise leagues use the DLS Professional Edition, which runs on ICC software and is not published. This calculator uses the published Standard Edition table, the ICC\'s official fallback, so targets usually match and sometimes differ by a few runs.' ),
			array( 'What is the par to tie?', 'It is one run less than the target. If the match ends with Team 2 exactly on the par score, the match is tied. When Team 2\'s innings is cut short for good, the par score decides the winner.' ),
			array( 'Which G50 should I use?', 'Use 245 for men\'s internationals and first-class teams. The ICC sets 200 for women\'s, associate and Under-19 matches, but the ECB\'s 2026 methodology lists 245 for Under-19 internationals, so check your competition\'s rules. Local leagues may set their own average first-innings score.' ),
			array( 'What if rain ends Team 1\'s innings early?', 'Add a Team 1 stoppage at the overs bowled when play stopped, with the wickets down, and enter every remaining over as overs lost. The calculator then treats Team 1\'s innings as finished and usually sets Team 2 a target above Team 1\'s score.' ),
			array( 'Can I save or share a result?', 'Yes. The page address updates with your inputs, so the link reproduces the same result. Use the WhatsApp button, copy the link, or print the page with the par table for the scorers.' ),
		),
		'dls-target-calculator-t20' => array(
			array( 'What is the minimum for a result in a T20?', 'Team 2 must be able to face at least 5 overs for a result in a 20-over match. If rain cuts the chase below 5 overs, there is no result unless Team 2 has already passed the target or been bowled out.' ),
			array( 'How do I read the T20 par score table?', 'Find the completed overs down the left and the wickets Team 2 has lost across the top. If play stops for good at that point, Team 2 wins with more runs than that number and the match is tied on it.' ),
			array( 'Do the IPL, PSL and T20 World Cup use DLS?', 'Yes, all three use Duckworth-Lewis-Stern. Their official targets come from the Professional Edition software, so they can differ from this Standard Edition calculator by a run or two in some situations.' ),
			array( 'Can the T20 target be more than Team 1\'s score plus one?', 'Yes, but only when Team 1 lost overs during its own innings and Team 2 did not lose as many. This page assumes Team 1 batted its full overs, so use the <a href="' . $calc . '">full DLS calculator</a> for a stoppage in the first innings.' ),
			array( 'Does this work for T10 and other short formats?', 'Yes. Set the overs per side to any number from 5 to 20, such as 10 for a T10 match. The minimum overs for a result in T10 leagues are set by each competition, so check its playing conditions.' ),
			array( 'Why does a 15-over chase not need three quarters of the runs?', 'Team 2 keeps all 10 wickets for fewer overs, so it can bat more aggressively. A 15-over innings is worth 45.2% against 56.6% for 20 overs, about 80% of the resources, so the target falls to about 80% of the score.' ),
		),
		'dls-par-score-table'       => array(
			array( 'What is a DLS par score?', 'It is the score Team 2 needs at a given point of the chase to be level with Team 1 under DLS. If play stops for good there, Team 2 wins with more runs than the par score and the match is tied on it.' ),
			array( 'How is the par score table calculated?', 'Each cell takes the resources Team 2 has used by that over with that many wickets down, divides them by Team 1\'s resources and multiplies by Team 1\'s score, rounded down. All values come from the ICC Standard Edition table.' ),
			array( 'Why does the par score rise so much with wickets?', 'Wickets are resources too. A team that is 4 down has less batting left than a team that is 1 down after the same overs, so it is judged to have used more of its resources and must have scored more to be ahead.' ),
			array( 'Is the par score the same as the target?', 'No. The target is the par score for the whole chase plus one run. The par score at each over is what Team 2 needs if play ends at that point, so it changes over by over and wicket by wicket.' ),
			array( 'Can I use the table for an ODI?', 'Yes. Choose 50 overs and enter Team 1\'s score to get the par after every over from 1 to 49, for 0 to 9 wickets down. Shorter matches from 5 overs upwards work the same way.' ),
			array( 'What if Team 1\'s innings was interrupted?', 'This table assumes Team 1 batted its full overs. If Team 1 lost overs or Team 2\'s chase has already been cut, use the <a href="' . $calc . '">DLS calculator</a>, which gives the par table for the rest of the chase after every stoppage.' ),
		),
	);
	return $faqs[ $slug ] ?? array();
}
