<?php
namespace chess;
/**
 * room resource
 *
 * @author luyue <544625106@qq.com>
 */
class room{

    /**
     * @var Platform Global Room Resource
     * @desc
     *     0-> player1 object
     *     1-> playre2 object
     *     2-> chess   object
     */
    private $roomResource;

    /**
     * @var max room number
     */
    private $roomNumber;

    public function  __construct($roomNumber) {
        $roomResource = new \SplFixedArray($roomNumber);
        foreach ($roomResource as $key => $value) {
            $roomResource[$key] = new \SplFixedArray(3);
        }
        $this->roomNumber = $roomNumber;
        $this->roomResource = $roomResource;
    }

    /**
     * player join a room
     * @param array player object
     * @param array player aimRoom
     * @return  array
     */
    public function joinRoom($player, $aimRoom){
        if($this->roomResource[$aimRoom['room']][$aimRoom['sit']] !== NULL){
            return ['ret'=>false, 'msg'=>'there is another player in the room'];
        }
        $originRoom = $player->roomInfo;
        if(!empty($originRoom)){
            $this->roomResource[$originRoom['room']][$originRoom['sit']] = NULL;
        }
        $this->roomResource[$aimRoom['room']][$aimRoom['sit']] = $player;
        return ['ret'=>true];
    }

    /**
     * player leave a room
     * @param array player Roomdata
     * @return  array
     */
    public function leaveRoom($aimRoom){
        if(isset($aimRoom[0]) && $aimRoom[0]<$this->roomNumber && isset($aimRoom[1]) && $aimRoom[1]<2){
            $this->roomResource[$aimRoom[0]][$aimRoom[1]] = NULL;
            return ['ret'=>true];
        }else{
            return ['ret'=>false, 'wrong data'];
        }
    }

    /**
     * check player 1 and player 2 ready status|| game statrt
     * @param array player playerCoordinate
     * @return  array
     */
    public function checkGame($playerCoordinate){
        if($playerCoordinate[1] == 0){
            return $this->roomResource[$playerCoordinate[0]][1]->readyStatus == 1 ? $this->roomResource[$playerCoordinate[0]][1]: false;
        }
        return $this->roomResource[$playerCoordinate[0]][0]->readyStatus == 1 ? $this->roomResource[$playerCoordinate[0]][0]: false;
    }

    /**
     * get room resource info
     * @return  array
     */
    public function getRoomInfo(){
        $tempinfo = $this->roomResource;
        foreach ($tempinfo as $key => $value) {
            if($value[2]!==NULL){
                $tempinfo[$key][4] = true;
            }
            if($value[0]!==NULL){
                $tempinfo[$key][0] = $value[0]->username;
                $tempinfo[$key][2] = $value[0]->readyStatus;
            }
            if($value[1]!==NULL){
                $tempinfo[$key][1] = $value[1]->username;
                $tempinfo[$key][3] = $value[1]->readyStatus;
            }
        }
        return $tempinfo;
    }

    /**
     * disconnect
     * @return  array
     */
    public function remove($player){
        $sit = $player->roomInfo;
        $this->roomResource[$sit[0]][$sit[1]] = NULL;
        $this->roomResource[$sit[0]][2] = NULL;
        return $sit[1] == 0? $this->roomResource[$sit[0]][1]->connection: $this->roomResource[$sit[0]][0]->connection;
    }
}

