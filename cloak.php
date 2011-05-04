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

if (!isset($p_title)) $p_title = pathinfo($_SERVER["PHP_SELF"], PATHINFO_BASENAME);
if (!isset($p_url)) $p_url = pathinfo($_SERVER["PHP_SELF"], PATHINFO_DIRNAME);
if (substr($p_url, -1) != '/') $p_url .= '/';
$p_favicon = parse_url($p_url, PHP_URL_HOST) . '/favicon.ico';
$p_favicon_alt = parse_url($p_url, PHP_URL_HOST) . '/favicon.gif';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title><?php print $p_title; ?></title>
	<link rel="shortcut icon" href="<?php print $p_favicon; ?>" type="image/x-icon" />
	<link rel="icon" href="<?php print $p_favicon_alt; ?>" type="image/gif" />
<?php if (isset($p_metadesc)) print "\t<meta name=\"description\" content=\"$p_metadesc\" />\n" ?>
<?php if (isset($p_metakeyw)) print "\t<meta name=\"keywords\" content=\"$p_metakeyw\" />\n" ?>
<?php /* TODO : CLOAK : Google Analytics code, for Version 03 */ ?>
</head>
<frameset rows="100%,*" border="0" frameborder="no">
	<frame name="__main" src="<?php print $p_url; ?>" noresize frameborder="0" />
	<noframes>
		<body>
		Please visit <a href="<?php print $p_url; ?>"><?php print $p_url; ?></a>.
		<script type="text/javascript">  <?php /* TODO : CLOAK : move to loadscript */ ?>
			<!--
			window.location = "<?php print $p_url; ?>";
			// -->
		</script>
		</body>
	</noframes>
</frameset>
</html>
