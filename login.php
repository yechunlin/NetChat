<?php
$redis = new Redis;
$redis->connect('127.0.0.1', 6379);
$timeKey = time().rand(1,999);
$secret = md5(rand(1000,9999).date('is'));
$redis->setex($timeKey, 1800, $secret);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge"> 
	<title>IM</title>
	<meta name="description" content="登录注册" />
	<meta name="author" content="Yechunlin" />
	<link rel="shortcut icon" href="../home/public/images/favicon.ico">
	<!-- <link href='http://fonts.googleapis.com/css?family=Raleway:200,400,800' rel='stylesheet' type='text/css'> -->
	<!--[if IE]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
<style>

            button,input{
                outline: none;
            }
            .wrapper {
                width: 100%;
                height: 100%;
                overflow: hidden;
                background-color: #969696;
            }
            .main {
                padding: 40px 0px;
                width: 100%;
                height: 100%;
                position: absolute;
                background-color: #969696;
                opacity: 0.5;
            }
            
            .form {
                width: 340px;
                margin: 0 auto;
                background-color: #FFFFFF;
                box-shadow: 0 0 15px 0 rgb(220, 222, 230);
                border-radius: 5px;
            }
            
            .main .form input {
                margin: 10px 0;
                width: 200px;
                height: 35px;
                border-radius: 3px;
                display: inline-block;    
                border: 1px solid #000;
                padding-left: 10px;
            }
            
    
            .top{
                height: 30px;
                text-align: center;
                position: relative;
            }
            .top .login1 {
                line-height: 30px;
                letter-spacing: 10px;
                float: left;
                width: 50%;
                border-right: 1px solid rgba(165, 161, 161, 0.1);
                border-bottom: 1px solid rgba(165, 161, 161, 0.1);
            }
            .top .registe{
                line-height: 30px;
                letter-spacing: 10px;
                float: left;
               width: 49%;
                border-right: 1px solid rgba(165, 161, 161, 0.1);
                border-bottom: 1px solid rgba(165, 161, 161, 0.1);                
            }
            .top .close{
                width: 20px;
                height: 20px;
                position: absolute;
                top: 5px;
                   right: 7px;
                   font-size: 20px;
                   
            }
            .clear{
                clear: both;
            }
            .body label{
                text-align: right;
                display: inline-block;
                width: 100px;
                height: 35px;
            }
            .btn2{
                display: flex;
                justify-content: space-around;
            }
            .main .form .btn2 input{
                width: 30%;
                background-color: lightskyblue;
                border-radius: 5px;
                line-height: 30px;
                letter-spacing: 10px;
                cursor:pointer;
                text-align: center;
            }
			.showPoint{
				background:#929292
			}
			.sendVerify{
				cursor: pointer;
			}
	</style>
</head>
<body>

<div class="wrapper mark" style="display: block;">        
                <div class="main">
                    <div class="form">
                        <div class="top">
                            <div class="login1 showPoint">
                                登录
                            </div>
                            <div class="registe">
                                注册
                            </div>
                        </div>
                        <div class="body">
                            <div class="body_login" style="display: block;">
                                <div class="userName clear">
                                    <label>邮箱：</label>
									<input type="text" id="email" placeholder="邮箱">
                                </div>
                                <div class="password1">
                                    <label>密码：</label>
									<input type="password" id="pwd" placeholder="密码">
                                </div>
                                <div class="btn2">
                                    <input type="button" class="sb1" value="登录"/>
                                    <input type="reset" value="重置"  />
                                </div>
                            </div>
                            <div class="body_registe" style="display: none;">
                                <div class="clear">
									<label>昵称：</label>
									<input name="nickname" type="text" class="nickname" placeholder="请输入昵称" />
                                    <label>邮箱：</label>
									<input name="email" type="text"  class="email" placeholder="请输入邮箱" />
                                    <label>密码：</label>
									<input name="password2" type="text" class="password2" placeholder="请输入密码" />
                                    <label>验证码：</label>
									<input name="verify" style="width:100px" class="verify" type="text" />
									<span class="sendVerify">发送验证码</span>
                                </div>
                                <div class="btn2">
                                    <input type="button" class="sb2" value="提交"  />
                                    <input type="reset" value="重置" />
                                </div>
                            </div>
                        </div>

                            
                    </div>
                </div>
        </div>

</body>
</html>
<script src="./public/js/jquery.min.js"></script>
<script type="text/javascript">
$('.top .login1').click(function(){
	$('.top div').removeClass('showPoint');
	$(this).addClass('showPoint');
	$('.body_login').show();
	$('.body_registe').hide();
});
$('.top .registe').click(function(){
	$('.top div').removeClass('showPoint');
	$(this).addClass('showPoint');
	$('.body_login').hide();
	$('.body_registe').show();
});
$('.sendVerify').click(function(){
	var email = $('.email').val();
	if(email == ""){
		alert('邮箱错误')
		return false;
	}
	if(!(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/.test(email))){
		alert('邮箱格式有误')
        return false;
    }
	$.post('./api/sendVerify.php',{'email':email, 'secret':"<?php echo $secret;?>", 'timestamp':"<?php echo $timeKey;?>"},function(res){
		if(Number(res.code)){
			alert('验证码已发送,请到邮箱查看')
			return false;
		}
		alert('发送失败')
	},'json');
})

$('.sb1').click(function(){
	var email = $('#email').val();
	var pwd = $('#pwd').val();

	if(email=="" || pwd==""){
		alert('请输入完整信息')
		return;
	}

	$.post('./api/login.php',{'password':pwd, 'email':email, 'secret':"<?php echo $secret;?>", 'timestamp':"<?php echo $timeKey;?>"},function(res){
		console.log(res)
		if(Number(res.code)){
			window.location.href="index.php?signature="+res.data.signature;
			return;
		}
		alert('登录失败')
	},'json');
})

$('.sb2').click(function(){
	var nickname = $('.nickname').val();
	var email = $('.email').val();
	var pwd = $('.password2').val();
	var verify = $('.verify').val();

	if(nickname=="" || pwd==""){
		alert('请输入完整信息')
		return;
	}

	$.post('./api/regist.php',{'nickname':nickname, 'password':pwd, 'email':email,'code':verify,'secret':"<?php echo $secret;?>", 'timestamp':"<?php echo $timeKey;?>"},function(res){
		if(Number(res.code)){
			window.location.href="index.php?signature="+res.data.signature;
			return;
		}
		alert('注册失败')
	},'json');
})

</script>
