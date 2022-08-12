<?php
/**
 * Plugin Name:       BJS
 * Description:       Creates a BJS server
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
 $path = '';
 if (is_link(dirname(__FILE__).'/classes/bjclasses.php'))
   {
      $path = readlink(dirname(__FILE__).'/classes/bjclasses.php');
   }
   require_once($path);
