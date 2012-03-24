<?php

require_once 'classes/user/Auth.php';
require_once 'classes/user/User.php';

require_once 'includes/log.php';

require_once 'classes/content/Item.php';

require_once 'classes/stats/StatsCounter.php';

require_once 'classes/utils/ResponseCache.php';

define("VIDEO_PIPETV", 1050);
define("VIDEO_GREENPIPE", 1102);
define("VIDEO_STO", 2020);
define("VIDEO_NTV", 2021);

try {
	include 'includes/authorize.php';
	include 'views/header.php';

	$cache = new ResponseCache('index', array());
	if ($cache->getAge() < 60 * 60) {
		echo $cache->get();
	} else {
		$cache->start();

		$indexVideo = false;
		try {
			$random = mt_rand(0, 3);
			$indexVideoId = VIDEO_GREENPIPE;
			switch ($random) {
			case 0:
				$indexVideoId = VIDEO_GREENPIPE;
				break;
			case 1:
				$indexVideoId = VIDEO_STO;
				break;
			case 2:
				$indexVideoId = VIDEO_NTV;
				break;
			}
			$indexVideo = Item::getById($indexVideoId);
		} catch (Exception $e) {
			global $LOG;
			@$LOG->exception($e);
		}

		$photosCount = Item::countByType(Item::PHOTO);
		$photoAlbumsCount = Group::countByType(Group::PHOTO_ALBUM);

		$videosCount = Item::countByType(Item::VIDEO);
		$videoAlbumsCount = Group::countByType(Group::VIDEO_ALBUM);

		$postsCount = Item::countByType(Item::BLOG_POST);
		$blogsCount = Group::countByType(Group::BLOG);

		$messagesCount = Comment::countForumMessages();
		$topicsCount = Item::countByType(Item::FORUM_TOPIC);
		$partsCount = Group::countByType(Group::FORUM_PART);

?>

<div id="index_top">
	<div id="index_video">
<?
		if ($indexVideo && $indexVideo instanceof Video) {
			echo $indexVideo->getHTML();
		} else {
?>
		<div style="width: 480px; height: 385px; background-color: #aaa;"></div>
<?
		}
?>

	</div>
	<ul id="index_hrefs">
		<li>
			<a href="#wasistdas">
				<span class="href" style="font-size: 2em;">Что такое pipe-in-pipe?</span>
			</a>
		</li>

		<li>
			<a href="#rules">
				<span class="href" style="font-size: 2em;">Спортивные правила</span>
			</a>
		</li>

		<li>
			<a href="#training">
				<span class="href" style="font-size: 1.5em;">Где можно поиграть в пайп?</span>
			</a>
		</li>

		<li>
			<a href="/media/photo">
				<span class="href" style="font-size: 1.2em;">Фотографии</span>
				<span class="hidden">
					<?=lang_number_sclon($photosCount, 'фотография', 'фотографии', 'фотографий')?> в
					<?=lang_number_sclon($photoAlbumsCount, 'альбоме', 'альбомах', 'альбомах')?>
				</span>
			</a>
		</li>

		<li>
			<a href="/media/video">
				<span class="href" style="font-size: 1.2em;">Видеозаписи</span>
				<span class="hidden">
					<?=lang_number_sclon($videosCount, 'видеозапись', 'видеозаписи', 'видеозаписей')?> в
					<?=lang_number_sclon($videoAlbumsCount, 'альбоме', 'альбомах', 'альбомах')?>
				</span>
			</a>
		</li>

		<li>
			<a href="/life/blog">
				<span class="href" style="font-size: 1.2em;">Блоги</span>
				<span class="hidden">
					<?=lang_number_sclon($postsCount, 'пост', 'поста', 'постов')?> в
					<?=lang_number_sclon($blogsCount, 'блоге', 'блогах', 'блогах')?>
				</span>
			</a>
		</li>

		<li>
			<a href="/forum">
				<span class="href" style="font-size: 1.2em;">Форум</span>
				<span class="hidden">
					<?=lang_number_sclon($messagesCount, 'сообщение', 'сообщения', 'сообщений')?> в
					<?=lang_number_sclon($topicsCount, 'теме', 'темах', 'темах')?> и
					<?=lang_number_sclon($partsCount, 'разделе', 'разделах', 'разделах')?>
				</span>
			</a>
		</li>

		<li id="hrefs_social">
			<a href="http://vkontakte.ru/pipeinpipe">
				<img src="/images/social/vkontakte.png" alt="Мы В Контакте" />
			</a>
			<a href="http://twitter.com/pipeinfo">
				<img src="/images/social/twitter.png" alt="Наш твиттер" />
			</a>
<!--			<a href="http://youtube.com/">
				<img src="/images/social/youtube.png" alt="Наш канал на YouTube" />
			</a>-->
		</li>
	</ul>
</div>

<div class="detach_bar"></div>

<a name="wasistdas"></a>
<center>
	<img src="/images/bg/pipe_text.png" alt="Игра в pipe-in-pipe" style="width: 100%; max-width: 1600px;" />
</center>


<?
		$yearsOld = date('Y') - 2007 + (date('m-d') > '10-23' ? 0 : -1);

		$pipemenCount = Player::countAll();
		$competitionsCount = Competition::countAll();
		$leaguesCount = League::countAll();
		$gamesCount = Game::countAll();

		$maxMatchesCompetitions = StatsCounter::getInstance()->getCompsWithMaxMatches();
		$maxMatchesCompetitionIds = array_keys($maxMatchesCompetitions);
		$maxMatchesCompetition = Competition::getById($maxMatchesCompetitionIds[0]);
		$maxMatchesCount = $maxMatchesCompetitions[$maxMatchesCompetitionIds[0]];

		$maxPlayersCompetitions = StatsCounter::getInstance()->getCompsWithMaxPman();
		$maxPlayersCompetitionIds = array_keys($maxPlayersCompetitions);
		$maxPlayersCompetition = Competition::getById($maxPlayersCompetitionIds[0]);
		$maxPlayersCount = $maxPlayersCompetitions[$maxPlayersCompetitionIds[0]];
?>

<div class="body_container">
	<h1>Pipe-in-pipe &mdash; это...</h1>
	<table class="tripple">
		<tbody>
			<tr class="upper">
				<td>
					<span class="capital">1.</span>
					<span class="abstract">Новый вид спорта, придуманный в Санкт-Петербурге более <?=$yearsOld?> лет назад.</span>					
				</td>
				<td>
					<span class="capital">2.</span>
					<span class="abstract">Игра для двух игроков, двух деков и двух труб.</span>					
				</td>
				<td>
					<span class="capital">3.</span>
					<span class="abstract">Большое сообщество интересных, разносторонних и целеустремлённых людей.</span>					
				</td>
			</tr>
			<tr>
				<td>
					<p>
						За прошешее время при участии <?=lang_number_sclon($pipemenCount, 'пайпмена', 'пайпменов', 'пайпменов')?>
						<?=lang_sclon($leaguesCount, 'была создана', 'были созданы', 'были созданы')?>
						<?=lang_number_sclon($leaguesCount, 'лига', 'лиги', 'лиг')?>, в
						<?=lang_number_sclon($competitionsCount, 'турнире', 'турнирах', 'турнирах')?>
						<?=lang_sclon($gamesCount, 'был сыгран', 'было сыграно', 'было сыграно')?>
						<?=lang_number_sclon($gamesCount, 'матч', 'матча', 'матчей')?>.
					</p>
					<p>
						Так в турнирах
						<a href="/sport/league/<?=$maxMatchesCompetition->getLeagueId()?>/competition/<?=$maxMatchesCompetition->getId()?>"><?=$maxMatchesCompetition->getName()?></a> и
						<a href="/sport/league/<?=$maxMatchesCompetition->getLeagueId()?>/competition/<?=$maxPlayersCompetition->getId()?>"><?=$maxPlayersCompetition->getName()?></a>
						было зарегистрировано максимальное количество матчей (<?=$maxMatchesCount?>) и участников соответственно (<?=$maxPlayersCount?>).
					</p>
				</td>
				<td>
					<p>
						<b>Большой пайп</b> &mdash; пластмассовая труба длиной 190 см и диамером 50 мм.
					</p>
					<p>
						<b>Малый пайп</b> &mdash; пластмассовая труба длиной 49 см и диаметром 40 мм, свободно перемещается внутри большой.
					</p>
					<p>
						<b>Дек</b> &mdash; фанерная дощечка (ширина 23 см, высота 32 см, толщина 8 мм), обтянутая тканью для смягчения удара.
					</p>
				</td>
				<td>
					<p>
						Помимо того, что пайп простой и захватывающий спорт, это ещё и отличная компания друзей.
						Не было ещё такого человека, который бы пожалел о своём знакомстве с пайп-сообществом.
					</p>
					<p>
						Участие в наших турнирах это не только игра, это ещё и хороший способ
						отдохнуть, развлечься и прекрасно провести время в компании единомышленников.
					</p>
				</td>
			</tr>
		</tbody>
	</table>

	<h1>Краткие правила / <a name="rules" href="/sport/rules">Полные правила</a></h1>
	<table class="tripple">
		<tbody>
			<tr class="upper">
				<td>
					<span class="capital">1.</span>
					<span class="abstract">Матч в пайп состоит из розыгрышей очков и всегда завершается победой одного из игроков.</span>
				</td>
				<td>
					<span class="capital">2.</span>
					<span class="abstract">Каждый розыгрыш начинается с подачи одного из игроков.</span>
				</td>
				<td>
					<span class="capital">3.</span>
					<span class="abstract">Игроки должны слёту отбивать малый пайп в сторону оппонента.</span>
				</td>
			</tr>
			<tr>
				<td>
					<p>Обычно матч в пайп идёт до 5 очков и до разницы в 2 очка. Таким образом победными считаются счета: 5:0, 5:1, 5:2, 5:3,
					6:4, 7:5 и т.д.</p>
				</td>
				<td>
					<p>Нужно засунуть малый пайп внутрь большого так, чтобы он частично остался снаружи, и ударить по малому.</p>
				</td>
				<td>
					<p>
						<b>Слёту</b> означает, что нельзя останавливать малый, а затем наносить повторный удар.
						Более формально это описывается следующим правилом.
					</p>
				</td>
			</tr>
			<tr class="upper">
				<td>
					<span class="capital">4.</span>
					<span class="abstract">После удара соперника у игрока есть только один удар, чтобы отразить малый пайп.</span>					
				</td>
				<td>
					<span class="capital">5.</span>
					<span class="abstract">Малый пайп может вылететь из большого. В этом случае проиграл тот, с чьего конца трубы это произошло.</span>
				</td>
				<td>
					<span class="capital">6.</span>
					<span class="abstract">Малый пайп может застрять внутри большого. В этом случае проиграл тот, кто нанес последний удар по малому.</span>
				</td>
			</tr>
			<tr>
				<td>
					<p>
						<b>Нельзя</b> останавливать малый, а затем наносить повторный удар.
					</p>
					<p>
						Ударив преждевременно по большому, уже <b>нельзя</b> наносить повторный удар по малому.
					</p>
					<p>
						Всё это приводит к ситуации, называемой <b>двойным касанием</b>, и к зачислению очка сопернику.
					</p>
				</td>
				<td>
					<p>Такая ситуация называется <b>пайп-аут</b>. Пайп-аут при подаче называется <b>эйс</b>.</p>
				</td>
				<td>
					<p>Такая ситуация называется <b>пайп-ин</b>. Первый пайп-ин при подаче приводит к переподаче, повторный &mdash; к переходу подачи и начислению очка сопернику.</p>
				</td>
			</tr>
		</tbody>
	</table>

<?
		$wprCompetitions = Competition::getByLeagueId(League::MAIN_LEAGUE_ID);
		$runningCompetitions = array();
		$registeringCompetitions = array();
		foreach ($wprCompetitions as $competition) {
			if ($competition->isRunning()) {
				$runningCompetitions[] = $competition;
			}
			if ($competition->isRegistering()) {
				$registeringCompetitions[] = $competition;
			}
		}
?>

	<a name="training"></a>
	<h1>Где можно поиграть в пайп?</h1>
	<table class="tripple">
		<tbody>
			<tr class="upper">
				<td>
					<span class="capital">1.</span>
					<span class="abstract">
						Ежегодно главной лигой пайпа (<a href="/sport/wpr/<?=League::MAIN_LEAGUE_ID?>">WPR</a>)
						проводятся открытые для всех желающих турниры.
					</span>
				</td>
				<td>
					<span class="capital">2.</span>
					<span class="abstract">
						Помимо основной существуют и другие лиги со своим списком турниров на сезон и правилами участия.
					</span>
				</td>
				<td>
					<span class="capital">3.</span>
					<span class="abstract">
						Также пайп традиционно является отличным развлечением на различных праздничных мероприятиях,
						организуемых нами и нашими друзьями.
					</span>
				</td>
			</tr>
			<tr>
				<td>
					<p>
<?
		if (!empty($registeringCompetitions)) {
			$comp = $registeringCompetitions[0];
?>

						Вам очень повезло! Сейчас идёт регистрация на
						<a href="/sport/league/<?=$comp->getLeagueId()?>/competition/<?=$comp->getId()?>"><?=$comp->getName()?></a>.
						Перейдите по <a href="/sport/league/<?=$comp->getLeagueId()?>/<?=$comp->getId()?>">ссылке</a>,
						чтобы оставить свою заявку на этот турнир.
<?
		} elseif (!empty($runningCompetitions)) {
			$count = count($runningCompetitions);
?>

						В данный момент <?=($count > 1) ? 'идут следующие турниры' : 'идёт турнир'?>
						<?if($count >= 1):?>
							<?foreach($runningCompetitions as $i => $comp):?>
						<a href="/sport/league/<?=$comp->getLeagueId()?>/competition/<?=$comp->getId()?>"><?=$comp->getName()?></a><?if($i < $count - 2):?>, <?elseif($i == $count - 2):?> и <?else:?>.<?endif;?>
							<?endforeach;?>
						<?endif;?>
<?
		} elseif ($auth->isAuth()) {
?>

						Вы уже зарегистрированы на сайте пайпа и поэтому знаете, 
						как <a href="/life">следить</a> за появлением новых турниров. :)
<?
		} else {
?>

						<a href="/sign_up">Зарегистрируйтесь</a> на сайте пайпа, чтобы постоянно быть в курсе всех новостей,
						в том числе и проведения новых турниров.
<?
		}
?>
					</p>
				</td>
				<td>
					<p>
						Вы можете ознакомиться со <a href="/sport/league">списком таких лиг</a> и найти подходящую.
					</p>
					<p>
						Либо запаситесь достаточным количеством энтузиазма и при нашей помощи организуйте свою!
					</p>
				</td>
				<td>
					<p>
						Мы можем помочь Вам организовать небольшой пайп-праздник самостоятельно или
						рассмотреть заявку на участие в уже запланированном событии. <a href="mailto:info@pipeinpipe.info">Пишите!</a>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<?
		$cache->store();
	}

	include 'views/footer.php';
} catch (Exception $e) {
	global $LOG;
	$LOG->exception($e);
}

?>
