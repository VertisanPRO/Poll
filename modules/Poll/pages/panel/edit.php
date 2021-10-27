<?php
// Can the user view the panel?
if ($user->isLoggedIn()) {
	if (!$user->canViewStaffCP()) {
		// No
		Redirect::to(URL::build('/'));
		die();
	}
	if (!$user->isAdmLoggedIn()) {
		// Needs to authenticate
		Redirect::to(URL::build('/panel/auth'));
		die();
	} else {
		if ($user->getMainGroup()->id != 2 && !$user->hasPermission('poll.manage')) {
			require_once(ROOT_PATH . '/403.php');
			die();
		}
	}
} else {
	// Not logged in
	Redirect::to(URL::build('/login'));
	die();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	Redirect::to(URL::build('/panel/poll'));
	die();
} else {
	$poll_id = $_GET['id'];
}

define('PAGE', 'panel');
define('PARENT_PAGE', 'poll_configuration');
define('PANEL_PAGE', 'poll_module');
$page_title = $poll_language->get('general', 'poll_module');
require_once(ROOT_PATH . '/core/templates/backend_init.php');


if (isset($_POST['delete_poll_options'])) {
	$errors = array();
	if (Token::check(Input::get('token'))) {

		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'delete_poll_options' => array(
				'required' => true
			)
		));

		if ($validation->passed()) {
			try {
				$queries->delete('poll_options', array('id', '=', Input::get('delete_poll_options')));

				Session::flash('staff_session', $poll_language->get('general', 'deleted_successfully'));
				Redirect::to(URL::build('/panel/poll/edit', 'id=' . $poll_id));
			} catch (Exception $e) {
				$errors[] = $e->getMessage();
			}
		}
	}
}

if (isset($_POST['poll_update'])) {
	$errors = array();
	if (Token::check(Input::get('token'))) {

		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'poll_update' => array(
				'required' => true,
				'min' => 2
			)
		));

		if ($validation->passed()) {

			if (Input::get('view_poll') == 1) {
				$view_poll = 1;
			} else {
				$view_poll = 0;
			}

			try {
				$queries->update('polls', $poll_id, array(
					'subject' => htmlspecialchars(Input::get('poll_update')),
					'view' => $view_poll,
				));
				Session::flash('staff_session', $poll_language->get('general', 'save_successfully'));
				Redirect::to(URL::build('/panel/poll/edit', 'id=' . $poll_id));
			} catch (Exception $e) {
				$errors[] = $e->getMessage();
			}
		}
	}
}


if (isset($_POST['add_poll_options'])) {
	$errors = array();
	if (Token::check(Input::get('token'))) {

		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'add_poll_options' => array(
				'required' => true,
				'min' => 2
			)
		));

		if ($validation->passed()) {

			try {
				$queries->create('poll_options', array(
					'name' => htmlspecialchars(Input::get('add_poll_options')),
					'poll_id' => $poll_id,
				));
				Session::flash('staff_session', $poll_language->get('general', 'add_successfully'));
				Redirect::to(URL::build('/panel/poll/edit', 'id=' . $poll_id));
			} catch (Exception $e) {
				$errors[] = $e->getMessage();
			}
		}
	}
}



$smarty->assign(array(
	'VIEW_RESULT_LABEL' => $poll_language->get('general', 'view_result_label'),
	'POLL_NAME_LABEL' => $poll_language->get('general', 'poll_name_label'),
	'ADD_OPTIONS_LABEL' => $poll_language->get('general', 'add_options_label'),
	'OPTIONS_NAME_LABEL' => $poll_language->get('general', 'options_name_label'),
	'POLL' => end($queries->getWhere('polls', array('id', '=', $poll_id))),
	'POLL_OPTIONS' => $queries->getWhere('poll_options', array('poll_id', '=', $poll_id)),
	'TOKEN' => Token::get(),
	'YOU_ARE_SURE' => $poll_language->get('general', 'you_are_sure'),
	'SUBMIT' => $poll_language->get('general', 'submit'),
	'YES' => $poll_language->get('general', 'yes'),
	'NO' => $poll_language->get('general', 'no'),
	'BACK_URL' => URL::build('/panel/poll'),
));


// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, array($navigation, $cc_nav, $mod_nav), $widgets);

$page_load = microtime(true) - $start;
define('PAGE_LOAD_TIME', str_replace('{x}', round($page_load, 3), $language->get('general', 'page_loaded_in')));

$template->onPageLoad();

if (Session::exists('staff_session'))
	$success = Session::flash('staff_session');

if (Session::exists('staff_session_err'))
	$errors = Session::flash('staff_session_err');

if (isset($success))
	$smarty->assign(array(
		'SUCCESS' => $success,
		'SUCCESS_TITLE' => $language->get('general', 'success')
	));

if (isset($errors) && count($errors))
	$smarty->assign(array(
		'ERRORS' => $errors,
		'ERRORS_TITLE' => $language->get('general', 'error')
	));

require(ROOT_PATH . '/core/templates/panel_navbar.php');

// Display template
$template->displayTemplate('poll/edit.tpl', $smarty);
