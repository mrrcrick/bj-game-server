<?php
class pack{
  public $cards = array();
  public function __construct( $cards = array() ){
    $this->cards = $cards;
  }
  public function generate_id( $id_num, $hash) {
    //$hash_string = strval( $hash . rand( 1,1000 ) );
    $hash_string = strval( $hash );
    $id = md5( strval( $id_num ) . $hash_string ) ;
    return $id;
  }
  public function load_cards( $cards ){
  //  echo "LOAD CARDS !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!";
//    print_r( $cards );
    $this->cards = $cards;
    return true;
  }
  public function deal_cards() {
    return $this->cards;
  }
  public function get_pack() {
    return $this->cards;
  }

  public function remove_card_from_hand( $card_ids ){

    // get option retrieve serialized data
    $pack = $this->cards ;
    $counter = 0;
    $id = '';

    foreach ($card_ids as $card_id ) {


      foreach ( $pack as $key => $card ){
      //  echo ": id : " . $card['accesskey'] . " : ";
        if ( $card['accesskey'] == $card_id ){
           $this->cards[$key]['in_hand'] = 'n';
        }
      //    $counter ++ ;
      }


  }


    return $this->cards;
  }

  public function get_card( $id ) {
    // get option retrieve serialized data
    $pack = $this->cards ;
    $counter = 0;
    foreach ( $pack as $card ){
    //  echo ": id : " . $card['accesskey'] . " : ";
      if ( $card['accesskey'] == $id ){
      // unset( $this->cards[ $counter ] );
       //$this->cards = array_values( $this->cards );
       //ksort( $this->cards );
      // echo " | Found check : " . $card['accesskey'] ." against : ". $id ." |";
       return $card;
      }
    //    $counter ++ ;
    }
    return false;
  }
  public function shuffle_deck( $number_of_cards = 51 ) {
    $numbers = range(0, $number_of_cards );
    shuffle( $numbers );
    $temp_pack = array();
    $count = 0;
    foreach ( $numbers as $number ) {
      $temp_pack[ $number ] = $this->cards[ $count ];
      $count ++ ;
    }
  //  $temp_pack  = array_values( $temp_pack  );
    ksort( $temp_pack );
    // update option save to option table
    $this->cards = $temp_pack;

  }
  public function reuse_old_cards( $card_ids ){
    $temp = $this->cards;
    $pack = $this->cards ;
    $msg = '';
    $count = 0;
    foreach ($card_ids as $card_id ) {
      // code...

      foreach ( $pack as $key=> $card ){
        if ( $card_id == $card['accesskey'] ) {
          $count ++;
          $pack[$key]['in_pack'] = 'y';
          $pack[$key]['in_hand'] = 'n';
          //$this->cards[$key]['in_hand'] = 'n';
          $pack[$key]['accesskey'] = $this->generate_id( $card['value'], $card['suit'] ) ;
          $msg = $count . 'cards reset !!! ';
        }

    }
  }
  $this->cards = $pack;
  return array( 'message' => $msg, 'pack' => $pack );
  }

  public function set_card_value( $card_id , $value ){
    foreach ( $this->cards as $key=> $card ) {
      if ( $card['accesskey'] == $card_id ) {
        $this->cards[$key]['value'] = $value ;
        $this->cards[$key]['can_cover_suit'] = array( $value - 1 , $value , $value + 1 );
        return $card;
      }
    }

  }

  public function set_card_suit( $card_id , $suit ){
    foreach ( $this->cards as $key=> $card ) {
      if ( $card['accesskey'] == $card_id ) {
        $this->cards[$key]['suit'] = $suit ;
        $this->cards[$key]['can_cover_suit'] = array( $suit );
        return $card;
      }
    }

  }

  public function get_available_cards(){
    $number_of_cards = 0;
    foreach ( $this->cards as $card ) {
      if ( $card['in_pack'] == 'y' && $card['in_hand'] == 'n'  ) {
        $number_of_cards++;
      }
    }
      error_log(" =============================================================" , 0);
    error_log(" RC: PICKUP CARD available: " . $number_of_cards , 0);

    return $number_of_cards;
  }


  public function get_x_cards( $num = 7){
  //  var_dump( $this->cards );
    $card_count =0;
    $counter = 0 ;// quick fix
    $cards_given = array();
    $temp = $this->cards;
    $pack = $this->cards ;
    foreach ($pack as $card ) {
    //  echo " looping  cards ..................";
    //  print_r( $card['id'] );
    //  if ( isset( $card['id'] )  ){
      // $cards_given[] =  $this->cards[ $counter ];
      // echo $this->cards[ $counter ]['in_pack'];
       if ( $card['in_pack'] == 'y' ) {
         $this->cards[ $counter ]['in_pack'] = 'n';
         $card['in_pack'] = 'n';
         $card['in_hand'] = 'y';
         $card_count ++ ;
         $cards_given[] =  $card;
       }


         $counter ++;
      if ($card_count == $num ){
        return $cards_given;
      }

    }
    return false;
  }
  public function hide_access_keys(){
    $cards = $this->cards ;
    foreach ($cards as $card ) {
      $card['accesskey'] = $this->generate_id( $card['value'], $card['suit'] ) ;
    }
    $this->cards = $cards ;
  }
  public function set_up_deck(){
    // set cards
    $suits = array( 'C', 'D', 'H', 'S');
  //  $suits = array( 'C', 'D');
    foreach ($suits as $suit) {
      for ($count = 1 ; $count < 14 ; $count++) {
        $card =  array( 'id'           => $count . $suit,
                        'accesskey'    => $count . '-' . $suit. '_' . rand(0,9999) ,
                        'value'        => $count,
                        'in_pack'      => 'y' ,
                        'suit'         => $suit,
                        'pickup_value' => 1,
                        'in_hand'      => 'n',
                        'tag'          => rand(0,300),
                        'details'      => $count . ' of ' . $suit);

        if ( $count == 2 ){
          $card['pickup_value'] = 2 ;
        }
        // set the card numbers this card can cover in a run
        if ( $count > 1 ) {
          $card['can_cover_value'] =  array( $count -1, $count, $count + 1 ) ;
        } else {
          // Ace can go on anything
          $card['can_cover_value'] = array( 13 , $count, $count + 1 ) ;
          $card['can_cover_suit']  = $suits ;
          $card['details']         =  'ace of ' . $suit . ' ';
        }

        // set the card suits this card can cover
        $card['can_cover_suit'] = array( $suit );
        // set special deal_cards
        if ( $count == 11 ) {
          $card['details'] = 'jack of ' . $suit . ' ';
        }
        if ( $count == 12 ) {
          $card['details']         = 'queen of ' . $suit . ' ';
          $card['can_cover_suit']  = $suits ;
          $card['can_cover_value'] = array( 1,2,3,4,5,6,7,8,9,10,11,12,13 );
        }
        if ( $count == 13 ) {
          $card['details'] = 'king of ' . $suit . ' ';
        }
        // Jack pick up value
        if ( $count == 11 && ( $suit ==  'S' || $suit == 'C') ){
            $card['pickup_value'] = 5;
        }

        $this->cards[] = $card;

      }


    }

  }

}



 ?>
