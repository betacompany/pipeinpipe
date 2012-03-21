<?php
require_once dirname(__FILE__) . '/../includes/config.php';

require_once dirname(__FILE__) . '/../../main/classes/cupms/Player.php';

require_once dirname(__FILE__) . '/../../main/classes/user/User.php';

require_once dirname(__FILE__) . '/cup_tree_view.php';
require_once dirname(__FILE__) . '/cup_players_view.php';
require_once dirname(__FILE__) . '/../templates/response.php';

function players_main_page() {
?>

<style type="text/css">
	.content_properties > div > div:first-child {
		width: 140px;
	}
</style>

	<div id="content">
        <div id="content_header">Управление пайп-менами</div>
		<div id="content_body">
			<div>
				<div id="player_selector">
					<script type="text/javascript">
						var peopleSelector = (new DynamicSelector({
							content: <?=json(Player::getAllToHTML());?>,
							onSelect: function (id) {
								player.fillById(id);
							}
						}))
						.setWidth(324)
						.appendTo($('#player_selector'));
					</script>
				</div>
				<div id="new_player_button">
					<script type="text/javascript">
						var newPlayerButton = (new Button({
							onClick: player.clearFields,
							container: 'new_player_button',
							html: "Новый Пайп-мен"
						}));
					</script>
				</div>
			</div>
			<div class="content_properties">
<?
	foreach (array("name", "surname", "gender", "city", "country", "email", "description", "user") as $value) {
		editDetail($value);
	}
?>
                <div>
					<div id="save_button">
						<script type="text/javascript">
							var playerSaveButton = (new Button({
								onClick: player.go,
								container: 'save_button',
								html: "Сохранить"
							}));
						</script>
					</div>
				</div>
			</div>
		</div>
    </div>
<?
}

function editDetail($str) {
?>
<div>
	<div><?=Player::getDetailName($str)?></div>
<?
    switch ($str) {
        case Player::INFO_KEYS_DESCRIPTION :
?>
	<div> <textarea style="width: 322px;" id="<?=$str?>"></textarea> </div>
<?
        break;

        case Player::INFO_KEYS_GENDER :
?>
	<div id="gender">
		<script type="text/javascript">
				var genSelector = (new DynamicSelector(
				{
					content: [
						{
							id: 'm',
							value: 'мужской'
						},
						{
							id: 'f',
							value: 'женский'
						}
					]
				}
			))
			.setWidth(324)
			.select('m')
			.appendTo($('#gender'));
		</script>
	</div>
<?
        break;

		case Player::INFO_KEYS_USER:
?>
	<div id="user_selector">
		<script type="text/javascript">
			var userSelector = (new DynamicSelector({
				content: <?=json(User::getAllToHTML());?>
			}))
			.setWidth(324)
			.appendTo($('#user_selector'));
		</script>
	</div>
<?

		break;

        default :
?>
	<div> <input type="text" id="<?=$str?>" /> </div>
<?
        break;
    }
?>
</div>
<?
}
?>
