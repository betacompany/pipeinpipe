<?php

require_once dirname(__FILE__) . '/../../auth/common-auth.php';

switch ($_REQUEST['act']) {
case 'in':
	CommonAuth::signIn('ortemij', 'acvbyirw', false);
	break;
case 'ins':
	CommonAuth::signIn('ortemij', 'acvbyirw', true);
	break;
case 'out':
	CommonAuth::signOut();
	break;
}

