<?php
/**
 * @author Innokenty Shuvalov
 */
require_once dirname(__FILE__).'/../views/sport_competition_functions.php';
require_once dirname(__FILE__).'/../includes/assertion.php';
require_once dirname(__FILE__).'/../includes/common.php';
require_once dirname(__FILE__).'/../classes/user/Auth.php';
require_once dirname(__FILE__).'/../classes/user/User.php';

try {
	assertIsset($_REQUEST['method'], 'method');
	$method = $_REQUEST['method'];

	$auth = new Auth();
	$user = $auth->getCurrentUser();

	if (isset($_REQUEST['cup_id'])) {
		$cupId = $_REQUEST['cup_id'];
		$cup = CupFactory::getCupById($cupId);

		switch ($method) {
			case 'load_cup' :
				sport_competition_show_cup_slide_block($cup);
				exit(0);

			case 'load_structure' :
				sport_competition_show_structure_slide_block($cup->getCompetition(), $cupId);
				exit(0);

			case 'load_children' :
				sport_competition_show_cup_children_preview($cup->getChildren());
				exit(0);
		}
	} else {
		switch ($method) {
			case 'registration':
				assertIsset($_REQUEST['comp_id'], 'comp_id');

				$uid = $user->getId();
				$competition = Competition::getById($_REQUEST['comp_id']);
				$comment = string_process(param('text'));
				
				$isRegistered = false;
				foreach ($competition->getRegisteredUsers() as $currentUser) {
					if ($currentUser->getId() == $uid) {
						$isRegistered = true;
						break;
					}
				}

				if (($isRegistered && $competition->unregister($uid, $user->getPmid())) ||
					(!$isRegistered && $competition->register($uid, $user->getPmid(), $comment))) {
					echo $isRegistered ? '' : sport_html_registered_user($user);
				} else {
					echo json(array('status' => 'failed'));
				}

				exit(0);
		}
	}

} catch (Exception $ex) {
    echo json(
            array(
                'status' => 'failed',
                'message' => $ex->getMessage()
            )
        );
}
	
?>
