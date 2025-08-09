<?php

const MAIL_SMS_REDIRECTION_CODE = 307;

function f_mailsms_isvalid_email($address) {
  return filter_var($address, FILTER_VALIDATE_EMAIL);
}

function f_mailsms_isvalid_phone($number) {
  return preg_match('/^\+?[1-9]\d{1,14}$/', $number) === 1; // International E.164 format
}

function f_mailsms_isvalid($data) {
  global $error;

  if (!isset($data['to'])) {
    $error = "A <code>to</code> is required.";
    return false;
  } else if (is_string($data['to'])) {
    $data['to'] = array($data['to']);  // convert to array
  }
  $validCnt = 0; // Special case use, -1 -> sms
  if (is_array($data['to'])) {
    foreach ($data['to'] as $address) {
      $validCnt += (is_string($address) && f_mailsms_isvalid_email($address) ? 1 : 0);
    }
    if ((0 == $validCnt) && (1 == count($data['to']))) {
      if (f_mailsms_isvalid_phone($data['to'][0])) {
        $validCnt = -1;
      }
    } else if ($validCnt != count($data['to'])) {
      $error = 'Invalid email address';
      return false;
    }
  }
  if (0 === $validCnt) {
    $error = "Invalid <code>to</code>";
    return false;
  }

  return $validCnt;
}

function f_mailsms_get_1_to($data) {
  global $error;

  $ret = false;
  if (isset($data['to'])) {
    if (is_string($data['to'])) {
      $ret = $data['to'];
    } else if (is_array($data['to']) && count(1 == $data['to'])) {
      $ret = $data['to'][0];
    }
  }
  if (false === $ret) {
    $error = __FILE__ . ":" . __LINE__; // should not get here, but just in case
  }
  return $ret;
}

function f_mailsms_get_to_array($data, $to = "to") {
  global $error;

  $ret = array();
  if (is_string($data[$to])) {
    // TODO : `to` may be a comma seperated list.
    $ret[] = $data[$to];
  } else if (is_array($data[$to])) {
    $ret = $data[$to];
  } else {
    $error = __FILE__ . ":" . __LINE__; // should not get here, but just in case
    return false;
  }
  return $ret;
}

function f_mailsms_get_message($data) {
  $ret = isset($data['body']) ? $data['body'] : false;
  if (false === $ret) {
    $ret = isset($data['message']) ? $data['message'] : false;
    if (false === $ret) {
      $ret = isset($data['content']) ? $data['content'] : false;
    }
  }
  return $ret;
}

function f_mailsms_get_email_url($data) {
  if (!isset($data['to'])) return false;
  $to = f_mailsms_get_to_array($data);
  $cc = f_mailsms_get_to_array($data, 'cc');
  $bcc = f_mailsms_get_to_array($data, 'bcc');
  $sbj = isset($data['subject']) ? $data['subject'] : false;
  $msg = f_mailsms_get_message($data);

  $url = "mailto:";
  $url .= implode(",", $to);
  $params = array();
  if (count($cc) > 0) { $params[] = "cc=" . implode(",", $cc); }
  if (count($bcc) > 0) { $params[] = "bcc=" . implode(",", $bcc); }
  if (is_string($sbj)) { $params[] = "subject=" . rawurlencode($sbj); }
  if (is_string($msg)) { $params[] = "body=" . rawurlencode($msg); }
  if (count($params) > 0) {
    $url .= "?" . implode("&", $params);
  }
  return $url;
}

function f_mailsms_redirection_mail($data, $config = false) {
  $url = f_mailsms_get_email_url($data);
  http_response_cache_now();
  http_response_redirection($url, MAIL_SMS_REDIRECTION_CODE, 0);
  return true;
}

function f_mailsms_redirection_sms($data, $config = false) {
  global $error;

  $to = f_mailsms_get_1_to($data);
  $msg = f_mailsms_get_message($data);
  if (false === $msg) {
    $error = 'SMS message missing';
    return false;
  }

  http_response_cache_now();
  http_response_redirection(
    ("sms:" . $to . "&body=" . rawurlencode($msg)),
    MAIL_SMS_REDIRECTION_CODE,
    0);
  return true;
}

function f_mailsms_redirection_at_mail($data, $config = false) {
  $url = f_mailsms_get_email_url($data);
  $url = liquefyStr($config['qr_code_engine'], ['data' => urlencode($url)]);
  return http_get_and_print_remote_file($url, $config['qr_content_type']);
}

function f_mailsms_sanitiseSmsMessage($message, $preserveNewlines = true) {
  $message = str_replace("\r\n", "\n", $message);
  if ($preserveNewlines) {
    $message = str_replace("\n", '%0A', $message);
  } else {
    $message = str_replace(array("\n", "\r"), ' ', $message);
  }
  $reserved = array(
    ':' => '%3A',
    ';' => '%3B',
    '%' => '%25',
    '&' => '%26',
    '?' => '%3F'
  );
  $message = strtr($message, $reserved);
  $message = preg_replace('/\s+/', ' ', $message);
  return trim($message);
}

function f_mailsms_redirection_at_sms($data, $config = false) {
  global $error;

  if ((!function_exists('http_get_and_print_remote_file')) || (!function_exists('liquefyStr'))) {
    return f_mailsms_redirection($data, $config);
  }

  $to = f_mailsms_get_1_to($data);
  $msg = f_mailsms_get_message($data);
  if (false === $msg) {
    $error = 'SMS message missing';
    return false;
  }

  $qr_data = "SMSTO:" . $to . ":" . f_mailsms_sanitiseSmsMessage($msg);
  $url = liquefyStr($config['qr_code_engine'], ['data' => urlencode($qr_data)]);
  return http_get_and_print_remote_file($url, $config['qr_content_type']);
}

function f_mailsms_redirection($data, $config = false) {
  // Used for normal redirection calls. i.e. `c1k.it/short`

  $validCnt = f_mailsms_isvalid($data);
  if (false === $validCnt) return false;

  if (-1 === $validCnt) {
    return f_mailsms_redirection_sms($data, $config);
  } else {
    return f_mailsms_redirection_mail($data, $config);
  }
}

function f_mailsms_redirection_at($data, $config = false) {
  // Used for displaying QR Code. i.e. `c1k.it/short@`

  $validCnt = f_mailsms_isvalid($data);
  if (false === $validCnt) return false;

  if (-1 === $validCnt) {
    return f_mailsms_redirection_at_sms($data, $config);
  } else {
    return f_mailsms_redirection_at_mail($data, $config);
  }
}
