<?php
include_once('../../global/global_netchat.php');
$nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : 'NetChat'.rand(100,999);
$ip = getIp();
$ms = new Mysql;
$data = array(
	'ip' => $ip,
	'nickname' => $nickname,
	'dated' => date('Y-m-d H:i:s')
);	
$ms->insert('clients', $data);

function getIp(){
	$ip = '';
	if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
		$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		foreach ($ips as $v) {
			$v = trim($v);
			if(!preg_match('/^(10|172\.16|192\.168)\./', $v)) {
				if (strtolower($v) != 'unknown') {
					$ip = $v;
					break;
				}
			}
		}
	} elseif ($_SERVER['HTTP_CLIENT_IP']) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	if (!preg_match('/[\d\.]{7,15}/', $ip)) {
		$ip = '';
	}
	return $ip;
}