<?php
/**
 * Famous rain-affected matches: facts, the inputs we give our DLS Calculator and the official
 * figure to compare with. Facts were checked on 19 October 2026 against the sources listed on each
 * page (Wikipedia match articles, live blogs and match reports, NZC, ICC and IPL reports).
 * Articles are in template-parts/content/<slug>.php; page code is in inc/matches-ui.php.
 *
 * Each match: title (page title and card), h1, seo, answer, card, format (odi or t20), rule (the
 * rain rule used on the day), in (DLS Calculator inputs, see calc() in assets/js/dls-engine.js),
 * entered (the inputs in words), official (label, text and the number our target or par is
 * compared with; null when no DLS figure existed), toc and faqs.
 *
 * @package DLSCalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The matches, oldest first.
 *
 * @return array<string, array>
 */
function dlscalc_matches() {
	return array(
		'1992-world-cup-semi-final-england-south-africa' => array(
			'title'    => 'England vs South Africa, 1992 World Cup semi-final',
			'h1'       => '1992 World Cup Semi-Final Rain Rule: 22 Off 13 Balls Became 21 Off 1',
			'seo'      => array(
				'title'       => '1992 World Cup Semi-Final Rain Rule: What DLS Would Have Set',
				'description' => 'How the most productive overs rule left South Africa needing 21 off 1 ball against England in Sydney in 1992, and the target the DLS method would have set instead.',
			),
			'answer'   => 'South Africa needed 22 off 13 balls against England in the 1992 World Cup semi-final when a 12-minute shower cost two overs. The rain rule of the day, "most productive overs", cut the target by just one run, so the equation became 21 off 1 ball. Under DLS the equation would have been a few runs off the last ball: our calculator gives a target of 235 in 43 overs (4 to win), and the D/L figure usually quoted is 236 (5 to win). Both are estimates, since the real overs were cut during England\'s innings.',
			'card'     => '22 off 13 became 21 off 1. DLS would have asked for 4 or 5.',
			'format'   => 'odi',
			'rule'     => 'Most productive overs',
			'in'       => array(
				'n' => '45',
				'r' => '252',
				'g' => '245',
				'b' => '42.5-6-2',
			),
			'entered'  => array(
				'Overs per side' => '45 (cut from 50 for South Africa\'s slow over rate)',
				'England'        => '252 for 6 in 45 overs',
				'Rain in the chase' => 'At 42.5 overs, South Africa 231 for 6; 2 overs lost',
				'G50'            => '245',
			),
			'official' => array(
				'label' => 'Target on the day (most productive overs)',
				'text'  => '252 in 43 overs (21 off 1 ball)',
				'n'     => 252,
			),
			'toc'      => array(
				'what-happened' => 'What happened in Sydney',
				'the-rule'      => 'How the old rule worked',
				'why-famous'    => 'Why it changed cricket',
			),
			'faqs'     => array(
				array( 'What did South Africa need off the last ball in 1992?', 'The official requirement was 21 runs off 1 ball, a target of 252 in 43 overs. The SCG scoreboard first showed 22 off 7 and then 22 off 1, which is why many fans remember it as 22 off 1.' ),
				array( 'Would DLS have saved South Africa in 1992?', 'Not quite. Our calculator gives a target of 235 in 43 overs, so South Africa, on 231 for 6, would have needed 4 off the last ball to win and 3 to tie. The D/L target usually quoted is 236, 5 to win and 4 to tie. Either way it was a real chance, unlike 21 off 1 ball.' ),
				array( 'Is this match why the Duckworth-Lewis method exists?', 'Yes. Frank Duckworth and Tony Lewis started work on a fairer rain rule after this semi-final, and their method was first used in international cricket in 1997 and formally adopted by the ICC in 1999.' ),
			),
		),
		'2003-world-cup-south-africa-sri-lanka'          => array(
			'title'    => 'South Africa vs Sri Lanka, 2003 World Cup',
			'h1'       => 'South Africa vs Sri Lanka 2003: The D/L Par Score Mix-Up in Durban',
			'seo'      => array(
				'title'       => 'South Africa vs Sri Lanka 2003 World Cup: The D/L Par Mix-Up',
				'description' => 'South Africa tied with Sri Lanka at the 2003 World Cup after reading the D/L par score as the winning target. The numbers, and why 229 only tied the match.',
			),
			'answer'   => 'At the 2003 World Cup in Durban, South Africa chased Sri Lanka\'s 268 for 9 and reached 229 for 6 after 45 overs when rain ended the match. They thought 229 was enough to win, but it was the Duckworth-Lewis par score, which only ties. The tie knocked the hosts out of their own World Cup. Our calculator gives the same par, 229.',
			'card'     => 'The hosts read the par as the target and went out on a tie.',
			'format'   => 'odi',
			'rule'     => 'Duckworth-Lewis',
			'in'       => array(
				'n' => '50',
				'r' => '268',
				'g' => '245',
				'b' => '45-6-5',
			),
			'entered'  => array(
				'Overs per side'     => '50',
				'Sri Lanka'          => '268 for 9 in 50 overs',
				'Rain in the chase'  => 'At 45 overs, South Africa 229 for 6; the last 5 overs lost',
				'G50'                => '245',
			),
			'official' => array(
				'label' => 'Par score on the day (D/L)',
				'text'  => 'Par 229 at 45 overs (match tied)',
				'n'     => 229,
			),
			'toc'      => array(
				'what-happened' => 'What happened in Durban',
				'the-numbers'   => 'Why 229 only tied',
				'why-famous'    => 'The lesson for every team',
			),
			'faqs'     => array(
				array( 'Why was the 2003 South Africa vs Sri Lanka match a tie?', 'Rain ended the match with South Africa on 229 for 6 after 45 overs. Under Duckworth-Lewis the par score at that point was 229, and a score exactly on par is a tie. South Africa needed 230 to win.' ),
				array( 'What is the difference between a par score and a target?', 'The par score ties the match if play ends at that point. The target is the par score plus one run. South Africa\'s dressing room passed on the par figure as if it were the target.' ),
				array( 'Would one more run have won it?', 'Yes. 230 for 6 would have been one run above par, and South Africa would have won on Duckworth-Lewis. Mark Boucher blocked the last ball before the rain, thinking the match was already won.' ),
			),
		),
		'2015-world-cup-semi-final-new-zealand-south-africa' => array(
			'title'    => 'New Zealand vs South Africa, 2015 World Cup semi-final',
			'h1'       => '2015 World Cup Semi-Final: How New Zealand\'s DLS Target Became 298 in 43 Overs',
			'seo'      => array(
				'title'       => '2015 World Cup Semi-Final DLS Target: 298 in 43 Overs Explained',
				'description' => 'Why New Zealand had to chase 298 in 43 overs after South Africa made 281 for 5 in Auckland in 2015, and what the published DLS table gives for the same match.',
			),
			'answer'   => 'Rain cut South Africa\'s innings to 43 overs at Eden Park in 2015, and they finished on 281 for 5. New Zealand were set 298 in 43 overs, more than South Africa scored, because the rain came when South Africa had wickets and overs in hand. Grant Elliott won it with a six off the second-last ball. The published Standard Edition gives 307, nine more than the official target.',
			'card'     => 'Target 298, more than South Africa scored. Elliott hit the six.',
			'format'   => 'odi',
			'rule'     => 'DLS',
			'in'       => array(
				'n' => '50',
				'r' => '281',
				'g' => '245',
				'c' => '43',
				'a' => '38-3-7',
			),
			'entered'  => array(
				'Overs per side'      => '50',
				'Rain in South Africa\'s innings' => 'At 38 overs, 216 for 3; 7 overs lost (innings cut to 43)',
				'South Africa'        => '281 for 5 in 43 overs',
				'New Zealand\'s overs' => '43',
				'G50'                 => '245',
			),
			'official' => array(
				'label' => 'Official target (Professional Edition)',
				'text'  => '298 in 43 overs',
				'n'     => 298,
			),
			'toc'      => array(
				'what-happened' => 'What happened at Eden Park',
				'why-higher'    => 'Why the target beat South Africa\'s score',
				'why-differ'    => 'Why the published table gives 307',
			),
			'faqs'     => array(
				array( 'Why did New Zealand need 298 when South Africa only made 281?', 'South Africa lost 7 overs while they still had 7 wickets in hand, so they were robbed of the overs in which they would have scored fastest. New Zealand knew from the start they had 43 overs, so DLS gave them extra runs to chase to make up for South Africa\'s lost resources.' ),
				array( 'Who hit the winning runs in the 2015 semi-final?', 'Grant Elliott, with a six off Dale Steyn from the second-last ball. He finished on 84 not out and New Zealand reached their first World Cup final.' ),
				array( 'Why does the DLS calculator give 307 for this match?', 'Our calculator uses the published Standard Edition, which adds runs above Team 1\'s score at a fixed rate based on G50. The ICC used the Professional Edition, which scales high scores differently, and it gave 298.' ),
			),
		),
		'2019-world-cup-semi-final-india-new-zealand'   => array(
			'title'    => 'India vs New Zealand, 2019 World Cup semi-final',
			'h1'       => '2019 World Cup Semi-Final DLS: The Targets India Never Had to Chase',
			'seo'      => array(
				'title'       => '2019 World Cup Semi Final DLS: India vs New Zealand Targets',
				'description' => 'Rain stopped New Zealand on 211 for 5 at Old Trafford in 2019. The DLS targets India would have faced in 20 to 46 overs, and why the match went to a reserve day instead.',
			),
			'answer'   => 'Rain stopped the 2019 World Cup semi-final with New Zealand on 211 for 5 after 46.1 overs. Targets for India were worked out in case more rain came, from 148 in 20 overs to 237 in 46. None of them was used: the match resumed on the reserve day with no overs lost, New Zealand made 239 for 8, and India were bowled out for 221.',
			'card'     => 'DLS targets for a chase that never came. Reserve day instead.',
			'format'   => 'odi',
			'rule'     => 'Not needed (reserve day)',
			'in'       => array(
				'n' => '50',
				'r' => '211',
				'g' => '245',
				'c' => '20',
				'a' => '46.1-5-3.5',
			),
			'entered'  => array(
				'Overs per side'     => '50',
				'New Zealand'        => '211 for 5 when rain stopped play at 46.1 overs; their innings ends there',
				'India\'s overs'     => '20 (the shortest chase reported that day)',
				'G50'                => '245',
			),
			'official' => array(
				'label' => 'Target reported on the day (not official)',
				'text'  => '148 in 20 overs',
				'n'     => 148,
			),
			'toc'      => array(
				'what-happened' => 'What happened at Old Trafford',
				'targets'       => 'The DLS targets for India',
				'why-differ'    => 'Why our targets are lower',
				'reserve-day'   => 'The reserve day',
			),
			'faqs'     => array(
				array( 'Was DLS used in the 2019 World Cup semi-final?', 'No. Rain stopped play on the first day, but the match resumed on the reserve day from where it stopped, with no overs lost. New Zealand finished on 239 for 8 and India chased the full 240 in 50 overs.' ),
				array( 'What would India\'s DLS target have been in 20 overs?', 'The figure reported on the day was 148 in 20 overs, if New Zealand had not batted again. Our Standard Edition calculator gives 137. Both are unofficial: no DLS target was ever set because overs were never lost.' ),
				array( 'By how many runs did New Zealand win the 2019 semi-final?', 'By 18 runs. India were bowled out for 221 in 49.3 overs. MS Dhoni was run out by Martin Guptill\'s direct hit in what turned out to be his last one-day international.' ),
			),
		),
		't20-world-cup-2022-india-bangladesh'           => array(
			'title'    => 'India vs Bangladesh, 2022 T20 World Cup',
			'h1'       => 'India vs Bangladesh 2022 T20 World Cup: How the DLS Target Became 151 in 16',
			'seo'      => array(
				'title'       => 'India vs Bangladesh 2022 DLS: 151 in 16 Overs Explained',
				'description' => 'Bangladesh were 66 for 0 and ahead of the DLS par when rain hit Adelaide in 2022. How their target became 151 in 16 overs, and how India won by 5 runs.',
			),
			'answer'   => 'Bangladesh were 66 for 0 after 7 overs, 17 runs ahead of the DLS par, when rain stopped their chase of India\'s 184 for 6 in Adelaide. Four overs were lost and the target became 151 in 16 overs, so they needed 85 off 9 with all 10 wickets. They lost wickets quickly after the restart and India won by 5 runs. Our calculator gives 149.',
			'card'     => '17 ahead on par at the break, then 85 off 9 overs.',
			'format'   => 't20',
			'rule'     => 'DLS',
			'in'       => array(
				'n' => '20',
				'r' => '184',
				'g' => '245',
				'b' => '7-0-4',
			),
			'entered'  => array(
				'Overs per side'    => '20',
				'India'             => '184 for 6 in 20 overs',
				'Rain in the chase' => 'At 7 overs, Bangladesh 66 for 0; 4 overs lost',
				'G50'               => '245',
			),
			'official' => array(
				'label' => 'Official target',
				'text'  => '151 in 16 overs',
				'n'     => 151,
			),
			'toc'      => array(
				'what-happened' => 'What happened in Adelaide',
				'the-break'     => 'The targets during the rain break',
				'why-lost'      => 'Why Bangladesh lost from ahead',
			),
			'faqs'     => array(
				array( 'Why did Bangladesh\'s target go down by only 34 runs?', 'They lost 4 overs but kept all 10 wickets, and a team with wickets in hand can bat harder in fewer overs. DLS kept most of the target and cut the overs, so the rate they needed rose from 9.15 to 9.44 an over.' ),
				array( 'Were Bangladesh ahead on DLS when the rain came?', 'Yes, by 17 runs. If no more play had been possible they would have won. Once play resumed, the par no longer mattered and they had to chase 151 in 16 overs.' ),
				array( 'Who won India vs Bangladesh in the 2022 T20 World Cup?', 'India won by 5 runs on DLS. Bangladesh finished on 145 for 6 in their 16 overs, after Litton Das was run out by KL Rahul soon after the restart.' ),
			),
		),
		't20-world-cup-2022-england-ireland'            => array(
			'title'    => 'England vs Ireland, 2022 T20 World Cup',
			'h1'       => 'England vs Ireland 2022 T20 World Cup: Ireland Win by 5 Runs on DLS',
			'seo'      => array(
				'title'       => 'England vs Ireland 2022 T20 World Cup DLS: The Par of 110',
				'description' => 'England were 105 for 5 after 14.3 overs, five runs behind the DLS par of 110, when rain ended the match at the MCG in 2022. How Ireland\'s famous win was worked out.',
			),
			'answer'   => 'Ireland made 157 all out at the MCG in 2022, and England were 105 for 5 after 14.3 overs when rain ended the match. The DLS par at that moment was 110, so Ireland won by 5 runs, one of the biggest upsets of the tournament. Our calculator gives the same par, 110.',
			'card'     => '105 for 5 against a par of 110. The upset at the MCG.',
			'format'   => 't20',
			'rule'     => 'DLS',
			'in'       => array(
				'n' => '20',
				'r' => '157',
				'g' => '245',
				'b' => '14.3-5-5.3',
			),
			'entered'  => array(
				'Overs per side'    => '20',
				'Ireland'           => '157 all out in 19.2 overs (an all-out innings counts as its full 20 overs of resources)',
				'Rain in the chase' => 'At 14.3 overs, England 105 for 5; no more play',
				'G50'               => '245',
			),
			'official' => array(
				'label' => 'Par score on the day',
				'text'  => 'Par 110 at 14.3 overs (Ireland won by 5 runs)',
				'n'     => 110,
			),
			'toc'      => array(
				'what-happened' => 'What happened at the MCG',
				'the-par'       => 'How the par of 110 is worked out',
				'why-famous'    => 'Why it still matters',
			),
			'faqs'     => array(
				array( 'Why did Ireland win by 5 runs?', 'When rain ended the match, England were 105 for 5 after 14.3 overs and the DLS par score was 110. The margin is the par minus England\'s score: 110 − 105 = 5 runs.' ),
				array( 'Does a team bowled out early lose resources?', 'No. Ireland were all out in 19.2 overs, but being bowled out means they used all of their resources, so DLS counts their innings as a full 100% of a 20-over innings, 56.6% on the 50-over scale.' ),
				array( 'Did England still win the 2022 T20 World Cup?', 'Yes. England recovered from the defeat and beat Pakistan in the final at the same ground.' ),
			),
		),
		't20-world-cup-2022-pakistan-south-africa'      => array(
			'title'    => 'Pakistan vs South Africa, 2022 T20 World Cup',
			'h1'       => 'Pakistan vs South Africa 2022 T20 World Cup: 142 in 14 Overs on DLS',
			'seo'      => array(
				'title'       => 'Pakistan vs South Africa 2022 DLS Target: 142 in 14 Explained',
				'description' => 'South Africa were 69 for 4 after 9 overs, behind the DLS par, when rain hit Sydney in 2022. How their target became 142 in 14 overs and Pakistan won by 33 runs.',
			),
			'answer'   => 'Pakistan made 185 for 9 in Sydney, and South Africa were 69 for 4 after 9 overs, 15 behind the DLS par of 84, when rain came. Six overs were lost and the target became 142 in 14 overs, leaving 73 off 5. South Africa finished on 108 for 9 and Pakistan won by 33 runs, a result that kept them alive on the way to the final. Our calculator gives 139.',
			'card'     => '73 needed off 5 overs after the rain. Pakistan stayed alive.',
			'format'   => 't20',
			'rule'     => 'DLS',
			'in'       => array(
				'n' => '20',
				'r' => '185',
				'g' => '245',
				'b' => '9-4-6',
			),
			'entered'  => array(
				'Overs per side'    => '20',
				'Pakistan'          => '185 for 9 in 20 overs',
				'Rain in the chase' => 'At 9 overs, South Africa 69 for 4; 6 overs lost',
				'G50'               => '245',
			),
			'official' => array(
				'label' => 'Official target',
				'text'  => '142 in 14 overs',
				'n'     => 142,
			),
			'toc'      => array(
				'what-happened' => 'What happened in Sydney',
				'the-target'    => 'Why 73 off 5 overs',
				'why-famous'    => 'Wickets before rain',
			),
			'faqs'     => array(
				array( 'Why was South Africa\'s target so hard after the rain?', 'They had lost 4 wickets in 9 overs, so DLS judged that they had used a lot of their resources. Losing 6 overs cut the target by only 44 runs, which left 73 needed off 30 balls.' ),
				array( 'What was the DLS par when rain stopped South Africa?', 'It was 84 after 9 overs with 4 wickets down, and South Africa were on 69, so Pakistan were 15 runs ahead. Our calculator gives a par of 85 at that point.' ),
				array( 'How did Pakistan win by 33 runs?', 'South Africa finished on 108 for 9 in 14 overs. The par score for the revised chase was 141, one run less than the target of 142, and 141 − 108 = 33.' ),
			),
		),
		'ipl-2023-final-csk-gt'                         => array(
			'title'    => 'Chennai Super Kings vs Gujarat Titans, IPL 2023 final',
			'h1'       => 'IPL 2023 Final DLS Target: How CSK Got 171 in 15 Overs',
			'seo'      => array(
				'title'       => 'IPL 2023 Final DLS Target: CSK 171 in 15 Overs Explained',
				'description' => 'Gujarat Titans made 214 for 4 and rain cut Chennai\'s chase to 15 overs after three balls. Why the DLS target was 171, and how Jadeja won the IPL 2023 final.',
			),
			'answer'   => 'Gujarat Titans made 214 for 4 in the IPL 2023 final in Ahmedabad. Rain stopped play after three balls of Chennai\'s chase, with CSK on 4 for 0, and the chase was cut to 15 overs with a DLS target of 171. CSK got there off the last ball when Ravindra Jadeja hit a six and a four, winning their fifth title. Our calculator gives the same target, 171.',
			'card'     => '171 in 15 overs. Jadeja finished it with 6 and 4.',
			'format'   => 't20',
			'rule'     => 'DLS',
			'in'       => array(
				'n' => '20',
				'r' => '214',
				'g' => '245',
				'b' => '0.3-0-5',
			),
			'entered'  => array(
				'Overs per side'    => '20',
				'Gujarat Titans'    => '214 for 4 in 20 overs',
				'Rain in the chase' => 'At 0.3 overs, CSK 4 for 0; 5 overs lost',
				'G50'               => '245',
			),
			'official' => array(
				'label' => 'Official target',
				'text'  => '171 in 15 overs',
				'n'     => 171,
			),
			'toc'      => array(
				'what-happened' => 'What happened in Ahmedabad',
				'the-target'    => 'Why 171 in 15 overs',
				'other-targets' => 'The other targets during the delay',
			),
			'faqs'     => array(
				array( 'What was CSK\'s target in the IPL 2023 final?', '171 in 15 overs on DLS, after rain cut five overs from their chase of 215. Chennai reached 171 for 5 off the last ball and won by 5 wickets.' ),
				array( 'Why was the target 171 and not 161?', 'A simple run-rate cut would give about 161 for 15 overs. DLS gives more because Chennai kept all 10 wickets for a shorter innings, and a team can bat more freely when it has fewer overs to use its wickets in.' ),
				array( 'When did play restart in the IPL 2023 final?', 'At 12:10 am, after the rain that came at 9:50 pm. The final had already been moved to the reserve day because the first day was washed out.' ),
			),
		),
		't20-world-cup-2024-afghanistan-bangladesh'     => array(
			'title'    => 'Afghanistan vs Bangladesh, 2024 T20 World Cup',
			'h1'       => 'Afghanistan vs Bangladesh 2024 T20 World Cup: 114 in 19 Overs on DLS',
			'seo'      => array(
				'title'       => 'Afghanistan vs Bangladesh 2024 DLS: 114 in 19 Overs Explained',
				'description' => 'Three rain breaks, a target of 114 in 19 overs and Afghanistan\'s first World Cup semi-final. How DLS worked in St Vincent in 2024, and the Gulbadin cramp row.',
			),
			'answer'   => 'Afghanistan made 115 for 5 in St Vincent at the 2024 T20 World Cup. Rain stopped Bangladesh\'s chase three times, and after the second break, at 81 for 7 in 11.4 overs, one over was lost and the target became 114 in 19 overs. Bangladesh were all out for 105 and Afghanistan won by 8 runs, reaching their first World Cup semi-final. Our calculator gives 113.',
			'card'     => 'Three rain breaks, one over lost, a first men\'s World Cup semi-final.',
			'format'   => 't20',
			'rule'     => 'DLS',
			'in'       => array(
				'n' => '20',
				'r' => '115',
				'g' => '245',
				'b' => '11.4-7-1',
			),
			'entered'  => array(
				'Overs per side'    => '20',
				'Afghanistan'       => '115 for 5 in 20 overs',
				'Rain in the chase' => 'At 11.4 overs, Bangladesh 81 for 7; 1 over lost (the other two breaks cost no overs)',
				'G50'               => '245',
			),
			'official' => array(
				'label' => 'Official target',
				'text'  => '114 in 19 overs',
				'n'     => 114,
			),
			'toc'      => array(
				'what-happened' => 'What happened in St Vincent',
				'three-breaks'  => 'Three rain breaks, one over lost',
				'gulbadin'      => 'The cramp and the par score',
			),
			'faqs'     => array(
				array( 'Why was Bangladesh\'s target 114 when Afghanistan scored 115?', 'Rain cost one over of Bangladesh\'s chase when they were 81 for 7. With 7 wickets already gone, that last over was worth very little, so DLS took only two runs off the target, from 116 to 114.' ),
				array( 'Why did Afghanistan win by 8 runs?', 'Bangladesh were all out for 105 in 17.5 overs. The par score for the 19-over chase was 113, one less than the target, and 113 − 105 = 8.' ),
				array( 'What happened with Gulbadin Naib?', 'He went down with cramp just as the rain arrived in the 12th over, with Afghanistan two runs ahead of the DLS par. Coach Jonathan Trott had just signalled to slow the game down, so many fans thought it was deliberate. Play stopped for the rain anyway, and the result stood.' ),
			),
		),
	);
}
