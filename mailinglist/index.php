<?
echo '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
echo "\n\n<!-- Site Developed and Copyright by FEF Consulting 2003 : www.fefconsulting.com -->\n\n";
echo "<html><head><title>Mass Mailer ::</title>";
echo "<style type='text/css'>";
echo "body { background: #000000; color: #00ADEF; font-size: 12px; font-family: arial, verdana, Helvetica, Courier }";
echo "</style></head>";
echo "<body><p>";

$db = @mysql_connect("","regdb","strongarm2");
@mysql_select_db("regdb")or die ("Unable to connect to MySQL");
$email_list = "mailto:info@cinsummer.com?bcc=";
$email_result = @mysql_query("select user_email from register_email",$db);
if( $email_row = @mysql_fetch_row($email_result) )
{
    do{
        $email_list .= $email_row[0] . ",";
    } while ($email_row = @mysql_fetch_row($email_result) );
    echo '    <form name="mailhtml" method="post" action="mailhtml.php">
<input type="hidden" name="selectmode" value="select user_email from register_email">
    All Registered Emails<br />';
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='listusers.php' target=_blank'>View List</a><br />";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . $email_list . "'>Send Regular Mass Email</a><br />";
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Send HTML Mass Email:<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Subject : <input type="text" name="subjecttxt" size="80" maxlength="80"><br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HTML : <textarea rows="10" cols="80" name="htmltxt"></textarea><br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="Send HTML Email"></form><br />';
}
$email_list = "mailto:info@cinsummer.com?bcc=";
$email_result = @mysql_query("select userMail from tutorial_user_auth",$db);
if( $email_row = @mysql_fetch_row($email_result) )
{
    do{
        $email_list .= $email_row[0] . ",";
    } while ($email_row = @mysql_fetch_row($email_result) );
    echo '    <form name="mailhtml" method="post" action="mailhtml.php">
<input type="hidden" name="selectmode" value="select userMail from tutorial_user_auth">
    All Registered Subscribers<br />';
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='listdjs.php' target=_blank'>View List</a><br />";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='" . $email_list . "'>Send Regular Mass Email</a><br />";
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Send HTML Mass Email:<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Subject : <input type="text" name="subjecttxt" size="80" maxlength="80"><br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HTML : <textarea rows="10" cols="80" name="htmltxt"></textarea><br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="Send HTML Email"></form><br />';
}


echo '<br /><br /><br /><br /><form name="mailhtml" method="post" action="mailhtml.php">
<input type="hidden" name="selectmode" value="manual">';
echo 'Send HTML Email to specified email addresses:<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;To (comma separated list) : <input type="text" name="totxt" size="80" maxlength="200"><br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Subject : <input type="text" name="subjecttxt" size="80" maxlength="80"><br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HTML : <textarea rows="10" cols="80" name="htmltxt"></textarea><br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="Send HTML Email"></form>';

echo "</p></body></html>";
echo "\n\n<!-- Site Developed and Copyright by FEF Consulting 2003 : www.fefconsulting.com -->\n";
?>

