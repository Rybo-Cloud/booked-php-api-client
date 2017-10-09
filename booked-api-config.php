<?php

/**
 * Title: booked-php-api-client
 * Ver: 0.1.1 Alpha
 * By: Ryan C Crawford
 * Email: ryan@mytrueserenity.com
 *
 * This file is part of Booked PHP Client Library.
 * There are other files that make up the whole library
 * and that are dependent on this file or that this file is dependent on.
 * 
 *
 */

namespace BookedAPI;

class config{

    //Change these to match your server and time zone.
    const YOURTIMEZONE = 'America/New_York';
    //The FULL URL to the Booked Web/services/index.php file
    const BOOKEDWEBSERVICESURL = 'http://localhost/booked/Web/services/index.php';
    // End Points
    const AUTHENTICATE      = '/Authentication/Authenticate';
    const GETRESOURCES      = '/Resources/';
    const GETRESERVATIONS   = '/Reservations/';
    const FILTERRESERVATION = '/Reservations';
    const GETAVAILABILITY   = '/Resources/Availability';
    const GETACCESSORY      = '/Accessories';
    const GETCATATTRIBUTE   = '/Attributes/Category';
    const GETATTRIBUTE      = '/Attributes';
    const GETGROUPS         = '/Groups';
    const RESERVATIONS      = '/Reservations/';
    const SCHEDULES         = '/Schedules/';
    const SLOTS             = '/Slots/';
    const STATUS            = '/Resources/Status';
    const STATUSREASONS     = '/Resources/Status/Reasons';
    const RESOURCETYPES     = '/Types/';
    const USERS             = '/Users';
    const INTERVALHOURS   = 'hours';
    const INTERVALMINUTES = 'minutes';
    const INTERVALDAYS    = 'days';
    // updateScope are this|full|future
    const UPDATESCOPE_THIS   = 'this';
    const UPDATESCOPE_FULL   = 'full';
    const UPDATESCOPE_FUTURE = 'future';
    // Recurrance Rule Const
    const RECURRENCETYPE_DAILY               = 'daily';
    const RECURRENCETYPE_MONTHLY             = 'monthly';
    const RECURRENCETYPE_NONE                = 'none';
    const RECURRENCETYPE_WEEKLY              = 'weekly';
    const RECURRENCETYPE_YEARLY              = 'yearly';
    const RECURRENCE_MONTHLY_TYPE_DAYOFMONTH = 'dayOfMonth';
    const RECURRENCE_MONTHLY_TYPE_DAYOFWEEK  = 'dayOfWeek';
    const RECURRENCE_MONTHLY_TYPE_NULL       = null;
    const RECURRENCE_WEEKDAY_SUN             = 0;
    const RECURRENCE_WEEKDAY_MON             = 1;
    const RECURRENCE_WEEKDAY_TUE             = 2;
    const RECURRENCE_WEEKDAY_WED             = 3;
    const RECURRENCE_WEEKDAY_THR             = 4;
    const RECURRENCE_WEEKDAY_FRI             = 5;
    const RECURRENCE_WEEKDAY_SAT             = 6;
    // Attibute Categories Enum
    const ATT_CAT_RESERVATION   = 1;
    const ATT_CAT_USER          = 2;
    const ATT_CAT_RESOURCE      = 4;
    const ATT_CAT_RESOURCE_TYPE = 5;
    // Attribute Types Enum
    const SINGLE_LINE_TEXT = 1;
    const SELECT_LIST      = 3;
    const MULTI_LINE_TEXT  = 2;
    const CHECK_BOX        = 4;
    //Other Settings
    const TIMEOUT = 600;

    public static $routes = array(
        'getAllAccessories'       => '/Accessories/',
        'CreateCustomAttribute'   => '/Attributes/',
        'updateCustomAttribute'   => '/Attributes/:attributeId',
        'getCategoryAttributes'   => '/Attributes/Category/:categoryId', //RESERVATION = 1, USER = 2, RESOURCE = 4
        'getAttribute'            => '/Attributes/:attributeId',
        'DeleteCustomAttribute'   => '/Attributes/:attributeId',
        'getAllGroups'            => '/Groups/',
        'GetGroup'                => '/Groups/:groupId',
        'CreateReservation'       => '/Reservations/',
        'UpdateReservation'       => '/Reservations/:referenceNumber',
        'approveReservation'      => '/Reservations/:referenceNumber/Approval',
        'checkInReservation'      => '/Reservations/:referenceNumber/CheckIn',
        'checkOutReservation'     => '/Reservations/:referenceNumber/CheckOut',
        'getAllReservations'      => '/Reservations/',
        'getReservation'          => '/Reservations/:referenceNumber',
        'DeleteReservation'       => '/Reservations/:referenceNumber',
        'CreateResource'          => '/Resources/',
        'UpdateResource'          => '/Resources/:resourceId',
        'getResourceStatuses'     => '/Resources/Status',
        'getAllResources'         => '/Resources/',
        'getResourceStatusReasons'        => '/Resources/Status/Reasons',
        'getAvailability'         => '/Resources/Availability',
        'getAllGroups'               => '/Resources/Groups',
        'getResource'             => '/Resources/:resourceId',
        'getResourceAvailability' => '/Resources/:resourceId/Availability',
        'DeleteResource'          => '/Resources/:resourceId',
        'getAllSchedules'         => '/Schedules/',
        'getSchedule'             => '/Schedules/:scheduleId',
        'getSlots'                => '/Schedules/:scheduleId/Slots',
        'CreateUser'              => '/Users/', //This service is only available to application administrators
        'UpdateUser'              => '/Users/:userId', //This service is only available to application administrators
        'UpdatePassword'          => '/Users/:userId/Password', //This service is only available to application administrators
        'GetAllUsers'             => '/Users/',
        'getUser'                 => '/Users/:userId',
        'DeleteUser'              => '/Users/:userId', // This service is only available to application administrators
    );

}
