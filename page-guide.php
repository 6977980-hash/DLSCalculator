<?php
/**
 * Guide pages: /dls-method/, /how-to-calculate-nrr/ and /vjd-method/ (see dlscalc_guides()).
 * The article is rendered first so the byline can show the reading time.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_slug  = dlscalc_current_slug();
$dlscalc_guide = dlscalc_guides()[ $dlscalc_slug ];

ob_start();
get_template_part( 'template-parts/content/' . $dlscalc_slug );
$dlscalc_article = ob_get_clean();

get_header();
the_post();
?>
<div class="content-area" id="primary">
	<main class="site-main guide-main" id="main">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<?php dlscalc_breadcrumbs(); ?>
				<h1 class="entry-title"><?php echo esc_html( $dlscalc_guide['h1'] ); ?></h1>
				<?php dlscalc_byline( dlscalc_read_minutes( $dlscalc_article ) ); ?>
			</header>
			<p class="answer"><?php echo esc_html( $dlscalc_guide['answer'] ); ?></p>
			<?php if ( $dlscalc_guide['toc'] ) : ?>
				<nav class="toc" aria-labelledby="toc-title">
					<h2 id="toc-title">On this page</h2>
					<ol>
						<?php foreach ( $dlscalc_guide['toc'] as $dlscalc_id => $dlscalc_label ) : ?>
							<li><a href="#<?php echo esc_attr( $dlscalc_id ); ?>"><?php echo esc_html( $dlscalc_label ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				</nav>
			<?php endif; ?>
			<div class="entry-content tool-prose">
				<?php echo $dlscalc_article; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme template output. ?>
			</div>
			<aside class="cta noprint" aria-label="Calculator">
				<p><?php echo esc_html( $dlscalc_guide['cta'][1] ); ?></p>
				<a class="btn btn--gold" href="<?php echo esc_url( dlscalc_page_url( $dlscalc_guide['cta'][0] ) ); ?>"><?php echo esc_html( $dlscalc_guide['cta'][2] ); ?></a>
			</aside>
			<?php
			dlscalc_author_box();
			dlscalc_related_html( $dlscalc_guide['related'] );
			?>
		</article>
	</main>
</div>
<?php
get_footer();
