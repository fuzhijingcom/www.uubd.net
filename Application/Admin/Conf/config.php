<?php
$menu = require_once 'menu.inc.php';
# 后台程序名称
defined('SystemName')			or define("SystemName", "无人试衣间(后台)");
defined('PoweredCorpName')		or define("PoweredCorpName", "无人试衣间");
defined('PoweredCorpID')		    or define("PoweredCorpID", "lse");

/*
 * 设定：一些特定的SESSION
 */
defined("SESSION") 				or define("SESSION", "lse~master");
defined("SESSION_ID") 			or define("SESSION_ID", SESSION."_id");
defined("SESSION_HASH") 		    or define("SESSION_HASH", SESSION."_hash");
defined("SESSION_PASS") 		    or define("SESSION_PASS", SESSION."_pass");
defined("SESSION_KEYFIELD") 	    or define("SESSION_KEYFIELD", SESSION."_keyField");
defined("SESSION_SUPERMASTER") 	or define("SESSION_SUPERMASTER", SESSION."_superMaster");
defined("SESSION_AUTH") 		    or define("SESSION_AUTH", "my#master@lse");
defined("SESSION_TOKEN") 		or define("SESSION_TOKEN", "my#master@lse@Token");

$config = array(
	'EXPIRE_TIME'   => 86400,
);

return array_merge($menu,$config);