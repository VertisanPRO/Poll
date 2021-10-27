<?php
/*
 *  Poll By xGIGABAITx
 */

// Initialise Poll language
$poll_language = new Language(ROOT_PATH . '/modules/Poll/language', LANGUAGE);

// Initialise module
require_once(ROOT_PATH . '/modules/Poll/module.php');
$module = new Poll_Module($language, $poll_language, $pages, $queries, $navigation, $cache, $endpoints);
