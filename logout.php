<?php
/**
 * @package    c1k.it
 * @author     Vino Rodrigues
 * @copyright  Tecsmith.com.au
 *   See LICENSE.TXT for copyright notice and details.
 * @license    Creative Commons Attribution-ShareAlike 3.0 Unported License
 * @author     Vino Rodrigues 
 *   clickit [dot] source [at] mail [dot] vinorodrigues [dot] com
 */

require_once('includes/library.php');
initialize_settings();
$db = initialize_db(false);
initialize_security(false);

if ($userid > 0) :

	// Kill PHP session
	if (session_id()) session_destroy();
	
	// Kill TOKEN cookie
	setcookie('token', '', 0);
	
	// Kill users table token
	// TODO : Move killing signon tokens to CRON
	/* $sql = "UPDATE $USERS_TABLE" .
		" SET " . $db->sql_build_array('UPDATE', array(
			'token' => '',
			)) .
		" WHERE " . $db->sql_build_array('SELECT', array('id' => $userid));
	$db->sql_query($sql); */
		
	poke_info('You have been logged out', true);

else :
	poke_warning('You where not logged in', true);
endif;

header('Location: ' . $page['base_path'], true, 302);
exit(302);
