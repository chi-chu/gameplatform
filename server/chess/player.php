<?php
namespace chess;
/**
 * Description of player
 *
 * @author luyue <544625106@qq.com>
 */
class player {

    /**
     * @var player temporary user name
     */
    private $username = '';

    /**
     * @var player room position 
     */
    private $roomInfo = array();
    
    /**
     * @var total room info
     */
    private $connection;

    /**
     * @var player status
     */
    public $readyStatus = 0;

    /**
     * @var player game status
     * 0 : opponents turn to do 
     * 1 : players turn to do
     */
    private $gameStatus = 0;

    /**
     * @var player game status
     * 0 : black
     * 1 : red
     */
    private $chessColor = null;

    public function  __construct($connection) {
        $this->connection = $connection;
    }

    public function __call($funname, $argv){
        echo 'function data '.$funname.' is not found'."\r\n";
    }
    /**
     * main action to decode player's act
     *
     * @param string message 
     */
    public function domain($buffer){
        $actionInfo = json_decode($buffer, true);
        if(!empty($actionInfo)){
            foreach ($actionInfo as $key => $value) {
                call_user_func_array(array($this, $key), array($value));
            }
        }
    }

    /**
     * say something thing to the chat
     *
     * @param string message 
     */
    public function chat($msg){
        $this->connection->getServer()->send($this->connection->getServer()->clients, json_encode(['chat'=>['name'=>$this->username, 'msg'=>$msg]]));
    }

    /**
     * enter room
     * @param string message 
     */
    public function enterRoom($roomInfo){
        $ret = $this->connection->getRoom()->joinRoom($this, $roomInfo);
        if($ret['ret']){
            $this->roomInfo = $roomInfo;
            $this->getRoomInfo(false);//每次都发全量的房间数据  有待优化。
        }else{
            $this->connection->send(json_encode(['error'=>$ret['msg']]));
        }
    }

    /**
     * set Chesspiece to the position
     * @param array
     */
    public function setChesspiece($position, $toposition){

    }

    /**
     * set userinfo to the position
     * @param array
     */
    public function setname($name){
        $this->username = $name['name'];
    }

    /**
     * player action to get romm info
     */
    public function getRoomInfo($buffer){
        if($buffer === false){
            $this->connection->send(json_encode(['room'=>['change'=>'', 'all'=>$this->connection->getRoom()->getRoomInfo()]]));
        }else{
            $this->connection->send(json_encode(['room'=>['all'=>$this->connection->getRoom()->getRoomInfo()]]));
        }
    }

    /**
     * change game status
     * @param int
     */
    public function changeGameStatus($status){
        $this->gameStatus = $status;
    }


    /**
     * player ready to start game
     */
    public function ready($status){
        $this->readyStatus = intval($status);
        if($status == 1){
            $obj = $this->connection->getRoom()->checkGame($this);
            if($obj !== false){
                $turn = rand(0,1);
                if($turn){
                    $this->changeGameStatus(1);
                    $this->chessColor = 1;
                    $this->connection->send(json_encode(['game'=>['info'=>'your are the red! It`s your turn to put a chessman!', 'begin'=>1]]));
                    $obj->connection->send(json_encode(['game'=>['info'=>'your are the black! Wait for your opponent to put a chessman!', 'begin'=>0]]));
                }else{
                    $obj->changeGameStatus(0);
                    $this->chessColor(1);
                    $this->connection->send(json_encode(['game'=>['info'=>'your are the black! Wait for your opponent to put a chessman!', 'begin'=>0]]));
                    $obj->connection->send(json_encode(['game'=>['info'=>'your are the red! It`s your turn to put a chessman!', 'begin'=>1]]));
                }
            }
        }
    }
}

