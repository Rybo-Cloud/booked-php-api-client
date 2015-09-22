# booked-php-api-client
Booked PHP API Client Library
by ryan@mytrueserenity.com

Hello every one!<br>

This is a simple to use PHP client library to be used with Booked (formerly phpScheduleIt) RESTful API by nick@twinkletoessoftware.com found at http://sourceforge.net/projects/phpscheduleit
<br><br>
I am just about done with the beta version of the library which I will be uploading for everyone to try out.
Please report any bugs or suggestions to ryan@mytrueserenity.com
<br><br>
This project started as a way for me to design a customized Rental Booking System for a wonderful place called True Serenity.
https://mytrueserenity.com . So because I have yet to make a contribution to the wonderful world of open source software, I decided to make this my first public attempt. I hope it is useful to someone. I would be open to suggestions and comments. Please be kind, as this is my very first public PHP program. I have been programming for several years, mostly in C#, and finally decided to learn PHP. I LOVE it! If there is something I’m not doing right or maybe a better way to do it then let me know!
INSTALLATION
You must have PHP5.5 or higher
First you need to edit the bookedphpapiconfig.php file
The line that reads: 
<code><?PHP
const BOOKEDAPIURL = ‘http://your-domain/booked/web/services/index.php’;
……
?></code>
needs to be changed to the URL of your booked API endpoint. You must also make sure the Booked configuration file has the option to enable the API set to ‘true’.
Next upload to your server and make sure to include the library in your PHP script. Like this

Your script:
<?PHP
	require_once(‘path to file/bookedapi.php’);
?>
Next, you need to make sure you use PHP’s startsession(). The class library uses the $_SESSION global to store authentication information and will fail if not used. So before you call anything other than the _construct function, remember to call startsession() first, like this…
Your script:
<?PHP
	require_once(‘path to file/bookedapi.php’);
	//some of your code
	startsession();
	$username = ‘your_booked_admin_username’;
	$password = ‘your_booked_admin_password;
	$bookedapiclient = new bookedapiclient($username, $password);
	
?>
Then, in the above example, replace the variable $username with your Booked user name and $password with your Booked password.
Next before you make any API calls, call authenticate(true) at least once. After that the client will automatically check to see if you are still authenticated to help the library preform faster by not having to re-authenticate every call. The library will also automatically re-authenticate you if it finds that your session token has expired. For example:
<?PHP
	require_once(‘path to file/bookedapi.php’);
	//some of your code
	startsession();
	$username = ‘your_booked_admin_username’;
	$password = ‘your_booked_admin_password;
	$bookedapiclient = new bookedapiclient($username, $password);
	$bookedapiclient-> authenticate(true);
	
?>
Next you want to make your API call. We will get the current authenticated users reservations, like this:
<?PHP
	require_once(‘path to file/bookedapi.php’);
	//some of your code
	startsession();
	$username = ‘your_booked_admin_username’;
	$password = ‘your_booked_admin_password;
	$bookedapiclient = new bookedapiclient($username, $password);
	$bookedapiclient-> authenticate(true);
	//call the get all reservation command
$allReservations = $bookedapiclient->getReservations();
//print the result to the screen
	print_r($allReservations);
	
?>

If the call succeeds it will return a decoded json PHP associative array. If the call fails it will not return a reason why it will just return a Boolean false. In my next release, when I have some more free time, I was thinking about adding more useable error messages in the return. However for now this should work just fine.  
For creating reservations, attributes and resources, there are some static methods in the class that can be used to help build the needed objects to then pass the related functions. I’m running out of time for now, but will be updating this readme with more examples. 

