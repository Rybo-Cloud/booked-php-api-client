<?php

?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>TEST</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div>
            <h1>Testing</h1>
            <p>Enter your user name and password for Booked.</p>
            
            <form action="test.php" method="post" name="test">
                
                <span><input type="text" name="username" value="" placeholder="User Name"/><label for="username">User Name </label></span><br>
                <span><input type="password" name="password" value="" placeholder="Password"/><label for="password"> Password </label></span>
                <br>
                <input type="submit" name="go" value="Go!"/>
            
            </form>

<?PHP 
        echo '<div>';
    if(isset($_POST['username'])){
         
        
        require_once('booked-api-config.php');
        
        require_once('booked-api.php');
                
        new \BookedAPI\config();
        
        $password = filter_var($_POST['password']);
        $username = filter_var($_POST['username']);
    
        $api = new \BookedAPI\Client($username,$password);
       $s = $api->getAllSchedules();
       $sid = $s['schedules'][0]['id'];
        $test = array(
            
                'getAllSchedules' => $s,
                'getAllUsers' => $api->getAllUsers(),
                'getSchedule' => $api-> getSchedule($sid),
                'getAllReservations' => $api->getAllReservations(),      
                'getAllResources' => $api->getAllResources(),     
                'getResourceStatuses' => $api->getResourceStatuses(),     
                'getAllGroups' => $api->getAllGroups(),      
                'getAllAccessories' => $api->getAllAccessories(),
         );
       
       
       
       echo '<p>Authenticated:  ' . ($api->isAuthenticated() ? 'Yes':'No') . '<br>'; 
       
       foreach($test as $key => $value){
            echo '<br>'. $key . '<br>';
             var_dump($value);
           
        }
        echo '</p>';
    }
    echo '<br>';
        
       echo '</div>';
          
?>
       
    </body>
</html>

  