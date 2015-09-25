<?php
/**
 * Title: booked-php-api-client
 * Ver: 0.0.1 Beta
 * By: Ryan C Crawford
 * Email: ryan@mytrueserenity.com
 * 
 * This file is part of Booked PHP Client Libary. 
 * There are other files that make up the whole libary
 * and that are dependent on this file or that this file is dependent on.
 * 
 * 
 *   
 */
require_once 'bookedapiconfig.php';
class bookedAPIclient {
	
	/**
	 *
	 * @var string
	 */
	public $username;
	/**
	 *
	 * @var string
	 */
	public $password;
	/**
	 *
	 * @var string
	 */
	public $root = BOOKEDWEBSERVICESURL;
	/**
	 * cURL Resource
	 */
	private $ch;
	
	/**
	 * Booked client constructor
	 *
	 * @param string $user
	 *        	Required $user - booked admin/user name
	 * @param string $password
	 *        	Required $password - booked admin/user password
	 * @param string $root
	 *        	Optional $root - your booked web services url / if not set here class will use BOOKEDWEBSERVICESURL in bookedapiconfig.php file
	 * @param array $opts
	 *        	Optional $opts - an array of options to set for CURL
	 */
	public function __construct($username, $password, $root = null, $opts = array()) {
		if (! $username || ! $password) {
			
			die ( 'You must supply a username and password' );
		}
		
		if (isset ( $root )) {
			
			$this->root = $root;
		}
		
		$this->username = $username;
		$this->password = $password;
		
		if (! isset ( $opts ['timeout'] ) || ! is_int ( $opts ['timeout'] )) {
			$opts ['timeout'] = 600;
		}
		
		$this->ch = curl_init ();
		
		if (isset ( $opts ['CURLOPT_FOLLOWLOCATION'] ) && $opts ['CURLOPT_FOLLOWLOCATION'] === true) {
			curl_setopt ( $this->ch, CURLOPT_FOLLOWLOCATION, true );
		}
		
		curl_setopt ( $this->ch, CURLOPT_USERAGENT, 'TS_Booked-PHP/1.0.0' );
		curl_setopt ( $this->ch, CURLOPT_HEADER, false );
		curl_setopt ( $this->ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $this->ch, CURLOPT_CONNECTTIMEOUT, 30 );
		curl_setopt ( $this->ch, CURLOPT_TIMEOUT, $opts ['timeout'] );
	}
	
	/**
	 * Class destuctor called atomatically when processing completes
	 */
	public function __destruct() {
		if (is_resource ( $this->ch )) {
			curl_close ( $this->ch );
		}
	}
	
