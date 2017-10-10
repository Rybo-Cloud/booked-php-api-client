<?php

/**
 * Title: booked-php-api-client
 * Ver: 0.2.1 Alpha
 * By: Ryan C Crawford
 * Email: ryanccrawford@live.com
 * 
 * This file is part of Booked PHP Client Library. 
 * There are other files that make up the whole library
 * and that are dependent on this file or that this file is dependent on.
 * 
 * 
 *   
 */

namespace BookedAPI;

use DateTime;
use Exception;

class Client{

    private $username;
    private $password;
    private $root;
    private $ch;
    private $isAuthenticated = false;
    private $retry = 0;
    /**
     * Booked client constructor
     *
     * @param string $_username
     *        	Required $user - booked admin/user name
     * @param string $_password
     *        	Required $password - booked admin/user password
     * @param string $_root
     *        	Optional $root - your booked web services URL / if not set here class will use BOOKEDWEBSERVICESURL in bookedapiconfig.php file
     * @param array $_opts
     *        	Optional $opts - an array of options to set for CURL
     */
    public function __construct($_username, $_password, $_root = null, $_opts = array()){

        if(is_resource($this -> ch)){
            return;
        }
        //Checks to see if username and password were supplied
        if( ! $_username || ! $_password){

            $this -> goodBye('You must supply a username and password');
        }

        $this -> username = $_username;
        $this -> password = $_password;

        //Checks and sits API Booking Location, if not supplied the defualt will be used
        if(isset($_root)){

            $this -> root = $_root;
        }else{

            $this -> root = config::BOOKEDWEBSERVICESURL;
        }


        try{

            $this -> initCURL($_opts);
        }catch(Exception $ex){

            $this -> goodBye($ex -> message);
        }


        if($this -> hasSessionExpired()){


            if( ! $this -> doAuth()){

                $this -> isAuthenticated = false;

                return;
            }
        }

        $this -> isAuthenticated = true;

    }

    public function isAuthenticated(){
        return $this -> isAuthenticated;

    }

    private function initCURL($options = array()){

        if( ! isset($options ['timeout'])){
            $options ['timeout'] = config::TIMEOUT;
        }

        $this -> ch = curl_init();

        if(isset($options ['CURLOPT_FOLLOWLOCATION']) && $options ['CURLOPT_FOLLOWLOCATION'] === true){
            curl_setopt($this -> ch, CURLOPT_FOLLOWLOCATION, true);
        }

        curl_setopt($this -> ch, CURLOPT_USERAGENT, 'BookedAPI-PHP/v0.1.1');
        curl_setopt($this -> ch, CURLOPT_HEADER, false);
        curl_setopt($this -> ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this -> ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this -> ch, CURLOPT_TIMEOUT, $options ['timeout']);

    }

    private function goodBye($message){
        __destruct();
        die($message);

    }

    private function __destruct(){
        if(is_resource($this -> ch)){
            curl_close($this -> ch);
        }

    }

    private function doAuth($force = false){

        if( ! self::hasSessionExpired() && ! $force){
            return true;
        }

        $endpoint = $this -> root . config::$routes[__FUNCTION__];

        $params ['username'] = $this -> username;

        $params ['password'] = $this -> password;

        $result = $this -> call($endpoint, $params, 'post');

        if( ! $result){
            return false;
        }

        $this -> createSession($result);

        return true;

    }

    private function createSession($result = array()){

        $_SESSION ['bookedapi_sessionToken'] = (string) $result ['sessionToken'];

        $_SESSION ['bookedapi_sessionExpires'] = (string) $result ['sessionExpires'];

        $_SESSION ['bookedapi_userId'] = (string) $result ['userId'];

    }

// Reservation Functions

