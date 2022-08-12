<?php
/**
 * Plugin Name:       black-jack-service
 * Description:       Start a black Jack game service. 
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Mr
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       firstapp
 *
 * @package           black-jack-service
 */
 /**
 * This is our callback function that embeds our phrase in a WP_REST_Response
 */

 require_once dirname(__FILE__).'/classes/bjclasses.php';

class black_jack_controller {
public static $number = 0;
public $game ='';

public function __construct() {
  self::$number = rand(1000,9000);

}

private function load( $game_session ){
  $game_option_title = 'bj_game_' . $game_session;
  $game = get_option( $game_option_title );
  if ( !is_array( $game ) ){
    $game = unserialize( $game );
  }
 //  $this->game = $game;
  return $game ;
}

private function save( $game_session, $game ){
  $game_option_title = 'bj_game_' . $game_session;
  if ( is_array( $game ) ){
    serialize( $game );
  }
  update_option( $game_option_title, $game );

}


public function update_board( $game_id, $game ){  
  $games_list = $this->load( 'Black_Jack_Games' );
  // loop through game list
  foreach( $games_list as $key => $curr_game ){
    if ( array_key_exists( 'game_id', $curr_game ) && $curr_game['game_id'] == $game_id ){
      $games_list[$key] = $game;
    }
  }
  $this->save( 'Black_Jack_Games',array_values( $games_list ) );


}

public function add_game_to_board( $game_data = array() ) {
  $games_list = $this->load( 'Black_Jack_Games' );
  if ( is_array( $games_list ) ){
    $games_list[]    = $game_data;
    $game_data       = $games_list;

  } else {
    $game_data = array( $game_data );
  }
  $this->save( 'Black_Jack_Games',array_values( $game_data ) );
}

public function remove_game_to_board( $game_id ) {
  $games_list = $this->load( 'Black_Jack_Games' );
  if ( is_array( $games_list ) ){
    $games_list[] = $game_data;
    $game_data    = $games_list;

  }
  $this->save( 'Black_Jack_Games', $game_data );


}



public function add_new_game_session( WP_REST_Request $request ){
 $name =  $request->get_param('player_name') . ' ' ;
 if ( empty( $name ) ) {
   $name = 'No Name!! ';
 }
 $cards = new pack();
 $cards->set_up_deck();
 $cards->shuffle_deck( 51 ) ;
 error_log(" ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++", 0);

 error_log(" RC: PICKUP CARD r initial count "  .  $cards->get_available_cards(), 0);

 // get player one new_cards
 //$new_cards = $cards->get_x_cards(5);
 // get deck card
$deck_card = $cards->get_x_cards(1);
error_log(" RC: PICKUP CARD r after hand dealt and deck set count "  .  $cards->get_available_cards(), 0);
 //print_r( $deck_card );
 $pack = $cards->get_pack();
 $session_id        = rand(1000,9000);
 $game_option_title = 'bj_game_' . $session_id;
 $next_turn_token   = md5( $session_id . time() );
 $moderator_token   = md5( rand(10,10000) . time() );


 $game = array( 'session_id'           => $session_id,
                'max_players'          => 4,
                'pack'                 => $pack,
                'deck'                 => array( $deck_card[0]['accesskey'] ),
                'current_card'         => $deck_card[0] ,
                'reversals'            => 0,
                'goes_missed'          => 0,
                'pickup'               => $deck_card[0]['pickup_value'],
                'current_suit'         => $deck_card[0]['suit'],
                'current_value'        => $deck_card[0]['value'],
                'current_card_id'      => $deck_card[0]['accesskey'],
                'next_turn_token'      => $next_turn_token ,
                'last_read'            => '' ,
                'public'               => true,
                'session_started'      => false,
                'moderator_token'      => $moderator_token ,
                'winner_id'            => '',
                'players_in_game'      => 0,
                'players_last_card_id' => '',
                'players_limit'        => 4,
                'next_game_id'         => '',
                'game_status'          => 'open',
                'players'              => array(),
                'name'                 => '',
                'names'                => array(),
                'current_player'       => '',
                'last_player_name'     => '',
                'current_player_name'  => '',
                'event'                => '',
                'player_index'         => 0 ,
                'winners'              => '',
              );

  $game_details = array(
                  'game_id'         => $game['session_id'],
                  'max_players'     => $game['max_players'],
                  'status'          => $game['game_status'],
                  'date'            => time(),
                  'players_in_game' => $game['players_in_game'],
                  'name'            => $game['name'],
                  'names'           => $game['names'],
                  'players'         => array(),
                  'winners'         => $game['winners'],
  );

  $this->add_game_to_board( $game_details );

  $client_game = array( 'session_id' => $session_id,
                        'moderator_token' => $moderator_token ,
                        //'id' =>  $player_1_id ,
                        //'name' => 'player_1',
                        'turn_token' => $next_turn_token,
                        //'cards'=> $new_cards ,
                        'current_card'    => $deck_card[0] ,
                        'reversals'       => 0,
                        'goes_missed'     => 0,
                        'pickup'          => $deck_card[0]['pickup_value'],
                        'current_suit'    => $deck_card[0]['suit'],
                        'current_value'   => $deck_card[0]['value'],
                        'current_card_id' => $deck_card[0]['accesskey'],

  );
  $this->save( $session_id, $game );
//  $time     = time() + (60*60*24*1 );
  //$cookie   = json_encode( array( 'id' =>  $player_1_id , 'turn_token' => $next_turn_token, 'moderator_token' => $moderator_token , 'session_id' => $session_id ) );
  $response = rest_ensure_response(  $client_game  );
  //$origin = $_SERVER['HTTP_ORIGIN'];
  $response->header( 'Content-Type', "application/json" );
  $response->header( 'Access-Control-Allow-Origin' , "*");
  //$response->header( 'Set-Cookie', 'bjs-game=' . $cookie  . '; expires=' . $time . ', 17-May-2022 14:39:58 GMT;path=/; domain=127.0.0.1' );

  return $response;
}
public function getgame( WP_REST_Request $request ){


 $session_req = $request->get_param('game_id');
 $game = $this->load( $session_req );
 echo "NEXT GAME TOKEN: " . $game['next_turn_token'];
 echo "***game id ".  $session_req . " Card on deck suit : " . $game[ 'current_suit' ] . " value: " .  $game[ 'current_value'] . " **";
 print_r( $game );
 return rest_ensure_response( '' ) ;

}

public function getgamelist( WP_REST_Request $request ){


  //$session_req = $request->get_param('game_id');
  $game     = $this->load( 'Black_Jack_Games');
  $game     = array_reverse( $game );
  $response = rest_ensure_response( $game );
  $response->header( 'Content-Type', "application/json" );
  $response->header( 'Access-Control-Allow-Origin' , "*");
  return $response;

}
// set the game data HACK:
public function setgamedata( WP_REST_Request $request ) {
  $session_id = $request->get_param('game_id');
  $game_token  = $request->get_param('game_token');
  $game = $this->load( $session_id );
  $game['next_turn_token'] = $game_token;
  $this->save( $session_id, $game );
  print_r( $game );

}

// pack itenery 
public function get_cards_in_pack( WP_REST_Request $request ){
  $session_id = $request->get_param('game_id');
  //echo ( $session_id);
  $game = $this->load( $session_id );
  $card_itenery  = '';
  //print_r( $game);
  $count = 0;
  $card_itenery .= '===================================CARDS IN PACK =============================================';

  foreach ( $game['pack'] as $pack ){
    if ( $pack['in_pack'] == 'y' ) {
      $card_itenery .= 'Card: ' . $pack['details']. ' ID: '. $pack['accesskey'] . ' in Pack: ' . $pack['in_pack'] . ' , ';
      $count ++;
    }
    }
  $card_itenery .= '============================== TOTAL: '. $count .'===============================================================';
  $count = 0;
  $card_itenery .= '===================================CARDS NOT IN PACK =============================================';

  foreach ( $game['pack'] as $pack ){
    if ( $pack['in_pack'] == 'n' ){
      $card_itenery .= 'Card: ' . $pack['details']. ' ID: '. $pack['accesskey'] . ' in Pack: ' . $pack['in_pack'] . ' , ';
      $count ++;
    }
    }
    $card_itenery .= '============================== TOTAL: '. $count .'===============================================================';
    return $card_itenery;

}

public function remove_player(  $player_id, $game  ){

  $new_players_arr = array();
//if ( $moderator_token == $game['moderator_token'] ){
    foreach ($game['players'] as $player ) {
      if ( $player['id'] !== $player_id ) {
        $new_players_arr[] = $player ;
      }
    }
    $game['players'] = $new_players_arr;
    return $new_players_arr;
  // }
  return false;
}

// game flow
public function set_next_player_token_in_game( $players , $token , $turns = 1 ){
  error_log(" RC>>>> number eights 2 == turns  " . $turns , 0);
  $current_players      = $players;
  $return_players_array = array();
  $new_token            = md5( rand( 0, 9000 ) . time() );
  error_log(" RC >>>> count of current players  " . count( $players ) . " new token : " . $new_token . " Old token " . $token     , 0);
  //echo "set next player";
  //print_r( $players );
  $player_index = 0;
  // loop and find player token index
  foreach ( $players as $key => $player ){

    if ( $player['turn_token'] == $token ){
    //  echo "found turn token ";
      $player_index = (int)$key;
      error_log(" RC>>>> number eights 2 found player token index   " . $player_index , 0);
    //  return array( 'players' => $current_players , 'new_token' => $new_token );
    }
  }
    $number_of_players = count( $players );
    // now we have a reference point to start loop from.
    $token_pointer = (int)$player_index;
    for ( $i = 0; $i < $turns ;$i ++ ){
      if ( isset( $current_players[ $token_pointer + 1]['turn_token'] ) ){
        $current_players[ $token_pointer ]['turn_token'] = '';
        $current_players[ $token_pointer + 1]['turn_token'] = $new_token  ;
        error_log(" RC>>>> number eights 2 move token from  " . $token_pointer . " to " . ( $token_pointer + 1)  , 0);
        $token_pointer ++ ;
      } else {
        $current_players[ $token_pointer ]['turn_token'] = '';
        // reset back to start and place token
        error_log(" RC>>>> number eights 2 Need to reset token pointer at index " . $token_pointer, 0);
        $token_pointer = 0;
        error_log(" RC>>>> number eights 2 Now token pointer reset to " . $token_pointer, 0);
        $current_players[ $token_pointer ]['turn_token'] = $new_token  ;
      }

    }
    $curr_player_id   ='';
    $curr_player_name = '';
    foreach( $current_players as $key => $check_player ){
      if ( $check_player['turn_token'] == $new_token ){
        $curr_player_id   = $check_player['id'];
        $curr_player_name = $check_player['name'];
      }

    }

    //error_log(" RC >>>>  returned players info from set token " . print_r( $current_players ), 0);

    return array( 'players'             => $current_players ,
                  'new_token'           => $new_token,
                  'current_player'      => $curr_player_id,
                  'current_player_name' => $curr_player_name,
                  'player_index'        => $token_pointer
                );
}















//=========================================================================================

public function get_player( WP_REST_Request $request ){
  $session_id  = $request->get_param( 'game_id' );
  $player_id   = $request->get_param( 'player_id' );
  $player      = array();
  if ( !empty( $session_id ) && !empty( $player_id ) ){
    $game = $this->load( $session_id );
    if ( $game ) {
    foreach ( $game['players'] as $game_player ){
      if ( $game_player['id'] == $player_id ){
        $player = $game_player;
      }
    }
  }
  }
  $response = rest_ensure_response( json_encode( $player ) );
  $response->header( 'Content-Type', "application/json" );
  $response->header( 'Access-Control-Allow-Origin' , "*");
  return $response;
}



public function add_player( WP_REST_Request $request  ){
  $session_id  = $request->get_param( 'game_id' );

  if ( !empty( $session_id ) ){
    //$game = get_option( 'bj_game_' . $game_session );

    //$game = unserialize( get_option( 'bj_game_' . $game_session ) );
    $game = $this->load( $session_id );
    $cards = new pack();
    $cards->load_cards( $game['pack'] );
    $new_cards    = $cards->get_x_cards( 5 );
    $pack         = $cards->get_pack();
    $game['pack'] = $pack;
    $player_name  = $request->get_param( 'player_name' );
    $player_name  = empty( trim( $player_name ) )? 'player_' . count( $game['players'] ) + 1 : $player_name;
    error_log(" RC>>>:  player name " . $player_name . " ++++++++++"  , 0);
    //'names'
    //'current_player'

    $player = array( 'id'                => count( $game['players'] ) + 1 . rand(10, 1999 ),
                       'name'            => $request->get_param( 'player_name' ),
                       'turn_token'      => '',
                       'moderator_token' => '',
                       'cards'           => $new_cards ,
                       );
    // if its the first player set moderator and next turn token.
    if ( empty( $game['players'] ) ) {
      $player['moderator_token']   = $game['moderator_token'];
      $player['turn_token']        = $game['next_turn_token'];
      $game['current_player']      = $player['id'];
      $game['current_player_name'] = $player['name'];
      $game['event']               = $player['name'] . ' starts ';
      $game['player_index']        =  0;
    }
    $game['players'][] = $player ;
    $game['names'][]   = $player_name;
    $game['players_in_game'] ++ ;
    error_log(" RC>>>:  players IN GAME  " .   $game['players_in_game']. " ++++++++++"  , 0);
    $players_info = $game['players'];

    $board_info = array(
      'game_id'         => $game['session_id'],
      'max_players'     => $game['max_players'],
      'players'         => $game['names'],
      'status'          => $game['game_status'],
//      'date'            => $game['date'],
      'players_in_game' => $game['players_in_game'],
      'name'            => $game['name'],
    );


    $this->update_board( $session_id, $board_info );
    $this->save( $session_id, $game );
    //$game = serialize( $game );
    //update_option( 'bj_game_' . $game_session  , $game );

  }
  $response = rest_ensure_response( json_encode( $player ) );
  $response->header( 'Content-Type', "application/json" );
  $response->header( 'Access-Control-Allow-Origin' , "*");
  return $response;
}

public function get_player_token( WP_REST_Request $request ){
  //error_log(" RC>>>> Plugin URL is : " .  plugin_dir_url( __FILE__ ) , 0);

  $token = '';
  $sent_id = $request->get_param('id');
  if ( isset($sent_id )  && !empty( $request->get_param('id') ) )  {
    $player_id = $request->get_param('id');
    if ( !empty( $request->get_param('game_id'))){
      $session_id = $request->get_param('game_id');
      $game = $this->load( $session_id );
      foreach( $game['players'] as $player ){
        if ( $player['id'] == $player_id ) {
          $token = $player['turn_token'];
        }
      }

    }

  }
  $response = rest_ensure_response( $token );
  $response->header( 'Content-Type', "application/json" );
  $response->header( 'Access-Control-Allow-Origin' , "*");
  return $response;
}
private function remove_from_cards_from_hand( $players , $cards, $player_id ) {
//echo "----------------------------------REMOVE CARDS FROM HAND ----------------------------------------";
$counter = 0 ;
$players_hand = $players;
//print_r( $cards );
//print_r( $players );
//echo "--------------------------REMOVE CARDS --------------------------------------------";

//echo " ID is : " . $player_id;
$player_indice = 0 ;
$new_cards = array();
 foreach ($players as $key=> $player ) {
   if ( $player['id'] == $player_id ) {
     // get the cards card_id
     foreach ($player['cards'] as $card_key => $player_card ) {
    //   echo " card : " . $player_card['accesskey'] ;
       if ( in_array( $player_card['accesskey'] , $cards ) ) {
        // echo " remove : " . print_r( $players_hand[$key]['cards'][$card_key] );
         unset( $players_hand[$key]['cards'][$card_key] );
         $player_indice = $key;

       }

     }

   } else {
    // $player_indice ++;
  }
  //$counter ++;
   }
  // print_r( $players_hand[$player_indice]['cards'] );
  //$players_hand[$player_indice]['cards'] = $new_cards;
//  echo "------------------------------";
//  echo $player_indice;
//  echo "------------------------------";
//  print_r( $players_hand );
  //exit;
  $players_hand[$player_indice ]['cards'] = array_values( $players_hand[$player_indice ]['cards'] );
  return $players_hand;

 }

private function remove_from_cards_from_pack( $game, $pack, $hand ) {
  $counter = 0 ;
  foreach ( $hand as $card ){

      if ( $pos = array_search( $card, $game['pack'] ) ) {
          unset( $game['pack'][$pos]  );
      }


    $counter ++ ;
  }
  return $game;
}
public function get_game_update( WP_REST_Request $request ) {
  $session_id          = $request->get_param( 'game_id' );
  $player_id           = $request->get_param( 'player_id' );
  $game                = $this->load( $session_id );
  $current_player      = $game['current_player'];
  $current_player_name = $game['current_player_name'];

  // get the current player
  if ( $current_player !== $player_id ){
    $game['current_player'] = '0';

  }

  $game_info     = array( 'current_suit'        => $game['current_suit'],
                          'current_value'       => $game['current_value'],
                          'pickup'              => $game['pickup'] ,
                          'deck'                => $game['deck'],
                          'current_player_name' => $game['current_player_name'],
                          'current_player'      => $game['current_player'],
                          'names'               => $game['names'],
                          'event'               => $game['event'],
                          'player_index'        => $game['player_index']
                          // move token
                        );

  $response      = rest_ensure_response( json_encode( $game_info) );
  $response->header( 'Content-Type', "application/json" );
  $response->header( 'Access-Control-Allow-Origin' , "*");
  return $response;

}

public function pick_up_card( WP_REST_Request $request ) {
  $player_id     = $request->get_param( 'player_id' );
  $turn_token    = $request->get_param( 'turn_token' );
  $session_id    = $request->get_param( 'game_id' );
  $game          = $this->load( $session_id );
  $pack          = new pack( $game['pack'] );
  $players       = $game['players'];
  $pick_up_cards = array();
  $player_index  = 0;
  $token         = '';
  $msg           = '';
  $last_player   = $game['current_player_name'];
  error_log(" RC: ++++++++++   PICKUP  CARDS on th Deck:  " . var_export( $game['deck'], 1 ) . " ++++++++++"  , 0);
  // check if turn token is equal to current turn token
  if ( $turn_token != $game['next_turn_token'] && $turn_token != 'test' ){
    return "Not your turn ";
  }
  foreach ( $players as $key => $player ){
    if ( $player['id'] == $player_id ){
      if ( ($pack->get_available_cards() - $game['pickup'] ) <  1 ){
        error_log(" RC: PICKUP CARD reseting cards "  , 0);

        $msg .= "Run out of cards to pick up Reusing the cards on the deck ";
        $top_deck_card = end( $game['deck'] );
        error_log(" RC: ++++++++++   Remove last element  :  " . $top_deck_card . " ++++++++++"  , 0);
        error_log(" RC: ++++++++++   POPPING GAME DECK BEFORE COUNT  :  " . count(  $game['deck'] ) . " ++++++++++"  , 0);
        array_pop( $game['deck'] );
        error_log(" RC: ++++++++++   POPPING GAME AFTER  COUNT  :  " . count(  $game['deck'] ) . " ++++++++++"  , 0);
        $new_resused_pack = $pack->reuse_old_cards( $game['deck'] );
        $pack = new pack( $new_resused_pack['pack'] ) ;

        error_log(" RC: ++++++++++   add it back to deck  :  " . $new_resused_pack['message'] . " ++++++++++"  , 0);
        $game['deck'] = array( $top_deck_card ) ;
        error_log(" RC: ++++++++++   resuse card result :  " . var_export(   $game['deck'] ,1) . " ++++++++++"  , 0);

      }
      $event_msg       = $game['current_player_name']   . ' picked up ' . $game['pickup'] ;
      $pick_up_cards   = $pack->get_x_cards( $game['pickup'] );
      $cards_picked_up = $pick_up_cards;
      //echo "cards picked up ==============================================================";
      //print_r( $pick_up_cards );
      //echo "==============================================================================";
      $token           = $player['turn_token'];
      $player_index    = $key;
      $pick_up_cards   = array_merge( $players[ $key ]['cards'], $pick_up_cards );
      $game['pack']    = $pack->get_pack();

    }

  }
  $current_game = $this->set_next_player_token_in_game( $players , $game['next_turn_token'] );
  if ( ! empty( $current_game ) ) {

    $game['players']                           = $current_game['players'];
    $game['next_turn_token']                   = $current_game['new_token'];
    $game['players'][ $player_index ]['cards'] = $pick_up_cards;
    $game['current_player_name']               = $current_game['current_player_name'];
    $game['event']                             = $event_msg ;
    $game['last_player_name']                  = $last_player;
    $game['current_player']                    = $current_game['current_player'];
    $game['player_index']                      = $current_game['player_index'];
    // move token
    //print_r( $game['players']  );
  //  exit;
    $updated_players_cards = $this->get_players_cards( $player_id, $game['players'] );
    $game['pickup'] = 1;
    //$game['current_player']
    $this->save( $session_id, $game );
} else {
  echo "could not save player session ";
}
$response = rest_ensure_response( array( $cards_picked_up, $updated_players_cards, $msg )  );
$response->header( 'Content-Type', "application/json" );
$response->header( 'Access-Control-Allow-Origin' , "*");
return $response;
}

private function get_players_cards( $player_id, $players )
{
  foreach ($players as $player ){
    if ( $player['id'] == $player_id ) {
      return $player;
    }
  }
  return array();
}
public function submit_hand( WP_REST_Request $request ){
  //echo "submit ";
  // check if turn token is equal to current turn token
  $session_id = $request->get_param( 'game_id' );
  $player_id  = $request->get_param( 'player_id' );
  $turn_token = $request->get_param( 'turn_token' );
  $hand       = $request->get_param( 'hand' );
  $set_cards  = ( $request->get_param( 'set_cards' ) )? $request->get_param( 'set_cards' ) : null;
  $winner     = '';
  $msg        = '';
  $nxt_player = true;
  error_log(" RC: @@@@ SET CARDS SENT TO SUBMIT :  " . var_export( $set_cards, 1 ) . " @@@@@ "  , 0);
  // get the game data
  //print_r($hand);
  $game                 = $this->load( $session_id );
  $current_players_name = $game['current_player_name'];
  error_log(" RC: ++++++++++   Submit hand - CARDS on th Deck:  " . var_export( $game['deck'], 1 ) . " ++++++++++"  , 0);
  $set_card = ( $set_cards ) ? end( $set_cards ) : null;
  //print_r( $game );
  //$game = $this->game;
  // check if player has the turn token  and set hand.
//  echo "TOKEN ---> ". $game['next_turn_token'] . " Player token " . $turn_token ;
  if ( isset( $session_id  ) && ( strval( trim( $game['next_turn_token'] ) )== strval(trim( $turn_token ) ) ) ) {
  // get the pack from database to use with submit hand
    $cards = new pack( $game['pack'] );
  //  print_r( $cards );
    $submitted_hand       = new hand( $cards , $game['current_card_id'], $hand, $game['pickup'], $set_cards, $game['current_suit'] );
    $submit_hand          = $submitted_hand ->validate();
  //  echo "token session set";
  //  print_r( $submit_hand);
    if ( $submit_hand['isValid'] ){
    //  echo "is valid";
      $game['event']     = '';
      $game_pickup_value = $submitted_hand ->get_pickup_value_of_hand();
      if (   ( $game['pickup']   > 1 ) && ( $game_pickup_value > 1 ) ) {
        $game_pickup_value = (int)  $game['pickup']   + (int)$game_pickup_value;
      }

      // get miss a goes


      $game_missed_goes = $submitted_hand->get_eights_in_hand() ;
      $game_missed_goes ++;
      $event_msg        = ( $game_missed_goes > 1 )? $game['current_player_name'] . ' has played ' . ( $game_missed_goes - 1). '8s  skipped ' . ( $game_missed_goes - 1 )  . ' goes ' : $event_msg = '' ;
      if ( count( $game['players'] ) <= 2 && ( $game_missed_goes > 0) ){
      //  $game_missed_goes = 2;
      } else {

      }
      // get reversals
      $game_reversals   = $submitted_hand->get_kings_in_hand();

      error_log(" RC>>>> number of reversals " . $game_reversals , 0);
      if ( count( $game['players'] ) < 3 && ( $game_reversals > 0 ) ){
        // set to 0 so stays on players
        //$game_missed_goes = 0;
        error_log(" RC>>>> number of will set back to player ****" . $game_reversals , 0);
        $event_msg        = $game['current_player_name'] . ' has reversed the game back to themselves';
        $nxt_player = false;
      } elseif ( $game_reversals % 2 == 0 && ( $game_reversals > 0 )) {
        error_log(" RC>>>> mod 2 reverse back to self ###" . $game_reversals , 0);
      //  $game_missed_goes = 0;
        $event_msg        = $game['current_player_name'] . ' has reversed the game back to themselves';
        $nxt_player = false;
      } elseif ( $game_reversals % 3 == 0 && ( $game_reversals > 0 ) ){
        $game['players']  = array_reverse( $game['players']  );
        $event_msg        = $game['current_player_name'] . ' has reversed the game back to the left';
        error_log(" RC>>>> mod 2 reverse left ###" , 0);

      } elseif( count( $game['players'] ) > 2  && ( $game_reversals == 1 ) ) {
      $game['players']  = array_reverse( $game['players']  );
      $event_msg        = $game['current_player_name'] . ' has reversed the game back to the right';
        error_log(" RC>>>> Other reverse -----" , 0);

      }
      error_log(" RC: ++++++++++ submit number of cards in deck before submit   " . count( $game['deck']) . " ++++++++++"  , 0);
      // check if turn token is equal to current turn token
      $game['deck']            = array_merge( $game['deck'] , $hand );
      error_log(" RC: ++++++++++ submit number of cards in deck after  submit   " . count( $game['deck']) . " ++++++++++"  , 0);

      $players                 = $this->remove_from_cards_from_hand( $game['players'] , $hand, $player_id );
      $newcard                 = $submitted_hand->get_the_top_card();
      $updated_pack            = $cards->remove_card_from_hand( $hand );
      $game['pack']            = $updated_pack;
      $game['pickup']          = $game_pickup_value ;
      $game['players']         = $players;
      $game['current_card']    = $newcard;
      $game['current_suit']    = $newcard['suit'];
      $game['current_value']   = $newcard['value'];
      $game['current_card_id'] = $newcard['accesskey'];


      $game['goes_missed'] = $game_missed_goes ;
      $current_game =  $this->set_next_player_token_in_game( $players , $game['next_turn_token'] , $game['goes_missed'] );


      if ( ! empty( $current_game ) ) {
        if ( $nxt_player ){
            error_log(" RC>>>> @@@@@@@ move to next player" , 0);
          $game['players']             = $current_game['players'];
          $game['next_turn_token']     = $current_game['new_token'];
          $game['current_player_name'] = $current_game['current_player_name'];
          $game['current_player']      = $current_game['current_player'];
          $game['player_index']        = $current_game['player_index'];
          $game['event']               = $event_msg ;
          // move token
          // get the  next player index , to set the token_pointer

          // get the name of the next player and set it

        } else {
            error_log(" RC>>>> @@@@@@@ dont MOVE player" , 0);
        }
      //  echo "players cards";
        $updated_players_cards = $this->get_players_cards( $player_id, $game['players'] );
        if ( count( $updated_players_cards['cards'] ) < 1 ) {
          $game['winner_id'] = $player_id;
          $winner            = $player_id;
          $game['event']     = $current_players_name . ' Has won !!!! ';
          if ( count( $game['players'] > 1 )  ) {
            $game['winners'] .= $current_players_name . ' ' ;
            // write to game board
            $gameboard            = $this->load( 'Black_Jack_Games');
            $gameboard['winners'] = $game['winners'];
            $this->update_board( $session_id, $gameboard );

            $game['players']  = $this->remove_player(  $player_id, $game  );
          }
        
        }

        $this->save( $session_id, $game );
        // get the players cards for response
        $response = rest_ensure_response(  array( $updated_players_cards , $winner, 'valid' )  );
        $response->header( 'Content-Type', "application/json" );
        $response->header( 'Access-Control-Allow-Origin' , "*");
        return $response;
      } else  {
        echo "Error cant save session !!!";
      }
      // if reversals
      // if miss a goes
      // move token to next player
    //  $this->save( $session_id, $game );

      //return 'err';
    } else{
      //echo "no val?";
    //  $this->save( $session_id, $game );
      //print_r( $game );
      //echo "here";
      $msg .= $submit_hand['msg'];

    }

  // if validated
  // set the games current values suit, value pick up or reversals
  // remove card totol from player
  // generate a new next turn and pass it to next player


} else {
  $msg = "Invalid token or game session !!!!";
}
//  $this->save( $session_id, $game );
  $response = rest_ensure_response(  array( array() , '', 'invalid', $msg )  );
  $response->header( 'Content-Type', "application/json" );
  $response->header( 'Access-Control-Allow-Origin' , "*");
  return $response;
//  return "Not your turn !!!!!!";
}
/**
 * This function is where we register our routes for our example endpoint.
 */
public function  prefix_register_example_routes(){

  // card itnery get_cards_in_pack
  register_rest_route( 'black-jack/v1', '/carditenery', array(
    // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
    'methods'  => 'GET',
    // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
    'callback' => array( $this,'get_cards_in_pack' ),
    'permission_callback' => '__return_true'
) );
   // pick up card setgamedata
   register_rest_route( 'black-jack/v1', '/pickupcard', array(
       // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
       'methods'  => 'GET',
       // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
       'callback' => array( $this,'pick_up_card' ),
       'permission_callback' => '__return_true'
   ) );
   // pick up card setgamedata
   register_rest_route( 'black-jack/v1', '/setgamedata', array(
       // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
       'methods'  => 'GET',
       // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
       'callback' => array( $this,'setgamedata' ),
       'permission_callback' => '__return_true'
   ) );

   // get the list of games
   register_rest_route( 'black-jack/v1', '/get_game_list', array(
       // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
       'methods'  => 'GET',
       // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
       'callback' => array( $this,'getgamelist' ),
       'permission_callback' => '__return_true'
   ) );

    // submit players hand
    register_rest_route( 'black-jack/v1', '/submithand', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => 'GET',
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => array( $this,'submit_hand' ),
        'permission_callback' => '__return_true'
    ) );

    // create game
    register_rest_route( 'black-jack/v1', '/creategame', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => array( $this,'add_new_game_session'),
         'permission_callback' => '__return_true'
    ) );
    // add players add_player
    register_rest_route( 'black-jack/v1', '/addplayer', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => 'GET',
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => array( $this,'add_player' ),
        'permission_callback' => '__return_true'
    ) );
    //get game
    register_rest_route( 'black-jack/v1', '/getgame', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => 'GET',
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => array( $this,'getgame'),
         'permission_callback' => '__return_true'
    ) );
    register_rest_route( 'black-jack/v1', '/getgameupdate', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => 'GET',
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => array( $this,'get_game_update'),
         'permission_callback' => '__return_true'
    ) );

    register_rest_route( 'black-jack/v1', '/getplayertoken', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => 'GET',
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => array( $this,'get_player_token'),
         'permission_callback' => '__return_true'
    ) );
    register_rest_route( 'black-jack/v1', '/getplayer', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => 'GET',
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => array( $this,'get_player'),
         'permission_callback' => '__return_true'
    ) );

}
}

function start_controller (){
 $controller = new black_jack_controller();
 $controller->prefix_register_example_routes();
}

add_action( 'rest_api_init', 'start_controller' );
