<?php
/* ╔═════════════════════════════════════════════════════════════════════════╗
 * ║  ClickIt-URL-Shortener Plugin to generate country specific redirects    ║
 * ║  Use it, for example, to create affiliate links to country sensitive    ║
 * ║  website like Amazon etc.                      .                        ║
 * ║                                                                         ║
 * ║  NB!:  All functions must start with `f_geoip_` to prevent conflicts    ║
 * ║        with other plugins.                                              ║
 * ╟─────────────────────────────────────────────────────────────────────────╢
 * ║                                                                         ║
 * ║  THIS IS FAR FROM A COMPLETE IMPLEMENTATION!  IT'S JUST ENOUGH FOR MY   ║
 * ║  USE.  IF YOU WANT TO HELP, PLEASE DO WITH A PR TO THE REPO.            ║
 * ║                                                                         ║
 * ╚═════════════════════════════════════════════════════════════════════════╝
 */

const GEOIP_REDIRECTION_CODE = 307;
const GEOIP_SERVICE_URL = 'service_url';
const GEOIP_SERVICE_TOKEN = 'service_token';
const GEOIP_DATA_TYPE = 'data_type';
const GEOIP_DATA_KEY = 'data_key';

global $f_geoip_init;
global $f_geoip_defaults;

if (!isset($f_geoip_init)) $f_geoip_init = false;

// TODO : Load these from the `$config` global

if (!isset($f_geoip_defaults))
  $f_geoip_defaults = [
    GEOIP_SERVICE_URL => 'https://ipinfo.io/{{ip}}/json?token={{token}}',
    GEOIP_DATA_TYPE => 'json',
    GEOIP_DATA_KEY => 'country'
  ];

if (!function_exists('array_key_merge')) {
  function array_key_merge(array $src, array $fill): array {
    foreach ($fill as $key => $value) {
        if (!array_key_exists($key, $src)) {
            $src[$key] = $value;
        }
    }
    return $src;
  }
}

function f_geoip_init() {
  global $f_geoip_init;
  global $f_geoip_user_ip;

  if (isset($f_geoip_init) && !!$f_geoip_init) return;

  if (in_array('HTTP_X_FORWARDED_FOR', $_SERVER)) {
    $f_geoip_user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
    $f_geoip_user_ip = false;
  }
  if (!$f_geoip_user_ip) {
    @$f_geoip_user_ip = $_SERVER['REMOTE_ADDR'];
  }

  $f_geoip_init = true;
}

function f_geoip_extract_for_serialize($data) {
  try {
    $data = @unserialize($data);
  } catch(Exception $e) {
    $data = false;
  }
  return $data;
}

function f_geoip_extract_for_json($data) {
  try {
    $data = json_decode($data, true);
  } catch(Exception $e) {
    $data = false;
  }
  return $data;
}

// ========== PLUGIN code ==========

function f_geoip_redirection($data, $config = false) {
  global $f_geoip_defaults;
  global $f_geoip_user_ip;
  global $error;

  f_geoip_init();

  $data = array_change_key_case($data, CASE_LOWER);
  ksort($data);
  if (false !== $config) {
    $config = array_key_merge($config, $f_geoip_defaults);
  } else {
    $config = $f_geoip_defaults;
  }

  $f_auth_token = isset($config[GEOIP_SERVICE_TOKEN]) ? $config[GEOIP_SERVICE_TOKEN] : '';

  $service_url = str_replace(['{{ip}}', '{{token}}'], [urlencode($f_geoip_user_ip), $f_auth_token], $config[GEOIP_SERVICE_URL]);

  if (function_exists('new_file_get_contents')) {
    $response = new_file_get_contents($service_url);
  } else {
    $response = file_get_contents($service_url);
  }

  $fn = 'f_geoip_extract_for_' . strtolower($config[GEOIP_DATA_TYPE]);
  if (!function_exists($fn)) {
    $error = '<tt>' . $fn . '</tt> does not exist';
    return false;
  }

  try {
    $response = $fn($response);
  } catch (Exception $e) {
    $response = false;
  }

  if (!!$response && array_key_exists($config[GEOIP_DATA_KEY], $response)) {
    $country_code = strtolower( $response[$config[GEOIP_DATA_KEY]] );
  } else {
    $country_code = false;
  }

  if (!!$country_code && array_key_exists($country_code, $data)) {
    $dest = $data[$country_code];
  } else {
    if (array_key_exists('_', $data)) {  // `_` is the default
      $dest = $data['_'];
    } else {
      $dest = reset($data);  // fetch 1st item in the array, ps: its alphabetically sorted
    }
  }
  if (strpos($dest, ',')) {
    $tmp = array_map('trim', explode(',', $dest));
    $url = isset($tmp[0]) ? $tmp[0] : false;
    $code = isset($tmp[1]) ? $tmp[1] : GEOIP_REDIRECTION_CODE;
  } else {
    // just a simple string
    $url = $dest;
    $code = GEOIP_REDIRECTION_CODE;
  }

  if (function_exists('http_response_redirection')) {
    http_response_cache_now();
    http_response_redirection($url, $code, 0);  // no cache in case client swaps VPN
    return true;
  } else {
    $error = 'Function `http_response_redirection` not found';
    return false;
  }
}

function f_geoip_redirection_at($data, $config = false) {
  global $short, $url, $error;

  $new_url = $config['self'] . '?u=' . $config['short'];
  $new_url = generateQRCodeURL(urlencode($new_url), $config['qr_code_engine']);

  if (isset($config['qr_content_type'])) {
    $filename = isset($config['qr_file_ext']) ? sanitize($short) . $config['qr_file_ext'] : false;
    http_get_and_print_remote_file($new_url, $config['qr_content_type'], $filename);
  } else {
    http_response_redirection($new_url, 307, 0);  // always 307, always no-cache
  }

  return true;
}

/* Exclude if not adding - `index.php` will show proper not implemented error */
// function f_geoip_redirection_dash($data, $config = false) {
//   global $promise, $error;

//   $promise = 501;
//   $error = '<tt>f_geoip_redirection_dash<tt> not implemented';

//   return false;
// }
