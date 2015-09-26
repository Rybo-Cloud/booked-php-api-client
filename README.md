# booked-php-api-client
Booked PHP API Client Library
by ryan@mytrueserenity.com

<h2>Hello every one!</h2>

<p>This is a simple to use PHP client library to be used with Booked (formerly phpScheduleIt) RESTful API by nick@twinkletoessoftware.com found at <a href='http://sourceforge.net/projects/phpscheduleit'>Booked Home</a></p>
<p>So here is the first beta of the library. Please report any bugs or suggestions to <a href="mailto:ryan@mytrueserenity.com">ryan@mytrueserenity.com</a></p>
<p>This project started as a way for me to design a customized Rental Booking System for a wonderful place called <a href='https://mytrueserenity.com'>True Serenity</a>. So because I have yet to make a contribution to the wonderful world of open source software, I decided to make this my first public attempt. I hope it is useful to someone. I would be open to suggestions and comments. Please be kind, as this is my very first public PHP program. If there is something I’m not doing right or maybe a better way to do it then let me know! Notes: I understand the code should be broken down into several classes for each object type and many other silly things. My main goal was to have something that worked and something simple. </p>
<h1>INSTALLATION</h1>
<ul><li>You must have PHP5.5 or higher</li></ul>
<p>1st - You need to edit the bookedapiconfig.php file
so that your booked web services URL points to where your Booked is hosted. Alos make sure to change the Time Zone const to macth yours. Then save the file:</p> 
<p>
<code>
const BOOKEDWEBSERVICESURL = ‘http://your-domain/booked/web/services/index.php’;
</code>
<code>
const YOURTIMEZONE = 'your time zone';
</code>
</p>
<p>Please note this library will only work if you have Booked config'ed correctly, which is beyond the scope of this document. Please visit the Booked website / forums to learn how to do this. The best way to find out if your Booked api endpoint is working type this in your browser - http://your-domain/booked/web/services/index.php - if it's working you will get a page that documents the API. If you get a blank page or a server error then you'll need to fix that before this library can be used.</p>

<p>Next upload this library to your server either copy the 2 files and put them in your root or put them in a seperate folder just make sure to include the location of the library in your PHP script. Like this
<br>
Your script:</p>
<p>
<code>
	require_once(‘path to file/bookedapi.php’);
</code>
</p>
<p>Next, make sure you use PHP’s <code>startsession();</code>. The class library uses the $_SESSION global to store authentication information and will fail if not used. So before you call anything other than the _construct function, remember to call <code>startsession();</code> first, like this…
<br>
Your script:</p>

<code>	require_once(‘path to file/bookedapi.php’);</code><br>
<code>	//some of your code</code><br>
<code>	startsession();</code><br>
<code>	$username = ‘your_booked_admin_username’;</code><br>
<code>	$password = ‘your_booked_admin_password';</code><br>
<code>	$bookedapiclient = new bookedapiclient($username, $password);</code><br>


<p>Then, in the above example, replace the variable <code>$username</code> with your Booked user name and <code>$password</code> with your Booked password. If you don't want to change the <code>const BOOKEDAPIURL</code> in the configuration file and instead would like to set it at runtime then add the extra param like in the next example (changes to the above code in <strong>bold</strong>):</p>

<code>	require_once(‘path to file/bookedapi.php’);</code><br>
<code>	//some of your code</code><br>
<code>	startsession();</code><br>
<code>	$username = ‘your_booked_admin_username’;</code><br>
<code>	$password = ‘your_booked_admin_password';</code><br>
<strong><code>	$bookedApiUrl = ‘http://your-domain/booked/web/services/index.php';</code></strong><br>
<code>	$bookedapiclient = new bookedapiclient($username, $password, <strong>$bookedApiUrl</strong>);</code><br>

<p>Next, before you make any API calls, you must call <code>authenticate(true)</code> at least once. After that, the client automatically checks to see if you are still authenticated by checking to see if the session token has expired. This is to help the library preform faster by not having to re-authenticate every call. The library also automatically re-authenticates you if your session token has expired. For example:</p>
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
<code>	//the next call will get all reservations for the  current user or all of them if that user is the admin</code><br>
<code>	$allReservations = $bookedapiclient->getReservation();</code><br>
<code>	//print the result to the screen</code><br>
<code>	print_r($allReservations);</code><br>

<p>If the call succeeds it will return a decoded json PHP associative array. If the call fails it will return a reason why unless it there was a server error like 500, then it will just return a Boolean false. In my next release, when I have some more free time, I was thinking about adding more useable error messages in the return. However for now this should work just fine.</p>  
<p>For creating reservations, attributes and resources, there are some static methods in the class that can be used to help build the needed objects to then pass the related functions. I’m running out of time for now, but will be updating this readme with more examples.</p>

