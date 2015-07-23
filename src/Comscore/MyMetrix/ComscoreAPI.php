<?php

namespace Comscore\MyMetrix;

use SoapClient;


/**
 * comScore Web API class
 *
 * @package Comscore\MyMetrix
 * @author  Unnikrishnan Bhargavakurup
 */
Class ComscoreAPI {
  
  private $soap_client;
  
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
      $instance = new ComscoreAPI();
      $this->soap_client = $client = new SoapClient('https://api.comscore.com/ReachFrequency/CampaignRf.asmx?WSDL', array(
        'login' => $user_name,
        'password' => $password,
      ));
    }
    return $instance;
  } 

  // should not allow 'new' for creating instances
  public function __construct() {
  }

  /**
   * For finding values for different parameters in SOAP request.
   * 
   * @param $parameter_id
   *  Parameter for which values need to be identified
   * @param $params
   *  dependent parameters
   */
  public function discover_parameter_values($parameter_id, $params = []) {
    $data = [
      'parameterId' => $parameter_id,
    ];
    if(!empty($params)) {
      $data['query'] = array(
        'Parameter' => array(),
      );
      foreach ($params as $key => $value) {
        $data['query']['Parameter'][] = array('KeyId' => $key,  'Value' => $value);
      }
    }
    $result = $this->soap_client->__soapCall("DiscoverParameterValues", array($data));
    if(!empty($result)) {
      return $result->DiscoverParameterValuesResult->EnumValue;
    }
    return null;
  }
}