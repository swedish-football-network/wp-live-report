<?php
/**
 * Represents the view for the public-facing component of the plugin.
 *
 * This typically includes any information, if any, that is rendered to the
 * frontend of the theme when the plugin is activated.
 *
 * @package   SFN Live Report
 * @author    Hampus Persson <hampus@hampuspersson.se>
 * @license   GPL-2.0+
 * @link      http://
 * @copyright 2013 Swedish Football Network
 */

	$dropdown_list = ""; // Will be populated with games played today
	$dropdown_title = "Inga matcher idag...";
	$todays_date = date('Ymd');
	$todays_date = '20130713'; // Uncomment for testing
	// Get all games in Superserien that are played today
	$args = array( 'post_type' => 'games', 'orderby' => 'date', 'meta_key' => 'serie', 'meta_value' => 'Superserien', 'meta_key' => 'datum', 'meta_value' => $todays_date );
	$loop = get_posts($args);

	if( $loop ) {
		$dropdown_title = "Välj match:"; // Set the dropdown title if games are available
	}

	// Loop through the results and create a listitem for each game
	foreach($loop as $post) : setup_postdata($post);
		$home = get_field('hemmalag', $post->ID);
		$away = get_field('bortalag', $post->ID);
		$home_score = get_field('hemmares', $post->ID);
		$away_score = get_field('bortares', $post->ID);
		$time = get_field('matchtid', $post->ID);
		$dropdown_list .= <<<EOL
	<li><a href="#" class="pick-game" data-game-id="{$post->ID}" data-home-score="$home_score" data-home-team="{$home[0]->post_title}" data-away-score="$away_score" data-away-team="{$away[0]->post_title}" data-game-time="$time">{$home[0]->post_title}  - {$away[0]->post_title}</a></li>
EOL;
	endforeach;
?>
<!doctype html>
<html lang="sv">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="initial-scale=1, maximum-scale=1,user-scalable=no">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-title" content="Resultat">
		<link rel="apple-touch-icon-precomposed" href="/apple-touch-icon-precomposed.png" />
		<!-- iPhone 5 (Retina) -->
		<link href="assets/images/apple-touch-startup-image-640x920.png" media="(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">
		<!-- iPhone 5 -->
		<link href="assets/images/apple-touch-startup-image-640x1096.png" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">
		<title>Rapportera matchresultat</title>
	</head>
	<body <?php body_class('sfn-live-report'); ?> >
		<h1 class="page-title">Rapportera matchresultat</h1>
		<form id="report-form" action="post">
			<input type="hidden" id="game-id" value="0">
			<input type="hidden" id="home-team" value="0">
			<input type="hidden" id="away-team" value="0">
			<div id="dd" class="dropdown" tabindex="1">
				<span><?php echo $dropdown_title; ?></span>
				<ul class="dropdown__list">
					<?php echo $dropdown_list; ?>
				</ul>
			</div>
			<div class="report-tools" id="report-tools">

				<div class="input-group">
					<label for="home-score">Poäng - Hemma</label>
					<input type="number" name="home-score" id="home-score">
				</div>
				<div class="input-group">
					<label for="away-score">Poäng - Borta</label>
					<input type="number" name="away-score" id="away-score">
				</div>
				<div class="input-group full">
					<label for="time-stamp">Tid <em>(Ex. Q2 7:31)</em></label>
					<input type="text" name="time-stamp" id="time-stamp">
				</div>
				<div class="input-group visual-checkbox">
					<input type="checkbox" name="sendToTwitter" id="sendToTwitter"><label for="sendToTwitter"><i class="icon-twitter"></i></label>
				</div>
				<div class="input-group visual-checkbox">
					<input type="checkbox" name="finalScore" id="finalScore"><label for="finalScore"><i class="icon-timer"></i></label>
				</div>
				<div class="input-group full">
					<input type="submit" id="send-btn" value="Registrera resultat">
				</div>
			</div>
		</form>
		<script>
			// To access ajaxurl this needs to be inline.
			// TODO: Move to external file
			$ = jQuery; // WP doesn't use $ for jQuery
			$('#report-form').on('submit', function(e) {
				e.preventDefault();

				var gameOver = false,
					game,
					home,
					away,
					time;
				game = $('#game-id').val();
				home = $('#home-score').val();
				homeTeam = $('#home-team').val();
				away = $('#away-score').val();
				awayTeam = $('#away-team').val();
				time = $('#time-stamp').val();
				sendToTwitter = $('#sendToTwitter').prop('checked');
				finalScore = $('#finalScore').prop('checked');

				console.log(finalScore);
				console.log(sendToTwitter);

				/*-------------------------------------------------------------------------
				| VALIDATE THE FORM
				|------------------------------------------------------------------------*/

				if( "" === game ) {
					alert("Du måste välje en match");
				} else if( "" === home ) {
					alert("Du måste ange hemmalagets poäng");
					$('#home-score').focus();
				} else if( "" === away ) {
					alert("Du måste ange bortalagets poäng");
					$('#away-score').focus();
				} else {
					if( $('#finalScore').prop('checked') ) {
						if( confirm("Är matchen slut?") ) {
							time = "";
							postResult(game, home, away, time);
						}
					} else {
						if( 2 > time.length ) {
							alert("Du måste ange en giltig matchtid");
							$('#time-stamp').focus();
						} else {
							postResult(game, home, away, time);
						}
					}
				}

				function postResult(game, home, away, time) {
					$('#send-btn').val('Skickar...').css('background-color', '#0058b1');

					// Generate a POST-request to ajaxurl and tell the user what's going on
					$.post( ajaxurl,
						{ action : 'sfn-submit', 'game': game, 'home-team': homeTeam, 'away-team': awayTeam, 'home': home, 'away': away, 'time': time, 'sendToTwitter': sendToTwitter, 'finalScore': finalScore } )
					.done(function() {
						$('#send-btn').val('Allt gick bra!').css('background-color', '#0058b1');

						$('a[data-game-id='+game+']').data('home-score', home).data('away-score', away).data('game-time', time);

						setTimeout(function(){
							$('#send-btn').val('Registrera resultat').css('background-color', '');
							$('#finalScore').prop('checked', false);
						}, 3000);
					})

					.fail(function() {
						$('#send-btn').val('Något fick fel! Försök igen...').css('background-color', '#F5001D');
						setTimeout(function(){
							$('#send-btn').val('Registrera resultat').css('background-color', '');
						}, 3000);
					});
				}
			});
		</script>
	</body>
</html>
<?php return ""; // To avoid the calling function to output a success variable ?>