<?php
/**
 * @author Artyom Grigoriev
 */

$team_ids = array(
	'1' => 'Руководитель',
	'9' => 'Разработчик',
	'77' => 'Разработчик',
	'17' => 'Дизайнер',
	'45' => 'Разработчик',
	'102' => 'Разработчик',
	'60' => 'Разработчик',
	'97' => 'Стажёр'
);

$helper_ids = array(
	'116' => 'Разработчик',
	'111' => 'Заготовитель контента',
	'154' => 'Разработчик',
	'162' => 'Разработчик',
	'21' => 'Текстописец',
	'133' => 'Консультант'
);

$team = array();
foreach ($team_ids as $id => $value) {
	$team[] = User::getById($id);
}

$helpers = array();
foreach ($helper_ids as $id => $value) {
	$helpers[] = User::getById($id);
}

?>

<div class="body_container">
	<h1>О сайте</h1>

	<div class="left_column">
		<div class="left_container">
			<img src="/images/bg/about_site.png" alt="Сайт пайпа внутри" style="width: 100%;" />
			<p>
				Сайт пайпа &mdash; уникальный информационный ресурс, посвящённый спортивной игре pipe-in-pipe.
			</p>
			<p>
				Наша история началась <b>23 сентября 2008 года</b>, когда по этому адресу была запущена вторая
				версия сайта. Первая была совсем уж тестовая и располагалась по другому адресу.
			</p>
			<p>
				<b>26 декабря 2008 года</b> сайт был номинирован на Интернет-премию <b>Enthusiast Internet Award</b>,
				однако призовое место так и не занял. Однако в его развитии был сделан очередной прорыв,
				результатом которого стал запуск <b>6 февраля 2009 года</b> третьей версии. Она служила верой и правдой
				поклонникам пайпа на протяжении более двух лет, однако была далека от идеала и очень трудно
				масштабируема.
			</p>
			<p>
				Шли годы, и пайп-сообщество серьёзно стало задумываться об обновлении сайта. Нужно было улучшать
				скорость работы под нагрузкой, внедрять <a href="/sport/league">лиги</a>, интегрироваться в
				социальные сети...
			</p>
			<p>
				И наконец, <b>6 июля 2010 года</b> мы начали сложный путь разработки нового сайта. <b>С нуля.</b>
			</p>
			<p>
				В разработке в общей сложности участвовало <b>12 человек</b>, среди которых 8 программистов,
				3 управляющих контентом и 1 дизайнер. Среди используемых технологий перечислим следующие:
				язык программирования для бизнес-логики &mdash; PHP; пользовательский интерфейс &mdash;
				XHTML, CSS, JavaScript, jQuery; фоновый обмен данными &mdash; AJAX, JSON; база данных &mdash;
				MySQL.
			</p>
			<p>
				Новая, действующая по сей день версия сайта пайпа была запущена утром <b>15 апреля 2011 года</b>.
			</p>
			<p>
				<b>12 июня 2011 года</b> мы переехали на собственный сервер в облаке компании
				<a target="_blank" href="http://selectel.ru">Selectel</a>.
			</p>
			<p>
				<b>13 июня 2011 года</b> была запущена <a href="http://m.pipeinpipe.info">мобильная версия сайта</a>.
			</p>
			<p>
				Мы продолжаем развиваться. Сейчас сайт пайпа &mdash; это
				<b><?=lang_number_sclon(count($team_ids), "разработчик", "разработчика", "разработчиков")?></b> и
				<b>более 50 тысяч строк кода</b>.
			</p>
		</div>
	</div>
	<div class="right_column">
		<div class="right_container">
			<h2 class="other">Команда разработчиков</h2>
			<table>
				<tbody>
<?
foreach ($team as $member) {
?>
					<tr class="member">
						<td class="name">
							<a href="/id<?=$member->getId()?>"><?=$member->getFullName()?></a>
							<div class="role"><?=$team_ids[$member->getId()]?></div>
						</td>
						<td class="photo">
							<img src="<?=$member->getImageUrl(User::IMAGE_SQUARE)?>" alt="<?=$member->getFullName()?>" />
						</td>
					</tr>
<?
}
?>

				</tbody>
			</table>

			<h2 class="other">Благодарим за помощь</h2>
			<table>
				<tbody>
<?
foreach ($helpers as $member) {
?>
					<tr class="member">
						<td class="name">
							<a href="/id<?=$member->getId()?>"><?=$member->getFullName()?></a>
							<div class="role"><?=$helper_ids[$member->getId()]?></div>
						</td>
						<td class="photo">
							<img src="<?=$member->getImageUrl(User::IMAGE_SQUARE)?>" alt="<?=$member->getFullName()?>" />
						</td>
					</tr>
<?
}
?>

				</tbody>
			</table>
		</div>
	</div>
</div>
