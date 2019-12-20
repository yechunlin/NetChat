<?php
class WebSocketServer
{
	public  $ws;
	static  $ssl = true;
	private $connectConf = array('ip'=>'172.21.0.16', 'port'=> 8888);

	public function __construct($conf = array()){
		$conf = array_merge($this->connectConf, $conf);
		$tcp = SWOOLE_SOCK_TCP;
		if($this::$ssl){
			$tcp = SWOOLE_SOCK_TCP | SWOOLE_SSL;
		}
		$this->ws = new swoole_websocket_server($conf['ip'],$conf['port'],SWOOLE_PROCESS,$tcp);

		$this->ws->table = $this->setTable();//设置内置表
		$this->ws->redis = $this->setRedis();//设置Redis
		//配置
		$setConf = array(
                                'task_worker_num' => 4,
                                'worker_num'      => 4,
                                'daemonize'       => 0,
                                'heartbeat_check_interval' => 300,
                                'heartbeat_idle_time'      => 600
		);
		if($this::$ssl){
			$setConf['ssl_cert_file'] = '/usr/local/ssl/Nginx/1_www.yechunlin.com_bundle.crt';
			$setConf['ssl_key_file'] = '/usr/local/ssl/Nginx/2_www.yechunlin.com.key';
		}
		$this->ws->set($setConf);		

		$this->ws->on('start', [$this,'onStart']);
		$this->ws->on('open', [$this,'onOpen']);
		$this->ws->on('message', [$this,'onMessage']);
		$this->ws->on('task', [$this,'onTask']);
		$this->ws->on('finish', [$this,'onFinish']);
		$this->ws->on('close', [$this,'onClose']);

		$this->ws->start();
	}

	private function setTable(){
		$table  = new swoole_table(4096);
		$table->column('userid', swoole_table::TYPE_INT);
		$table->column('nickname', swoole_table::TYPE_STRING,64);
		$table->column('img', swoole_table::TYPE_STRING,128);
		$table->create();
		return $table;
	}
	private function setRedis(){
		$redis = new Redis;
		$redis->connect('127.0.0.1', 6379);
		$redis->select(1);
		return $redis;
	}

	public function onStart(){
		//启动触发
		echo "服务启动\r\n";
	}
	public function onOpen($ws, $request){
		echo $request->fd." Joined successfully\r\n";
	}
	public function onMessage($ws, $frame){
		parse_str($frame->data, $data);//解析字符串为数组
		$data['id'] = $frame->fd;
		$task_id = $ws->task($data);//投递task异步任务
	}
	public function onTask($ws, $task_id, $from_id, $data){
		$this->workerFun($ws,$data);
		$ws->finish('ok');
	}
	public function onFinish($ws, $task_id, $data){
		echo $data."\r\n";
	}
	public function onClose($ws, $fd){
		$client = $ws->table->get($fd);
		if($client){
			$data = array(
				'flag'=>'leave',
				'id'  =>$fd,
				'nickname'=>$client['nickname']
			);
			$ws->table->del($fd);
			$this->sendMsg($ws,$ws->table, $data);//通知其他人，某人下线
			echo "client:{$fd} has closed\r\n";
		}
	}
	public function workerFun($ws,$data){
		$redis = $ws->redis;
		$old_clients = $ws->table;//连接池
		//新人
		if($data['flag'] == 'new'){
			$clients = array();
			foreach($ws->table as $key => $val){
				$clients[$key] = $val;
			}
			$old_clients = $clients;
			$clients[$data['id']] = array(
				'userid'   => $data['id'],
				'nickname' => $data['nickname'],
				'img'      => $data['img']
			);
			$ws->table->set($data['id'],$clients[$data['id']]);
			$data['clients'] = $clients;
			$ws->push($data['id'], json_encode($data));//新人进来单独发送一份消息
			unset($data['clients']);
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
			$userid     = $ws->table->get($data['id'],'userid');
			$for_userid = $ws->table->get($data['for_id'],'userid');
			$this->sendMsg($ws,$sl_arr,$data);
			//$this->savemsg($redis, $userid, $for_userid, $data);
		}else{
			//群聊
			if($old_clients){
				$this->sendMsg($ws,$old_clients,$data);
			}
		}
	}

	//发送数据
	public function sendMsg($ws,$clients,$data){
		foreach($clients as $fd => $name){
        		$ws->push($fd, json_encode($data));
		}	
	}

}
new WebSocketServer();
