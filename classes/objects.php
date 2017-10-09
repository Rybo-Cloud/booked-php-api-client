<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BookedAPI\Classes;
/**
 * Description of objects
 *
 * @author ryan
 */
class objects{

    /*
     * These object are not used yet just my notes Is not used yet 
     * 
     * 
     * 
     */

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
    public static function buildReservationObject($userId, $title, $resourceId, $resourcesObject, $accessoriesObject, $customAttributesObject, $description, $startDateTime, $endDateTime, $recurrenceRuleObject, $inviteesObject, $participantsObject, $startReminderObject, $endReminderObject){
        $reservationObject = array(
            'accessories'      => (array)$accessoriesObject,
            'customAttributes' => (array)$customAttributesObject,
            'description'      => (string) $description,
            'endDateTime'      => (string)$endDateTime,
            'startDateTime'    => (string)$startDateTime,
            'invitees'         => (array)$inviteesObject,
            'participants'     => (array)$participantsObject,
            'resourceId'       => (int) $resourceId,
            'resources'        => (array)$resourcesObject,
            'recurrenceRule'   => (array)$recurrenceRuleObject,
            'title'            => (string) $title,
            'userId'           => (int) $userId,
            'startReminder'    => (array)$startReminderObject,
            'endReminder'      => (array)$endReminderObject
        );

        return $reservationObject;

    }

    public static function buildAccessoryObject($accessoryId, $quantityRequested){
        $ao = array(
            'accessoryId'       => (int) $accessoryId,
            'quantityRequested' => (int) $quantityRequested
        );

        return $ao;

    }

    public static function buildAccessoriesObject($accessories = array()){
        return $accessories;

    }

    public static function buildAttributeObject($attributeId, $attributeValue){
        $atto = array(
            'attributeId'    => (int) $attributeId,
            'attributeValue' => (string) $attributeValue
        );
        return $atto;

    }

    public static function buildAttributesObject($attributes = array()){
        return $attributes;

    }

    public static function buildInviteeObject($inviteeId){
        $i = (int) $inviteeId;

        return $i;

    }

    public static function buildInviteesObject($invitees = array()){
        return $invitees;

    }

    public static function buildParticipantObject($participantId){
        $i = (int) $participantId;

        return $i;

    }

    public static function buildParticipantsObject($participants = array()){
        return $participants;

    }

    public static function getResourceObject($resourceId){
        $r = (int) $resourceId;

        return $r;

    }

    public static function buildResourcesObject($resources = array()){
        $resources;

        return $resources;

    }

    public static function buildReminderObject($value, $interval){

        // interval = hours or minutes or days
        $ro = array(
            'value'    => (int) $value,
            'interval' => (string) $interval
        );

        return $ro;

    }

    // recurrenceRule
    // {"type":"daily|monthly|none|weekly|yearly","interval":3,"monthlyType":"dayOfMonth|dayOfWeek|null","weekdays":[0,1,2,3,4,5,6],"repeatTerminationDate":"2015-09-16T20:51:36-0700"}
    public static function buildRecurrenceRuleObject($type, $interval, $monthlyType, $repeatTerminationDate, $weekdays = array()){
        $rule = array(
            'type'                  => (string) $type,
            'interval'              => (int) $interval,
            'monthlyType'           => (string) $monthlyType,
            'weekdays'              => $weekdays,
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
    $label, $type, $categoryId, $appliesToId, $sortOrder = 0, $regex = null, $required = false, $possibleValues = array()){

        $attributeObject = array(
            'label'          => (string) $label,
            'type'           => (int) $type,
            'categoryId'     => (int) $categoryId,
            'appliesToId'    => (int) $appliesToId,
            'sortOrder'      => (int) $sortOrder,
            'regex'          => (string) $regex,
            'required'       => (boolean) $required,
            'possibleValues' => $possibleValues);

        return $attributeObject;

    }

    // {"name":"resource name","location":"location","contact":"contact information","notes":"notes","minLength":"1d0h0m","maxLength":"3600","requiresApproval":true,"allowMultiday":true,"maxParticipants":100,"minNotice":"86400","maxNotice":"0d12h30m","description":"description","scheduleId":10,"autoAssignPermissions":true,"customAttributes":[{"attributeId":1,"attributeValue":"attribute value"}],"sortOrder":1,"statusId":1,"statusReasonId":2,"resourceTypeId":1}
    public static function buildResourceObject(
            $name, $location, $contact, $notes, $minLength, // in "seconds" or "1d2h3m" format
            $maxLength, // in "seconds" or "1d2h3m" format
            $requiresApproval, $allowMultiday, $maxParticipants, $minNotice, // in "seconds" or "1d2h3m" format
            $maxNotice, // in "seconds" or "1d2h3m" format
            $description, $scheduleId, $autoAssignPermissions, $customAttributesObject, // {"attributeId":1,"attributeValue":"attribute value"}
            $sortOrder, $statusId, $statusReasonId, $resourceTypeId){

        $resourceObject = array(
            'name'                  => (string) $name,
            'location'              => (string) $location,
            'contact'               => (string) $contact,
            'notes'                 => (string) $notes,
            'minLength'             => (string) $minLength, // in "seconds" or "1d2h3m" format
            'maxLength'             => (string) $maxLength, // in "seconds" or "1d2h3m" format
            'requiresApproval'      => (boolean) $requiresApproval,
            'allowMultiday'         => (boolean) $allowMultiday,
            'maxParticipants'       => (int) $maxParticipants,
            'minNotice'             => (string) $minNotice, // in "seconds" or "1d2h3m" format
            'maxNotice'             => (string) $maxNotice, // in "seconds" or "1d2h3m" format
            'description'           => (string) $description,
            'scheduleId'            => (int) $scheduleId,
            'autoAssignPermissions' => (boolean) $autoAssignPermissions,
            'customAttributes'      => $customAttributesObject,
            'sortOrder'             => (int) $sortOrder,
            'statusId'              => (int) $statusId,
            'statusReasonId'        => (int) $statusReasonId,
            'resourceTypeId'        => (int) $resourceTypeId);

        return $resourceObject;

    }

    // {"attributeId":1,"attributeValue":"attribute value"}
    public static function buildCustomAttributeObject($attributeId, $attributeValue){

        $customAttributeObject = array(
            'attributeId'    => (int) $attributeId,
            'attributeValue' => (string) $attributeValue);

        return $customAttributeObject;

    }

    //{"password":"unencrypted password","language":"en_us","firstName":"first","lastName":"last","emailAddress":"email@address.com","userName":"username","timezone":"America\/Chicago","phone":"123-456-7989","organization":"organization","position":"position","customAttributes":[{"attributeId":99,"attributeValue":"attribute value"}],"groups":[1,2,4]}
    public static function buildUserobject($password, $userName, $language = 'en_us', $firstName = null, $lastName = null, $emailAddress = null, $timezone = YOURTIMEZONE, $phone = null, $organization = null, $position = null, $customAttributesObject = array(), $groupsObject = array()){
        $userobject = array(
            'password'         => (string) $password,
            'language'         => (string) $language,
            'firstName'        => (string) $firstName,
            'lastName'         => (string) $lastName,
            'emailAddress'     => (string) $emailAddress,
            'userName'         => (string) $userName,
            'timezone'         => (string) $timezone,
            'phone'            => (string) $phone,
            'organization'     => (string) $organization,
            'position'         => (string) $position,
            'customAttributes' => $customAttributesObject,
            'groups'           => $groupsObject);

        return $userobject;

    }

}
