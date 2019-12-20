<?php
class WebSocketServer
{
	public  $ws;
	public  $table;
	public  $redis;
	private $ssl = true;
	private $connectConf = array(
				'ip'      => '172.21.0.16',
				'port'    => 8889
			);

	public function __construct($conf = array()){
		$conf = array_merge($this->connectConf, $conf);
		if($this->ssl){
			$this->ws = new swoole_websocket_server($conf['ip'],$conf['port'],SWOOLE_PROCESS,SWOOLE_SOCK_TCP | SWOOLE_SSL);
		}else{
			$this->ws = new swoole_websocket_server($conf['ip'],$conf['port'],SWOOLE_PROCESS,SWOOLE_SOCK_TCP);
		}
		$this->setTable();//设置内置表
		$this->setRedis();
		//配置
		$this->ws->set(array(
				'task_worker_num' => 4,
				'worker_num'      => 4,
				'daemonize'       => 0,
				'heartbeat_check_interval' => 300,
				'heartbeat_idle_time'      => 600,
				'ssl_cert_file' => '/usr/local/ssl/Nginx/1_www.yechunlin.com_bundle.crt',
				'ssl_key_file'  => '/usr/local/ssl/Nginx/2_www.yechunlin.com.key'
			)
		);

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
		$this->table = $table;
	}
	private function setRedis(){
		$redis = new Redis;
		$redis->connect('127.0.0.1', 6379);
		$redis->select(1);
		$this->redis = $redis;
	}

	public function onStart(){
		//启动触发
		echo "服务启动\r\n";
	}
	public function onOpen($ws, $request){}
	public function onMessage($ws, $frame){}
	public function onTask($ws, $task_id, $from_id, $data){}
	public function onFinish($ws, $task_id, $data){}
	public function onClose($ws, $fd){}

}
new WebSocketServer();