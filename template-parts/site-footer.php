<?php
/**
 * Site footer: copyright, domain and footer menu.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer class="site-foot">
	<div class="wrap site-foot__inner">
		<span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> DLSCalc &middot; dlscalculator.com</span>
		<?php dlscalc_menu( 'footer', 'Footer', 'foot-nav' ); ?>
	</div>
</footer>
