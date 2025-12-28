<?php

if (!isset($GLOBALS['semaphore'])) {
  http_response_code(418);
  exit(1);
}

/* Alias for `mailsms` plugin */

@include_once 'plugin_mailsms.php';

function f_mail_redirection($data, $config = false) {
  return f_mailsms_redirection($data, $config);
}

function f_mail_redirection_at($data, $config = false) {
  return f_mailsms_redirection_at($data, $config);
}
