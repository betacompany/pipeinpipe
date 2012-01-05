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
	'102' => 'Разработчик'
);

$helper_ids = array(
	'116' => 'Разработчик',
	'111' => 'Заготовитель контента',
	'154' => 'Разработчик',
	'162' => 'Разработчик',
	'60' => 'Текстописец',
	'21' => 'Текстописец'
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
