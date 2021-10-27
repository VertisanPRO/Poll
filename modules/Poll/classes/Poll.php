<?php
/*
 * Управляющий класс Poll 
 * Этот класс используется для управления системой онлайн опросов и голосований
 * @author    CodexWorld.com
 * @url       http://www.codexworld.com
 * @license   http://www.codexworld.com/license
 */
class Poll
{
	private $dbHost;
	private $dbUser;
	private $dbPwd;
	private $dbName;
	private $dbPort;
	private $db      = false;
	private $pollTbl;
	private $optTbl;
	private $voteTbl;

	public function __construct()
	{

		$this->dbHost  = Config::get('mysql/host');
		$this->dbUser  = Config::get('mysql/username');
		$this->dbPwd   = Config::get('mysql/password');
		$this->dbName  = Config::get('mysql/db');
		$this->dbPort  = Config::get('mysql/port');

		$this->pollTbl  = Config::get('mysql/prefix') . 'polls';
		$this->optTbl   = Config::get('mysql/prefix') . 'poll_options';
		$this->voteTbl  = Config::get('mysql/prefix') . 'poll_votes';


		if (!$this->db) {
			// Устанавливаем соединение с базой данных
			$conn = new mysqli($this->dbHost, $this->dbUser, $this->dbPwd, $this->dbName, $this->dbPort);
			if ($conn->connect_error) {
				die("Failed to connect with MySQL: " . $conn->connect_error);
			} else {
				$this->db = $conn;
			}
		}
	}

	/*
     * Выполняем запрос к базе данных
     * @param строка SQL
     * @param строка count, single, all
     */
	private function getQuery($sql, $returnType = '')
	{
		$data = array();
		$result = $this->db->query($sql);
		if ($result) {
			switch ($returnType) {
				case 'count':
					$data = $result->num_rows;
					break;
				case 'single':
					$data = $result->fetch_assoc();
					break;
				default:
					if ($result->num_rows > 0) {
						while ($row = $result->fetch_assoc()) {
							$data[] = $row;
						}
					}
			}
		}
		return !empty($data) ? $data : false;
	}

	/*
     * Получаем данные опроса
     * Возвращаем данные одного или нескольких вопросов вместе с соответствующими им вариантами ответов
     * @param строка single, all
     */
	public function getPolls($pollType = 'single')
	{
		$pollData = array();
		$sql = "SELECT * FROM " . $this->pollTbl . " WHERE status = '1' ORDER BY id DESC";
		$pollResult = $this->getQuery($sql, $pollType);
		if (!empty($pollResult)) {
			if ($pollType == 'single') {
				$pollData['poll'] = $pollResult;
				$sql2 = "SELECT * FROM " . $this->optTbl . " WHERE poll_id = " . $pollResult['id'];
				$optionResult = $this->getQuery($sql2);
				$pollData['options'] = $optionResult;
			} else {
				$i = 0;
				foreach ($pollResult as $prow) {
					$sql2 = "SELECT * FROM " . $this->optTbl . " WHERE poll_id = " . $prow['id'];
					$optionResult = $this->getQuery($sql2);
					$pollData[$i] = array(
						'poll' => $prow,
						'options' => $optionResult

					);
					$i++;
				}
			}
		}
		return !empty($pollData) ? $pollData : false;
	}

	/*
     * Подтверждаем ответ
     * @param массив вариантов ответов
     */
	public function vote($data = array())
	{
		if (!isset($data['poll_id']) || !isset($data['poll_option_id']) || !isset($data['user_id'])) {
			return false;
		} else {
			$sql = "SELECT * FROM " . $this->voteTbl . " WHERE poll_id = " . $data['poll_id'] . " AND user_id = " . $data['user_id'];
			$preVote = $this->getQuery($sql, 'count');
			if ($preVote > 0) {
				$query = "UPDATE " . $this->voteTbl . " SET poll_option_id = " . $data['poll_option_id'] . " WHERE poll_id = " . $data['poll_id'] . " AND user_id = " . $data['user_id'];
				$update = $this->db->query($query);
			} else {
				$query = "INSERT INTO " . $this->voteTbl . " (poll_id,poll_option_id,user_id) VALUES (" . $data['poll_id'] . "," . $data['poll_option_id'] . "," . $data['user_id'] . ")";
				$insert = $this->db->query($query);
			}
			return true;
		}
	}


	/*
     * Получаем результаты опроса
     * @param ID опроса
     */
	public function getResult($pollID)
	{
		$resultData = array();
		if (!empty($pollID)) {
			$sql = "SELECT * FROM `nl2_poll_options` JOIN `nl2_poll_votes` ON nl2_poll_votes.poll_option_id = nl2_poll_options.id WHERE nl2_poll_options.poll_id = " . $pollID;
			$pollResult = $this->getQuery($sql, 'all');

			if (!empty($pollResult)) {

				foreach ($pollResult as $value) {
					$resultData[$value['poll_option_id']]++;
				}
			}
		}
		return !empty($resultData) ? $resultData : false;
	}
}
