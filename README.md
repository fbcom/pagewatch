pagewatch
============

A simple "website changed" notification mailer.

The script loops over a list of urls and sends out an email to a list of predefined subscribers when it detects that the contents found under the specific url has changed.

Note: whitesspaces, JavaScript and comments are stripped from the contents before 
a comparison is made.

Email messages and a list of subscribers can be configured independently for each url.
A means for basic templating to generate email bodys is also provided.

+ **pagewatch.php** - The mailer script
+ **pagewatch.json** - The mailer configuration

Works best with cron.
