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

  //Total Population Based
  private $rfCensusOption = 1;
  private $rfReachOption = 1;  
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

  /**
   * For finding all the media available in a geographical area.
   * 
   * @param $criteria
   *  search criteria for the media name
   */
  public function fetch_media($criteria, $params = []) {
    $data = [
      'parameterId' => 'media',
      'fetchMediaQuery' => array(
       'xmlns' => 'http://comscore.com/FetchMedia',
       'SearchCritera' => array(
          array('Critera' => 'Platform'),
          array('Critera' => 'Advertising'),
          array("ExactMatch" => false, 'Critera' => $criteria),
        ),
      ),
      'reportQuery' => array(
        'xmlns' => 'http://comscore.com/FetchMedia',
        'Parameter' => array(
          array('KeyId' => 'geo',  'Value' => $params['geo']), 
          array('KeyId' => 'timeType',  'Value' => $params['timeType']),
          array('KeyId' => 'timePeriod',  'Value' => $params['timePeriod']),
          array('KeyId' => 'mediaSetType',  'Value' => $params['mediaSetType']),
        ),
      )    
    ];
    $result = $this->soap_client->__soapCall("FetchMedia", array($data));
    if(!empty($result)) {
      return $result->FetchMediaResult->MediaItem;
    }
  }

  /**
   * For finding media metrix.
   * 
   * @param $media
   *  media array for which we need to find metrics
   * @param $params
   *  different dependency parameter for finding media report
   */
  public function submit_report($media, $params = []) {
    $data = [
      'query' => array(
        'Parameter' => array(
          // geography
          array('KeyId' => 'geo',  'Value' => $params['geo']), 
          // for all locations.
          array('KeyId' => 'loc',  'Value' => $params['loc']), 
          array('KeyId' => 'timePeriod',  'Value' => $params['timePeriod']), 
          array('KeyId' => 'targetGroup',  'Value' => $params['targetGroup']), 
          array('KeyId' => 'targetType',  'Value' => $params['targetType']), 
          array('KeyId' => 'rfCensusOption',  'Value' => $this->rfCensusOption),
          array('KeyId' => 'rfReachOption',  'Value' => $this->rfReachOption),
        ),
        'RFInput' => array(),
      )
    ];
    foreach($media as $medium) {
      $data['query']['Parameter'][] = array(
        'KeyId' => 'media',  
        'Value' => $medium['comscore_id'],
      );
      $data['query']['RFInput'][] = array(
        "MediaId" => $medium['comscore_id'],
        "TargetId" => $this->target_Id, 
        "Duration" => $medium['duration'],
        "Impressions" => $medium['impressions'],
        "FrequencyCap" => $medium['frequency_cap'], 
        "ReachFactor" => $medium['reach_factor'], 
        "CPM" => $medium['cpm'],
      );
    }
    $data['query']['Parameter'][] = array('KeyId' => 'target', 'Value' => $params['ageGroup']);
    $result = $this->soap_client->__soapCall("SubmitReport", array($data));
    if(!empty($result)) {
      return $result->SubmitReportResult;
    }
  }

  /**
   * For fetching report from comScore.
   * 
   * @param $jobid
   *  jobid for which report need to be fetched.
   */
  public function fetch_report($jobid) {
    $data = [
      'jobId' => $jobid,
    ];
    $result = $this->soap_client->__soapCall("FetchReport", array($data));
    if(!empty($result)) {
      return $result->FetchReportResult;
    }
  }
  
  /**
   * For checking the job status in comScore.
   * 
   * @param $jobid
   *   jobid for which status need to checked.
   */
  public function ping_job_status($jobid) {
    $data = [
      'jobId' => $jobid,
    ];
    $result = $this->soap_client->__soapCall("PingReportStatus", array($data));
    if(!empty($result)) {
      return $result->PingReportStatusResult->Status;
    }
  }
}