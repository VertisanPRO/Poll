<?php

if (!$user->isLoggedIn()) {
	Redirect::to(URL::build('/login'));
	die();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	Redirect::to(URL::build('/'));
	die();
} else {
	$poll_id = $_GET['id'];
}

define('PAGE', 'Poll');

$page_title = $poll_language->get('general', 'poll_module');

require_once(ROOT_PATH . '/core/templates/frontend_init.php');


$poll_data = end($queries->getWhere('polls', array('id', '=', $poll_id)));

if (!$user->hasPermission('poll.view')) {
	if ($poll_data->view != 1) {
		Redirect::to(URL::build('/'));
	}
}

$poll_votes = $queries->getWhere('poll_votes', array('poll_id', '=', $poll_id));
$poll_votes_data = array();

foreach ($poll_votes as $key => $value) {
	if (!isset($poll_votes_data[$value->poll_option_id])) {
		$poll_votes_data[$value->poll_option_id] = array($user->idToName($value->user_id));
	} else {
		array_push($poll_votes_data[$value->poll_option_id], $user->idToName($value->user_id));
	}
}

$smarty->assign(array(
	'VIEW_POLL' => $poll_data,
	'VIEW_POLL_OPTIONS' => $queries->getWhere('poll_options', array('poll_id', '=', $poll_id)),
	'VIEW_POLL_VOTES' => $poll_votes_data,
));



$template_file = 'Poll/view.tpl';

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, array($navigation, $cc_nav, $mod_nav), $widgets, $template);

$page_load = microtime(true) - $start;
define('PAGE_LOAD_TIME', str_replace('{x}', round($page_load, 3), $language->get('general', 'page_loaded_in')));

$template->onPageLoad();

$smarty->assign('WIDGETS_LEFT', $widgets->getWidgets('left'));
$smarty->assign('WIDGETS_RIGHT', $widgets->getWidgets('right'));

require(ROOT_PATH . '/core/templates/navbar.php');
require(ROOT_PATH . '/core/templates/footer.php');



$template->displayTemplate($template_file, $smarty);
