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

if (!defined('IN_CLICKIT')) die('Restricted');

include_once('library.php');
include_once('lang.php');

if (isset($settings['meta_description']))
	$page['head'] .= "\t<meta name=\"description\" content=\"" . $settings['meta_description'] . "\" />\n";
if (isset($settings['meta_keywords'])) 
	$page['head'] .= "\t<meta name=\"keywords\" content=\"" . $settings['meta_keywords'] . "\" />\n";

// fix for iPhone, iPad, iPod
$bf = isset($settings['func_getbrowser']) ? 'includes/'.$settings['func_getbrowser'] : false;
if ($bf && file_exists($bf)) :
	include_once($bf);
	$brwsr = _get_browser($_SERVER['HTTP_USER_AGENT']);
	if ($brwsr && preg_match('/^(iPhone|iPad|iPod)/i', $brwsr['browser']) > 0) :
		$page['head'] .= "\t<link rel=\"apple-touch-icon\" href=\"touchicon.png\" />\n";
		$page['head'] .= "\t<meta name=\"viewport\" content=\"width=device-width; initial-scale=1.0; maximum-scale=1.0;\">\n";
		if (!isset($page['head_suffix'])) $page['head_suffix'] = '';
		$page['head_suffix'] .= "\t<link rel=\"stylesheet\" href=\"" . $page['base_path'] . "css/apple.css\" type=\"text/css\" />\n"; 
	endif;
endif;

$use_fb = (isset($settings['facebook_id']) && (!empty($settings['facebook_id'])));

/* $domain = preg_replace("/^www./i", "", $_SERVER['HTTP_HOST']);
foreach ($page as $n => $v) :  // XXX : Debug use only  
	$page[$n] = str_replace(array('c1k.it'), array($domain), $v);
endforeach; */

print __('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
print __('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
	'<!DOCTYPE html>') . "\n";
?>
<html<?php
	print __(' xmlns="http://www.w3.org/1999/xhtml"');
	if ($use_fb) : print ' xmlns:fb="http://www.facebook.com/2008/fbml"'; endif;
?>>
<head>
<?php if (isset($page['head_prefix'])) print $page['head_prefix']; ?>
	<title><?php print $page['head_title']; ?></title>
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
	<link rel="icon" type="image/gif" href="favicon.gif" />
	<base href="<?php @print $page['full_path']; ?>" />
<?php print $page['head']; ?>
	<link rel="stylesheet" type="text/css" href="<?php yuicdn(); ?>build/cssbase/base-min.css" />
	<link rel="stylesheet" type="text/css" href="<?php yuicdn(); ?>build/cssgrids/grids-min.css" />
	<link rel="stylesheet" href="<?php print $page['base_path']; ?>css/style.css" type="text/css" />	
	<script <?php print "type=\"text/javascript\""; /* Eclipse doesn't like 'type' attrib */ ?>>//<![CDATA[
	if (window.screen.colorDepth >= 24) document.documentElement.setAttribute('high-color-depth', 'yes');
	//]]></script>
<?php if (isset($page['head_suffix'])) print $page['head_suffix']; ?>
</head>  
<body>
<div id="cell"><div id="box"><div id="frame" class="rounded shadow">
<div id="branding" class="branding clearfix rounded-top">
<a href="<?php print $page['base_path']; ?>" id="logo-link"><img src="<?php print $page['base_path'] . $page['logo']?>" alt="<?php print $page['site_name']; ?>" id="logo-image" /></a>
<b class="red" style="float:right"><?php print CLICKIT_VER; ?></b>
</div>
<?php if (isset($page['navigation']) && (!empty($page['navigation']))) : ?>
<div id="navigation" class="navigation clearfix"><?php print $page['navigation']; ?></div>
<?php endif; ?>
<?php 
if (isset($messages) && (!empty($messages))) :
	print '<div id="messages">';
	foreach ($messages as $msg => $type) :
		if (empty($type)) $type = 'message';
		print "<div class=\"$type\">$msg</div>";
	endforeach;
	print '</div>';
endif;
?>
<div id="content" class="boxed clearfix rounded-bottom">
<?php if ($page['title']) : ?><h1 id="title"><?php print $page['title']; ?></h1><?php endif; ?>
<?php print $page['content']; ?>
</div></div>
<div id="footer"><?php
	print T('COPYRIGHT', array('url' => 'http://tecsmith.com.au' )) . ' - ' .
		T('LICENSE', array('url' => $page['base_path'] . 'license.php' )) . ' | ' . 
		T('TERMSOS', array('url' => $page['base_path'] . 'tos.php' )) . ' | ' . 
		T('PRIVACY', array('url' => $page['base_path'] . 'privacy.php' )); 
?></div>
<?php if (isset($page['footer'])) print $page['footer']; ?>
</div></div>
<?php if (isset($page['scripts'])) print $page['scripts']; ?>
</body>
</html>		
