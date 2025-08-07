<?php
/* ╔═════════════════════════════════════════════════════════════════════════╗
 * ║  ClickIt-URL-Shortener Plugin to generate RFC6350 compliant VCARDs      ║
 * ║  @see: https://www.rfc-editor.org/rfc/rfc6350 (technical)               ║
 * ║  @see: https://en.wikipedia.org/wiki/vcard    (simple)                  ║
 * ║                                                                         ║
 * ║  Content-Type: text/vcard   (or is it text/v-card, or text/x-vcard ?)   ║
 * ║  File extension: .vcf or .vcard                                         ║
 * ║                                                                         ║
 * ║  NB!:  All functions must start with `f_vcard_` to prevent conflicts    ║
 * ║        with other plugins.                                              ║
 * ╟─────────────────────────────────────────────────────────────────────────╢
 * ║                                                                         ║
 * ║  THIS IS FAR FROM A COMPLETE IMPLEMENTATION!  IT'S JUST ENOUGH FOR MY   ║
 * ║  USE.  IF YOU WANT TO HELP, PLEASE DO WITH A PR TO THE REPO.            ║
 * ║                                                                         ║
 * ╚═════════════════════════════════════════════════════════════════════════╝
 */

const DEBUG_VC = false;
const PHP_CRLF = "\r\n";  // VCard spec requires CR LF line ends.

// ========== Helpers ==========

function f_vcard_normalizeFileName($str = '') {
  // @see: https://stackoverflow.com/a/19018736
  $str = strip_tags($str);
  $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
  $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
  $str = strtolower($str);
  $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
  $str = htmlentities($str, ENT_QUOTES, "utf-8");
  $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
  $str = str_replace(' ', '-', $str);
  $str = rawurlencode($str);
  $str = str_replace('%', '-', $str);
  return $str;
}

function f_vcard_sanitizeData($data) {
  $clean = [
    'name' => [ 'first' => '', 'last' => '', 'add' => '', 'prefix' => '', 'suffix' => '' ],
    'company' => false,  // set to true when the vCard is for an organisation
    'fname' => '',
    'title' => '',
    'org' => '',  // not doig the X.520 Organization Unit attribute thing
    'tel' => [ 'voice' => '', 'cell' => '' ],
    'email' => [ 'work' => '', 'home' => '' ],
    'url' => ''
    ];

  // NB! `array_merge_recursive` does not work here!  Keep this code
  foreach ( $clean as $key => $value ) {
    if (is_array($value))  {
      // -- Level 1 --
      foreach ( $value as $key1 => $value1 ) {
        if (!isset($data[$key])) $data[$key] = array();
        if (!is_array($data[$key])) {
          // wtf? - this should be an array, so make it so
          $val = $data[$key];
          unset($data[$key]);
          $data[$key] = array();
          $data[$key][] = $val;
        }
        if (!isset($data[$key][$key1]))
          $data[$key][$key1] = $clean[$key][$key1];
      }
    } else {
      if (!isset($data[$key]))
        $data[$key] = $clean[$key];
    }
  }

  if (isset($data['name'][0])) {  // in case name came in as a string
    $data['name']['last'] = $data['name'][0];
    unset($data['name'][0]);
  }

  if (isset($data['tel'][0])) {  // in case name came in as a string
    $data['tel']['voice'] = $data['tel'][0];
    unset($data['tel'][0]);
  }

  if (isset($data['email'][0])) {  // in case name came in as a string
    $data['email']['work'] = $data['email'][0];
    unset($data['email'][0]);
  }

  if (empty($data['fname']))
    $data['fname'] = trim($data['name']['first'] . ' ' . $data['name']['last']);

  return $data;
}

function f_vcard_generateVCard($data) {
  $vcf = 'BEGIN:VCARD' . PHP_CRLF . 'VERSION:4.0' . PHP_CRLF;
  $vcf .= sprintf('N:%s;%s;%s;%s;%s',
    $data['name']['last'],
    $data['name']['first'],
    $data['name']['add'],
    $data['name']['prefix'],
    $data['name']['suffix']
    ) . PHP_CRLF;
  $vcf .= sprintf('FN:%s', $data['fname'] ) . PHP_CRLF;

  // --- optional ---

  if (!empty($data['title'])) {
    $vcf .= sprintf('TITLE:%s', $data['title'] ) . PHP_CRLF;
  }

  if (!empty($data['org'])) {
    if (isset($data['company']) && (true === $data['company'])) {
      $vcf .= 'KIND:org' . PHP_CRLF;
    }
    $vcf .= sprintf('ORG:%s', $data['org'] ) . PHP_CRLF;
  }

  if (!empty($data['tel'])) {
    if (!empty($data['tel']['voice'])) {
      $vcf .= sprintf('TEL;VOICE:%s', $data['tel']['voice'] ) . PHP_CRLF;
    }
    if (!empty($data['tel']['cell'])) {
      $vcf .= sprintf('TEL;CELL:%s', $data['tel']['cell'] ) . PHP_CRLF;
    }
  }

  if (!empty($data['email'])) {
    if (!empty($data['email']['work'])) {
      $vcf .= sprintf('EMAIL;WORK;INTERNET:%s', $data['email']['work'] ) . PHP_CRLF;
    }
    if (!empty($data['email']['home'])) {
      $vcf .= sprintf('EMAIL;HOME;INTERNET:%s', $data['email']['home'] ) . PHP_CRLF;
    }
  }

  if (!empty($data['url'])) {
    $vcf .= sprintf('URL:%s', $data['url'] ) . PHP_CRLF;
  }

  $vcf .= 'END:VCARD' . PHP_CRLF;
  return $vcf;
}

