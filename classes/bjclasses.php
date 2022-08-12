<?php
// loader 
require_once dirname(__FILE__).'/pack.php';
require_once dirname(__FILE__).'/hand.php';

  class deck {
    public $session_id;
    public $players;

  }



  class player {
    private $player_id ='';
    private $hand = array();
    private $turn = false;
    private $turn_token;

    public function __construct( $id, $hand ){
      $this->id = $id;
      $this->hand = $hand;
    }
    public function set_hand( $hand ){
      $this->hand = $hand;
    }
    public function set_turn( $turn ){
      $this->turn = $turn;
    }
    public function get_hand(){
      return $this->hand;
    }
  }




?>