	/**
	 * Used to set $_SESSION var for authentification.
	 * There is no need to call this on your own because it's called atomatically by the class when needed.
	 *
	 * Returns true on success and false if call fails.
	 *
	 * @param boolean $force
	 * Set this to true to force a new autherization.
	 * @return boolean
	 *
	 */
	public function authenticate($force = false) {
		if (self::isAuthenticated () && $force == false) {
			return true;
		}
		
		$endpoint = $this->root . AUTHENTICATE;
		
		$params ['username'] = $this->username;
		
		$params ['password'] = $this->password;
		
		$result = $this->call ( $endpoint, $params, 'post' );
		
		if (! $result) {
			return false;
		}
		
		$_SESSION ['bookedapi_sessionToken'] = ( string ) $result ['sessionToken'];
		
		$_SESSION ['bookedapi_sessionExpires'] = ( string ) $result ['sessionExpires'];
		
		$_SESSION ['bookedapi_userId'] = ( string ) $result ['userId'];
		
		return true;
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
	public function getReservation($referenceNumber = null, $userId = null, $resourceId = null, $scheduleId = null, $startDateTime = null, $endDateTime = null) {
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		if ($referenceNumber != null) {
			
			$endpoint = $this->root . GETRESERVATIONS . $referenceNumber;
		} else {
			
			if ($userId != null || $resourceId != null || $scheduleId != null || $startDateTime != null || $endDateTime != null) {
				
				$filters = '?userId=' . $userId . '&resourceId=' . $resourceId . '&scheduleId=' . $scheduleId . '&startDateTime=' . $startDateTime . '&endDateTime=' . $endDateTime;
				
				$endpoint = $this->root . FILTERRESERVATION . $filters;
			} else {
				
				$endpoint = $this->root . GETRESERVATIONS;
			}
		}
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
		if (! $result) {
			return false;
		}
		return $result;
	}
	
	
	// Schedule Functions
	
	/**
	 * Loads all schedules
	 * @return boolean|Ambigous <boolean, mixed>
	 */
	public function getAllSchedules(){
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . SCHEDULES;
	
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
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
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . SCHEDULES . $scheduleId;
		
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
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
	 * If dates do not include the timezone offset, the timezone of the authenticated user will be assumed.
	 * 
	 * @param integer $scheduleId
	 * @param integer $resourceId
	 * @param string $startDateTime
	 * @param string $endDateTime
	 * @return boolean|Ambigous <boolean, mixed>
	 */
	public function getSlots($scheduleId, $resourceId = null, $startDateTime = null, $endDateTime = null){
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . SCHEDULES . $scheduleId . SLOTS;
		
		if($resourceId || $startDateTime || $endDateTime){
			
			$endpoint = $endpoint . "?resourceId=" . $resourceId;
			$endpoint = $endpoint . "&startDateTime=" . $startDateTime;
			$endpoint = $endpoint . "&endDateTime=" . $endDateTime;
		}
		
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
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
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		if ($resourceId != null && is_int ( $resourceId )) {
			
			$endpoint = $this->root . GETRESOURCES . $resourceId;
		} else {
			
			$endpoint = $this->root . GETRESOURCES;
		}
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
		if (! $result) {
			return false;
		}
		
		return $result;
	}
	
	/**
	 * @return boolean|Ambigous <boolean, mixed>
	 */
	public function getResourceStatuses(){
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
						
		$endpoint = $this->root . GETRESOURCES . STATUS;
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
		if (! $result) {
			return false;
		}
		
		return $result;
		
	}
	
	/**
	 * @return boolean|Ambigous <boolean, mixed>
	 */
	public function getResourceStatusReasons(){
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . GETRESOURCES . STATUS . STATUSREASONS;
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
		if (! $result) {
			return false;
		}
		
		return $result;
	}
	
	/**
	 * @return boolean|Ambigous <boolean, mixed>
	 */
	public function getResourceTypes(){
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . GETRESOURCES . RESOURCETYPES;
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
		if (! $result) {
			return false;
		}
		
		return $result;
		
	}
	
	public function createResource($resourceObject){
		

		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . GETRESOURCES;
		
		$result = $this->call ( $endpoint, $resourceObject, 'post', true);
		
		if (! $result) {
			return false;
		}
		
		return $result;
		
		
	}
	
	public function updateResource($resourceId, $resourceObject){
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . GETRESOURCES. $resourceId;
		
		$result = $this->call ( $endpoint, $resourceObject, 'post', true);
		
		if (! $result) {
			return false;
		}
		
		return $result;
		
	}
	
	/**
	 * @param integer $resourceId
	 * @return boolean|Ambigous <boolean, mixed>
	 */
	public function deleteResource($resourceId){
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . GETRESOURCES. $resourceId;
		
		$result = $this->call ( $endpoint, null, 'delete', true);
		
		if (! $result) {
			return false;
		}
		
		return $result;
		
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
	public function getAccessory($accessoryId = null) {
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		if (is_int ( $accessoryId )) {
			
			$endpoint = $this->root . GETACCESSORY . $accessoryId;
		} else {
			
			$endpoint = $this->root . GETACCESSORY;
		}
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
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
		
		
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . GETATTRIBUTE;
		
		$result = $this->call ( $endpoint, $attibuteObject, 'post', true);
		
		if (! $result) {
			return false;
		}
		
		return $result;
		
	}
	
	/**
	 * @param integer $attributeId
	 * @param array $attibuteObject
	 * @return boolean|Ambigous <boolean, mixed>
	 */
	public function updateCustomAttribute($attributeId, $attibuteObject){
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . GETATTRIBUTE . $attributeId;
		
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
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . GETATTRIBUTE . $attributeId;
		
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
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . GETCATATTRIBUTE . $categoryId;
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
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
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . GETATTRIBUTE . $attributeId;
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
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
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		if ($dateTime == null) {
			
			$endpoint = $this->root . GETAVAILABILITY;
		} else {
			
			$endpoint = $this->root . GETAVAILABILITY . '?dateTime=' . urlencode($dateTime);
		}
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
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
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		if ($groupId != null && is_int ( $groupId )) {
			
			$endpoint = $this->root . GETGROUPS . $groupId;
		} else {
			
			$endpoint = $this->root . GETGROUPS;
		}
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
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
		
		
		$endpoint = $this->root . RESERVATIONS;
		
		$result = $this->call ( $endpoint, $reservationObject, 'post', true );
		
		if (! $result) {
			return false;
		}
		
		return $result;
	}
	public function approveReservation($referenceNumber){
		
		$endpoint = $this->root . RESERVATIONS . $referenceNumber . '/Approval';
		
		$result = $this->call( $endpoint, null, 'post', true );
		
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
		if (checkScope($updateScope)){
			$endpoint = $this->root . RESERVATIONS . $referenceNumber . '?updateScope=' . $updateScope;
		}else{
			$endpoint = $this->root . RESERVATIONS . $referenceNumber;
		}
		
		
	
		$result = $this->call( $endpoint, $reservationObject, 'post', true );
	
		if (! $result) {
			return false;
		}
	
		return $result;
	}
	
	public function deleteReservation($referenceNumber, $updateScope = null){
		//this|full|future
		
		if (checkScope($updateScope)){
			$endpoint = $this->root . RESERVATIONS . $referenceNumber . '?updateScope=' . $updateScope;
		}else{
			$endpoint = $this->root . RESERVATIONS . $referenceNumber;
		}
		
		$result = $this->call( $endpoint, null, 'delete', true );
		
		if (! $result) {
			return false;
		}
		
		return $result;
		
		
	}
	
	public function createUser($userObject){
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . USERS;
		
		$result = $this->call ( $endpoint, $userObject, 'post', true);
		
		if (! $result) {
			return false;
		}
		
		return $result;
	}
	
	public function updateUser($userId, $userObject){
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . USERS . $userId;
		
		$result = $this->call ( $endpoint, $userObject, 'post', true);
		
		if (! $result) {
			return false;
		}
		
		return $result;
	}
	
	public function getAllUsers($username = null, $email = null, $firstName = null, $lastName = null, $phone = null, $organization = null){
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		if ($username || $email || $firstName || $lastName || $phone || $organization){
			$username = urlencode($username);
			$email = urlencode($email);
			$firstName = urlencode($firstName);
			$lastName = urlencode($lastName);
			$phone = urlencode($phone);
			$organization = urlencode($organization);
			$endpoint = $this->root . USERS . '?username=' . $username . '&email=' . $email . '&firstName=' . $firstName . '&lastName=' . $lastName . '&phone=' . $phone . '&organization=' . $organization;
		}else{
		$endpoint = $this->root . USERS;
		}
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
		if (! $result) {
			return false;
		}
		
		return $result;
	}
	
	public function getUser($userId){
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . USERS . $userId;
		
		
		$result = $this->call ( $endpoint, self::buildAuthParams (), 'get' );
		
		if (! $result) {
			return false;
		}
		
		return $result;
	}
	
	public function deleteUser($userId){
		
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
		$endpoint = $this->root . USERS . $userId;
		
		$result = $this->call ( $endpoint, null, 'delete', true);
		
		if (! $result) {
			return false;
		}
		
		return $result;
	}
	
	
	private function checkScope($scope){
		
		if ($updateScope == 'this' || $updateScope == 'full' || $updateScope == 'future'){
			return true;
		}
		return false;
	}
	/**
	 * @return boolean|number
	 */
	public function getCurrentUserId() {
		if (! self::isAuthenticated ()) {
			if (! $this->authenticate ( true )) {
				return false;
			}
		}
		
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
	private function call($endpoint, $params = array(), $method, $postAuth = false) {
		
		$ch = $this->ch;
		
		curl_setopt ( $ch, CURLOPT_URL, $endpoint );
		
		if ($method == 'post') {
			curl_setopt ( $this->ch, CURLOPT_POST, true );
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
					'Content-Type: application/json' 
			) );
			
			if ($params != null){
				$params = json_encode ( $params );
				curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
			}
			
			
		}
		
		if ($method == 'delete') {
			    $ch = curl_init();
			    curl_setopt ( $this->ch, CURLOPT_POST, false );
			    curl_setopt ( $this->ch, CURLOPT_HTTPGET, false );
    			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    			if (isset($params)){
    				$json = json_encode($params);
    			}else{
    				$json = '';
    			}
   				curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    		   
    			
		}
		
		if ($method == 'get') {
			curl_setopt ( $this->ch, CURLOPT_POST, false );
			curl_setopt ( $this->ch, CURLOPT_HTTPGET, true );
			$this->buildAuthHttpHeader ( $params ['SessionToken'], $params ['UserId'] );
		}
		
		if ($postAuth) {
			if (! self::isAuthenticated ()) {
				if (! $this->authenticate ( true )) {
					return false;
				}
			}
			$this->buildAuthHttpHeader ( $_SESSION ['bookedapi_sessionToken'], $_SESSION ['bookedapi_userId'] );
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
	private function buildAuthHttpHeader($SessionToken, $UserId) {
		$XBookedSessionToken = 'X-Booked-SessionToken: ' . $SessionToken;
		$XBookedUserId = 'X-Booked-UserId: ' . $UserId;
		curl_setopt ( $this->ch, CURLOPT_HTTPHEADER, array (
				$XBookedSessionToken,
				$XBookedUserId 
		) );
	}
	
	/**
	 *
	 * @return boolean
	 */
	private static function isAuthenticated() {
		$date1 = new DateTime ( $_SESSION ['bookedapi_sessionExpires'], new DateTimeZone ( YOURTIMEZONE ) );

		$date2 = new DateTime ( date ( DATE_ISO8601, time () ), new DateTimeZone ( YOURTIMEZONE ) );
		
		$minutesInterval = date_interval_create_from_date_string ( '1 minute' );
		
		$date1->sub ( $minutesInterval );
		
		if ((! $_SESSION ['bookedapi_sessionToken'] == null) && ($date2 <= $date1)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 *
	 * @return array
	 */
	private static function buildAuthParams() {
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
	public static function buildResourceObject($resourceId) {
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

?>

