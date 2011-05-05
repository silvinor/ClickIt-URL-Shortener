<?php
/*
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *	  http://recaptcha.net/plugins/php/
 *    - Get a reCAPTCHA API Key
 *	  https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *	  http://groups.google.com/group/recaptcha
 *
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * Copyleft (?) 2011 Vino Rodrigues
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 *   J. Esteban Acosta Villafane (PLD League)
 *   Wez Furlong
 *   Vino Rodrigues
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * The reCAPTCHA server URL's
 */
define("RECAPTCHA_API_SERVER", "http://www.google.com/recaptcha/api");
define("RECAPTCHA_API_SECURE_SERVER", "https://www.google.com/recaptcha/api");
define("RECAPTCHA_VERIFY_SERVER", "www.google.com");
define("RECAPTCHA_VERIFY_URL", "/recaptcha/api/verify");

/**
 * curl_config : allows configurate cURL service
 * Additional cURL configuration, remember do not use a string as index, please
 * use scURL constant.
 * Note: For simple proxy configuration just define keys: CURLOPT_PROXY and
 *       CURLOPT_PROXYPORT
 * For Example:
 * $GLOBALS['curl_config'][CURLOPT_PROXY] = "proxy.mydomain.com";
 * $GLOBALS['curl_config'][CURLOPT_PROXYPORT] = "8181";
 */
$GLOBALS['curl_config'] = array();

/**
 * Encodes the given data into a query string format
 * @param $data - array of string elements to be encoded
 * @return string - encoded request
 */
function _recaptcha_qsencode($data) {
	$v = explode('.', phpversion());
	if ($v[0] >= 5) return http_build_query($data);

	// PHP 4
	$req = "";
	foreach ( $data as $key => $value )
		$req .= $key . '=' . urlencode( stripslashes($value) ) . '&';
	// Cut the last '&'
	$req = substr($req, 0, strlen($req)-1);
	return $req;
}

/**
 * Submits an HTTP POST to a reCAPTCHA server
 * @param string $host
 * @param string $path
 * @param array $data
 * @param int port
 * @return array response
 */

function _recaptcha_http_post($host, $path, $data, $port = 80)
{
	$m = 'fsockopen';  // fallback, slowest

	if (function_exists('curl_init') && function_exists('curl_exec')) {
		$m = 'curl';  // fastest
	} elseif (function_exists('fopen') && ini_get('allow_url_fopen'))
		$m = 'fopen';  // okayish

	return call_user_func(
		'_recaptcha_http_post_' . $m,
		$host,
		$path,
		$data,
		$port);
}

function _recaptcha_http_post_curl($host, $path, $data, $port = 80)
{
	// This code adapted from "legacy" (J. Esteban Acosta Villafane)
	// http://pldleague.com/code/server-side-coding/recaptcha-php-library-with-curl-support/

	$add_headers = array(
		"Host: $host",
		);

	// merge config array - don't use array_merge as CURLOPT_xx are numeric
	$__config = array(
		CURLOPT_POST => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
		CURLOPT_USERAGENT => "reCAPTCHA/PHP",
		CURLOPT_HEADER => true
		);
	// with provided overrides
	foreach ($GLOBALS['curl_config'] as $n => $v) :
		$__config[$n] = $v;
	endforeach;

	$url = 'http://' . $host . (($port != 80) ? ':' . $port : '') . $path;
	$ch = curl_init( $url );
	curl_setopt_array( $ch , $__config );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $add_headers );
	$response = curl_exec( $ch );
	curl_close($ch);

	if ( $response === false ) {
		trigger_error('Error connecting to ' . $host . '.', E_USER_ERROR);
		return false;
	}

	$response = explode("\r\n\r\n", $response, 2);

	return $response;
}

function _recaptcha_http_post_fopen($host, $path, $data, $port = 80)
{
	// This code adapted from Wez Furlong
	// http://wezfurlong.org/blog/2006/nov/http-post-from-php-without-curl/
	// and PHP Manual
	// http://www.php.net/manual/en/context.http.php

	$optional_headers = "Host: $host\r\n";
	$optional_headers .= "Content-Type: application/x-www-form-urlencoded;\r\n";
	$optional_headers .= "User-Agent: reCAPTCHA/PHP\r\n";

	$params = array(
		'http' => array(
			'method' => 'POST',
			'content' => _recaptcha_qsencode($data),
			'header' => $optional_headers,
            )
		);

	$url = 'http://' . $host . (($port != 80) ? ':' . $port : '') . $path;

	$ctx = stream_context_create($params);

	$handle = @fopen($url, 'rb', false, $ctx);
	if (!$handle) {
		trigger_error("Problem with $url, $php_errormsg", E_USER_ERROR);
		return false;
	}

	$response = @stream_get_contents($handle);
	@fclose($handle);

	if ($response === false)
		trigger_error("Problem reading data from $url, $php_errormsg", E_USER_ERROR);

	// fopen on returns the content part, so create an array so the the
	// response is at array index [1]
	$response = array('', $response);

	return $response;
}