function f_vcard_generateQRCodeURL($data, $qr_code_engine) {
  if (!function_exists('liquefyStr')) {
    echo '`liquefyStr()` not found!';
    die();  // can't be bothered to do this properly, obviously dependant on `index.php`
  }
  return liquefyStr($qr_code_engine, ['data' => urlencode($data)]);
}

// ========== PLUGIN code ==========

function f_vcard_redirection($data, $config = false) {
  $data = f_vcard_sanitizeData($data);

  $filename = f_vcard_normalizeFileName( $data['name']['first'] . ' ' . $data['name']['last'] . '.vcard' );
  header('Content-Type: ' . (DEBUG_VC ? 'text/plain' : 'text/vcard'), true);
  header('Content-Disposition: inline; filename="' .  $filename . '"');

  $vcard = f_vcard_generateVCard($data);
  print( $vcard );
  return true;
}

function f_vcard_redirection_at($data, $config = false) {
  if (!$config || !isset($config['qr_code_engine']) || !isset($config['qr_content_type']) || !isset($config['qr_file_ext'])) {
    f_vcard_redirection($data, $config);
    print('ERROR: Cannot generate QR-Code' . PHP_CRLF);
    return true;
  }

  if (function_exists('http_get_and_print_remote_file')) {
    $data = f_vcard_sanitizeData($data);

    $self = (bool) ($data["self"] ?? false);
    if ($self) {
      $remote_url = f_vcard_generateQRCodeURL( $config["self"].$config["short"], $config['qr_code_engine'] );
    } else {
      $vcard = f_vcard_generateVCard($data);
      $remote_url = f_vcard_generateQRCodeURL($vcard, $config['qr_code_engine']);
    }
    $filename = f_vcard_normalizeFileName( $data['name']['first'] . ' ' . $data['name']['last'] . $config['qr_file_ext'] );

    return http_get_and_print_remote_file($remote_url, $config['qr_content_type'], $filename);
  } else {
    return f_vcard_redirection($data, $config);
  }
}

function f_vcard_redirection_dash($data, $config = false) {
  $data = f_vcard_sanitizeData($data);
  $title = htmlspecialchars($data['fname'], ENT_QUOTES | ENT_HTML5, 'UTF-8');

  if (is_array($config) && isset($config['self']) && isset($config['short'])) {
    $url = $config['self'] . '?u=' . $config['short'];
    $fnm = $url . '@';
  } else {
    $url = false;
    $fnm = 'https://via.placeholder.com/300';
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <style>
    :root {
      --background: #eee;
      --color: #111;
      --border: rgba(128,128,128,0.5);
      --shadow: rgba(0,0,0,0.2) 0px 20px 30px;
    }
    @media (prefers-color-scheme: dark) {
      :root {
        --background: #222;
        --color: #ddd;
        --border: rgba(85,85,85,0.5);
        --shadow: rgba(128,128,128,0.2) 0px 20px 30px;
      }
    }
    body {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
      color: var(--color, default);
      background-color: var(--background);
    }
    h1 {
      margin: 0;
      margin-bottom: 0.5em;
    }
    img {
      max-width: 100%;
      height: auto;
    }
    .f {
      display: block;
      padding: 1rem;
      border-radius: 2rem;
      border: 1px solid var(--border);
      background: #fff !important;
      box-shadow: var(--shadow);
    }
  </style>
</head>
<body>
  <h1><?= $title ?></h1>
  <div class="f">
    <?php if ($url) { ?><a href="<?= $url ?>"><?php } ?><img src="<?= $fnm ?>" alt="<?= $title ?>"><?php if ($url) { ?></a><?php } ?>
  </div>
</body>
<?php
  return true;
}
