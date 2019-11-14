#!/bin/sh

#添加本地执行路径
export LD_LIBRARY_PATH=./

while true; do
        #启动一个循环，定时检查进程是否存在
        #server=`ps aux | grep web_server_t.php | grep -v grep`
        server=`lsof -i:8888`
	if [ ! "$server" ]; then
            #如果不存在就重新启动
            echo "web_server_t.php挂了，准备重启"
	    /usr/bin/php /data/www/default/alade/chartim/web_server_t.php
	    sleep 5
        fi
        #每次循环沉睡10s
        sleep 5
done

