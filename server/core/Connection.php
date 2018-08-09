<?php
namespace core;
use chess\player;
/**
 * WebSocket Connection class
 * @author luyue <544625106@qq.com>
 */
class Connection
{
    /**
     * @var Class Main server class
     */
    private $server;

    /**
     * @var Socket Client socket
     */
    private $socket;

    /**
     * @var Socket Client socket
     */
    private $room;

    /**
     * @var Boolen If handshake is made
     */
    private $handshaked = false;

    /**
     * @var player after handshake is made , this connection became a player
     */
    private $player = null;

    
    public function __construct($server, $socket, $room){
        $this->server = $server;
        $this->socket = $socket;
        $this->room = $room;
        $this->log('Connecting.....');
    }

    /**
     * get server
     */
    public function getServer(){
        return $this->server;
    }

    /**
     * get socket
     */
    public function getSocket(){
        return $this->socket;
    }

    /**
     * get room object
     */
    public function getRoom(){
        return $this->room;
    }

    private function handshake($data){
        $this->log('start handshake....');
        $lines = preg_split("/\r\n/", $data);
        if (! preg_match('/\AGET (\S+) HTTP\/1.1\z/', $lines[0], $matches)){
            $this->log('Invalid request: ' . $lines[0]);
            socket_close($this->socket);
            return false;
        }
        //$matches => [0=> 'GET / HTTP/1.1', 1=>'/'];
        $path = $matches[1];
        foreach ($lines as $line){
            $line = chop($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)){
                $headers[$matches[1]] = $matches[2];
            }
        }
        $new_key = base64_encode(sha1($headers['Sec-WebSocket-Key'] . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
        $new_message = "HTTP/1.1 101 Switching Protocols\r\n".
                        "Upgrade: websocket\r\n".
                        "Sec-WebSocket-Version: 13\r\n".
                        "Connection: Upgrade\r\n".
                        "Sec-WebSocket-Origin: {$headers['Origin']}\r\n".
                        "Sec-WebSocket-Location: ws://{$headers['Host']}{$path}\r\n".
                        "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
        $this->server->send($this->socket, $new_message);
        //['chat':[], 'game':[], 'room':[]]
        $this->log('Handshake sent...');
        return true;
    }


    /**
     * Receive data from Sever layer
     *
     * @param string date 
     */
    public function onData($data){
        if ($this->handshaked) {
            $this->player->domain($this->_decode($data));
        } else {
            if($this->handshake($data)){
                $this->handshaked = true;
                $this->player = new player($this);
            }else{
                $this->handshaked = false;
            }
        }
    }

    /**
     * Handle the received data
     * @param string date
     * @return string
     */
    private function _decode($buffer){
        $len = $masks = $data = $decoded = null;
        $len = ord($buffer[1]) & 127;
        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        } else if ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        } else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }

    /**
     * Handle the send data
     * @param string date
     * @return  string
     */
    private function _frame($buffer){
        $len = strlen($buffer);
        if ($len <= 125) {
            return "\x81" . chr($len) . $buffer;
        } else if ($len <= 65535) {
            return "\x81" . chr(126) . pack("n", $len) . $buffer;
        } else {
            return "\x81" . char(127) . pack("xxxxN", $len) . $buffer;
        }
    }

    /**
     * Send data to client
     *
     * @param string date
     */
    public function send($data){
        $data = $this->_frame($data);
        if (! @socket_write($this->socket, $data, strlen($data))) {
            @socket_close($this->socket);
            $this->socket = false;
        }
    }

    /**
     * Disconnect the connection
     */
    public function onDisconnect(){
        //用户信息  棋局信息 房间信息
        $oppSocket = $this->room->remove($this->player);
        if($oppSocket !== null){
            $oppSocket->send(json_encode(['error'=>['disconnect'=>['msg'=>'your opponent was disconnected!']]]));
        }
        $this->server->send($this->server->clients, json_encode(['room'=>['all'=>$this->room->getRoomInfo()]]));
        //通知所有用户房间  通知对手掉线
        $this->log('Disconnected', 'info');
        socket_close($this->socket);
    }

    /**
     * Log a message
     *
     * @param string $message The message
     * @param string $type The type of the message
     */
    public function log($message, $type = 'info'){
        socket_getpeername($this->socket, $addr, $port);
        $this->server->log('[client ' . $addr . ':' . $port . '] ' . $message, $type);
    }

    /**
     * player reconnect to the server
     * socket fp resource
     */
    public function reConnect($resource){
        $this->socket = $resource;
        $this->handshaked = false;
    }
}