<?php

class PollWG extends WidgetBase
{
	private $_smarty, $_poll_language;
	public function __construct($pages = array(), $smarty, $user, $poll_language)
	{
		parent::__construct($pages);
		$this->_smarty = $smarty;
		$this->_poll_language = $poll_language;
		$this->_user = $user;
		// Get order
		$order = DB::getInstance()->query('SELECT `location`, `order` FROM nl2_widgets WHERE `name` = ?', array('PollWG'))->first();

		// Set widget variables
		$this->_module = 'Poll';
		$this->_name = 'PollWG';
		$this->_location = isset($order->location) ? $order->location : null;
		$this->_description = 'Poll Widget';
		$this->_order = $order->order;
	}
	public function initialise()
	{



		require_once(ROOT_PATH . '/core/templates/frontend_init.php');
		require_once(ROOT_PATH . '/modules/Poll/classes/Poll.php');
		$poll = new Poll();


		//Проверяем, отправлен ли ответ
		if ($this->_user->isLoggedIn()) {
			if (isset($_POST['voteSubmit'])) {
				$voteData = array(
					'poll_id' => $_POST['pollID'],
					'poll_option_id' => $_POST['voteOpt'],
					'user_id' => $this->_user->data()->id,
				);
				//Оправляем результаты опроса с помощью класса Poll 
				$poll->vote($voteData);
			}
		} else {
			$this->_smarty->assign(array(
				'NO_LOGIN_TEXT' => $this->_poll_language->get('general', 'no_login_text'),
			));
		}


		$poll_data = $poll->getPolls('all', 1);
		$totalVotes = array();
		$poll_options = array();

		foreach ($poll_data as $pl) {
			$pollID = $pl['poll']['id'];

			if (empty($pl['options'])) {
				continue;
			}

			foreach ($poll_data as $key => $value) {
				if (empty($value['options'])) {
					continue;
				}
				$poll_data_arr[$value['poll']['id']] = $value;
			}

			$pollResult[$pollID] = $poll->getResult($pollID);
			foreach ($pollResult[$pollID] as $value) {
				$tv[$pollID] = $tv[$pollID] + $value;
			}
			$totalVotes[$pollID] = $tv[$pollID];

			$poll_options = array_merge($poll_options, $poll_data_arr[$pollID]['options']);
		}


		$this->_smarty->assign(array(
			'POLL_DATA' => $poll_data_arr,
			'POLL_OPTIONS' => json_encode($poll_options),
			'POLL_RESULT' => $pollResult,
			'TOTAL_VOTES' => $totalVotes,
			'VOTE_LABEL' => $this->_poll_language->get('general', 'vote_label'),
			'VIEW_URL' => URL::build('/poll/view', 'id='),
		));

		$this->_content = $this->_smarty->fetch('Poll/widgets/poll.tpl');
	}
}
