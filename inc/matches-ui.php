<?php
/**
 * Famous DLS matches: the hub (/famous-dls-matches/) and one page per match under it.
 * The pages are guides (page-guide.php); this file adds their URLs, SEO data, FAQs and the
 * "recalculated with our calculator" box. Match facts live in dlscalc_matches() (inc/matches.php).
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Path of a site page below the home URL. Match pages sit under the hub.
 *
 * @param string $slug Page slug.
 * @return string
 */
function dlscalc_page_path( $slug ) {
	$slug = trim( $slug, '/' );
	return isset( dlscalc_matches()[ $slug ] ) ? 'famous-dls-matches/' . $slug : $slug;
}

/**
 * The hub and the match pages as guides: heading, answer box, contents, call to action, related links.
 *
 * @return array
 */
function dlscalc_match_guides() {
	$guides = array(
		'famous-dls-matches' => array(
			'h1'        => 'Famous DLS Matches: Rain-Hit Classics Recalculated',
			'answer'    => 'These are the rain-affected matches cricket fans still argue about, from the 1992 World Cup semi-final to the 2024 T20 World Cup. For each one we put the real scores into our DLS Calculator and compare its target with the one the umpires announced, so you can see how the rain rule worked and where our published-table answer differs.',
			'published' => '2026-10-19',
			'toc'       => array(
				'matches'    => 'The matches',
				'accuracy'   => 'Our target vs the official one',
				'why-differ' => 'Why some targets differ',
				'lessons'    => 'What these matches teach',
				'faq'        => 'FAQ',
			),
			'cta'       => array( 'dls-calculator', 'Rain in your own match?', 'Open the DLS Calculator' ),
			'related'   => array( 'dls-calculator', 'dls-target-calculator-t20', 'dls-method', 'live-dls-par-score' ),
		),
	);
	// Each match page also links to the next two matches of the same format, so every match gets links from two others.
	$by_format = array();
	foreach ( dlscalc_matches() as $slug => $m ) {
		$by_format[ $m['format'] ][] = $slug;
	}
	foreach ( dlscalc_matches() as $slug => $m ) {
		$same = $by_format[ $m['format'] ];
		$at   = array_search( $slug, $same, true );
		$next = array();
		for ( $i = 1; $i <= min( 2, count( $same ) - 1 ); $i++ ) {
			$next[] = $same[ ( $at + $i ) % count( $same ) ];
		}
		$guides[ $slug ] = array(
			'h1'        => $m['h1'],
			'answer'    => $m['answer'],
			'published' => '2026-10-19',
			'toc'       => $m['toc'] + array(
				'recalculated' => 'Recalculated with our calculator',
				'faq'          => 'FAQ',
			),
			'cta'       => array( 'dls-calculator', 'Rain in your own match?', 'Open the DLS Calculator' ),
			'related'   => array_merge( array( 'famous-dls-matches', 'dls-calculator', 't20' === $m['format'] ? 'dls-target-calculator-t20' : 'dls-par-score-table', 'dls-method' ), $next ),
		);
	}
	return $guides;
}

/**
 * Title tags and meta descriptions for the hub and match pages.
 *
 * @return array<string, array{title: string, description: string}>
 */
function dlscalc_match_seo_pages() {
	$pages = array(
		'famous-dls-matches' => array(
			'title'       => 'Famous DLS Matches: Rain Rule Controversies Recalculated',
			'description' => 'Famous rain-hit cricket matches from 1992 to the 2024 T20 World Cup, recalculated with our DLS calculator and compared with the official targets.',
		),
	);
	foreach ( dlscalc_matches() as $slug => $m ) {
		$pages[ $slug ] = $m['seo'];
	}
	return $pages;
}

/**
 * FAQs for the hub and match pages.
 *
 * @param string $slug Page slug.
 * @return array<int, array{0: string, 1: string}>
 */
