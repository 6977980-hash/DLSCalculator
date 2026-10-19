<?php
/**
 * Home page.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_author = dlscalc_author();
$dlscalc_quick  = dlscalc_dls_calc(
	array(
		'n' => '20',
		'r' => '168',
		'g' => '245',
		'c' => '15',
	)
);

get_header();
?>
<main id="main" class="home-main">
	<section class="hero">
		<div class="wrap hero__inner">
			<div class="hero__copy">
				<p class="eyebrow">Free cricket calculators</p>
				<h1>Rain stopped play? Get the DLS target in seconds.</h1>
				<p class="hero__lede">Accurate DLS revised targets, par scores and net run rate scenarios for T20, ODI and local league matches. No sign-up, works on your phone.</p>
				<div class="hero__actions">
					<a class="btn btn--gold" href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">Open the DLS Calculator</a>
					<a class="btn btn--ghost" href="<?php echo esc_url( dlscalc_page_url( 'net-run-rate-calculator' ) ); ?>">Net Run Rate Calculator</a>
				</div>
			</div>
			<form class="quick" method="get" action="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) . '#result' ); ?>" data-tool="quick" novalidate>
				<h2>Quick DLS target</h2>
				<div class="fields">
					<label class="fld">Match overs
						<select name="n">
							<option value="20" selected>20 overs (T20)</option>
							<option value="50">50 overs (ODI)</option>
						</select>
					</label>
					<label class="fld">Team 1 score
						<input type="number" inputmode="numeric" min="0" max="999" name="r" value="168" autocomplete="off">
					</label>
					<label class="fld">Overs for Team 2
						<input type="text" inputmode="decimal" name="c" value="15" autocomplete="off">
					</label>
				</div>
				<input type="hidden" name="g" value="245">
				<div class="quick__out" aria-live="polite">
					<div>
						<div class="quick__label">Team 2 target</div>
						<div class="quick__big" data-out="big"><?php echo (int) $dlscalc_quick['target']; ?></div>
					</div>
					<div class="quick__line" data-out="line"><?php echo esc_html( 'in ' . dlscalc_dls_overs( $dlscalc_quick['allot'] ) . ' overs (par ' . $dlscalc_quick['par'] . ')' ); ?></div>
				</div>
				<?php // Below the minimum overs for the format (5 in a T20, 20 in an ODI) there is no result (audit F24). ?>
				<p class="alert alert--soft" data-out="warn"<?php echo $dlscalc_quick['warn'] ? '' : ' hidden'; ?>><?php echo esc_html( $dlscalc_quick['warn'] ); ?></p>
				<button type="submit" class="btn btn--green">See the full working</button>
				<p class="quick__more">Rain in the middle of an innings? The <a href="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>">full DLS calculator</a> handles every stoppage.</p>
			</form>
		</div>
	</section>

	<section class="section">
		<div class="wrap">
			<h2 class="section__title">Choose a calculator</h2>
			<div class="card-grid">
				<?php
				foreach ( dlscalc_tools() as $dlscalc_slug => $dlscalc_tool ) :
					// A new tool's page only exists once WP admin has been opened after the deploy.
					if ( ! dlscalc_page_is_live( $dlscalc_slug ) ) {
						continue;
					}
					?>
					<a class="card-link" href="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) ); ?>">
						<span class="card-link__title"><?php echo esc_html( $dlscalc_tool['title'] ); ?></span>
						<span class="card-link__text"><?php echo esc_html( $dlscalc_tool['blurb'] ); ?></span>
						<span class="card-link__cta">Open calculator</span>
					</a>
				<?php endforeach; ?>
			</div>
			<p class="section__more">Need a sheet for the scorers? Print the <a href="<?php echo esc_url( dlscalc_page_url( 'dls-par-score-table' ) ); ?>">DLS par score table</a> for any match from 5 to 50 overs. Scoring a chase right now? The <a href="<?php echo esc_url( dlscalc_page_url( 'live-dls-par-score' ) ); ?>">live DLS par tracker</a> shows who is ahead after every over.</p>
		</div>
	</section>

	<section class="section">
		<div class="wrap feature-grid">
			<div>
				<h3>Standard Edition tables</h3>
				<p>Targets use the published DLS Standard Edition resource table. International matches use the Professional Edition: in T20s the official target is usually within 3 runs of ours, but high-scoring ODIs can differ by 10 to 20 runs or more.</p>
			</div>
			<div>
				<h3>Fast on any phone</h3>
				<p>Everything runs in your browser. No app, no account and no waiting while the covers are on.</p>
			</div>
			<div>
				<h3>For leagues and fans</h3>
				<p>Scorers in local leagues get the ICC Standard Edition, the official DLS method for matches played without the ICC software, explained step by step.</p>
			</div>
		</div>
	</section>

	<section class="section section--last">
		<div class="wrap guide-row">
			<a class="card-link" href="<?php echo esc_url( dlscalc_page_url( 'dls-method' ) ); ?>">
				<span class="card-link__kicker">Guide</span>
				<span class="card-link__title">DLS method explained, with the resource table</span>
			</a>
			<a class="card-link" href="<?php echo esc_url( dlscalc_page_url( 'how-to-calculate-nrr' ) ); ?>">
				<span class="card-link__kicker">Guide</span>
				<span class="card-link__title">How to calculate NRR, step by step</span>
			</a>
			<a class="card-link" href="<?php echo esc_url( dlscalc_page_url( 'vjd-method' ) ); ?>">
				<span class="card-link__kicker">Guide</span>
				<span class="card-link__title">VJD method vs DLS: India's other rain rule</span>
			</a>
			<div class="maker">
				<div class="maker__badge" aria-hidden="true"><?php echo esc_html( $dlscalc_author['initials'] ); ?></div>
				<p>Built by <a href="<?php echo esc_url( dlscalc_page_url( 'about' ) ); ?>"><?php echo esc_html( $dlscalc_author['name'] ); ?></a>, a cricketer and tournament organiser who needed accurate rain targets during live matches.</p>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
