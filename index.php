<?php
$token = isset($_GET['signature']) ? trim($_GET['signature']) : '';
$redis = new Redis;
$redis->connect('127.0.0.1', 6379);
$user = $redis->get(base64_decode($token));
if(!$user){
	header('Location: https://www.yechunlin.com/NetChat/login.php');
}
$user = unserialize($user);
?>
<html>
<head>
<meta charset="utf-8"/>
<link rel="stylesheet" type="text/css" href="./public/css/client.css">
<title>NetChat</title>
<script type="text/javascript">
var userId = "<?php echo $user['id'];?>";
var clientName = "<?php echo $user['nickname'];?>";
</script>
</head>
<body>
<div id="wrap">
    <!-- 个人信息 -->
	<div id="login">
	    <div id="login_user_img"></div>
		<div id="login_user"></div>
		<div style="width:100%;height:20px;color:#fff;position:relative;">
			<span id="qunliao">广场</span>
			<span class="red_cicle" id="red_0"></span>
		</div>
	</div>
     <!--用户列表 -->
	<div id="user_list">

	</div>

	<!--聊天框 -->
	<div id="content_box">
		<div id="ct_top">广场</div>
		<div id="content_0" class="content">
		</div>
		<div id="ct_file">
			<div id="emoji_box"></div>
			<span class="file_img" title="表情"></span>
			<span class="file_img" title="文件">
				<input type="file" name="sendFile" id="sendFile">
			</span>
			<span class="file_img" title="聊天记录"></span>
			<span class="file_img" title="截图"></span>
			<span class="file_img" title="视频"></span>
			<div id="msgnotice"></div>
		</div>
		<div contentEditable="true" id="input_box"></div>
		<!-- <textarea id="input_box"></textarea> -->
		<div id="sb_box">
			<input type="button" value="发送" id="sb">
		</div>
	</div>
</div>
<div class="mouseright">
	<div class="mouselist" data-index="0">@召唤他</div>
	<div class="mouselist" data-index="0">发送消息</div>
</div>
</body>
</html>
<script type="text/javascript" src="./public/js/jquery.min.js"></script>
<script type="text/javascript" src="./public/js/js-upload.js"></script>
<script type="text/javascript" src="./public/js/common.js"></script>
<script type="text/javascript" src="./public/js/client.js"></script>


