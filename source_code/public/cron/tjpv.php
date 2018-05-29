<?php
include 'common.php';
/**
 *update pv
 */
$cache = Cache_Factory::getCache();
$pv  = intval($cache->get('game_pv'));
if (Game_Service_Stat::incrementPv($pv)) {
		$cache->delete('game_pv');
}

//update uv
$uv  = intval($cache->get('game_uv'));
if (Game_Service_Stat::incrementUv($uv)) {
	$cache->delete('game_uv');
}

echo CRON_SUCCESS;