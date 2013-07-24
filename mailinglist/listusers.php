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
$email_result = @mysql_query("select user_email from register_email order by user_email",$db);
if( $email_row = @mysql_fetch_row($email_result) )
{
    do{
      echo "$email_row[0]<br />";
    } while ($email_row = @mysql_fetch_row($email_result) );
}

@mysql_close( $db );
echo "</p></body></html>";
echo "\n\n<!-- Site Developed and Copyright by FEF Consulting 2003 : www.fefconsulting.com -->\n";
?>

