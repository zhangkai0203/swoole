<?php
class server{

    private $server;

    public function __construct($host = '0.0.0.0',$port = 9502){

        $this->server = new swoole_websocket_server($host,$port);
        $this->server->set(array(
            'worker_num' => 8,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'debug_mode' => 1
        ));

        $this->server->on('open',array($this,'onOpen'));
        $this->server->on('message',array($this,'onMessage'));
        $this->server->on('close',array($this,'onClose'));

        $this->server->start();
    }

    public function onOpen($server,$req){

        //存储链接ID
        global $map;
        $map['fd'] = $req->fd;
        $server->push($req->fd,json_encode(['code'=>0,'fd'=>$req->fd,'msg'=>'欢迎光临']));

    }


    //发送给所有人
    public function onMessage($server,$frame){

        $msg = json_decode($frame->data,true);

        foreach ($server->connections as $fd) {
            $data = array(
                'fd' => $msg['fd'],
                'msg' => $msg['msg']
            );

            $server->push($fd,json_encode($data));
        }

        //$server->push($frame->fd,$frame->data);
    }

    public function onClose($server,$fd){
         //global $map;
         //unset($map['fd'][$fd]);
        foreach ($server->connections as $fr) {
            $data = array( 'fd' => $fd, 'msg' => '掉线');
            $server->push($fr,json_encode($data));
        }
    }
}

new server();