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

define('PAGE', 'panel');
define('PARENT_PAGE', 'poll_configuration');
define('PANEL_PAGE', 'poll_module');
$page_title = $poll_language->get('general', 'poll_module');
require_once(ROOT_PATH . '/core/templates/backend_init.php');


if (isset($_POST['poll_status'])) {
	$errors = array();
	if (Token::check(Input::get('token'))) {

		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'poll_id' => array(
				'required' => true
			)
		));

		if ($validation->passed()) {

			if (Input::get('poll_status') == 1) {
				$poll_status = 0;
			} else {
				$poll_status = 1;
			}

			try {
				$queries->update('polls', (int) Input::get('poll_id'), array(
					'status' => $poll_status,
				));
				Redirect::to(URL::build('/panel/poll'));
			} catch (Exception $e) {
				$errors[] = $e->getMessage();
			}
		}
	}
}


if (isset($_POST['poll_subject'])) {
	$errors = array();
	if (Token::check(Input::get('token'))) {

		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'poll_subject' => array(
				'required' => true,
				'min' => 2
			)
		));

		if ($validation->passed()) {

			try {
				$queries->create('polls', array(
					'subject' => htmlspecialchars(Input::get('poll_subject')),
					'status' => 1,
					'view' => 0,
				));
				Redirect::to(URL::build('/panel/poll/edit', 'id=' . $queries->getLastId()));
			} catch (Exception $e) {
				$errors[] = $e->getMessage();
			}
		}
	}
}

if (isset($_POST['delete_poll'])) {
	$errors = array();
	if (Token::check(Input::get('token'))) {

		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'delete_poll' => array(
				'required' => true
			)
		));

		if ($validation->passed()) {
			try {
				$queries->delete('polls', array('id', '=', Input::get('delete_poll')));
				Session::flash('staff_session', $poll_language->get('general', 'deleted_successfully'));
				Redirect::to(URL::build('/panel/poll'));
			} catch (Exception $e) {
				$errors[] = $e->getMessage();
			}
		}
	}
}



$smarty->assign(array(
	'ADD_POLL_LABEL' => $poll_language->get('general', 'add_poll_label'),
	'POLL_NAME_LABEL' => $poll_language->get('general', 'poll_name_label'),
	'POLLS' => $queries->getWhere('polls', array('id', '<>', 0)),
	'TOKEN' => Token::get(),
	'YOU_ARE_SURE' => $poll_language->get('general', 'you_are_sure'),
	'SUBMIT' => $poll_language->get('general', 'submit'),
	'YES' => $poll_language->get('general', 'yes'),
	'NO' => $poll_language->get('general', 'no'),
	'EDIT_URL' => URL::build('/panel/poll/edit', 'id='),
	'VIEW_URL' => URL::build('/poll/view', 'id='),
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
$template->displayTemplate('poll/poll.tpl', $smarty);
