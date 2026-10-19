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
		</div>
	</section>

	<section class="section">
		<div class="wrap">
			<h2 class="section__title">Choose a calculator</h2>
			<div class="card-grid">
				<?php foreach ( dlscalc_tools() as $dlscalc_slug => $dlscalc_tool ) : ?>
					<a class="card-link" href="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) ); ?>">
						<span class="card-link__title"><?php echo esc_html( $dlscalc_tool['title'] ); ?></span>
						<span class="card-link__text"><?php echo esc_html( $dlscalc_tool['blurb'] ); ?></span>
						<span class="card-link__cta">Open calculator</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="wrap feature-grid">
			<div>
				<h3>Standard Edition tables</h3>
				<p>Targets use the published DLS Standard Edition resource table. International matches use the Professional Edition, so results can differ by a few runs.</p>
			</div>
			<div>
				<h3>Fast on any phone</h3>
				<p>Everything runs in your browser. No app, no account and no waiting while the covers are on.</p>
			</div>
			<div>
				<h3>For leagues and fans</h3>
				<p>Scorers in local leagues get the same method the big tournaments use, explained step by step.</p>
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
			<div class="maker">
				<div class="maker__badge" aria-hidden="true"><?php echo esc_html( $dlscalc_author['initials'] ); ?></div>
				<p>Built by <a href="<?php echo esc_url( dlscalc_page_url( 'about' ) ); ?>"><?php echo esc_html( $dlscalc_author['name'] ); ?></a>, a cricketer and tournament organiser who needed accurate rain targets during live matches.</p>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
