<?php
/**
 * chess class
 *
 * main game
 * @author luyue <544625106@qq.com>
 */

final class chess {

    /**
     * @var game data
     * 兵1 炮2 车3 马4 相5 士6 将7 0 null
     * >0 own to red player
     * <0 own to black player
     */
    protected $games = [[-3, -4, -5, -6, -7, -6, -5, -4, -3],
                        [ 0,  0,  0,  0,  0,  0,  0,  0,  0],
                        [ 0, -2,  0,  0,  0,  0,  0, -2,  0],
                        [-1,  0, -1,  0, -1,  0, -1,  0, -1],
                        [ 0,  0,  0,  0,  0,  0,  0,  0,  0],
                        [ 0,  0,  0,  0,  0,  0,  0,  0,  0],
                        [ 1,  0,  1,  0,  1,  0,  1,  0,  1],
                        [ 0,  2,  0,  0,  0,  0,  0,  2,  0],
                        [ 0,  0,  0,  0,  0,  0,  0,  0,  0],
                        [ 3,  4,  5,  6,  7,  6,  5,  4,  3]];

    public function  __construct(){
        
    }

    /**
     * Player to set the chessman
     * @param $chessColor player color
     * @param array chess position
     * @param array chess aimed position
     * @return bool
     */
    public function move($chessColor, $postion, $to_position){
        //start oppends chessman
        if($chessColor){
            if($this->games[$position[0]][$postion[1]]<0 || $this->games[$to_position[0]][$to_position[1]]>0){
                return false;
            }
        }else{
            if($this->games[$position[0]][$postion[1]]>0 || $this->games[$to_position[0]][$to_position[1]]<0){
                return false;
            }
        }
        switch (abs($this->games[$position[0]][$postion[1]])) {
            case 1:
                # code...
                break;
            case 2:
                # code...
                break;
            case 3:
                # code...
                break;
            case 4:
                # code...
                break;
            case 5:
                # code...
                break;
            case 6:
                # code...
                break;
            case 7:
                # code...
                break;
            default:
                return false;
                break;
        }
        return true;
    }
    
    /**
     * return the chess data wo player
     * 
     * @return array
     */
    public function getChessData(){
        return $this->games;
    }

    /**
     * check the color player if he is win
     * 
     * @return bool
     */
    public function checkWin($chessColor){

    }

    /**
     * check the color player if he is ready to win
     * 
     * @return bool
     */
    public function checkReadyToWin($chessColor){

    }
}
