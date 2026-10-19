<?php
/**
 * Site header: logo and main menu.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header class="site-head">
	<div class="wrap site-head__inner">
		<?php dlscalc_logo(); ?>
		<?php dlscalc_menu( 'primary', 'Main', 'site-nav' ); ?>
	</div>
</header>
