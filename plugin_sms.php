<?php

/* Alias for `mailsms` plugin */

@include_once 'plugin_mailsms.php';

function f_sms_redirection($data, $config = false) {
  return f_mailsms_redirection($data, $config);
}

function f_sms_redirection_at($data, $config = false) {
  return f_mailsms_redirection_at($data, $config);
}
