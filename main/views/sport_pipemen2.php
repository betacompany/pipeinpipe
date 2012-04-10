<?php
require_once dirname(__FILE__) . '/../classes/cupms/Player.php';
require_once dirname(__FILE__) . '/../classes/cupms/League.php';

require_once dirname(__FILE__) . '/../classes/utils/ResponseCache.php';

$cache = new ResponseCache('sport/pipemen', array());
if ($cache->getAge() <= 60 * 60) {
	echo $cache->get();
} else {

	$cache->start();

	$pipemenInfo = array();

	foreach (Player::getAll() as $player) {
		$temp = array(
			'id' => $player->getId(),
			'name' => $player->getFullName(),
			'url' => $player->getURL(),
			'city' => $player->getCity(),
			'imageUrl' => $player->getImageURL(),
			'victories' => $player->countVictories(),
			'games' => $player->countGames(),
			'play_off' => $player->countPlayOffGames()
		);

		$leagues = $player->getTopLeagues();
		$leagueInfos = $player->getTopLeagueInfos();
		$temp['leagues'] = array();
		foreach ($leagues as $i => $league) {
			$info = $leagueInfos[$i];
			$temp['leagues'] = array(
				'name' => $league->getName(),
				'place' => $info['place']
			);
		}

		$pipemenInfo[] = $temp;
	}
?>

<script type="text/javascript">
	$$(function () {
		var pipemen = <?=json($pipemenInfo)?>;

		var comparators = [];
		//by id
		comparators[0] = function(a, b) {
			a.id = parseInt(a.id);
			b.id = parseInt(b.id);
			if (a.id > b.id) {
				return 1;
			} else if (a.id == b.id) {
				return 0;
			} else {
				return -1;
			}
		};

		//by victories
		comparators[1] = function(a, b) {
			c = parseInt(a.victories);
			d = parseInt(b.victories);
			return d - c;
		};

		//by vitories percentage
		comparators[2] = function(a, b) {
			vic1 = parseFloat(a.victories);
			total1 = parseFloat(a.games);
			vic2 = parseFloat(b.victories);
			total2 = parseFloat(b.games);
			if (total1 == 0) {
				return 1;
			}
			if (total2 == 0) {
				return -1;
			}
			return -(vic1 / total1 - vic2 / total2);
		};

		//by total games
		comparators[3] = function(a, b) {
			t1 = parseInt(a.games);
			t2 = parseInt(b.games);
			return t2 - t1;
		};

		//by play-off games
		comparators[4] = function(a, b) {
			p1 = parseInt(a['play_off']);
			p2 = parseInt(b['play_off']);
			return p2 - p1;
		};

		var sortSelector = new Selector({
			content: [
				{id: 1, value: "по умолчанию"},
				{id: 2, value: "по количеству побед"},
				{id: 3, value: "по проценту побед"},
				{id: 4, value: "по количеству сыгранных матчей"},
				{id: 5, value: "по количеству матчей в плей-офф"}
			],
			onSelect: function(id) {
				pipemen.sort(comparators[id - 1]);
				render(pipemen);
			}
		});

		function render(pipemenInfo) {
			debug('start');
			$('#pipemen_container').html('');
			for (var i = 0; i < pipemenInfo.length; i++) {
				var pm = pipemenInfo[i];

				var leaguesContainer = $('<div/>').addClass('leagues');
				if (pm.leagues) {
					for (var j = 0; j < 2; j++) {
						if (pm.leagues[j]) {
							leaguesContainer.append(
								$('<div/>')
									.addClass('league_info')
									.append('#' + pm.leagues[j].place + ' ' + pm.leagues[j].name)
							);
						}
					}
				}

				$('<div/>')
					.addClass('pipeman_wrapper')
					.appendTo($('#pipemen_container'))
					.append(
						$('<div/>')
							.addClass('pipeman')
							.append(
								$('<a>')
									.attr('href', '/pm' + pm.id)
									.append(
										$('<img/>')
											.attr('src', pm.imageUrl)
									)
							)
							.append(
								$('<div/>')
									.addClass('description')
									.append(
										$('<div/>')
											.addClass('name')
											.html('<a href="/pm' + pm.id + '">' + pm.name + '</a>')
									)
									.append(
										$('<div/>')
											.addClass('city')
											.html('999')
									)
									.append(leaguesContainer)
							)
					);
			}
			debug('end');
		}

		$(function(){
			sortSelector.select(1);
			sortSelector.appendTo($('#sort_selector'));
			render(pipemen);
		});
	});
</script>
<div class="body_container">
	<div style="float:left; margin-left:10px; margin-right:3px;">
		Сортировать:
	</div>
	<div id="sort_selector">

	</div>
</div>
<div id="pipemen_container" class="body_container">

</div>
<?
	$cache->store();
}
?>