    /**
     * Gets all reservations if $referenceNumber is not set, otherwise
     * returns only the reservation $referenceNumber refers to.
     *
     * @param string $referenceNumber
     *        	Reference Number of Reservation
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function getAllReservations(){

        $endpoint = $this -> root . config::$routes[__FUNCTION__];

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

      
        return $result;

    }

    //$options is array of $referenceNumber = null, $userId = null, $resourceId = null, $scheduleId = null, $startDateTime = null, $endDateTime = null
    public function getReservation($referenceNumber, $options = array()){
  
        $endpoint = $this->root . tr_replace(':referenceNumber', $referenceNumber, config::$routes[__FUNCTION__]);
       
        if(isset($options) && count($options) > 0){
  
            $endpoint .= $this->buildFilters($options);
      
        }
  
        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }

// Schedule Functions

    /**
     * Gets all schedules
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function getAllSchedules(){

        $endpoint = $this -> root . config::$routes[__FUNCTION__];

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');
     
        return $result;

    }

    /**
     * Loads a specific schedule by id
     * @param integer $scheduleId
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function getSchedule($scheduleId){

        $endpoint = $this -> root . str_replace(':scheduleId', $scheduleId, config::$routes[__FUNCTION__]);
        
        $result   = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }

    /**
     * Loads slots for a specific schedule 
     * 
     * Optional query string parameters: resourceId, startDateTime, endDateTime. 
     * If no dates are provided the default schedule dates will be returned. 
     * If dates do not include the timezone offset, the timezone of the doAuthd user will be assumed.
     * 
     * @param integer $scheduleId
     * @param integer $resourceId
     * @param string $startDateTime
     * @param string $endDateTime
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function getSlots($scheduleId, $resourceId = null, $startDateTime = null, $endDateTime = null){

        $endpoint = $this -> root . str_replace(':scheduleId', $scheduleId, config::$routes[__FUNCTION__]);

            $endpoint .= $this->buildFilters(array(
                'resourceId'=>$resourceId,
                'startDateTime'=>$startDateTime,
                'endDateTime'=>$endDateTime,));
  
        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }

    // Resource Functions

    /**
     * if $resourceId is null, will return all resources
     * otherwise will return the resource with the id of $resourceId
     * @param integer $resourceId
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function getResource($resourceId){

        $endpoint = $this -> root . str_replace(':resourceId', $resourceId, config::$routes[__FUNCTION__]);
  
        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }

    public function getAllResources(){

        $endpoint = $this -> root . config::$routes[__FUNCTION__];

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }
    
    public function getResourceAvailability($resourceId){
       
        $endpoint = $this -> root . str_replace(':resourceId', $resourceId, config::$routes[__FUNCTION__]);
  
        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;
        
    }

    /**
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function getResourceStatuses(){

        $endpoint = $this -> root . config::$routes[__FUNCTION__];

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }

    /**
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function getResourceStatusReasons(){

        $endpoint = $this -> root . config::$routes[__FUNCTION__];

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }


    public function createResource($resourceObject){

        return getResponse( config::$routes[__FUNCTION__], $resourceObject);

    }

    private function getResponse($_endpoint, $_createThis, $_opt = null){


        $endpoint = $this -> root . $_endpoint;

        if( ! isset($_opt['method'])){
            $_opt[] = [
                'method'     => 'post',
                'autherized' => isAuthenticated(),
            ];
        }

        return $this -> call($endpoint, $_createThis, $_opt);

    }

    public function updateResource($resourceId, $resourceObject){

        
            $endpoint = $this -> root . str_replace(':resourceId', $resourceId, config::$routes[__FUNCTION__]);

            $result = $this -> call($endpoint, $resourceObject, 'post', true);

          return $result;
 

    }

    /**
     * @param integer $resourceId
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function deleteResource($resourceId){

        if(isset($resourceId)){

            $endpoint = $this -> root . str_replace(':resourceId', $resourceId, config::$routes[__FUNCTION__]);

            $result = $this -> call($endpoint, null, 'delete', true);
           
            return $result;
        }

        return false;

    }

    // Accessory Functions

   
    public function getAllAccessories(){

        $endpoint = $this -> root . config::$routes[__FUNCTION__];

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

       return $result;

    }

    // Attribute Functions

    /**
     * @param array $attibuteObject
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function createCustomAttribute($attibute){

        return getResponse(config::$routes[__FUNCTION__], $attibute);

    }

    /**
     * @param integer $attributeId
     * @param array $attibuteObject
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function updateCustomAttribute($attributeId, $attibuteObject){

        $endpoint = $this -> root . str_replace(':attributeId', $attributeId, config::$routes[__FUNCTION__]);

        $result = $this -> call($endpoint, $attibuteObject, 'post', true);

        return $result;

    }

    /**
     * @param integer $attributeId
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function deleteCustomAttribute($attributeId){


        $endpoint = $this -> root . str_replace(':attributeId', $attributeId, config::$routes[__FUNCTION__]);

        $result = $this -> call($endpoint, null, 'delete', true);

        return $result;

    }

    /**
     *
     * @param integer $categoryId        	
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function getCategoryAttributes($categoryId){

     
        if($categoryId != ATT_CAT_RESERVATION && $categoryId != ATT_CAT_USER && $categoryId != ATT_CAT_RESOURCE){

            return false;
        }

       $endpoint = $this -> root . str_replace(':categoryId', $categoryId, config::$routes[__FUNCTION__]);

       $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }

    /**
     *
     * @param integer $attributeId        	
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function getAttribute($attributeId){

        $endpoint = $this -> root . str_replace(':attributeId', $attributeId, config::$routes[__FUNCTION__]);

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }

    /**
     *
     * @param string $dateTime        	
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function getAvailability($dateTime = null){

        if(!isset($dateTime)){

            $endpoint = $this -> root . config::$routes[__FUNCTION__];
        }else{

            $endpoint = $this -> root . config::$routes[__FUNCTION__] . '?dateTime=' . urlencode($dateTime);
        }

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }

    /**
     *
     * @param integer $groupId        	
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function getGroup($groupId){

        $endpoint = $this -> root . str_replace(':groupId', $groupId, config::$routes[__FUNCTION__]);

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }
    
    
     public function getAllGroups(){

       $endpoint = $this -> root . config::$routes[__FUNCTION__];
      
       $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }

    public function checkInReservation($referenceNumber){
        
        $endpoint = $this -> root . str_replace(':referenceNumber', $referenceNumber, config::$routes[__FUNCTION__]);

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

        
    }
    
    public function checkOutReservation($referenceNumber){
        
        $endpoint = $this -> root . str_replace(':referenceNumber', $referenceNumber, config::$routes[__FUNCTION__]);

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }
    /**
     *
     * @param array $reservationObject        	
     * @return boolean|Ambigous <boolean, mixed>
     */
    public function createReservation($reservation = array()){

        return $this -> getResponse(config::$routes[__FUNCTION__], $reservation);

    }

