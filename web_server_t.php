<?php
$ws = new swoole_websocket_server('172.21.0.16',8888,SWOOLE_PROCESS, SWOOLE_SOCK_TCP);

//启用swoole内置表
$table  = new swoole_table(4096);
$table->column('userid', swoole_table::TYPE_INT);
$table->column('nickname', swoole_table::TYPE_STRING,64);
$table->column('img', swoole_table::TYPE_STRING,128);
$table->create();

$ws->table = $table;
$redis = new Redis;
$redis->connect('127.0.0.1', 6379);
$redis->select(1);

//配置
$ws->set(array(
		'task_worker_num' => 4,
		'worker_num' => 4,
		'daemonize'=>1
	)
);

//启动触发
$ws->on('start',function(){
	echo "服务启动\r\n";
});

//客户端连接成功后触发
$ws->on('open', function($ws, $request){
	echo $request->fd." have connected!\r\n";
	/*$ws->table->set($request->fd,array(
		'name'=>'ycl',
		'img' =>'aydfudgsydsgdfsghs'
	));
	echo count($ws->table)."\r\n";*/
});

//task异步任务处理
$ws->on('task',function($ws, $task_id, $from_id, $data){
    handleData($ws,$data);
	$ws->finish('ok');
});

//task处理结束回调
$ws->on('finish',function($ws, $task_id, $data){
	echo $data."\r\n";
});

//接收请求回调
$ws->on('message', function($ws, $frame){
    parse_str($frame->data, $data);
	$data['id'] = $frame->fd;
	$task_id = $ws->task($data);//投递task异步任务
});

//关闭连接
$ws->on('close', function($ws, $fd){
	$client = $ws->table->get($fd);
	if($client){
		$data = array(
			'flag'=>'leave',
			'id'  =>$fd,
			'nickname'=>$client['nickname']
		);
		$ws->table->del($fd);
		sendmsg($ws,$ws->table, $data);//通知其他人，某人下线
		echo "client:{$fd} has closed\r\n";
	}
});

//启动
$ws->start();

//处理消息
function handleData($ws, $data){
	global $redis;
	$old_clients = $ws->table;//连接池

	//新人
    if($data['flag'] == 'new'){
		$clients = array();
		foreach($ws->table as $key => $val){
			$clients[$key] = $val;
		}
		$old_clients = $clients;
		$clients[$data['id']] = array(
			'userid'=>$data['id'],
			'nickname'=>$data['nickname'],
			'img'=>$data['img']
		);
		$ws->table->set($data['id'],$clients[$data['id']]);
		$data['clients'] = $clients;
		$ws->push($data['id'], json_encode($data));//新人进来单独发送一份消息
		unset($data['clients']);
	}

	//接收图片消息
	if($data['flag'] == 'pic'){
		//图片二进制流内的'+'会被转为空格，整理处理一下
		$data['msg'] = preg_replace('/ /', '+', $data['msg']);
	}

	//@召唤
	if(isset($data['msg'])){
		preg_match_all('/data-to="(.*?)"/', $data['msg'], $arr_to);
		if(!empty($arr_to[1])){
			$data['at'] = $arr_to[1];
			$data['msg'] .= "&nbsp";
		}
	}
	
	$data['img'] = $ws->table->get($data['id'],'img');
	$data['nickname'] = $ws->table->get($data['id'],'nickname');

	//连续五分钟内无交互，显示一次时间
	if(!$redis->get('web_socket_time')){
		$redis->set('web_socket_time', 1, 300);
		$data['date'] = date('Y-m-d H:i:s');
	}
	$redis->set('web_socket_time', 1, 300);

	if(isset($data['private'])){
		//私聊
		$sl_arr[$data['id']] = $data['id'];
		$sl_arr[$data['for_id']] = $data['for_id'];
		$userid = $ws->table->get($data['id'],'userid');
		$for_userid = $ws->table->get($data['for_id'],'userid');
		sendmsg($ws,$sl_arr,$data);
		//savemsg($redis, $userid, $for_userid, $data);
	}else{
		//群聊
		if($old_clients){
			sendmsg($ws,$old_clients,$data);
		}
	}
}

//发送消息
function sendmsg($ws,$clients,$data){
	foreach($clients as $fd => $name){
        	$ws->push($fd, json_encode($data));
	}
}
//消息记录
function savemsg($redis, $userid, $for_userid, $data){
	$key = $userid + $for_userid;
	$redis->lPush('chart_'.$key, serialize(array('userid'=>$userid,'time'=>time(),'content'=>$data['msg'])));
	$redis->expire($key, '3600');
}

