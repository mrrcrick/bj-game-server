<?php
class hand {
  public $isValid       = false;
  public $cards_in_hand = array();
  public $deck_card     = '';
  public $pack          = array();
  public $game_pickup_value;
  public $set_card_array = null;
  public $suit ;

  public function __construct( $pack, $deck_card_id, $cards_in_hand_ids, $game_pickup_value, $set_card_array = null, $suit = null ){
    $this->pack              = $pack;
    $this->deck_card         = $deck_card_id;
    $this->cards_in_hand     = $cards_in_hand_ids;
    $this->game_pickup_value = $game_pickup_value;
    $this->set_card_array    = $set_card_array;
    $this->suit              = $suit;
    error_log(" RC>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> suit s " . var_export( $suit, 1 ), 0);

  }
  public function set_cards_to_in_hand(){
    error_log(" RC>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> suit s " . var_export( $this->set_card_array, 1 ), 0);
    if ( $this->set_card_array ){
      foreach ( $this->set_card_array as $key => $set_card ) {
        // if ace set the suit
        if ( $this->pack->get_card( $set_card['id'] )['value'] == 1 ){
          $this->pack->set_card_suit( $set_card['id'] , $set_card['suit'] );
        }
      }
    }
    return true;
  }
  public function validate() {
     error_log(" RC >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>" , 0);
  //  syslog(LOG_INFO, "LOG INFO============================= ");
    //print_r( $this->cards_in_hand );
    // set cards
    $this->set_cards_to_in_hand();

    $this->isValid = false;
    $message       = '';
    $live_card     = ( $this->game_pickup_value > 1 )? true : false;
  // echo "* Deck Card " . $this->deck_card ;
    // check if first card can cover
    if ( ! $deck_card  = $this->pack->get_card( $this->deck_card ) ) {

      return array( 'isValid' => false, 'msg' => 'Error cant find card on deck !!!!' );
    }
    if ( ! $first_card = $this->pack->get_card( $this->cards_in_hand[0] ) ){
    //  echo "first card " . $this->cards_in_hand[0] ;
      //print_r( $this->pack->get_pack() );
      return array( 'isValid' => false, 'msg' => 'Error cant find card in pack !!!!' );

    }

  //  check first card matches value or suit unless its a 2 , any suit or value on queen
    if ( in_array(  $first_card['suit'], $deck_card['can_cover_suit'] ) || ( $first_card['value'] == 1 ) && ( ! $live_card ) &&( $deck_card['value'] != 2 ) || ( in_array( $deck_card['value'], array( 12 ) ) ) ){
      $this->isValid = true;
    } else {
      $message .= ' Cant put first card ' . $first_card['suit'] . ' on Deck card ' . $deck_card['suit'] . ' ';
    }
    if ( $deck_card['value'] == $first_card['value'] ){
        //echo "2";
        $this->isValid = true;
    } else{
      $message .= ' Cant put first card ' . $first_card['value'] . ' on Deck card ' . $deck_card['value'] . ' ';
    }

    // 2 check
    if ( ( $live_card ) && ( $deck_card['value'] == 2 ) &&  ( $first_card['value'] !=2 ) ) {
      //echo "3";
      $this->isValid =false;
    } else {
      $message      .= " Can put  first card on 2 "  ;
    }
    // blackjack check
    if ( ( $live_card ) && ( $deck_card['value'] == 11) && in_array( $deck_card['suit'], array('S','C') ) && ( $first_card['value'] !=11 ) ) {
      //echo "3";
      $this->isValid =false;
    } else {
      $message      .= " Can put  first card on Jack "  ;
    }
    // check for duplicate cards submitted
    if( count( array_unique( $this->cards_in_hand ) )<count( $this->cards_in_hand ) )
    {
        $this->isValid = false;
        $message      .= " Duplicate cards detected !!!!";
    }
    if ( $this->isValid ){
    // check if card combo is correct
    $count = 0;
    $card_ids = $this->cards_in_hand;
    // need to reverse the hand to make sure we check the quen card correctly.
    $reverse_pack = ( $card_ids );
    foreach ( $card_ids as $card_id ) {
      $deck = $this->pack;
      //echo ' ' . $deck ->get_card( $card_id )['details'];
      if ( isset( $card_ids[ $count + 1 ] ) && ( $this->isValid ) ){
      // set valid to false, now goe through conditions to set isvalid to true
        $this->isValid = false;
        // if card on deck same value as card on top
        if ( $deck->get_card( $card_id )['value'] == $deck->get_card( $card_ids[ $count + 1 ] )['value'] ){
          $this->isValid = true;
        } else {
          $message .= ' Card ' . $deck->get_card( $card_id )['value'] . 'doe not match ' . $deck->get_card( $card_ids[ $count + 1 ] )['value'];
        }


        // valid combo card same suit card on top same suit  within allowed values
        if ( ( in_array( $deck->get_card( $card_ids[ $count + 1 ] )['suit'] , $deck->get_card( $card_id )['can_cover_suit'] ) ) && in_array( $deck->get_card( $card_ids[ $count + 1 ] )['value'] , $deck->get_card( $card_id )['can_cover_value'] ) ){
          $this->isValid = true;
        } else{
            $message .= " | not valid combo because : " . $deck->get_card( $card_id )['details'] .' and ' . $deck ->get_card( $card_ids[ $count + 1 ])['details'] . '| ';
          //  $this->isValid = false;
        }

      }
      $count++;
    }
   }
    return array( 'isValid' => $this->isValid, 'msg' => $message );
  }

  public function get_the_top_card() {
    if ( $this->isValid ){
      return $this->pack->get_card( end( $this->cards_in_hand ) );

    }

  }

  public function get_eights_in_hand() {
    $number_of_eights = 0;
    error_log(" RC>>>> number eights " . $number_of_eights , 0);

    if ( $this->isValid ) {
      $pack = $this->pack;
      $cards = $this->cards_in_hand;
      foreach ( $cards as $card ){
        // set if 2 or blackjack

        if ( $pack->get_card( $card )['value'] == 8 ){
         $number_of_eights ++;
        } else {
          $number_of_eights= 0;
        }
      }

    }
      error_log(" RC>>>> number eights " . $number_of_eights , 0);
      return $number_of_eights;
  }




  public function get_kings_in_hand() {
    $number_of_kings = 0;
    if ( $this->isValid ) {
      $pack = $this->pack;
      $cards = $this->cards_in_hand;
      foreach ( $cards as $card ){
        // set if 2 or blackjack

        if ( $pack->get_card( $card )['value'] == 13 ){
         $number_of_kings ++;
        } else {
          $number_of_kings= 0;
        }
      }

    }
      return $number_of_kings;

  }



  public function get_pickup_value_of_hand(){
    //echo "get pickup value";
    if ( $this->isValid ) {
      $pickup_value = 0;
      $pack = $this->pack;
      $cards = $this->cards_in_hand;
    //  print_r( $cards);
      foreach ( $cards as $card ){
        // set if 2 or blackjack

        if ( $pack->get_card( $card )['pickup_value'] == 2 || $pack->get_card( $card )['pickup_value'] == 5  ){
          if ( $pickup_value % $pack->get_card( $card )['value'] == 0){
              $pickup_value = $pickup_value + $pack->get_card( $card )['pickup_value'] ;
          } else {
              $pickup_value = $pack->get_card( $card )['pickup_value'] ;
          }

        } else {
          $pickup_value = 1;
        }

        // if different value i.e 2 on jack  reset to top card value

      }

    }
  //  echo $pickup_value;
  //   die("stopped!!!");
      return $pickup_value;
  }

}


 ?>
