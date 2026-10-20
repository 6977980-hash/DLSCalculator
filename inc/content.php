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
 * When each theme-written page's content last changed, in UTC. The byline, schema, article:modified_time
 * and the sitemap's lastmod use this instead of the WordPress post date, because the text lives in the
 * theme, not in the editor. Update a page's entry in the same commit that changes its text (FAQs in this
 * file, notes in inc/*-ui.php and inc/matches*.php count too); IndexNow pings the changed pages after the
 * deploy (inc/indexnow.php). A date without a time means noon UTC.
 *
 * @return array<string, string> Slug => 'Y-m-d H:i' (UTC).
 */
function dlscalc_content_dates() {
	// Audit F79-F90 (20 Oct 2026): accuracy sentence, titles, FAQs and match notes.
	$f79 = '2026-10-20 08:30';
	// Audit F74-F94 (20 Oct 2026): grey no-result rows in par tables, T20 rate label, Privacy and Terms.
	$f74 = '2026-10-20 16:00';
	return array(
		'home'                       => $f79,
		'dls-calculator'             => $f74,
		'dls-target-calculator-t20'  => $f74,
		'dls-par-score-table'        => $f74,
		'net-run-rate-calculator'    => '2026-10-19 21:31',
		'run-rate-calculator'        => $f79,
		'strike-rate-calculator'     => '2026-10-19 17:49',
		'bowling-average-calculator' => $f79,
		'live-dls-par-score'         => $f74,
		'dls-method'                 => $f79,
		'how-to-calculate-nrr'       => $f79,
		'vjd-method'                 => $f79,
		'about'                      => $f74,
		'contact'                    => '2026-10-19',
		'privacy-policy'             => $f74,
		'terms'                      => $f74,
		'famous-dls-matches'         => '2026-10-19 21:22',
	) + array_fill_keys( array_keys( dlscalc_matches() ), $f79 );
}

/**
 * Unix time a page last changed: its theme content date or the WordPress edit, whichever is later.
 *
 * @param string       $slug Page slug ('home' for the front page).
 * @param WP_Post|null $post The page, or null for the current one.
 * @return int|null Null when the theme does not write that page.
 */
function dlscalc_content_time( $slug, $post = null ) {
	$dates = dlscalc_content_dates();
	if ( ! isset( $dates[ $slug ] ) ) {
		return null;
	}
	$date  = $dates[ $slug ];
	$theme = strtotime( $date . ( strlen( $date ) > 10 ? ':00' : ' 12:00:00' ) . ' UTC' );
	return max( $theme, (int) get_post_modified_time( 'U', true, $post ) );
}

/**
 * The one accuracy sentence used wherever a page compares this calculator with official targets, so the
 * T20 and ODI figures never drift apart. Numbers come from dev/tests/real-matches.json (table on
 * /famous-dls-matches/#real-matches); update both together.
 *
 * @return string Plain text, one sentence.
 */
function dlscalc_accuracy_line() {
	return 'In 34 checks from 32 recent rain-hit matches, every T20 target and par came within 3 runs of this calculator, while ODIs differed by up to 23 runs after a big first innings.';
}

/**
 * Last-updated date for the current page.
 *
 * @param string $format PHP date format.
 * @return string
 */
