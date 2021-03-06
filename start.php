<?php

/**
 * elgg_sendgrid
 *
 * @author Bortoli German
 * @link http://community.elgg.org/pg/profile/pedroprez
 * @copyright (c) Keetup 2010
 * @link http://www.keetup.com/
 * @license GNU General Public License (GPL) version 2
 */

define('elgg_sendgrid_PATH', dirname(__FILE__).'/');
define('elgg_sendgrid_URL', elgg_normalize_url('mod/leancanvas/'));
define('elgg_sendgrid_GRAPHICS', elgg_sendgrid_URL.'graphics/');

include_once(elgg_sendgrid_PATH.'lib/functions.php');
include_once(elgg_sendgrid_PATH.'lib/events.php');
include_once(elgg_sendgrid_PATH.'lib/hooks.php');
	
elgg_register_event_handler('init', 'system', 'elgg_sendgrid_init');

function elgg_sendgrid_init() {
	
	//Selective php library, this is loaded only when you require
	elgg_register_library('elgg.elgg_sendgrid', elgg_sendgrid_PATH.'lib/main_lib.php');
	//elgg_load_library('elgg.elgg_sendgrid'); // Load this file if you need the PHP globally in the site
	
	//Register the JS to be loaded with simplecache
	elgg_register_js('elgg.elgg_sendgrid', elgg_get_simplecache_url('js', 'elgg_sendgrid/main'), 'footer');
	elgg_register_simplecache_view('js/elgg_sendgrid/main');
	//elgg_load_js('elgg.elgg_sendgrid'); // Load this file if you need the JS globally in the site
	
	//Register the CSS to be loaded with simplecache
	elgg_register_css('elgg.elgg_sendgrid', elgg_get_simplecache_url('css', 'elgg_sendgrid/style'));
	elgg_register_simplecache_view('css/elgg_sendgrid/style');
//	elgg_load_css('elgg.elgg_sendgrid'); // Load this file if you need the CSS globally in the site
	
	
	//Register the page handler
//	elgg_register_page_handler('elgg_sendgrid', 'elgg_sendgrid_page_handler');
	
	//Register action
//	elgg_register_action('elgg_sendgrid/action', elgg_sendgrid_PATH.'actions/elgg_sendgrid/action_a.php', 'logged_in');
	
	
	if (elgg_get_plugin_setting('sendgrid_enabled','elgg_sendgrid') == 'yes') {
		elgg_load_library('elgg.elgg_sendgrid');
		
		register_notification_handler('email', 'elgg_sendgrid_notify_handler');
		elgg_register_plugin_hook_handler('email', 'system', 'elgg_sendgrid_mail_override');
	}	
	
}



/**
 * Send a notification via email using sendgrid
 *
 * @param ElggEntity $from The from user/site/object
 * @param ElggUser $to To which user?
 * @param string $subject The subject of the message.
 * @param string $message The message body
 * @param array $params Optional parameters (not used)
 * @return bool
 */
function elgg_sendgrid_notify_handler(ElggEntity $from, ElggUser $to, $subject, $message, array $params = NULL) {

	if (!$from) {
		throw new NotificationException(sprintf(elgg_echo('NotificationException:MissingParameter'), 'from'));
	}

	if (!$to) {
		throw new NotificationException(sprintf(elgg_echo('NotificationException:MissingParameter'), 'to'));
	}

	if ($to->email=="") {
		throw new NotificationException(sprintf(elgg_echo('NotificationException:NoEmailAddress'), $to->guid));
	}

	$from_email = elgg_sendgrid_extract_from_email();

	$site = elgg_get_site_entity();
	$from_name = $site->name;

	return elggSendGrid::sendEmail($from_email, $to->email, $subject, $message);
}

/**
 * Dispatcher for elgg_sendgrid.
 *
 * URLs take the form of
 *  Index elgg_sendgrid:			elgg_sendgrid/
 *  All elgg_sendgrid:				elgg_sendgrid/all
 *  User's elgg_sendgrid:			elgg_sendgrid/owner/<username>
 *  Friends' elgg_sendgrid:			elgg_sendgrid/friends/<username>
 *  View elgg_sendgrid:				elgg_sendgrid/view/<guid>/<title>
 *  New elgg_sendgrid:				elgg_sendgrid/add/<guid> (container: user, group, parent)
 *  Edit elgg_sendgrid:				elgg_sendgrid/edit/<guid>
 *  Group elgg_sendgrid:			elgg_sendgrid/group/<guid>/all
 *
 * Title is ignored
 *
 * @param array $page
 * @return bool
 */
function elgg_sendgrid_page_handler($page) {
	$path = elgg_sendgrid_PATH.'pages/elgg_sendgrid/';
	
	$section = elgg_extract('0', $page, 'index');
	
	switch($section) {
		case 'index':
			include_once("{$path}index_p.php");
			break;
		default:
			return FALSE;
			break;
	}
	
	elgg_pop_context();
	return TRUE;
}

