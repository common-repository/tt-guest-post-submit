<?php
/* Begin easycaptcha validate code */
if (isset($_REQUEST['confirm_code']))
{
        if ( isset($_COOKIE['Captcha']) )
        {
		list($Hash, $Time) = explode('.', $_COOKIE['Captcha']);
                if ( md5("OASDOIJQWOIJDASDOI".$_REQUEST['confirm_code'].$_SERVER['REMOTE_ADDR'].$Time) != $Hash )
                {
                        print "<em>Confirm code is wrong.</em>";
                }
		elseif( (time() - 5*60) > $Time)
		{
			print "<em>Captcha code is only valid for 5 minutes</em>";
		}
		else
		{
			print "<em>Captcha code entered correctly</em>";
		}
	}
        else
        {
                print "<em>No captcha cookie given. Make sure cookies are enabled.</em>";
        }
}
/* End easycaptcha validate code */

/* Begin easycaptcha form code */
print '<form><img src="easycaptcha.php" /><br />
	Enter code from above image: <input type="text" name="confirm_code" /><br />
	<input type="Submit" value="Submit"/></form>';
/* End easycaptcha form code */
?>