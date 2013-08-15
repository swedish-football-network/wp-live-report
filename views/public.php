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
?>

<?php
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
	<li><a href="#" class="pick-game" data-game-id="{$post->ID}" data-home-score="$home_score" data-away-score="$away_score" data-game-time="$time">{$home[0]->post_title}  - {$away[0]->post_title}</a></li>
EOL;
		endforeach;
?>
<!doctype html>
<html lang="sv">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="apple-mobile-web-app-title" content="Resultat">
		<link rel="apple-touch-icon-precomposed" href="/apple-touch-icon-precomposed.png" />
		<title>Rapportera matchresultat</title>
		<link rel="stylesheet" href="<?php echo plugins_url( 'assets/css/style.css', __FILE__ ); ?>">
	</head>
	<body>
		<h1 class="page-title">Rapportera matchresultat</h1>
		<form id="report-form" action="post">
			<input type="hidden" id="game-id" value="0">
			<div id="dd" class="dropdown" tabindex="1">
				<span><?php echo $dropdown_title; ?></span>
				<ul class="dropdown__list">
					<?php echo $dropdown_list; ?>
				</ul>
			</div>
			<div class="report-tools" id="report-tools">
				<div class="input-group">
					<label for="home-score">Poäng - Hemma</label>
					<input type="text" name="home-score" id="home-score">
				</div>
				<div class="input-group">
					<label for="away-score">Poäng - Borta</label>
					<input type="text" name="away-score" id="away-score">
				</div>
				<div class="input-group full">
					<label for="time-stamp">Tid (Ex. Q2 7:31. Lämna tom när matchen är slut.)</label>
					<input type="text" name="time-stamp" id="time-stamp" value="1Q 10:00">
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

				var game,
						home,
						away,
						time;
				game = $('#game-id').val();
				home = $('#home-score').val();
				away = $('#away-score').val();
				time = $('#time-stamp').val();

				$('#send-btn').val('Skickar...').css('background-color', '#FFF700');

				// Generate a POST-request to ajaxurl and tell the user what's going on
				$.post( ajaxurl,
					{ action : 'sfn-submit', 'game': game, 'home': home, 'away': away, 'time': time } )
				.done(function() {
					$('#send-btn').val('Allt gick bra!').css('background-color', '#2DD700');
					setTimeout(function(){
						$('#send-btn').val('Registrera resultat').css('background-color', '#48DD00');
					}, 3000);
				})

				.fail(function() {
					$('#send-btn').val('Något fick fel! Försök igen...').css('background-color', '#F5001D');
					setTimeout(function(){
						$('#send-btn').val('Registrera resultat').css('background-color', '#48DD00');
					}, 3000);
				});
			});
		</script>
	</body>
</html>
<?php return ""; // To avoid the calling function to output a success variable ?>