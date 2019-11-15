<?php
$url = 'http://49.233.147.39/NetChat/api/index.php';
echo $res = curl_post($url, $_POST);
die();
function curl_post($url,$data){
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	//curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36');
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,true);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;	
}

?>