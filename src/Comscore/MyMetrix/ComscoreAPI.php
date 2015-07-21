<?php

namespace Comscore\MyMetrix;

use Artisaninweb\SoapWrapper\Facades\SoapWrapper;


/**
 * comScore Web API class
 *
 * @package Comscore\MyMetrix
 * @author  Unnikrishnan Bhargavakurup
 */
Class ComscoreAPI {
  
  private $soap_response = null;
  
  /**
   * For getting a singleton instance of comScore.
   *
   * @param $user_name
   *  comscore username
   * @param $password
   *  comscore password
   */
  public static function instance($user_name, $password) {

    static $instance = null;

    if($instance === null) {
      $instance = new ComscoreAPI($user_name, $password);
      SoapWrapper::add(function ($service) {
          $service
              ->name('comscore')
              ->wsdl('https://api.comscore.com/ReachFrequency/CampaignRf.asmx?WSDL')
              ->trace(true)  
              ->options(['login' => $user_name, 'password' => $password]);                                                // Optional: (parameter: true/false)
      });
    }
    return $instance;
  }

  public function __construct() {
  }
}