<?php
/**
 * Runs every case through inc/dls.php and inc/nrr.php. Usage: php run-php.php cases.json > out.json
 *
 * @package DLSCalc
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}
define( 'ABSPATH', __DIR__ );
require __DIR__ . '/../../inc/dls.php';
require __DIR__ . '/../../inc/nrr.php';

$map   = array(
	'calc'        => 'dlscalc_dls_calc',
	't20'         => 'dlscalc_dls_t20',
	'parTable'    => 'dlscalc_dls_par_table',
	'live'        => 'dlscalc_dls_live',
	'nrr.calc'    => 'dlscalc_nrr_calc',
	'nrr.solve'   => 'dlscalc_nrr_solve',
	'nrr.runRate' => 'dlscalc_nrr_run_rate',
	'nrr.convert' => 'dlscalc_nrr_convert',
);
$cases = json_decode( file_get_contents( $argv[1] ), true );
$out   = array();
foreach ( $cases as $c ) {
	$out[] = $map[ $c['fn'] ]( $c['p'] );
}
echo json_encode( $out, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION );
