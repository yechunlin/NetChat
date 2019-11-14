var content ;
var userlist = $('#user_list');
var ws = null;
var clientName;
var clientImg;
var clientId=0;
var aitasize=0;
var isPrivate=0;
//判断当前浏览器是否支持WebSocket
if ('WebSocket' in window) {
	ws = new WebSocket("ws://49.233.147.39:8888/");
} else {
	alert('当前浏览器不支持WebSocket')
}

//连接成功
ws.onopen=function(){
	var params = GetRequest();
	$.post('./api/index.php',params,function(data){
		if(parseInt(data.code)){
			clientName = data.nickname;
			clientImg = data.img;
			$('#login_user_img').css('background-image','url('+clientImg+')');
			$('#login_user').text(clientName);
			ws.send('flag=new&nickname='+clientName+'&img='+clientImg);
		}	
	},'json');
}

//消息触发回调
ws.onmessage=function(msg){
	eval('var data='+msg.data);
	if(parseInt(data.private)){
		if(data.id == clientId){
			content = $('#content_'+data.for_id);
			var red_notice = $('#red_'+data.for_id);
			var ct = document.getElementById('content_'+data.for_id);
		}else{
			if($('#content_'+data.id).length==0){
				$('#ct_top').after('<div id="content_'+data.id+'" class="content" style="display:none"></div>');
				$('#'+data.id).addClass('point_bg');
				$('#'+data.id).append('<span class="red_cicle" id="red_'+data.id+'"></span>');
				$('#'+data.id).append('<span class="show_sl">charting</span>');
			}
			content = $('#content_'+data.id);
			var red_notice = $('#red_'+data.id);
			var ct = document.getElementById('content_'+data.id);
		}
	}else{
		content = $('#content_0');
		var red_notice = $('#red_0');
		var ct = document.getElementById('content_0');
	}
	if(content.css('display') == 'none'){
		var num = red_notice.text() == "" ? 0 : red_notice.text();
		num++;
		if(num > 99){num = 99;}
		red_notice.text(num);
		red_notice.show();
	}
	//静默五分钟显示一次消息时间
	if(data.date){
		content.append(addSystem(data.date));
	}
	//@提醒
	if(data.at){
		if($.inArray(clientId.toString(), data.at) >= 0){
			var size = data.at.length;
			aitasize = aitasize + size;
			if(aitasize>1){
				var fromname = aitasize+'人';
			}else{
				var fromname = $('#'+data.id+' .username').text();
			}
			var msgnotice = $('#msgnotice');
			msgnotice.find('span').remove();//防止标签重复
			msgnotice.append('<span>'+fromname+'提醒到了你</span><span class="rm">x</span>');
			msgnotice.show();
		}
	}
	
	if(data.flag == 'new'){
		//新人加入
		content.append(addSystem(data.nickname+'加入群聊'));
		if(data.clients){
			for(var i in data.clients){
				userlist.append(addClients(i,data.clients[i]['img'],data.clients[i]['nickname']));
			}
		}else{
			userlist.append(addClients(data.id,data.img,data.nickname));
		}
		if(clientId == 0){
			clientId = data.id;//纪录当前用户id
		}
	}else if(data.flag == 'normal'){
		//文字消息
		if(clientId == data.id){
			content.append(addNormal(data,'_right'));
		}else{
			content.append(addNormal(data));
		}
	}else if(data.flag == 'pic'){
		//发送图片
		if(clientId == data.id){
			content.append(addPic(data,'_right'));
		}else{
			content.append(addPic(data));	
		}
		
	}else if(data.flag == 'leave'){
		//离开
		content.append(addSystem(data.name+'离开群聊'));
		$('#'+data.id).remove();
	}
	//滚动条事件，只能原生写，真是服
	ct.scrollTop = ct.scrollHeight;
}

//client列表
function addClients(id,img,name){
	return '<div class="userlist" id="'+id+'"><img src="'+img+'" class="userimg"><div class="username">'+name+'</div></div>';
}

//normal消息回复
function addNormal(data,loc=''){
	var show_name = '<div class="show_name">'+data.nickname+'</div>';
	if(parseInt(isPrivate) || loc!=''){
		show_name = '';
	}
	return '<div class="send_msg_box"><div class="send_msg_img'+loc+'" style="background-image:url('+data.img+')"></div>'+show_name+'<div class="send_msg'+loc+'">'+data.msg+'</div></div>';
}

//pic消息回复
function addPic(data,loc=''){
	var show_name = '<div class="show_name">'+data.nickname+'</div>';
	if(parseInt(isPrivate) || loc!=''){
		show_name = '';
	}
	return '<div class="send_msg_box"><div class="send_msg_img'+loc+'" style="background-image:url('+data.img+')"></div>'+show_name+'<img src="'+data.msg+'" style="width:'+data.w+'px;height:'+data.h+'px;background:none" class="send_msg'+loc+'"></div>';
}

//系统消息
function addSystem(systemmsg){
	return '<div class="send_msg_box"><div class="system_msg">'+systemmsg+'</div></div>';
}

//获取url参数
function GetRequest() {
   var url = decodeURI(location.search); //获取url中"?"符后的字串,支持汉字url解码
   var theRequest = new Object();
   if (url.indexOf("?") != -1) {
	  var str = url.substr(1);
	  strs = str.split("&");
	  for(var i = 0; i < strs.length; i ++) {
		theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);
	  }
   }
   return theRequest;
}

$('#sb').click(function(){
	var msg = $('#input_box');
	if(msg.html() == ""){
		alert('写点什么');
		return false;
	}
	var message = 'flag=normal&msg='+msg.html();
	if(isPrivate > 0){
		message = message+'&private=1&for_id='+isPrivate;
	}
	ws.send(message);
	$('#input_box').html('');
})

$("#input_box").bind("keydown",function(event){
	var keycode = event.which;
	var inputTxt = $(this);

	// 回车-->发送消息
	if (keycode == 13 && !(event.ctrlKey)) {
		$('#sb').click();
		event.preventDefault();//阻止默认事件
		return false;
	}
	// ctrl+回车-->换行 (失效)
	if (event.ctrlKey && keycode == 13) {
		//$("#input_box").html($("#input_box").html()+'<div><br></div>');
		//return false;
	}
});

//选取图片
$('#sendimg').on('change',function(e){
	var file = e.target.files[0];
	if(!/image\/\w+/.test(file.type)) {
           alert("暂时只支持图片类型");
           return false;
    }
	var obj = new FileReader();
	obj.readAsDataURL(file);
	obj.onload=function(f){
		var image = new Image();
		image.src = f.target.result;
		image.onload = function(){
			var iw = this.width;
			var ih = this.height;
			if(iw > 100){
				ih = (100*ih)/iw;
				ih = ih.toFixed(2);
				iw = 100;
			}
			var message = 'flag=pic&msg='+f.target.result+'&w='+iw+'&h='+ih;
			if(isPrivate > 0){
				message = message+'&private=1&for_id='+isPrivate;
			}
			ws.send(message);
		}

	}
	
})

$('#ct_file .file_img').click(function(event){
	var index = $(this).index();
	if(index == 1){
		event.stopPropagation();
		var emoji = $('#emoji_box');
		var state = emoji.css('display');
		if(state == 'none'){
			for(var i=0;i<61;i++){
				emoji.append('<img src="./public/images/emoji/face/'+i+'.gif" id="gif_'+i+'">');
			}
			emoji.show();
		}else{
			emoji.hide();
			emoji.find('img').remove();
		}
	}else if(index == 3 || index == 4 || index == 5){
		alert('莫点，暂不支持！');
		return false;
	}
	

})
//emoji表情
//$('#ct_file .file_img').eq(0).click(function(event){
	//event.stopPropagation();

//})

//隐藏emoji
$(document).click(function(){
	var emoji = $('#emoji_box');
		emoji.hide();
		emoji.find('img').remove();
})

//绑定未来事件，得放在$(function(){}里
$(function(){
	//发送表情
	$('#emoji_box').on('click','img',function(event){
		event.stopPropagation();
		var src = $(this).attr('src');
        $('#input_box').append('<img src="'+src+'">');
	})
	//绑定未来右键事件
	$('#user_list').on('contextmenu','.userlist',function(){
		return false;
	})

	$(".mouseright").contextmenu(function(e){
		return false;
	})

	//左键点击消失
	$(document).click(function(){
		$(".mouseright").hide();
	})

	//区域监听鼠标右键
	$('#user_list').on('mousedown','.userlist',function(e){
		if(e.which == 3){
			if($(this).attr('id') != clientId){
				var x = e.clientX;
				var y = e.clientY;
				var this_obj = $($(".mouseright")[0]);
				this_obj.show().css({left:x,top:y});
				this_obj.find('.mouselist').attr('data-index',$(this).attr('id'));
			}
		}
	})

	//@用户
	$('.mouselist').click(function(){
		var index = $(this).index();
		var id = $(this).attr('data-index');
		var to_from = $('#'+id+' .username').text();
		if(!parseInt(index)){
			$('#input_box').append('<input type="button" value="@'+to_from+'" class="zhaohuan" data-to="'+id+'">');
		}else if(parseInt(index)){
			$('#ct_top').text(to_from);
			content.hide();
			if($('#content_'+id).length==0){
				$('#ct_top').after('<div id="content_'+id+'" class="content"></div>');
				$('#'+id).append('<span class="red_cicle" id="red_"'+id+'></span>');
				$('#'+id).append('<span class="show_sl">charting</span>');
			}
			$('.userlist').removeClass('point_bg');
			$('#'+id).addClass('point_bg');
			isPrivate=id;
		}

	})

	$('#user_list').on('click','.userlist',function(){
		var id = $(this).attr('id');
		if($('#content_'+id).length>0){
			$('#ct_top').text($(this).find('.username').text());
			$('.content').hide();
			$('#content_'+id).show();
			$('.userlist').removeClass('point_bg');
			$(this).addClass('point_bg');
			isPrivate=id;
			$('#red_'+id).hide();
			$('#red_'+id).text('0');
		}
	})

	//去掉@提醒
	$('#msgnotice').on('click','.rm',function(){
		aitasize=0;
		$('#msgnotice span').remove();
		$('#msgnotice').hide();
	})
})


//返回群聊界面
$('#qunliao').click(function(){
	$('#ct_top').text('群聊');
	content = $('#content_0');
	$('.content').hide();
	content.show();
	$('#red_0').hide();
	$('#red_0').text('0');
	isPrivate=0;
	$('.userlist').removeClass('point_bg');
})

//输入框背景颜色改变
$("#input_box").focus(function(){
  $("#input_box").css("background","#FFf");
  $("#sb_box").css("background","#FFF");
});
$("#input_box").blur(function(){
  $("#input_box").css("background","#f5f5f5");
  $("#sb_box").css("background","#f5f5f5");
});

 //开启摄像头 要求https
 /*
	var video = document.getElementById('video'),
        canvas = document.getElementById('canvas'),
        snap = document.getElementById('tack'),
        img = document.getElementById('video_img'),
        vendorUrl = window.URL || window.webkitURL;
        
    //媒体对象
    navigator.getMedia = navigator.getUserMedia || navagator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
    navigator.getMedia({
        video: true, //使用摄像头对象
        audio: false  //不适用音频
    }, function(strem){
        console.log(strem);
        video.src = vendorUrl.createObjectURL(strem);
        video.play();
    }, function(error) {
        //error.code
        console.log(error);
    });
    snap.addEventListener('click', function(){
    
        //绘制canvas图形
        canvas.getContext('2d').drawImage(video, 0, 0, 400, 300);
        
        //把canvas图像转为img图片
        img.src = canvas.toDataURL("image/png");
        
    })*/