function dlscalc_updated_date( $format ) {
	$time = dlscalc_content_time( dlscalc_current_slug() );
	return null === $time ? get_the_modified_date( $format ) : wp_date( $format, $time );
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
 * Sources cited on the NRR and run rate pages.
 *
 * @return array<int, array{0: string, 1: string}> Label, URL.
 */
function dlscalc_nrr_sources() {
	return array(
		array( 'ICC Men\'s T20I Playing Conditions, October 2026, clause 16.10.2: net run rate (PDF)', 'https://images.icc-cricket.com/image/upload/prd/bsxcho7ufe2pjban0wjp.pdf' ),
		array( 'ICC Men\'s ODI Playing Conditions, October 2026 (PDF)', 'https://images.icc-cricket.com/image/upload/prd/bxkzkcyvfn6tvi70zh0w.pdf' ),
		array( 'ICC playing conditions for every format', 'https://www.icc-cricket.com/about/cricket/rules-and-regulations/playing-conditions' ),
	);
}

/**
 * Sources cited on the stats calculator pages.
 *
 * @return array<int, array{0: string, 1: string}> Label, URL.
 */
function dlscalc_stats_sources() {
	return array(
		array( 'MCC: the Laws of Cricket (Law 21 no ball, Law 22 wide, Law 23 bye and leg bye)', 'https://www.lords.org/mcc/the-laws-of-cricket' ),
		array( 'ICC playing conditions for every format', 'https://www.icc-cricket.com/about/cricket/rules-and-regulations/playing-conditions' ),
	);
}

/**
 * Sources list.
 *
 * @param array $sources Label and URL pairs; the DLS sources by default.
 */
function dlscalc_sources_html( $sources = null ) {
	echo '<ul class="sources">';
	foreach ( $sources ?? dlscalc_dls_sources() as $source ) {
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
		'dls-calculator'             => array( 'DLS Calculator', 'Stoppages in both innings, any format.' ),
		'dls-target-calculator-t20'  => array( 'DLS Target Calculator T20', 'Rain slider and par table for 20-over matches.' ),
		'dls-par-score-table'        => array( 'DLS Par Score Table', 'Par for every over and wicket, 5 to 50 overs.' ),
		'live-dls-par-score'         => array( 'Live DLS Par Score', 'Who is ahead on DLS, over by over, during the chase.' ),
		'dls-method'                 => array( 'DLS method explained', 'Resources, the formula and the ICC examples.' ),
		'how-to-calculate-nrr'       => array( 'How to calculate NRR', 'The net run rate formula, step by step.' ),
		'vjd-method'                 => array( 'VJD method vs DLS', 'Jayadevan\'s rain rule and how it differs.' ),
		'run-rate-calculator'        => array( 'Run Rate Calculator', 'Required rate and runs per ball for the chase.' ),
		'net-run-rate-calculator'    => array( 'Net Run Rate Calculator', 'Your NRR and the margin you need to qualify.' ),
		'strike-rate-calculator'     => array( 'Strike Rate Calculator', 'Strike rate and batting average, one innings or a season.' ),
		'bowling-average-calculator' => array( 'Bowling Average Calculator', 'Bowling average, economy rate and bowling strike rate.' ),
		'famous-dls-matches'         => array( 'Famous DLS matches', 'Rain-hit classics recalculated, target by target.' ),
	) + array_map(
		function ( $m ) {
			return array( $m['title'], $m['card'] );
		},
		dlscalc_matches()
	);
	echo '<nav class="related noprint" aria-label="Related calculators and guides">';
	foreach ( $slugs as $slug ) {
		if ( ! dlscalc_page_is_live( $slug ) ) {
			continue;
		}
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
	$nrr  = esc_url( dlscalc_page_url( 'net-run-rate-calculator' ) );
	$faqs = array(
		'dls-calculator'             => array(
			array( 'Is this the official ICC DLS calculator?', 'No. DLSCalc is an independent calculator, not an ICC product. It uses the DLS Standard Edition resource table and formulas that the ICC publishes in its playing conditions, the official method when the ICC\'s Professional Edition software is not available. International matches use that software, so an official target can differ from ours. ' . dlscalc_accuracy_line() ),
			array( 'Is this a Duckworth-Lewis calculator or a DLS method calculator?', 'Both. The Duckworth-Lewis (D/L) method became Duckworth-Lewis-Stern in 2014, when Steven Stern updated it, so a Duckworth-Lewis calculator, a D/L calculator and a DLS method calculator all mean a tool like this one. It uses the current DLS Standard Edition table, not the old D/L figures.' ),
			array( 'How many overs are needed for a DLS result?', 'Team 2 must be able to face at least 20 overs in an ODI and 5 overs in a T20, even if the match started with fewer overs. If fewer are possible, there is no result, unless Team 2 has already passed the target or been bowled out. Some knockout matches ask for more: at the ICC Men\'s T20 World Cup, the semi-finals and the final need at least 10 overs a side. The calculator warns you when the chase falls below the usual minimum for the format you pick.' ),
			array( 'Can I add more than one rain break?', 'Yes. Add a row for every stoppage in either innings, up to ten per innings. The resources lost in each stoppage are added together, which is how the ICC playing conditions handle several interruptions.' ),
			array( 'Why can my answer differ from the official target?', 'International matches and big franchise leagues use the DLS Professional Edition, which runs on ICC software and is not published. This calculator uses the published Standard Edition table, the ICC\'s official fallback, so targets usually match or come close. ' . dlscalc_accuracy_line() . ' See how close it comes in <a href="' . esc_url( dlscalc_page_url( 'famous-dls-matches' ) ) . '">famous rain-hit matches</a>.' ),
			array( 'What is the par to tie?', 'It is one run less than the target. If the match ends with Team 2 exactly on the par score, the match is tied. When Team 2\'s innings is cut short for good, the par score decides the winner.' ),
			array( 'Which G50 should I use?', 'The ICC sets 245 for matches involving full-member nations and between first-class teams, and 200 for women\'s internationals, Under-19 and Under-15 internationals and matches between associate members, but the ECB\'s 2026 methodology lists 245 for Under-19 internationals, so check your competition\'s rules. Local leagues may set their own average first-innings score.' ),
			array( 'How do penalty runs change the DLS target?', 'Only penalty runs given to Team 1 while Team 2 is batting change it: the target and every par score go up by that many runs, with no new calculation (ICC DLS rules, section 6). Enter them in the penalty runs box. Penalty runs Team 1 earned while batting are already in its score, and penalty runs to Team 2 at any time go on Team 2\'s score without changing the target.' ),
			array( 'What if rain ends Team 1\'s innings early?', 'Add a Team 1 stoppage at the overs bowled when play stopped, with the wickets down, and enter every remaining over as overs lost. The calculator then treats Team 1\'s innings as finished and usually sets Team 2 a target above Team 1\'s score.' ),
			array( 'Can I save or share a result?', 'Yes. The page address updates with your inputs, so the link reproduces the same result. Use the WhatsApp button, copy the link, or print the page with the par table for the scorers.' ),
		),
		'dls-target-calculator-t20'  => array(
			array( 'What is the minimum for a result in a T20?', 'Team 2 must be able to face at least 5 overs for a result in a 20-over match. If rain cuts the chase below 5 overs, there is no result unless Team 2 has already passed the target or been bowled out. Knockout matches can ask for more: at the ICC Men\'s T20 World Cup, the semi-finals and the final need at least 10 overs a side.' ),
			array( 'How do I read the T20 par score table?', 'Find the completed overs down the left and the wickets Team 2 has lost across the top. If play stops for good at that point, Team 2 wins with more runs than that number and the match is tied on it.' ),
			array( 'Do the IPL, PSL and T20 World Cup use DLS?', 'Yes, all three use Duckworth-Lewis-Stern. Their official targets come from the Professional Edition software, so they can differ from this Standard Edition calculator. ' . dlscalc_accuracy_line() ),
			array( 'Can the T20 target be more than Team 1\'s score plus one?', 'Yes, but only when Team 1 lost overs during its own innings and Team 2 did not lose as many. This page assumes Team 1 batted its full overs, so use the <a href="' . $calc . '">full DLS calculator</a> for a stoppage in the first innings.' ),
			array( 'Does this work for T10 and other short formats?', 'Yes. Set the overs per side to any number from 5 to 20, such as 10 for a T10 match. This page keeps the T20 rule that Team 2 must be able to face at least 5 overs, whatever the overs per side. The minimum for a result in T10 leagues is set by each competition, so if yours allows fewer, use the <a href="' . $calc . '">DLS calculator</a> with the T10 format, which sets no minimum.' ),
			array( 'Why does a 15-over chase not need three quarters of the runs?', 'Team 2 keeps all 10 wickets for fewer overs, so it can bat more aggressively. A 15-over innings is worth 45.2% against 56.6% for 20 overs, about 80% of the resources, so the target falls to about 80% of the score.' ),
		),
		'dls-par-score-table'        => array(
			array( 'What is a DLS par score?', 'It is the score Team 2 needs at a given point of the chase to be level with Team 1 under DLS. If play stops for good there, Team 2 wins with more runs than the par score and the match is tied on it.' ),
			array( 'How is the par score table calculated?', 'Each cell takes the resources Team 2 has used by that over with that many wickets down, divides them by Team 1\'s resources and multiplies by Team 1\'s score, rounded down. All values come from the ICC Standard Edition table.' ),
			array( 'Why does the par score rise so much with wickets?', 'Wickets are resources too. A team that is 4 down has less batting left than a team that is 1 down after the same overs, so it is judged to have used more of its resources and must have scored more to be ahead.' ),
			array( 'Is the par score the same as the target?', 'No. The target is the par score for the whole chase plus one run. The par score at each over is what Team 2 needs if play ends at that point, so it changes over by over and wicket by wicket.' ),
			array( 'Can I use the table for an ODI?', 'Yes. Choose 50 overs and enter Team 1\'s score to get the par after every over from 1 to 49, for 0 to 9 wickets down. Overs 1 to 19 are grey, because an ODI chase needs 20 overs for a result. Shorter matches from 5 overs upwards work the same way.' ),
			array( 'What if Team 1\'s innings was interrupted?', 'This table assumes Team 1 batted its full overs. If Team 1 lost overs or Team 2\'s chase has already been cut, use the <a href="' . $calc . '">DLS calculator</a>, which gives the par table for the rest of the chase after every stoppage.' ),
		),
		'net-run-rate-calculator'    => array(
			array( 'What is a good net run rate?', 'Anything above zero means a team scores faster than its opponents do against it. In a tight group a gap of 0.1 can decide who qualifies, and anything above +1.000 after a few matches is a strong position.' ),
			array( 'What happens to NRR when a team is bowled out?', 'The full quota of overs is counted, not the overs the team actually faced. A team bowled out for 120 in 15 overs of a T20 is counted as 120 runs in 20 overs. If rain cut the match, the overs that side was given count instead, so enter them in "Overs per side in this match". Tick "all out" on a match and the calculator does this for you.' ),
			array( 'How does DLS affect net run rate?', 'When a match finishes after DLS has changed the target, Team 1 is credited with the target minus one run, off the overs Team 2 was given. If the match is abandoned but DLS decides a result, Team 1 is credited with Team 2\'s par score off the overs Team 2 actually faced. Enter those figures for Team 1 in the match row.' ),
			array( 'Do no-result matches count towards NRR?', 'No. Only matches with a result count, so a washout or a match abandoned without a DLS result is left out completely. Tick "no result" on a match to keep it in your list without counting it.' ),
			array( 'Does the qualification scenario include other teams\' matches?', 'It assumes the team you want to pass does not play again before your match, unless that team is your next opponent, in which case its NRR changes too. Run it again after each result to keep the picture current.' ),
			array( 'Can I use it for the IPL, PSL or T20 World Cup?', 'Yes. Every major league and ICC event uses the same net run rate rule. Copy the runs and overs for and against from the official points table into the qualification scenario, then pick your team and the team to pass. ICC events compare wins before net run rate when teams are level on points, so choose "Most wins first" under "Level on points" for those; most leagues go straight to net run rate.' ),
			array( 'Why is my answer different from the official table?', 'Check that overs are written as overs and balls (19.4, not 19.67), that a side bowled out has its full quota, and that no-result matches are left out. Official tables round to three decimals, as this calculator does.' ),
			array( 'Is my points table saved?', 'Yes, on this device only. The page keeps your table in the browser, so it is there when you come back after the next match. Nothing you type is sent to us. Use the share link to send the same table to someone else.' ),
		),
		'run-rate-calculator'        => array(
			array( 'What is a good run rate in T20 cricket?', 'Around 8 to 9 runs an over is a par score in most T20 leagues today, and above 10 is a fast innings. In ODIs, 5.5 to 6 an over is typical, with the fastest innings going well past 7.' ),
			array( 'What is the required run rate formula?', 'Required run rate = runs needed ÷ overs left. Runs needed is the target minus the runs scored, and overs left are counted in balls, so 44 balls left is 7.33 overs, not 7.4.' ),
			array( 'What is the difference between run rate and net run rate?', 'Run rate is how fast one team scores in one innings. <a href="' . $nrr . '">Net run rate</a> compares a team\'s scoring rate with its opponents\' rate across a whole tournament, and is used to separate teams level on points.' ),
			array( 'Why is 12.5 overs not 12.5 in decimal?', 'The number after the point counts balls, not tenths. 12.5 overs is 12 overs and 5 balls, which is 12.83 overs in decimal. Mixing the two up is the most common mistake in run rate sums.' ),
			array( 'How is the projected score worked out?', 'Projected score = runs so far + run rate × overs left. The calculator shows it at the current rate and at 1, 2 and 3 runs an over faster, plus a few fixed rates, rounded to the nearest run.' ),
			array( 'What if rain changes the target?', 'Use the revised DLS target from the <a href="' . $calc . '">DLS calculator</a> as the target here, and the reduced overs per side. The required run rate is then worked out for the new chase.' ),
		),
		'strike-rate-calculator'     => array(
			array( 'How do you calculate strike rate in cricket?', 'Strike rate = runs scored ÷ balls faced × 100. A batter who makes 45 off 30 balls has a strike rate of 150.00, which means 150 runs for every 100 balls at that pace.' ),
			array( 'How is batting average calculated?', 'Batting average = runs ÷ times out, and times out = innings minus not outs. 182 runs in 5 innings with 1 not out is 182 ÷ 4 = 45.50. Dividing by innings gives the runs per innings instead.' ),
			array( 'Do wides and no-balls count as balls faced?', 'No-balls do, because the batter can score off them and any runs hit off a no-ball go to the batter. Wides do not count as balls faced, and the runs from wides are extras, not the batter\'s runs.' ),
			array( 'What is a good strike rate in T20 cricket?', 'For a top-order batter in professional T20 cricket, 125 to 140 is typical and 150 or more is fast. In ODIs 80 to 95 is typical, and in Tests 45 to 60. Club and tennis ball cricket each have their own levels.' ),
			array( 'What is the average of a batter who has never been out?', 'There is none yet: the average divides by the number of dismissals, and with no dismissals it cannot be worked out. Scorecards show it as a dash, and the calculator says "None yet" until the batter is out once.' ),
			array( 'Is bowling strike rate the same thing?', 'No. A bowler\'s strike rate is the balls bowled per wicket taken, so lower is better. The <a href="' . esc_url( dlscalc_page_url( 'bowling-average-calculator' ) ) . '">bowling average calculator</a> works it out with the economy rate.' ),
		),
		'bowling-average-calculator' => array(
			array( 'How do you calculate bowling average?', 'Bowling average = runs conceded ÷ wickets taken. 212 runs for 9 wickets is an average of 23.56. Lower is better, and a bowler with no wickets has no average yet.' ),
			array( 'How do you calculate economy rate in cricket?', 'Economy rate = runs conceded ÷ overs bowled, with the overs counted in balls. 38.4 overs is 232 balls, or 38.67 overs, so 212 runs is an economy rate of 5.48, not 5.52.' ),
			array( 'What is bowling strike rate?', 'It is the balls bowled per wicket: balls ÷ wickets. 232 balls for 9 wickets is a strike rate of 25.8, a wicket roughly every four overs. Lower is better.' ),
			array( 'Do wides and no-balls count in a bowler\'s figures?', 'Yes. The runs from wides and no-balls are charged to the bowler, but these are not legal balls, so they do not count in the overs. Byes and leg byes are not charged to the bowler.' ),
			array( 'Does a run out count as a bowler\'s wicket?', 'No. The bowler is credited only for bowled, caught, leg before wicket, stumped and hit wicket. Run outs and the other ways of getting out go in the team\'s total but not in the bowler\'s wickets.' ),
			array( 'What is a good economy rate in T20 cricket?', 'In professional T20 cricket an economy rate under 7.5 an over is good and under 7 is excellent. In ODIs under 5 is good, and in Tests under 3.' ),
		),
		'live-dls-par-score'         => array(
			array( 'What does the live DLS par score mean?', 'It is the score the chasing team needs at that moment to be level under DLS if play never resumes. Above par, Team 2 would win; below par, Team 1 would win; exactly on par, the match is tied.' ),
			array( 'How often should I update the score?', 'After every over is enough for most matches. When dark clouds arrive, add a row in the middle of the over too, like 12.3, so the par matches the exact ball when the umpires take the players off.' ),
			array( 'Why does the par jump when a wicket falls?', 'Wickets are resources too. A team that loses a wicket has less batting left, so DLS judges that it has used more of its resources and must already have more runs to stay ahead.' ),
			array( 'What if rain has already cut the overs?', 'If overs were lost before the chase began, enter the reduced overs for Team 2 and the target and every par are worked out for the shorter chase. If rain has interrupted the chase itself, the target changes, so use the <a href="' . $calc . '">DLS calculator</a> for that stoppage. Use it too if rain cut Team 1\'s innings or Team 1 was awarded penalty runs during the chase: the tracker assumes neither.' ),
			array( 'Is there a minimum number of overs for a result?', 'Yes. Team 2 must have faced at least 20 overs in an ODI, or 5 overs in a T20, for DLS to decide the result, even if the match started with fewer overs, so pick the format as well as the overs. Before that point the tracker warns that a stoppage would mean no result. At the ICC Men\'s T20 World Cup, the semi-finals and the final need at least 10 overs a side.' ),
			array( 'Will I lose the score if I close the page?', 'No. The tracker keeps the match on this device, so a reload or a closed tab picks up where you left off. Use "Start a new match" to clear it, or share the link so another scorer can follow the same match.' ),
			array( 'Does it work with no signal at the ground?', 'Yes. After you open DLSCalc once with a connection, your phone keeps the calculators, the par table and this tracker, so they open and work offline. On most phones you can also add DLSCalc to the home screen from the browser menu, and it opens like an app.' ),
			array( 'Is this a live DLS calculator?', 'Yes. It is a live DLS calculator for the chase: enter Team 1\'s score once, then add Team 2\'s score and wickets as the overs go by, and it shows the par and who is ahead at every point. For the revised target after a stoppage, use the <a href="' . $calc . '">DLS calculator</a>.' ),
			array( 'Is this the same par that appears on TV?', 'International matches use the DLS Professional Edition, which runs on ICC software and is not published. This tracker uses the published Standard Edition, so the TV par can differ. ' . dlscalc_accuracy_line() ),
		),
		'dls-method'                 => array(
			array( 'Why is DLS used instead of the average run rate?', 'Run rate ignores wickets. A team on 100 for 0 and a team on 100 for 8 after 20 overs are in very different positions, and DLS accounts for that by treating wickets in hand as part of a team\'s resources.' ),
			array( 'Is DLS used in T20 cricket?', 'Yes. DLS sets rain targets in every limited-overs format, including T20 internationals, the T20 World Cup and leagues such as the IPL and the PSL, which adopt it in their own playing conditions. The resource table is the same for every format: a full 20-over innings is 56.6% of the resources of a 50-over innings.' ),
			array( 'Can the DLS target be higher than Team 1\'s score?', 'Yes. When Team 1 loses overs during its own innings but Team 2 gets more overs to bat, Team 2 has more resources, and the target is raised using G50.' ),
			array( 'Who decides how many overs are lost?', 'The match officials, based on the playing time lost and the playing conditions of the competition. DLS only works out the target for the overs they set.' ),
			array( 'Why is a DLS target rounded down and then increased by one?', 'The formula gives the par score, which ties the match. It is rounded down to a whole run, and the target to win is one run more.' ),
			array( 'Who invented the DLS method?', 'Frank Duckworth and Tony Lewis, two English statisticians, created the Duckworth-Lewis method in the 1990s. Steven Stern, an Australian statistics professor, updated it, and it became Duckworth-Lewis-Stern in 2014.' ),
		),
		'how-to-calculate-nrr'       => array(
			array( 'What is a good net run rate?', 'Anything above zero means a team scores faster than its opponents. In tight groups a gap of 0.1 or less often decides who qualifies, so every run counts.' ),
			array( 'Is NRR the same as run rate?', 'No. Run rate is how fast one innings is scored. Net run rate compares a team\'s scoring rate with its opponents\' rate across all its matches in a tournament.' ),
			array( 'Why do overs like 17.3 matter so much?', 'Because 17.3 overs is 17.5 overs as a number, not 17.3. The digit after the point counts balls out of six. Using the wrong value is the most common NRR mistake.' ),
			array( 'Can NRR be calculated for a single match?', 'Yes. Use the same formula for just that match: runs scored ÷ overs faced minus runs conceded ÷ overs bowled. In one match the two teams\' NRRs are equal and opposite.' ),
			array( 'Does a bigger win always improve NRR more?', 'Usually, but how you win matters too. Chasing a target in fewer overs can lift NRR more than a similar winning margin batting first, because the overs you save count as much as the runs.' ),
			array( 'Do super overs count in NRR?', 'No. Under ICC playing conditions only the runs and overs of the main match count towards net run rate, not a super over played to break a tie.' ),
		),
		'vjd-method'                 => array(
			array( 'What is the full form of VJD in cricket?', 'VJD stands for V. Jayadevan, the Indian engineer from Thrissur, Kerala, who created the method. It is also called the Jayadevan system.' ),
			array( 'Is the VJD method still used?', 'Yes, in BCCI domestic cricket in India. Match reports show Vijay Hazare Trophy matches decided by VJD, including a quarter-final in January 2026. International matches and the IPL use DLS.' ),
			array( 'Which is better, VJD or DLS?', 'Neither has been shown to be clearly better in every situation. The ICC reviewed VJD in 2012 and kept Duckworth-Lewis, while the BCCI has kept VJD for its domestic matches.' ),
			array( 'Is VJD used in the IPL?', 'No. The IPL sets its own playing conditions and uses the DLS method for rain-affected matches, even though reports in 2010 suggested VJD would be tried.' ),
			array( 'How does VJD treat wickets?', 'Wickets count for the overs a team has already played, through the normal curve. For the overs still to come after an interruption, the target curve is used, and it does not depend on wickets.' ),
		),
	);
	return $faqs[ $slug ] ?? dlscalc_match_faqs( $slug );
}
