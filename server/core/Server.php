<?php
namespace core;
use chess\room;

/**
 * Simple WebSockets server
 * @author luyue <544625106@qq.com>
 */

/**
 * This is the main Server class
 */
class Server extends Socket
{
    /**
     * @var Socket Holds the client Connection Array
     */
    public $clients = array();

    public $room;

    public $chess;

    public function __construct($host = '0.0.0.0', $port = 8000, $max = 100, $roomNumber=50){
        parent::__construct($host, $port, $max);
        $this->room = new room($roomNumber);
        $this->log('Server created...');
    }

    /**
     * Start the server to do polling
     */
    public function run(){
        while (true){
            $changed_sockets = $this->allsockets;
            @socket_select($changed_sockets, $write = NULL, $exceptions = NULL, NULL);
            foreach ($changed_sockets as $socket){
                // Master socket
                if ($socket === $this->master){
                    if (($ressource = socket_accept($this->master)) < 0){
                        $this->log('Socket error: ' . socket_strerror(socket_last_error($ressource)));
                        continue;
                    }else{
                        // Creat an new Connection object for the new socket
                        // and take the socket as the index
                        socket_getpeername($ressource, $addr, $por);
                        $this->clients[$addr.':'.$por] = new Connection($this, $ressource, $this->room);
                        $this->allsockets[] = $ressource;
                    }
                }else{
                    // Client sockets
                    socket_getpeername($socket, $addr, $por);
                    $client = $this->clients[$addr.':'.$por];
                    $bytes = @socket_recv($socket, $data, 4096, 0);
                    if ($bytes === 0){
                        $this->clients[$addr.':'.$por]->onDisconnect();
                        unset($this->clients[$addr.':'.$por]);
                        unset($this->allsockets[array_search($socket, $this->allsockets)]);
                    }else{
                        $this->clients[$addr.':'.$por]->onData($data);
                    }
                }
            }
        }
    }
}