function dlscalc_match_faqs( $slug ) {
	if ( 'famous-dls-matches' === $slug ) {
		$method = esc_url( dlscalc_page_url( 'dls-method' ) );
		return array(
			array( 'What is the most controversial rain rule match?', 'The 1992 World Cup semi-final between England and South Africa in Sydney. The old "most productive overs" rule turned South Africa\'s 22 needed off 13 balls into 21 off 1 ball after a 12-minute shower. That match is the reason the Duckworth-Lewis method was invented.' ),
			array( 'Why does your target sometimes differ from the official one?', 'International matches and big leagues use the DLS Professional Edition, which runs on ICC software that is not published. Our calculator uses the published Standard Edition table, the ICC\'s official fallback. In 32 recent matches every T20 figure was within 3 runs, while ODIs differed by up to 23 runs when Team 1 made a big score or a long innings was cut short.' ),
			array( 'Was DLS used in the 2019 World Cup semi-final?', 'No. Rain stopped New Zealand on 211 for 5 after 46.1 overs, but the match finished on the reserve day with no overs lost, so India chased the full 240. DLS targets were only worked out in case more rain came.' ),
			array( 'Has a team ever lost because it misread the DLS par score?', 'Yes. At the 2003 World Cup South Africa thought 229 after 45 overs would win against Sri Lanka, but 229 was the par score, which only tied the match. The tie knocked the hosts out of their own World Cup.' ),
			array( 'Where can I learn how the targets are worked out?', 'Our <a href="' . $method . '">DLS method guide</a> explains resources, the formula, the resource table and the ICC worked examples step by step.' ),
		);
	}
	$matches = dlscalc_matches();
	return isset( $matches[ $slug ] ) ? $matches[ $slug ]['faqs'] : array();
}

/**
 * Our calculator's result for a match, run through the PHP engine on its real numbers.
 *
 * @param array $m Match from dlscalc_matches().
 * @return array Engine result.
 */
function dlscalc_match_result( $m ) {
	return dlscalc_dls_calc( $m['in'] );
}

/**
 * Our figure in words: the target for a chase that went on, or the par score when the chase ended.
 *
 * @param array $r Engine result.
 * @return string
 */
function dlscalc_match_ours( $r ) {
	if ( ! empty( $r['err'] ) ) {
		return '–';
	}
	return $r['ended']
		? 'Par ' . $r['par'] . ' at ' . dlscalc_dls_overs( $r['lastAt'] ) . ' overs'
		: dlscalc_match_target( $r );
}

/**
 * "235 in 43 overs".
 *
 * @param array $r Engine result.
 * @return string
 */
function dlscalc_match_target( $r ) {
	return $r['target'] . ' in ' . dlscalc_dls_overs( $r['allot'] ) . ' overs';
}

/**
 * Our number minus the official one, in words.
 *
 * @param array $m Match.
 * @param array $r Engine result.
 * @return string
 */
function dlscalc_match_diff( $m, $r ) {
	if ( ! empty( $r['err'] ) || null === $m['official']['n'] ) {
		return 'No DLS figure then';
	}
	$diff = ( $r['ended'] ? $r['par'] : $r['target'] ) - $m['official']['n'];
	if ( 0 === $diff ) {
		return 'Same';
	}
	return sprintf( '%s%d %s', $diff > 0 ? '+' : '−', abs( $diff ), 1 === abs( $diff ) ? 'run' : 'runs' );
}

/**
 * The calculator link for a match: the tool page with the match's inputs in the address.
 *
 * @param array $m Match.
 * @return string
 */
function dlscalc_match_permalink( $m ) {
	return dlscalc_dls_permalink( 'dls-calculator', $m['in'] );
}

/**
 * Box on a match page: the inputs we used, our figure next to the official one, and the working.
 *
 * @param string $slug Match slug.
 */
