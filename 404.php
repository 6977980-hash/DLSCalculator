<?php
/**
 * Not found page: points visitors to the calculators instead of a search box.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="site-main notfound">
	<h1 class="entry-title">That page is not here</h1>
	<p>The link may be old or mistyped. These are the calculators on DLSCalc:</p>
	<div class="card-grid">
		<?php foreach ( dlscalc_tools() as $dlscalc_slug => $dlscalc_tool ) : ?>
			<a class="card-link" href="<?php echo esc_url( dlscalc_page_url( $dlscalc_slug ) ); ?>">
				<span class="card-link__title"><?php echo esc_html( $dlscalc_tool['title'] ); ?></span>
				<span class="card-link__text"><?php echo esc_html( $dlscalc_tool['blurb'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
	<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Go to the home page</a></p>
</main>
<?php
get_footer();
