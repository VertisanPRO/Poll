<?php
class Poll_Module extends Module
{
	private $_language;
	private $_poll_language;

	public function __construct($language, $poll_language, $pages, $queries, $navigation, $cache, $endpoints)
	{
		$this->_language = $language;
		$this->_poll_language = $poll_language;

		$name = 'Poll';
		$author = '<a href="https://tensa.co.ua" target="_blank" rel="nofollow noopener">xGIGABAITx</a>';
		$module_version = '1.0.4';
		$nameless_version = '2.0.0-pr10';

		parent::__construct($this, $name, $author, $module_version, $nameless_version);


		$pages->add('Poll', '/panel/poll', 'pages/panel/poll.php');
		$pages->add('Poll', '/panel/poll/edit', 'pages/panel/edit.php');
		$pages->add('Poll', '/poll/view', 'pages/view.php', $name, true);
	}

	public function onInstall()
	{
		// Initialise
		$this->initialise();
	}

	public function onUninstall()
	{
		// Not necessary
	}

	public function onEnable()
	{
		// Check if we need to initialise again
		$this->initialise();
	}

	public function onDisable()
	{
		// Not necessary
	}

	public function onPageLoad($user, $pages, $cache, $smarty, $navs, $widgets, $template)
	{


		require_once(ROOT_PATH . '/modules/Poll/widgets/poll_widget.php');
		$module_pages = $widgets->getPages('PollWG');
		$poll_language = $this->_poll_language;
		$PollWG = new PollWG($module_pages, $smarty, $user, $poll_language);
		$widgets->add($PollWG);


		if (defined('BACK_END')) {
			// Navigation
			$cache->setCache('panel_sidebar');

			PermissionHandler::registerPermissions('Poll', array(
				'poll.manage' => $this->_poll_language->get('general', 'poll_manage_perm'),
				'poll.view' => $this->_poll_language->get('general', 'poll_view_perm'),
			));

			$navs[2]->add('poll_divider', mb_strtoupper($this->_poll_language->get('general', 'poll_module'), 'UTF-8'), 'divider', 'top', null, '', '');
			$order = $navs[2]->returnNav()['poll_divider']['order'];


			if ($user->hasPermission('poll.manage')) {

				$poll_icon = '<i class="nav-icon fas fa-poll-h"></i>';

				$navs[2]->add('poll_configuration', $this->_poll_language->get('general', 'poll_module'), URL::build('/panel/poll'), 'top', null, $order + 0.1, $poll_icon);
			}
		}
	}

	private function initialise()
	{

		$queries = new Queries();

		try {
			// Update main admin group permissions
			$group = $queries->getWhere('groups', array('id', '=', 2));
			$group = $group[0];

			$group_permissions = json_decode($group->permissions, TRUE);
			$group_permissions['poll.manage'] = 1;
			$group_permissions['poll.view'] = 1;

			$group_permissions = json_encode($group_permissions);
			$queries->update('groups', 2, array('permissions' => $group_permissions));
		} catch (Exception $e) {
			// Error
		}

		try {
			$engine = Config::get('mysql/engine');
			$charset = Config::get('mysql/charset');
			$prefix = Config::get('mysql/prefix');
		} catch (Exception $e) {
			$engine = 'InnoDB';
			$charset = 'utf8mb4';
			$prefix = 'nl2_';
		}

		if (!$engine || is_array($engine))
			$engine = 'InnoDB';

		if (!$charset || is_array($charset))
			$charset = 'latin1';

		if (!$prefix || is_array($prefix))
			$prefix = 'nl2_';



		if (!$queries->tableExists('polls')) {
			try {
				$queries->createTable(
					'polls',
					' 
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
					`status` int(11) NOT NULL DEFAULT 1,
					`view` int(11) DEFAULT NULL,
				PRIMARY KEY (`id`)',
					"ENGINE=$engine DEFAULT CHARSET=$charset"
				);
			} catch (Exception $e) {
			}
		}

		if (!$queries->tableExists('poll_options')) {
			try {
				$queries->createTable(
					'poll_options',
					' 
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`poll_id` int(11) NOT NULL,
					`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`id`),
				KEY `poll_id` (`poll_id`),
        CONSTRAINT `' . $prefix . 'poll_options_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `' . $prefix . 'polls` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION',
					"ENGINE=$engine DEFAULT CHARSET=$charset"
				);
			} catch (Exception $e) {
			}
		}


		if (!$queries->tableExists('poll_votes')) {
			try {
				$queries->createTable(
					'poll_votes',
					' 
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`poll_id` int(11) NOT NULL,
					`poll_option_id` int(11) NOT NULL,
					`user_id` int(11) DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `poll_id` (`poll_id`),
 				KEY `poll_option_id` (`poll_option_id`),
 				CONSTRAINT `' . $prefix . 'poll_votes_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `' . $prefix . 'polls` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
 				CONSTRAINT `' . $prefix . 'poll_votes_ibfk_2` FOREIGN KEY (`poll_option_id`) REFERENCES `' . $prefix . 'poll_options` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION',
					"ENGINE=$engine DEFAULT CHARSET=$charset"
				);
			} catch (Exception $e) {
			}
		}
	}
}