function dlscalc_match_box( $slug ) {
	$m = dlscalc_matches()[ $slug ];
	$r = dlscalc_match_result( $m );
	?>
	<div class="table-wrap">
		<table class="data">
			<caption>The numbers we entered and what our calculator gives</caption>
			<tbody>
				<?php foreach ( $m['entered'] as $label => $value ) : ?>
					<tr><th scope="row"><?php echo esc_html( $label ); ?></th><td><?php echo esc_html( $value ); ?></td></tr>
				<?php endforeach; ?>
				<tr><th scope="row">Our result (Standard Edition)</th><td><strong><?php echo esc_html( dlscalc_match_ours( $r ) ); ?></strong></td></tr>
				<tr><th scope="row"><?php echo esc_html( $m['official']['label'] ); ?></th><td><?php echo esc_html( $m['official']['text'] ); ?></td></tr>
				<tr><th scope="row">Difference</th><td><?php echo esc_html( dlscalc_match_diff( $m, $r ) ); ?></td></tr>
			</tbody>
		</table>
	</div>
	<p><strong>The working, step by step:</strong></p>
	<?php dlscalc_dls_steps( $r ); ?>
	<p><a class="btn btn--gold" href="<?php echo esc_url( dlscalc_match_permalink( $m ) ); ?>">Open these numbers in the calculator</a></p>
	<?php
}

/**
 * Hub table: every match, the official target and ours.
 */
function dlscalc_matches_table() {
	?>
	<div class="table-wrap">
		<table class="data">
			<caption>Official target or par score against our Standard Edition calculator</caption>
			<thead><tr><th scope="col">Match</th><th scope="col">Rule used</th><th scope="col">Official</th><th scope="col">Our calculator</th><th scope="col">Difference</th></tr></thead>
			<tbody>
				<?php
				foreach ( dlscalc_matches() as $slug => $m ) :
					$r = dlscalc_match_result( $m );
					?>
					<tr>
						<th scope="row"><a href="<?php echo esc_url( dlscalc_page_url( $slug ) ); ?>"><?php echo esc_html( $m['title'] ); ?></a></th>
						<td><?php echo esc_html( $m['rule'] ); ?></td>
						<td><?php echo esc_html( $m['official']['text'] ); ?></td>
						<td><?php echo esc_html( dlscalc_match_ours( $r ) ); ?></td>
						<td><?php echo esc_html( dlscalc_match_diff( $m, $r ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Hub cards: one link per match with its one-line story.
 */
function dlscalc_match_cards() {
	echo '<div class="related related--matches">';
	foreach ( dlscalc_matches() as $slug => $m ) {
		if ( ! dlscalc_page_is_live( $slug ) ) {
			continue;
		}
		printf(
			'<a class="card-link" href="%s"><span class="card-link__title">%s</span><span class="card-link__text">%s</span></a>',
			esc_url( dlscalc_page_url( $slug ) ),
			esc_html( $m['title'] ),
			esc_html( $m['card'] )
		);
	}
	echo '</div>';
}

/**
 * End of a match article: the recalculation, the FAQ and the sources.
 *
 * @param string $slug    Match slug.
 * @param array  $sources Label and URL pairs for this match; the ICC DLS sources follow them.
 */
function dlscalc_match_tail( $slug, $sources ) {
	echo '<h2 id="recalculated">What does our DLS calculator give for this match?</h2>';
	dlscalc_match_box( $slug );
	echo '<p>Our calculator uses the published ICC Standard Edition table. International matches and big franchise leagues use the Professional Edition, which runs on ICC software and is not published, so official figures can differ. ' . esc_html( dlscalc_accuracy_line() ) . ' The <a href="' . esc_url( dlscalc_page_url( 'famous-dls-matches' ) ) . '#accuracy">famous matches page</a> compares every match side by side.</p>';
	echo '<h2 id="faq">FAQ</h2>';
	dlscalc_faq_html( $slug );
	echo '<h2 id="sources">Sources</h2>';
	dlscalc_sources_html( array_merge( $sources, dlscalc_dls_sources() ) );
}
