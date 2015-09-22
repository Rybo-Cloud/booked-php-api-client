# booked-php-api-client
Booked PHP API Client Library
by ryan@mytrueserenity.com

<h2>Hello every one!</h2>

<p>This is a simple to use PHP client library to be used with Booked (formerly phpScheduleIt) RESTful API by nick@twinkletoessoftware.com found at <a href='http://sourceforge.net/projects/phpscheduleit'>Booked Home</a></p>
<p>I am just about done with the beta version of the library which I will be uploading for everyone to try out.
Please report any bugs or suggestions to ryan@mytrueserenity.com</p>
<p>This project started as a way for me to design a customized Rental Booking System for a wonderful place called <a href='https://mytrueserenity.com'>True Serenity</a>. So because I have yet to make a contribution to the wonderful world of open source software, I decided to make this my first public attempt. I hope it is useful to someone. I would be open to suggestions and comments. Please be kind, as this is my very first public PHP program. I have been programming for several years, mostly in C#, and finally decided to learn PHP. I LOVE it! If there is something I’m not doing right or maybe a better way to do it then let me know!</p>
<h1>INSTALLATION</h1>
<ul><li>You must have PHP5.5 or higher</li></ul>
<p>First you need to edit the bookedphpapiconfig.php file
The line that reads:</p> 
<p>
<code>
const BOOKEDAPIURL = ‘http://your-domain/booked/web/services/index.php’;
</code>
</p>
<p>needs to be changed to the URL of your booked API endpoint. You must also make sure the Booked configuration file has the option to enable the API set to ‘true’.</p>
<p>Next upload to your server and make sure to include the library in your PHP script. Like this
<br>
Your script:</p>
<p>
<code>
	require_once(‘path to file/bookedapi.php’);
</code>
</p>
<p>Next, you need to make sure you use PHP’s <code>startsession();</code>. The class library uses the $_SESSION global to store authentication information and will fail if not used. So before you call anything other than the _construct function, remember to call <code>startsession();</code> first, like this…
<br>
Your script:</p>

<code>	require_once(‘path to file/bookedapi.php’);</code><br>
<code>	//some of your code</code><br>
<code>	startsession();</code><br>
<code>	$username = ‘your_booked_admin_username’;</code><br>
<code>	$password = ‘your_booked_admin_password';</code><br>
<code>	$bookedapiclient = new bookedapiclient($username, $password);</code><br>


<p>Then, in the above example, replace the variable <code>$username</code> with your Booked user name and <code>$password</code> with your Booked password. If you don't want to change the const BOOKEDAPIURL and instead would like to set it at runtime then do this instead:</p>

<code>	require_once(‘path to file/bookedapi.php’);</code><br>
<code>	//some of your code</code><br>
<code>	startsession();</code><br>
<code>	$username = ‘your_booked_admin_username’;</code><br>
<code>	$password = ‘your_booked_admin_password';</code><br>
<strong><code>	$bookedApiUrl = ‘http://your-domain/booked/web/services/index.php';</code></strong><br>
<code>	$bookedapiclient = new bookedapiclient($username, $password, <strong>$bookedApiUrl</strong>);</code><br>

<p>Next before you make any API calls, you must call <code>authenticate(true)</code> at least once. After that the client will automatically check to see if you are still authenticated to help the library preform faster by not having to re-authenticate every call. The library will also automatically re-authenticate you if it finds that your session token has expired. For example:</p>
<code>	require_once(‘path to file/bookedapi.php’);</code><br>
<code>	//some of your code</code><br>
<code>	startsession();</code><br>
<code>	$username = ‘your_booked_admin_username’;</code><br>
<code>	$password = ‘your_booked_admin_password;</code><br>
<code>	$bookedapiclient = new bookedapiclient($username, $password);</code><br>
<code>	$bookedapiclient-> authenticate(true);</code><br>

<p>Next you want to make your API call. We will get the current authenticated users reservations, like this:</p>
<code>	require_once(‘path to file/bookedapi.php’);</code><br>
<code>	//some of your code</code><br>
<code>	startsession();</code><br>
<code>	$username = ‘your_booked_admin_username’;</code><br>
<code>	$password = ‘your_booked_admin_password;</code><br>
<code>	$bookedapiclient = new bookedapiclient($username, $password);</code><br>
<code>	$bookedapiclient-> authenticate(true);</code><br>
<code>	//call the get all reservation command</code><br>
<code>	$allReservations = $bookedapiclient->getReservations();</code><br>
<code>	//print the result to the screen</code><br>
<code>	print_r($allReservations);</code><br>

<p>If the call succeeds it will return a decoded json PHP associative array. If the call fails it will not return a reason why it will just return a Boolean false. In my next release, when I have some more free time, I was thinking about adding more useable error messages in the return. However for now this should work just fine.</p>  
<p>For creating reservations, attributes and resources, there are some static methods in the class that can be used to help build the needed objects to then pass the related functions. I’m running out of time for now, but will be updating this readme with more examples.</p>