    public function approveReservation($referenceNumber){

        $endpoint = $this -> root .  str_replace(':referenceNumber', $referenceNumber, config::$routes[__FUNCTION__]);

        $result = $this -> call($endpoint, 'post', null, true);

        return $result;

    }

    public function updateReservation($referenceNumber, $updatedReservation, $updateScope = null){

        // updateScope are this|full|future
        $_options = array(
            'scope'   => $updateScope,
            'segment' => $referenceNumber,
            'method'  => 'post',
        );

        return update_delete(config::$routes[__FUNCTION__], $updatedReservation, $_options);

    }

    public function deleteReservation($referenceNumber, $updateScope = null){
        //this|full|future

        $_options = array(
            'scope'   => $updateScope,
            'segment' => $referenceNumber,
            'method'  => 'delete',
        );

        return update_delete(config::$routes[__FUNCTION__], null, $_options);

    }

    private function update_delete($_endpoint, $_updateObject, $_updateopt = array()){

        $url = $this -> root . $_endpoint . $_updateopt['segment'];

        $endpoint = hasScope($_updateopt['scope']) ? $url . '?updateScope=' . $_updateopt['scope'] : $url;

        $result = $this -> call($endpoint, $_updateObject, $_updateopt, true);

        return $result;

    }

    public function createUser($user){

        return $this -> getResponse(config::$routes[__FUNCTION__], $user);

    }

     public function updatePassword($userId, $password){

        $endpoint = $this -> root . str_replace(':userId', $userId, config::$routes[__FUNCTION__]);

        $p = array('Password'=>$password);
        
        $result = $this -> call($endpoint, $p, 'delete', true);

        return $result;

    }

    
    public function updateUser($userId, $userObject){

        $endpoint = $this -> root . config::$routes[__FUNCTION__] . $userId;

        $result = $this -> call($endpoint, $userObject, 'post', true);

        return $result;

    }

    public function getAllUsers($options = array()){
       
        
        if(count($options)> 0){
           
            $endpoint     = $this -> root . config::$routes[__FUNCTION__] . $this->buildFilters($options);
        
            
        }else{
            
            $endpoint = $this -> root . config::$routes[__FUNCTION__];
        }

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }

    public function getUser($userId){

        $endpoint = $this -> root . str_replace(':userId', $userId, config::$routes[__FUNCTION__]);

        $result = $this -> call($endpoint, self::getAuthParams(), 'get');

        return $result;

    }

    public function deleteUser($userId){

        $endpoint = $this -> root . str_replace(':userId', $userId, config::$routes[__FUNCTION__]);

        $result = $this -> call($endpoint, null, 'delete', true);

        return $result;

    }

