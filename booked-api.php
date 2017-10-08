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



        class Client {

              
                private $username;
              
                private $password;
              
                private $root;
                
                private $ch;
                
                private $isAuthenticated = false;

                
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
                public function __construct($_username, $_password, $_root = null, $_opts = array()) {
                    
                    if(is_resource($this->ch)){
                        return;
                    }
                    //Checks to see if username and password were supplied
                    if (! $_username || ! $_password) {

                                $this->goodBye('You must supply a username and password');
                        }
                        
                        $this->username = $_username;
                        $this->password = $_password;
                        
                    //Checks and sits API Booking Location, if not supplied the defualt will be used
                   if (isset ( $_root )) {

                            $this->root = $_root;
                             
                    }else{
                            
                            $this->root = config::BOOKEDWEBSERVICESURL;
                    }
                            
                        
                        try{
                            
                            $this->initCURL($_opts);
                            
                        } catch (Exception $ex) {
                                    
                            $this->goodBye( $ex->message );
                        }
                        
                        
                        if ($this->hasSessionExpired()){
                          
                            
                            if(!$this->doAuth()){
                                    
                                $this->isAuthenticated = false;
                                
                                return;
                                
                            }
                        
                            
                        }
                        
                        $this->isAuthenticated = true;

                       
                }
                
                public function isAuthenticated(){
                    return $this->isAuthenticated;
                }

                private function initCURL($options = array()){
                    
                     if (! isset ( $options ['timeout'] )) {
                                $options ['timeout'] = config::TIMEOUT;
                        }
                       
                        $this->ch = curl_init ();

                        if (isset ( $options ['CURLOPT_FOLLOWLOCATION'] ) && $options ['CURLOPT_FOLLOWLOCATION'] === true) {
                                curl_setopt ( $this->ch, CURLOPT_FOLLOWLOCATION, true );
                        }

                        curl_setopt ( $this->ch, CURLOPT_USERAGENT, 'BookedAPI-PHP/v0.1.1' );
                        curl_setopt ( $this->ch, CURLOPT_HEADER, false );
                        curl_setopt ( $this->ch, CURLOPT_RETURNTRANSFER, true );
                        curl_setopt ( $this->ch, CURLOPT_CONNECTTIMEOUT, 30 );
                        curl_setopt ( $this->ch, CURLOPT_TIMEOUT, $options ['timeout'] );
                                   
                }

                private function goodBye($message){
                    __destruct();
                    die ( $message );
                    
                }

                private function __destruct() {
                        if (is_resource ( $this->ch )) {
                                curl_close ( $this->ch );
                        }
                }

                private function doAuth($force = false) {
                        
                    if (!self::hasSessionExpired () && !$force) {
                                return true;
                        }

                        $endpoint = $this->root . config::AUTHENTICATE;

                        $params ['username'] = $this->username;

                        $params ['password'] = $this->password;

                        $result = $this->call ( $endpoint, $params, 'post' );

                        if (! $result) {
                                return false;
                        }

                        $this->createSession($result);

                        return true;
                }
                
                private function createSession($result = array()){
                    
                    $_SESSION ['bookedapi_sessionToken'] = ( string ) $result ['sessionToken'];

                    $_SESSION ['bookedapi_sessionExpires'] = ( string ) $result ['sessionExpires'];

                    $_SESSION ['bookedapi_userId'] = ( string ) $result ['userId'];
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
        public function getAllReservations() {

                $endpoint = $this->root . config::GETRESERVATIONS;

                $result = $this->call ( $endpoint, self::getAuthParams(), 'get' );

                if (! $result) {
                        return false;
                }
                return $result;
        }

        public function getReservation($referenceNumber = null, $userId = null, $resourceId = null, $scheduleId = null, $startDateTime = null, $endDateTime = null) {

        if (isset($referenceNumber)) {

                $endpoint = $this->root . config::GETRESERVATIONS . $referenceNumber;
        } else {

                if (isset($userId) || isset($resourceId) || isset($scheduleId) || isset($startDateTime) || isset($endDateTime)) {

                        $filters = '?userId=' . $userId . '&resourceId=' . $resourceId . '&scheduleId=' . $scheduleId . '&startDateTime=' . $startDateTime . '&endDateTime=' . $endDateTime;

                        $endpoint = $this->root . config::FILTERRESERVATION . $filters;
                } else {

                        return false;
                }
        }

        $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

        if (! $result) {
                return false;
        }

        return $result;
        }


// Schedule Functions

        /**
         * Gets all schedules
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function getAllSchedules(){

                $endpoint = $this->root .  config::SCHEDULES;


                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;
        }

        /**
         * Loads a specific schedule by id
         * @param integer $scheduleId
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function getSchedule($scheduleId){

                $endpoint = $this->root . config::SCHEDULES . $scheduleId;
                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

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

                $endpoint = $this->root . config::SCHEDULES . $scheduleId . config::SLOTS;

                if($resourceId || $startDateTime || $endDateTime){

                        $endpoint = $endpoint . "?resourceId=" . $resourceId;
                        $endpoint = $endpoint . "&startDateTime=" . $startDateTime;
                        $endpoint = $endpoint . "&endDateTime=" . $endDateTime;
                }


                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;


        }


        // Resource Functions

        /**
         * if $resourceId is null, will return all resources
         * otherwise will return the resource with the id of $resourceId
         * @param integer $resourceId
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function getResource($resourceId = null) {

            if (isset($resourceId) && is_int ($resourceId)) {

                        $endpoint = $this->root . config::GETRESOURCES . $resourceId;
                } else {

                        return false;
                }

                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;
        }
        
        public function getAllResources() {

           $endpoint = $this->root . config::GETRESOURCES . '/';
               
           $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

           if (! $result) {
               return false;
            }

            return $result;
        }

        /**
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function getResourceStatuses(){

                $endpoint = $this->root . config::GETRESOURCES . '/' . STATUS / '/';

                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;

        }

        /**
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function getResourceStatusReasons(){

                $endpoint = $this->root .config::GETRESOURCES . '/' . config::STATUS . '/' . config::STATUSREASONS;

                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;
        }

        /**
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function getResourceTypes(){

                $endpoint = $this->root . config::GETRESOURCES . config::RESOURCETYPES;

                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;

        }

        public function createResource($resourceObject){

                return getResponse(config::GETRESOURCES, $resourceObject);

        }

        private function getResponse($_endpoint, $_createThis, $_opt = null){


             $endpoint = $this->root . $_endpoint;

             if(!isset($_opt['method'])){
             $_opt[] = [
                 'method' => 'post',
                 'autherized' => isAuthenticated(),
             ];

             }

            return $this->call( $endpoint, $_createThis, $_opt);
      }


        public function updateResource($resourceId, $resourceObject){

            if(isset($resourceId) && isset($resourceObject)){

                $endpoint = $this->root . config::GETRESOURCES . $resourceId;

                $result = $this->call( $endpoint, $resourceObject, 'post', true);

                if (! $result) {
                    return false;
                }

                return $result;
            }

               return false;

        }

        /**
         * @param integer $resourceId
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function deleteResource($resourceId){

            if(isset($resourceId)){

                $endpoint = $this->root . config::GETRESOURCES. $resourceId;

                $result = $this->call ( $endpoint, null, 'delete', true);

                if (! $result) {
                        return false;
                }

                return $result;
            }

            return false;

        }

        // Accessory Functions

        /**
         * Gets all accessories if $accessoryId is null
         * otherwise if $accessoryId is a valid integer
         * the function will return just that accessory.
         * Will return false if accessory id or accessories don't exist.
         *
         * @param integer $accessoryId        	
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function getAccessory($accessoryId) {

                if (isset( $accessoryId )) {

                        $endpoint = $this->root . config::GETACCESSORY . '/' . $accessoryId;
                } else {

                        return false;
                }
                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;
        }
        
         public function getAllAccessories() {

            $endpoint = $this->root . config::GETACCESSORY ;
             
                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;
        }

        // Attribute Functions

        /**
         * @param array $attibuteObject
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function createCustomAttribute($attibuteObject){

                return getResponse(config::GETATTRIBUTE, $attibuteObject);
        }

        /**
         * @param integer $attributeId
         * @param array $attibuteObject
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function updateCustomAttribute($attributeId, $attibuteObject){

               $endpoint = $this->root . config::GETATTRIBUTE . $attributeId;

                $result = $this->call ( $endpoint, $attibuteObject, 'post', true);

                if (! $result) {
                        return false;
                }

                return $result;

        }

        /**
         * @param integer $attributeId
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function deleteCustomAttribute($attributeId){

               
                $endpoint = $this->root . config::GETATTRIBUTE . $attributeId;

                $result = $this->call ( $endpoint, null, 'delete', true);

                if (! $result) {
                        return false;
                }

                return $result;

        }

        /**
         *
         * @param integer $categoryId        	
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function getCategoryAttributes($categoryId) {

                if (! is_int ( $categoryId )) {
                        return false;
                }

                if ($categoryId != ATT_CAT_RESERVATION || $categoryId != ATT_CAT_USER || $categoryId != ATT_CAT_RESOURCE) {

                        return false;
                }

                if(!$this->isAuthenticated()) 
                     return false;

                $endpoint = $this->root . config::GETCATATTRIBUTE . $categoryId;

                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;
        }

        /**
         *
         * @param integer $attributeId        	
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function getAttribute($attributeId) {

            if (! is_int ( $attributeId )) {
                        return false;
                }

                $endpoint = $this->root . config::GETATTRIBUTE . $attributeId;

                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;
        }

        /**
         *
         * @param string $dateTime        	
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function getAvailability($dateTime = null) {

                if ($dateTime == null) {

                        $endpoint = $this->root . config::GETAVAILABILITY;
                } else {

                        $endpoint = $this->root . config::GETAVAILABILITY . '?dateTime=' . urlencode($dateTime);
                }

                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;
        }

        /**
         *
         * @param integer $groupId        	
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function getGroups($groupId = null) {

                if (isset($groupId) && is_int ( $groupId )) {

                        $endpoint = $this->root . config::GETGROUPS . $groupId;
                } else {

                        $endpoint = $this->root . config::GETGROUPS;
                }

                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;
        }

        /**
         *
         * @param array $reservationObject        	
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function createReservation($reservationObject) {

                return $this->getResponse(config::RESERVATIONS, $reservationObject);
        }


        public function approveReservation($referenceNumber){

                $endpoint = $this->root . config::RESERVATIONS . $referenceNumber . '/Approval';

                $result = $this->call ( $endpoint, 'post', null, true );

                if (! $result) {
                        return false;
                }

                return $result;
        }
        /**
         * @param string $referenceNumber
         * @param array $updatedReservationObject
         * @param string $updateScope
         * @return boolean|Ambigous <boolean, mixed>
         */
        public function updateReservation($referenceNumber, $updatedReservationObject, $updateScope = null) {

            // updateScope are this|full|future
            $_options = array(
                'scope'=> $updateScope,
                'segment' => $referenceNumber,
                'method'=>'post',
               );

             return update_delete(config::RESERVATIONS,$updatedReservationObject, $_options);

        }

        public function deleteReservation($referenceNumber, $updateScope = null){
                //this|full|future

               $_options = array(
                'scope'=> $updateScope,
                'segment' => $referenceNumber,
                'method'=>'delete',
               );

               return update_delete(config::RESERVATIONS,null,$_options);


        }

        private function update_delete($_endpoint,$_updateObject, $_updateopt = array()){

                $url = $this->root . $_endpoint . $_updateopt['segment'];

                $endpoint = hasScope($_updateopt['scope']) ? $url  . '?updateScope=' . $_updateopt['scope'] : $url ;

                $result = $this->call( $endpoint, $_updateObject, $_updateopt, true );

                if (! $result) {
                        return false;
                }

                return $result;
        }


        public function createUser($userObject){

               return   $this->getResponse(USERS, $userObject);
        }

        public function updateUser($userId, $userObject){

                $endpoint = $this->root . config::USERS . $userId;

                $result = $this->call ( $endpoint, $userObject, 'post', true);

                if (! $result) {
                        return false;
                }

                return $result;
        }

        public function getAllUsers($username = null, $email = null, $firstName = null, $lastName = null, $phone = null, $organization = null){
                if (! self::isAuthenticated ()) {
                       return false;
                }
                if ($username || $email || $firstName || $lastName || $phone || $organization){
                        $username = urlencode($username);
                        $email = urlencode($email);
                        $firstName = urlencode($firstName);
                        $lastName = urlencode($lastName);
                        $phone = urlencode($phone);
                        $organization = urlencode($organization);
                        $endpoint = $this->root . config::USERS . '?username=' . $username . '&email=' . $email . '&firstName=' . $firstName . '&lastName=' . $lastName . '&phone=' . $phone . '&organization=' . $organization;
                }else{
                $endpoint = $this->root . config::USERS . '/';
                }

                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;
        }

        public function getUser($userId){

             if(!$this->isAuthenticated()) 
               return false;

                $endpoint = $this->root . config::USERS . $userId;


                $result = $this->call ( $endpoint, self::getAuthParams (), 'get' );

                if (! $result) {
                        return false;
                }

                return $result;
        }

        public function deleteUser($userId){

                  if(!$this->isAuthenticated()) 
               return false;

                $endpoint = $this->root . config::USERS . $userId;

                $result = $this->call ( $endpoint, null, 'delete', true);

                if (! $result) {
                        return false;
                }

                return $result;
        }


        private function hasScope($scope){

                if ($scope == 'this' || $scope == 'full' || $scope == 'future'){
                        return true;
                }
                return false;
        }
        /**
         * @return boolean|number
         */
        public function getCurrentUserId() {

           return ( int ) $_SESSION ['bookedapi_userId'];
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
        private function call($endpoint, $params = null, $method = null, $postAuth = false) {

                $ch = $this->ch;

                if ($postAuth) {

                    if (!self::isAuthenticated) {
                            return false;
                    }
                    $this->setAuthHttpHeader ( $_SESSION ['bookedapi_sessionToken'], $_SESSION ['bookedapi_userId'] );
                }

                curl_setopt ( $ch, CURLOPT_URL, $endpoint );

                if (!isset($method)){
                    return false;
                }

                switch ($method) {
                    case 'post':

                        $header = array ('Content-Type: application/json');
                        curl_setopt ( $this->ch, CURLOPT_POST, true );
                        curl_setopt ( $this->ch, CURLOPT_HTTPGET, false );
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header);
                        break;

                    case 'delete':

                        curl_setopt ( $this->ch, CURLOPT_POST, false );
                        curl_setopt ( $this->ch, CURLOPT_HTTPGET, false );
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                        break;

                    case 'get':

                        curl_setopt ( $this->ch, CURLOPT_POST, false );
                        curl_setopt ( $this->ch, CURLOPT_HTTPGET, true );
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                        $this->setAuthHttpHeader ( $params ['SessionToken'], $params ['UserId'] );
                        break;

                    default:
                        return false;

                }


                if (isset($params)){
                                $params = json_encode ( $params );
                                curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
                }

                $response_body = curl_exec ( $ch );

                $info = curl_getinfo ( $ch );

                if (curl_error ( $ch )) {
                        return json_decode ( $info, true );
                }

                if (floor ( $info ['http_code'] / 100 ) >= 4) {
                        return false;
                }

                return json_decode ( $response_body, true );
        }

        /**
         *
         * @param string $SessionToken        	
         * @param integer $UserId        	
         */
        private function setAuthHttpHeader($SessionToken, $UserId) {

            $authHeader = array(
                'X-Booked-SessionToken: ' . $SessionToken,
                'X-Booked-UserId: ' . $UserId,
            );

            curl_setopt ( $this->ch, CURLOPT_HTTPHEADER, $authHeader);
        }

        /**
         *
         * @return boolean
         */
        private static function hasSessionExpired() {
                date_default_timezone_set(YOURTIMEZONE);
                if(!isset($_SESSION ['bookedapi_sessionExpires'] )){
                    return true;
                }
                //$date1 = new DateTime ( $_SESSION ['bookedapi_sessionExpires'], new DateTimeZone ( YOURTIMEZONE ) );
                $date1 = new DateTime ( $_SESSION ['bookedapi_sessionExpires'] );

                //$date2 = new DateTime ( date ( DATE_ISO8601, time () ), new DateTimeZone ( YOURTIMEZONE ) );
                $date2 = new DateTime ( date ( DATE_ISO8601, time () ) );

                $minutesInterval = date_interval_create_from_date_string ( '1 minute' );

                $date1->sub ( $minutesInterval );

                if ((! $_SESSION ['bookedapi_sessionToken'] == null) && ($date2 <= $date1)) {
                        return false;
                }

                return true;
        }

        /**
         *
         * @return array
         */
        private static function getAuthParams() {
                $params ['SessionToken'] = $_SESSION ['bookedapi_sessionToken'];
                $params ['UserId'] = $_SESSION ['bookedapi_userId'];
                return $params;
        }

        /**
         *
         * @param integer $userId        	
         * @param string $title        	
         * @param integer $resourceId        	
         * @param array $resourcesObject        	
         * @param array $accessoriesObject        	
         * @param array $customAttributesObject        	
         * @param string $description        	
         * @param string $startDateTime        	
         * @param string $endDateTime        	
         * @param array $recurrenceRuleObject        	
         * @param array $inviteesObject        	
         * @param array $participantsObject        	
         * @param array $startReminderObject        	
         * @param array $endReminderObject        	
         * @return array
         */
        public static function buildReservationObject($userId, $title, $resourceId, $resourcesObject = array (), $accessoriesObject = array (), $customAttributesObject = array (), $description = null, $startDateTime, $endDateTime, $recurrenceRuleObject = array (), $inviteesObject = array (), $participantsObject = array (), $startReminderObject = array (), $endReminderObject = array ()) {
                $reservationObject = array (
                                'accessories' => $accessoriesObject,
                                'customAttributes' => $customAttributesObject,
                                'description' => ( string ) $description,
                                'endDateTime' => $endDateTime,
                                'startDateTime' => $startDateTime,
                                'invitees' => $inviteesObject,
                                'participants' => $participantsObject,
                                'resourceId' => ( int ) $resourceId,
                                'resources' => $resourcesObject,
                                'recurrenceRule' => $recurrenceRuleObject,
                                'title' => ( string ) $title,
                                'userId' => ( int ) $userId,
                                'startReminder' => $startReminderObject,
                                'endReminder' => $endReminderObject 
                );

                return $reservationObject;
        }
        public static function buildAccessoryObject($accessoryId, $quantityRequested) {
                $ao = array (
                                'accessoryId' => ( int ) $accessoryId,
                                'quantityRequested' => ( int ) $quantityRequested 
                );

                return $ao;
        }
        public static function buildAccessoriesObject($accessories = array()) {
                return $accessories;
        }
        public static function buildAttributeObject($attributeId, $attributeValue) {
                $atto = array (
                                'attributeId' => ( int ) $attributeId,
                                'attributeValue' => ( string ) $attributeValue 
                );
                return $atto;
        }
        public static function buildAttributesObject($attributes = array()) {
                return $attributes;
        }
        public static function buildInviteeObject($inviteeId) {
                $i = ( int ) $inviteeId;

                return $i;
        }
        public static function buildInviteesObject($invitees = array()) {
                return $invitees;
        }
        public static function buildParticipantObject($participantId) {
                $i = ( int ) $participantId;

                return $i;
        }
        public static function buildParticipantsObject($participants = array()) {
                return $participants;
        }
        public static function getResourceObject($resourceId) {
                $r = ( int ) $resourceId;

                return $r;
        }
        public static function buildResourcesObject($resources = array()) {
                $resources;

                return $resources;
        }
        public static function buildReminderObject($value, $interval) {

                // interval = hours or minutes or days
                $ro = array (
                                'value' => ( int ) $value,
                                'interval' => ( string ) $interval 
                );

                return $ro;
        }

        // recurrenceRule
        // {"type":"daily|monthly|none|weekly|yearly","interval":3,"monthlyType":"dayOfMonth|dayOfWeek|null","weekdays":[0,1,2,3,4,5,6],"repeatTerminationDate":"2015-09-16T20:51:36-0700"}
        public static function buildRecurrenceRuleObject($type, $interval, $monthlyType, $weekdays = array(), $repeatTerminationDate) {
                $rule = array (
                                'type' => ( string ) $type,
                                'interval' => ( int ) $interval,
                                'monthlyType' => ( string ) $monthlyType,
                                'weekdays' => $weekdays,
                                'repeatTerminationDate' => $repeatTerminationDate 
                );

                return $rule;
        }

        //{"label":"attribute name","type":"Allowed values for type: 4 (checkbox), 2 (multi line), 3 (select list), 1 (single line)","categoryId":"Allowed values for category: 1 (reservation), 4 (resource), 5 (resource type), 2 (user)","regex":"validation regex","required":true,"possibleValues":["possible","values","only valid for select list"],"sortOrder":100,"appliesToId":10}

        /**
         * @param string $label
         * @param integer $type
         * @param integer $categoryId
         * @param integer $appliesToId
         * @param integer $sortOrder
         * @param string $regex
         * @param boolean $required
         * @param array $possibleValues
         * @return array 
         */
        public static function buildAttibuteObject(
                                                                                        $label,
                                                                                        $type, 
                                                                                        $categoryId, 
                                                                                        $appliesToId, 
                                                                                        $sortOrder = 0, 
                                                                                        $regex = null,
                                                                                        $required = false, 
                                                                                        $possibleValues = array()){

                $attributeObject = array (
                                                        'label' => (string) $label,
                                                        'type'  => (int) $type,
                                                        'categoryId' => (int) $categoryId,
                                                        'appliesToId' => (int) $appliesToId,
                                                        'sortOrder' => (int) $sortOrder,
                                                        'regex' => (string) $regex,
                                                        'required' => (boolean) $required,
                                                        'possibleValues' => $possibleValues);

                return $attributeObject;

        }

        // {"name":"resource name","location":"location","contact":"contact information","notes":"notes","minLength":"1d0h0m","maxLength":"3600","requiresApproval":true,"allowMultiday":true,"maxParticipants":100,"minNotice":"86400","maxNotice":"0d12h30m","description":"description","scheduleId":10,"autoAssignPermissions":true,"customAttributes":[{"attributeId":1,"attributeValue":"attribute value"}],"sortOrder":1,"statusId":1,"statusReasonId":2,"resourceTypeId":1}
        public static function buildResourceObject(
                                                                                $name,
                                                                                $location = null,
                                                                                $contact = null,
                                                                                $notes = null,
                                                                                $minLength, // in "seconds" or "1d2h3m" format
                                                                                $maxLength, // in "seconds" or "1d2h3m" format
                                                                                $requiresApproval = false,
                                                                                $allowMultiday = false,
                                                                                $maxParticipants,
                                                                                $minNotice, // in "seconds" or "1d2h3m" format
                                                                                $maxNotice, // in "seconds" or "1d2h3m" format
                                                                                $description = null,
                                                                                $scheduleId,
                                                                                $autoAssignPermissions = true,
                                                                                $customAttributesObject = array (), // {"attributeId":1,"attributeValue":"attribute value"}
                                                                                $sortOrder = 0,
                                                                                $statusId = null,
                                                                                $statusReasonId = null,
                                                                                $resourceTypeId = null){

                $resourceObject = array (
                                                                                'name' => (string) $name,
                                                                                'location' => (string) $location,
                                                                                'contact' => (string) $contact,
                                                                                'notes' => (string) $notes,
                                                                                'minLength' => (string) $minLength, // in "seconds" or "1d2h3m" format
                                                                                'maxLength' => (string) $maxLength, // in "seconds" or "1d2h3m" format
                                                                                'requiresApproval' => (boolean) $requiresApproval,
                                                                                'allowMultiday' => (boolean) $allowMultiday,
                                                                                'maxParticipants' => (int) $maxParticipants,
                                                                                'minNotice' => (string) $minNotice, // in "seconds" or "1d2h3m" format
                                                                                'maxNotice' => (string) $maxNotice, // in "seconds" or "1d2h3m" format
                                                                                'description' => (string) $description,
                                                                                'scheduleId' => (int) $scheduleId,
                                                                                'autoAssignPermissions' => (boolean) $autoAssignPermissions,
                                                                                'customAttributes' => $customAttributesObject, 
                                                                                'sortOrder' => (int) $sortOrder,
                                                                                'statusId' => (int) $statusId,
                                                                                'statusReasonId' => (int) $statusReasonId,
                                                                                'resourceTypeId' => (int) $resourceTypeId);

                return $resourceObject;
        }

        // {"attributeId":1,"attributeValue":"attribute value"}
        public static function buildCustomAttributeObject($attributeId,$attributeValue){

                $customAttributeObject = array (
                                'attributeId' => (int) $attributeId,
                                'attributeValue' => (string) $attributeValue);

                return $customAttributeObject;
        }

        //{"password":"unencrypted password","language":"en_us","firstName":"first","lastName":"last","emailAddress":"email@address.com","userName":"username","timezone":"America\/Chicago","phone":"123-456-7989","organization":"organization","position":"position","customAttributes":[{"attributeId":99,"attributeValue":"attribute value"}],"groups":[1,2,4]}
        public static function buildUserobject( $password, 
                                                                                        $language = 'en_us',
                                                                                        $firstName = null,
                                                                                        $lastName = null,
                                                                                        $emailAddress = null,
                                                                                        $userName,
                                                                                        $timezone = YOURTIMEZONE,
                                                                                        $phone = null,
                                                                                        $organization = null,
                                                                                        $position = null,
                                                                                        $customAttributesObject = array(),
                                                                                        $groupsObject = array ()) {
                $userobject = array (
                                        'password' => (string) $password,
                                        'language' => (string) $language,
                                        'firstName'=> (string) $firstName,
                                        'lastName' => (string) $lastName,
                                        'emailAddress' => (string) $emailAddress,
                                        'userName' => (string) $userName,
                                        'timezone' => (string) $timezone,
                                        'phone' => (string) $phone,
                                        'organization' => (string) $organization,
                                        'position' => (string) $position,
                                        'customAttributes' => $customAttributesObject,
                                        'groups' => $groupsObject);

                return $userobject;
        }


        }