function _recaptcha_http_post_fsockopen($host, $path, $data, $port = 80)
{
	// Code from original recaptchalib.php

	$req = _recaptcha_qsencode($data);

	$http_request  = "POST $path HTTP/1.0\r\n";
	$http_request .= "Host: $host\r\n";
	$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
	$http_request .= "Content-Length: " . strlen($req) . "\r\n";
	$http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
	$http_request .= "\r\n";
	$http_request .= $req;

	$response = '';
	if ( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
		trigger_error('Could not open socket', E_USER_ERROR);
		return false;
	}

	fwrite($fs, $http_request);

	while ( !feof($fs) )
		$response .= fgets($fs, 1160); // One TCP-IP packet
	fclose($fs);
	$response = explode("\r\n\r\n", $response, 2);

	return $response;
}

/**
 * Gets the challenge HTML (javascript and non-javascript version).
 * This is called from the browser, and the resulting reCAPTCHA HTML widget
 * is embedded within the HTML form it was called from.
 * @param string $pubkey A public key for reCAPTCHA
 * @param string $error The error given by reCAPTCHA (optional, default is null)
 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)

 * @return string - The HTML to be embedded in the user's form.
 */
function recaptcha_get_html($pubkey, $error = null, $use_ssl = false, $use_noscript = false)
{
	if ($pubkey == null || $pubkey == '') {
		die("To use reCAPTCHA you must get an API key from <a href='" .
			recaptcha_get_signup_url() . "'>" . recaptcha_get_signup_url() .
			"</a>");
	}

	if ($use_ssl) {
		$server = RECAPTCHA_API_SECURE_SERVER;
	} else {
		$server = RECAPTCHA_API_SERVER;
	}

	$errorpart = "";
	if ($error) {
		$errorpart = "&error=" . $error;
	}
	$html = '<script type="text/javascript"
	src="'. $server . '/challenge?k=' . $pubkey . $errorpart . '">
</script>
';
	if ($use_noscript)
	$html .= '<noscript>
	<iframe src="'. $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="100%" frameborder="0"></iframe><br />
	<textarea name="recaptcha_challenge_field" rows="2" cols="38"></textarea>
	<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
</noscript>
';

	return $html;
}

/**
 * A ReCaptchaResponse is returned from recaptcha_check_answer()
 */
class ReCaptchaResponse {
	var $is_valid;
	var $error;
}

/**
  * Calls an HTTP POST function to verify if the user's guess was correct
  * @param string $privkey
  * @param string $remoteip
  * @param string $challenge
  * @param string $response
  * @param array $extra_params an array of extra variables to post to the server
  * @return ReCaptchaResponse
  */
function recaptcha_check_answer($privkey, $remoteip, $challenge, $response, $extra_params = array())
{
	if ($privkey == null || $privkey == '') {
		die("To use reCAPTCHA you must get an API key from <a href='" .
			recaptcha_get_signup_url() . "'>" . recaptcha_get_signup_url() .
			"</a>");
	}

	if ($remoteip == null || $remoteip == '') {
		die("For security reasons, you must pass the remote ip to reCAPTCHA");
	}

	//discard spam submissions
	if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
		$recaptcha_response = new ReCaptchaResponse();
		$recaptcha_response->is_valid = false;
		$recaptcha_response->error = 'incorrect-captcha-sol';
		return $recaptcha_response;
	}

	$response = _recaptcha_http_post(
		RECAPTCHA_VERIFY_SERVER,
		RECAPTCHA_VERIFY_URL,
		array (
			'privatekey' => $privkey,
			'remoteip' => $remoteip,
			'challenge' => $challenge,
			'response' => $response
			) + $extra_params
		);

	$answers = explode ("\n", $response[1]);
	$recaptcha_response = new ReCaptchaResponse();

	if (trim ($answers[0]) == 'true') {
		$recaptcha_response->is_valid = true;
	}
	else {
		$recaptcha_response->is_valid = false;
		$recaptcha_response->error = $answers[1];
	}
	return $recaptcha_response;
}

/**
 * gets a URL where the user can sign up for reCAPTCHA. If your application
 * has a configuration page where you enter a key, you should provide a link
 * using this function.
 * @param string $domain The domain where the page is hosted
 * @param string $appname The name of your application
 */
function recaptcha_get_signup_url($domain = null, $appname = null) {
	$url = "https://www.google.com/recaptcha/admin/create";
	if ($domain != null) $url .= "?" . _recaptcha_qsencode(
		array('domains' => $domain, 'app' => $appname) );
	return $url;
}

// The MailHide code was removed for this implementation

?>