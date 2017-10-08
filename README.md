# booked-php-api-client
Booked PHP API Client Library
by ryanccrawford@live.com

<h2>Completely Rewritten! </h2>

<p>This is a simple to use PHP client/wrapper library to be used with Booked (formerly phpScheduleIt) RESTful API by nick@twinkletoessoftware.com found at <a href='http://sourceforge.net/projects/phpscheduleit'>Booked Home</a></p>
<p>This library has been rewritten and has some work to be done. However this is a prerelease to get everyone excited. Please report any bugs or suggestions to <a href="mailto:ryan@mytrueserenity.com">ryan@mytrueserenity.com</a></p>
<p>This project started as a way for me to design a customized Rental Booking System for a wonderful place called <a href='https://mytrueserenity.com'>True Serenity</a>. So because I have yet to make a contribution to the wonderful world of open source software, I decided to make this my first public attempt. I hope it is useful to someone.</p>
<h1>INSTALLATION</h1>
<ul><li>You must have PHP 5.6 or higher and you must have CURL installed. No other dependencies are required (Except for Booked)</li></ul>
<p>1st - You need to edit the booked-api-config.php file so that your booked web services URL points to your Booked API (Usually http://your-domain.com/web/services/). Make sure to change the Time Zone const to match yours. Then save the file:</p> 
<p>
<code>
const BOOKEDWEBSERVICESURL = ‘http://your-domain/booked/web/services/index.php’;
const YOURTIMEZONE = 'your time zone';
</code>
</p>
<p>If you run into trouble, first try visiting your Booked API web URL. If Booked is configured correctly you will see the API documentation page. The URL 
is http://your-booked-domain.com/web/services/index.php. If you get an error or can't see the page, you must fix your Booked configuration, otherwise this client will not work.</p>
</p><p>To test this client simply run the included test.php file. You will see a few of the API calls return some of your data.</p>
<p>
<code>
	require_once(‘path to file/bookedapi.php’);
</code>
</p>
<p>Open and study the test.php for an example of how to make your own calls. I will be making more in depth documentation when time allows</p>