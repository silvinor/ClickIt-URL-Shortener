# How to write plugins

Assuming you want to write a plugin called "xyz". i.e. Your `short_urls.json` file contains `"short": { plugin: "xyz" ... }`

File name of the plugin should be: `plugin_xyz.php`.

This file should then contain the following functions:

```php
function f_xyz_redirection($data, $config = false) {
  // Used for normal redirection calls. i.e. `c1k.it/short`
  return true;  // returning `false` will generate a `500` error
}

function f_xyz_redirection_at($data, $config = false) {
  // Used for displaying QR Code. i.e. `c1k.it/short@`
  return true;
}

function f_xyz_redirection_dash($data, $config = false) {
  // Used for displaying HTML information page. i.e. `c1k.it/short-`
  return true;
}
```

The redirection function, if redirecting, should then call:

```php
  http_response_cache_now();  // OR
  http_response_cache_never(); // OR
  http_response_cache_for($secs); // AND
  http_response_redirection($url, $code, $secs);  // no cache in case client swaps VPN
```

The `_at` and `_dash` functions can be omitted, in which case the appropriate *"Not Implemented"* error will display if used.

Any functions you need within should follow the same naming convention: `f_xyz_...`. This prevents any potential conflicts with other plugins.

```php
function f_xyz_your_function_name() {
  // you do you here
}
```

You should then have access to:

```php
  // ** from function calls **
  $data;  // this will contain the data passed from the `short_urls.json` page
  $config;  // this may contain the system $config

  // ** Globals, that you should __not__ be needing **
  globals
    $command,  // Will be `"+"` for plugins
    $promise,  // Will be the same as `$data`
    $content,  // Should be `false`
    $short,    // Should be the short called, e.g. `"short"`
    $url,      // Should be the plugin name, e.g. `"xyz"`
    $error,    // Should be `NULL`
    $config;   // Should contain the entire site config
```
