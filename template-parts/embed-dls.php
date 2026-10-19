<?php
/**
 * The embeddable DLS widget (served by dlscalc_serve_embed() at /embed/dls/). Standalone page:
 * no theme header or WordPress scripts, system fonts, about 3 KB of CSS. Works without JavaScript
 * (the form reloads the page); assets/js/embed.js updates the result as the user types.
 *
 * @package DLSCalc
 *
 * @var array $in Inputs from dlscalc_embed_inputs().
 * @var array $r  Engine result.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dlscalc_err  = $r['err'] ?? '';
$dlscalc_full = dlscalc_dls_permalink( 'dls-calculator', $in + array( 'g' => '245' ) );
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, follow">
<title>DLS calculator widget | DLSCalc</title>
<style>
*{box-sizing:border-box}
body{margin:0;font:16px/1.4 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#10231c;background:transparent}
.w{max-width:480px;margin:0 auto;padding:16px;border:1px solid #dad6ca;border-radius:14px;background:#fff}
h1{margin:0 0 4px;font-size:20px;color:#13452f}
.sub{margin:0 0 12px;font-size:13px;color:#5b6660}
.f{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px}
label{display:flex;flex-direction:column;gap:4px;font-size:13px;font-weight:600}
input{width:100%;padding:9px 10px;font:inherit;font-size:17px;border:1px solid #b9b4a6;border-radius:8px;background:#fff;color:inherit}
input:focus{outline:2px solid #f2b33d;outline-offset:1px}
button{margin-top:10px;padding:9px 16px;font:inherit;font-weight:700;color:#fff;background:#13452f;border:0;border-radius:8px;cursor:pointer}
.js button{display:none}
.res{margin-top:12px;padding:12px 14px;border-radius:10px;background:#13452f;color:#f5f3ec}
.res b{display:block;font-size:44px;line-height:1.05;color:#f2b33d}
.res span{font-size:14px}
.err{margin:12px 0 0;color:#a32d22;font-weight:600}
.ft{display:flex;justify-content:space-between;gap:8px;margin-top:10px;font-size:13px}
a{color:#13452f;font-weight:600}
@media (max-width:360px){.f{grid-template-columns:1fr 1fr}}
</style>
<script>document.documentElement.className='js'</script>
</head>
<body>
<div class="w">
	<h1>DLS target calculator</h1>
	<p class="sub">Rain before the chase? Enter the overs Team 2 gets. ICC DLS Standard Edition.</p>
	<form method="get" action="" data-embed>
		<div class="f">
			<label>Overs per side<input name="n" inputmode="decimal" value="<?php echo esc_attr( $in['n'] ); ?>" autocomplete="off"></label>
			<label>Team 1 score<input name="r" inputmode="numeric" value="<?php echo esc_attr( $in['r'] ); ?>" autocomplete="off"></label>
			<label>Team 2 overs<input name="c" inputmode="decimal" value="<?php echo esc_attr( $in['c'] ); ?>" placeholder="<?php echo esc_attr( $in['n'] ); ?>" autocomplete="off"></label>
		</div>
		<button type="submit">Calculate</button>
	</form>
	<p class="err" data-o="err" role="alert"<?php echo $dlscalc_err ? '' : ' hidden'; ?>><?php echo esc_html( $dlscalc_err ); ?></p>
	<div class="res" aria-live="polite" data-o="res"<?php echo $dlscalc_err ? ' hidden' : ''; ?>>
		<span>Team 2 target</span>
		<b data-o="target"><?php echo $dlscalc_err ? '' : (int) $r['target']; ?></b>
		<span data-o="line"><?php echo $dlscalc_err ? '' : esc_html( 'in ' . dlscalc_dls_overs( $r['allot'] ) . ' overs · par to tie ' . $r['par'] ); ?></span>
	</div>
	<div class="ft">
		<a data-o="full" href="<?php echo esc_url( $dlscalc_full ); ?>" target="_blank" rel="noopener">Stoppages during the chase? Full calculator</a>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">DLSCalc</a>
	</div>
</div>
<script src="<?php echo esc_url( DLSCALC_URI . '/assets/js/dls-engine.js?ver=' . dlscalc_asset_version( 'assets/js/dls-engine.js' ) ); ?>" defer></script>
<script src="<?php echo esc_url( DLSCALC_URI . '/assets/js/embed.js?ver=' . dlscalc_asset_version( 'assets/js/embed.js' ) ); ?>" defer data-full="<?php echo esc_url( dlscalc_page_url( 'dls-calculator' ) ); ?>"></script>
</body>
</html>
