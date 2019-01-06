<?php

$settings['trusted_host_patterns'] = array(
  '^gendered_drupal\.test$',
  '^genderedtextproject\.lndo\.site$',
  '^genderedtextproject\.com$',
  '^.+-gendered.pantheonsite.io$',
);

$settings['hash_salt'] = 'dDcHZCdnyNTj8xLQDGwTkX7wuIlCn29I2bZI15A5wzlstWRR1VXiyQboqUom1_xIlv9W-14PpA';

$config_directories = array(
  CONFIG_SYNC_DIRECTORY => 'sites/default/config',
);

$settings['file_private_path'] = 'sites/default/files/private';

if (php_sapi_name() != "cli") {
  // Logic to redirect traffic to HTTPS.
  $url = $_SERVER['HTTP_HOST'];
  $redirect = FALSE;
  $www = strpos($url, 'www.');
  if ($www === 0) {
    // The request begins with "www." . Rewrite the URL only to include
    // everything after "www." and trigger the redirect.
    $url = substr($url, 4);
    $redirect = TRUE;
  }

  // Determine the protocol across multiple methods.
  // HTTP_X_FORWARDED_PROTO is an available element on Pantheon
  // $_SERVER['HTTPS'] is what can be accessed on other servers.
  $protocol = "http";
  if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'OFF') {
    $protocol = "https";
  }
  if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
  }
  if ($protocol != 'https') {
    // The request is to HTTP. Trigger the redirect.
    $redirect = TRUE;
  }
  else {
    // Ensure that HTTPS does specify port 443. This is necessary for SAML.
    // See https://github.com/simplesamlphp/simplesamlphp/issues/450
    // and https://github.com/acquia/blt/pull/1958 .
    $_SERVER['SERVER_PORT'] = 443;
  }
  if ($redirect) {
    // Send all traffic to HTTPS.
    header('HTTP/1.0 301 Moved Permanently');
    header('Location: https://' . $url . $_SERVER['REQUEST_URI']);
    header('Cache-Control: public, max-age=3600');
    exit();
  }
}
