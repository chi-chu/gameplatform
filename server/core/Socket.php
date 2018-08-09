<?php
namespace core;
/**
 * Socket class
 * @author luyue <544625106@qq.com>
 */

class Socket
{
    /**
     * @var Socket Holds the master socket
     */
    public $master;

    /**
     * @var array Holds all connected sockets
     */
    public $allsockets = array();

    public function __construct($host, $port, $max){
        ob_implicit_flush(true);
        $this->createSocket($host, $port, $max);
    }

    /**
     * Create a socket on given host/port
     * 
     * @param string $host The host/bind address to use
     * @param int $port The actual port to bind on
     * @param int $max The max num port to listen to
     */
    private function createSocket($host, $port, $max){
        if (($this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0){
            die("socket_create() failed, reason: " . socket_strerror($this->master));
        }
        $this->log("Socket {$this->master} created.");
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
        #socket_set_option($master,SOL_SOCKET,SO_KEEPALIVE,1);
        if (($ret = socket_bind($this->master, $host, $port)) < 0){
            die("socket_bind() failed, reason: " . socket_strerror($ret));
        }
        $this->log("Socket bound to {$host}:{$port}.");
        if (($ret = socket_listen($this->master, $max)) < 0){
            die("socket_listen() failed, reason: " . socket_strerror($ret));
        }
        $this->log('Start listening on Socket.');
        # add master socket to allsockets array
        $this->allsockets[] = $this->master;
    }

    /**
     * Sends a message over the socket
     * @param socket $client The destination socket
     * @param string $msg The message
     */
    public function send($client, $msg){
        if(is_array($client)){
            foreach ($client as $connection) {
                $connection->send($msg);
            }
        }else{
            socket_write($client, $msg, strlen($msg));
        }
    }

    /**
     * Log a message
     *
     * @param string $message The message
     * @param string $type The type of the message
     */
    public function log($message, $type = 'info'){
        echo date('Y-m-d H:i:s') . ' [' .$type. '] ' . $message . PHP_EOL;
    }
}
