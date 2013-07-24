<?
echo '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
echo "\n\n<!-- Site Developed and Copyright by FEF Consulting 2003 : www.fefconsulting.com -->\n\n";
echo "<html><head><title>Mass Mailer ::</title>";
echo "<style type='text/css'>";
echo "body { background: #000000; color: #00ADEF; font-size: 12px; font-family: arial, verdana, Helvetica, Courier }";
echo "</style></head>";
echo "<body><p>";

$htmltxt2 = str_replace('\\"', '"', $htmltxt);
$htmltxt3 = str_replace("\\'", "'", $htmltxt2);
if( $selectmode != 'manual' )
{
$db = @mysql_connect("","regdb","strongarm2");
@mysql_select_db("regdb")or die ("Unable to connect to MySQL");
$email_list = "mailto:info@cinsummer.com?bcc=";
$email_result = @mysql_query($selectmode,$db);
if( $email_row = @mysql_fetch_row($email_result) )
{
    do{
        mail("$email_row[0]","$subjecttxt","$htmltxt3","From: info@cinsummer.com\nContent-Type: text/html;\n\tcharset=ISO-8859-1" );
        echo "Mail sent to : " . $email_row[0] . "<br />";
    } while ($email_row = @mysql_fetch_row($email_result) );
}
@mysql_close( $db );
}
else
{
    mail("$totxt","$subjecttxt","$htmltxt3","From: info@cinsummer.com\nContent-Type: text/html;\n\tcharset=ISO-8859-1" );
    echo "Mail sent to : " . $totxt . "<br />";
}
echo "</p></body></html>";
echo "\n\n<!-- Site Developed and Copyright by FEF Consulting 2003 : www.fefconsulting.com -->\n";
?>