    private function hasScope($scope){

        if($scope == 'this' || $scope == 'full' || $scope == 'future'){
            return true;
        }
        return false;

    }

    /**
     * @return boolean|number
     */
    public function getCurrentUserId(){

        return (int) $_SESSION ['bookedapi_userId'];

    }

    public function signOut(){
        //TODO: Create signOut API CALL

    }

    /**
     *
     * @param string $endpoint        	
     * @param array $params        	
     * @param string $method        	
     * @return boolean|mixed
     */
    private function call($endpoint, $params = null, $method = null, $postAuth = false){

        $ch = $this -> ch;

        if($postAuth){

            if( ! self::isAuthenticated){
                return false;
            }
            $this -> setAuthHttpHeader($_SESSION ['bookedapi_sessionToken'], $_SESSION ['bookedapi_userId']);
        }

        curl_setopt($ch, CURLOPT_URL, $endpoint);

        if( ! isset($method)){
            return false;
        }

        switch($method){
            case 'post':

                $header = array('Content-Type: application/json');
                curl_setopt($this -> ch, CURLOPT_POST, true);
                curl_setopt($this -> ch, CURLOPT_HTTPGET, false);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                break;

            case 'delete':

                curl_setopt($this -> ch, CURLOPT_POST, false);
                curl_setopt($this -> ch, CURLOPT_HTTPGET, false);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;

            case 'get':

                curl_setopt($this -> ch, CURLOPT_POST, false);
                curl_setopt($this -> ch, CURLOPT_HTTPGET, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                $this -> setAuthHttpHeader($params ['SessionToken'], $params ['UserId']);
                break;

            default:
                return false;
        }


        if(isset($params)){
            $params = json_encode($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        $response_body = curl_exec($ch);

        $info = curl_getinfo($ch);

        if(curl_error($ch)){
            return json_decode($info, true);
        }

        if(floor($info ['http_code'] / 100) >= 4){
            switch($info['http_code']){
                case 401:
                   $exception = new Exception('Not logged in');
                    if($this->retry < config::NUMBEROFRETRIES){
                        $this->retry = $this->retry + 1;
                         sleep (config::TIMEBETWEENRETRIES );
                        $this->call($endpoint, $params, $method,true);
                        return 401;
                    }
                    
               break;
                case 501:
                   $exception = new Exception('Server Error');
                    return 501;
                break;
            default :
                $exception = new Exception('Error code ' . $info['http_code'] .'.');
                return 0;
                    }
            return false;
        }

        return json_decode($response_body, true);

    }

    /**
     *
     * @param string $SessionToken        	
     * @param integer $UserId        	
     */
    private function setAuthHttpHeader($SessionToken, $UserId){

        $authHeader = array(
            'X-Booked-SessionToken: ' . $SessionToken,
            'X-Booked-UserId: ' . $UserId,
        );

        curl_setopt($this -> ch, CURLOPT_HTTPHEADER, $authHeader);

    }

    /**
     *
     * @return boolean
     */
    private static function hasSessionExpired(){
        date_default_timezone_set(YOURTIMEZONE);
        if( ! isset($_SESSION ['bookedapi_sessionExpires'])){
            return true;
        }
        //$date1 = new DateTime ( $_SESSION ['bookedapi_sessionExpires'], new DateTimeZone ( YOURTIMEZONE ) );
        $date1 = new DateTime($_SESSION ['bookedapi_sessionExpires']);

        //$date2 = new DateTime ( date ( DATE_ISO8601, time () ), new DateTimeZone ( YOURTIMEZONE ) );
        $date2 = new DateTime(date(DATE_ISO8601, time()));

        $minutesInterval = date_interval_create_from_date_string('1 minute');

        $date1 -> sub($minutesInterval);

        if(( ! $_SESSION ['bookedapi_sessionToken'] == null) && ($date2 <= $date1)){
            return false;
        }

        return true;

    }

    /**
     *
     * @return array
     */
    private static function getAuthParams(){
        $params ['SessionToken'] = $_SESSION ['bookedapi_sessionToken'];
        $params ['UserId']       = $_SESSION ['bookedapi_userId'];
        return $params;

    }
    
    private function buildFilters($options){
        
         $filters = '?';
            
            foreach($options as $filter => $value){
                
                $filters .= isset($value) ? '&' . $filter . '=' . urlencode($value) : '';
                
            }

               return strlen($filters) > 1 ? $filters : '';
        
    }

}
