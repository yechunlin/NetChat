<?php
include_once('../../global/global_netchat.php');
$nickname = isset($_POST['nickname']) || !empty($_POST['nickname']) ? trim($_POST['nickname']) : 'Chat'.rand(100,999);
$ip = getIp();
$ms = new Mysql;
$data = array(
	'ip' => $ip,
	'nickname' => $nickname,
	'dated' => date('Y-m-d H:i:s')
);	
$res = $ms->insert('clients', $data);
$returnData = array(
	'code' => 0,
	'nickname' => '',
	'img' => ''
);
if($res){
	$returnData['code'] = 1;
	$returnData['nickname'] = $nickname;
	$returnData['img'] = './public/images/heads/default.jpg';
}
die(json_encode($returnData));
