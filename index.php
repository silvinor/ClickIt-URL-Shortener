<?php
/**
 * @package    c1k.it
 * @author     Silvino R. (@silvinor)
 * @copyright  (c) 2024 - See LICENSE.md for copyright notice and details.
 * @license    MIT
 */
/* ╔═════════════════════════════════════════════════════════════════════════╗
 * ║                                                                         ║
 * ║  ClickIt-URL-Shortener, © 2024 Silvino R.                               ║
 * ║                                                                         ║
 * ╚═════════════════════════════════════════════════════════════════════════╝
 */

const DEFAULT_REDIRECTION_CODE = 307;
const DEFAULT_CACHE_TIME = (24 * 60 * 60);  // 1 day, in seconds
const DEFAULT_CURL_TIMEOUT = 15;  // in seconds
define('DEBUG', (getenv('APP_ENV') === 'development' || ini_get('display_errors')));

if (DEBUG) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

global $config, $command, $promise, $content, $short, $url, $error;

/*
 * You can skip using an external URLs file by populating your list right here.
 * To do that, just uncomment the line below and populate the array with your URLs.
 * Note 1: This will also disable the loading of the `$config` object, so edit that too.
 * Note 2: API will not work if the file `short_urls.json` is not present.
 */
/*
    $urls = [
      's1' => 'http://{your_url}',
      's2' => 'http://{your_url}, 302',
      's3' => [ 'https://{your_url}', 302 ]
    ];
*/

$config = [
  'json_data_filename' => 'short_urls.json',
  // Thanks to https://goqr.me/api/doc/create-qr-code/
  'qr_code_engine' => 'https://api.qrserver.com/v1/create-qr-code/?format=svg&color=000000&bgcolor=FFFFFF&qzone=2&margin=0&size=300x300&ecc=L&data={{data}}',
  'qr_content_type' => 'image/svg+xml',
  'qr_file_ext' => '.svg',
  'extra_css' => '<style>.img-qrcode{width:300px;height:300px}</style>',
  'default_title' => 'c1k.it',
  // Thanks to https://www.srihash.org/ for the SRI Hash Generator
  'bootstrap_css' => [
    'url' => 'https://cdn.jsdelivr.net/gh/silvinor/bootlace@5.3.7/dist/css/bootlace.min.css',
    'hash' => 'sha384-eot1opfzGFlwUV+MPPLx3Bn3q/s3cWjnH2wWSurFX36RR6y5XbAuJ1d9gbQUiYKG'
  ],
  'fontawesome_css' => [
    'url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css',
    'hash' => 'sha384-tGBVFh2h9Zcme3k9gJLbGqDpD+jRd419j/6N32rharcTZa1X6xgxug6pFMGonjxU'
  ],
  'highlight_css' => [
    'url' => 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/default.min.css',
    'hash' => 'sha384-4Y0nObtF3CbKnh+lpzmAVdAMtQXl+ganWiiv73RcGVdRdfVIya8Cao1C8ZsVRRDz'
  ],
  // Don't think I need popper for this project
  // 'popper_js' => [
  //   'url' => 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js',
  //   'hash' => 'sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r'
  // ],
  'bootstrap_js' => [
    'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js',
    'hash' => 'sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK'
  ],
  'highlight_js' => [
    'url' => 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/highlight.min.js',
    'hash' => 'sha384-RH2xi4eIQ/gjtbs9fUXM68sLSi99C7ZWBRX1vDrVv6GQXRibxXLbwO2NGZB74MbU'
  ],
  'copyright' => '&copy; 2011-2025 Silvino R. | <a href="https://github.com/silvinor/clickit-url-shortener"><i class="fa-brands fa-github-alt"></i> ClickIt-URL-Shortener</a>'
];

/* --------- Static content --------- */

$images = [
  'icon.svg' => [
    'c' => 'image/svg+xml',
    'b' => false,
    'h' => 2592000, // 30 days
    'd' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 506 503"><defs><linearGradient id="a" x1="0%" x2="100%" y1="0%" y2="50%"><stop offset="0%" stop-color="#0066A3" stop-opacity=".8"/><stop offset="100%" stop-color="#003B5F" stop-opacity=".8"/></linearGradient></defs><path fill="url(#a)" d="m232.37 218.89 220.2-108.185-220.2-100.27-220.202 100.27z" transform="translate(20.63)"/><path d="M248.923 1.477 28.715 101.747a9.85 9.85 0 0 0-5.766 8.816 9.85 9.85 0 0 0 5.505 8.983l220.208 108.185a9.847 9.847 0 0 0 8.676 0l220.208-108.185a9.85 9.85 0 0 0 5.505-8.983 9.85 9.85 0 0 0-5.766-8.817L257.077 1.476a9.847 9.847 0 0 0-8.154 0ZM253 21.254l197.185 89.792L253 207.923 55.815 111.046 253 21.254Z"/><path fill="url(#a)" d="M237.801 124.77 13.718 12.15l23.593 228.518 202.255 126.233z" transform="translate(-3 126.03)"/><path d="M15.138 129.385a9.847 9.847 0 0 0-14.215 9.807l23.592 228.516a9.847 9.847 0 0 0 4.585 7.346l202.254 126.23a9.847 9.847 0 0 0 15.054-8.422l-1.762-242.131a9.847 9.847 0 0 0-5.423-8.731L15.138 129.385Zm7.224 25.661L225 256.9l1.592 218.2L43.608 360.9 22.362 155.046Z"/><path fill="url(#a)" d="M14.26 124.77 238.343 12.15 214.75 240.667 12.496 366.901z" transform="translate(256.938 126.03)"/><path d="M497.692 128.638a9.847 9.847 0 0 0-6.83.747L266.777 242a9.847 9.847 0 0 0-5.423 8.73l-1.762 242.132a9.848 9.848 0 0 0 15.054 8.423L476.9 375.054a9.847 9.847 0 0 0 4.585-7.346l23.592-228.516a9.847 9.847 0 0 0-7.385-10.554Zm-14.054 26.408L462.392 360.9 279.408 475.1 281 256.9l202.638-101.854Z"/></svg>'
  ],
  'logo.svg' => [
    'c' => 'image/svg+xml',
    'b' => false,
    'h' => 2592000, // 30 days
    'd' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 565 128"><defs><linearGradient id="a" x1="0%" x2="100%" y1="0%" y2="50%"><stop offset="0%" stop-color="#0066A3" stop-opacity=".8"/><stop offset="100%" stop-color="#003B5F" stop-opacity=".8"/></linearGradient></defs><path fill="url(#a)" d="m59 55.578 55.91-27.47L59 2.65 3.09 28.109z" transform="translate(5)"/><path d="M62.965.375 7.053 25.834a2.501 2.501 0 0 0-.067 4.52l55.912 27.468a2.5 2.5 0 0 0 2.204 0l55.912-27.468a2.501 2.501 0 0 0-.067-4.52L65.035.375a2.5 2.5 0 0 0-2.07 0ZM64 5.396l50.066 22.8L64 52.792 13.934 28.195 64 5.396Z"/><path fill="url(#a)" d="M60.38 31.68 3.482 3.085l5.99 58.022 51.354 32.051z" transform="translate(-1 32)"/><path d="M3.605 32.852a2.5 2.5 0 0 0-3.609 2.49l5.99 58.021A2.5 2.5 0 0 0 7.15 95.23l51.354 32.05a2.5 2.5 0 0 0 3.822-2.138l-.447-61.479a2.5 2.5 0 0 0-1.377-2.217L3.605 32.852Zm1.834 6.515L56.891 65.23l.404 55.402-46.461-28.996-5.395-52.268Z"/><path fill="url(#a)" d="M3.62 31.68 60.518 3.085l-5.99 58.022L3.172 93.158z" transform="translate(65 32)"/><path d="M126.129 32.662a2.5 2.5 0 0 0-1.734.19L67.498 61.445a2.5 2.5 0 0 0-1.377 2.217l-.447 61.479a2.5 2.5 0 0 0 3.822 2.138l51.354-32.05a2.5 2.5 0 0 0 1.164-1.866l5.99-58.021a2.5 2.5 0 0 0-1.875-2.68Zm-3.568 6.705-5.395 52.268-46.46 28.996.403-55.402 51.452-25.862Z"/><path fill="url(#a)" d="M150.204 84.625c0-4.794.752-9.306 2.256-13.536 1.504-4.23 3.807-7.896 6.909-10.998 3.102-3.196 7.003-5.687 11.703-7.473 4.794-1.88 10.481-2.82 17.061-2.82 3.948 0 7.52.282 10.716.846 3.196.564 6.439 1.504 9.729 2.82l-3.807 13.959c-1.88-.658-3.995-1.222-6.345-1.692-2.256-.564-5.17-.846-8.742-.846-4.136 0-7.614.517-10.434 1.551-2.726.94-4.935 2.303-6.627 4.089-1.692 1.692-2.914 3.76-3.666 6.204-.752 2.444-1.128 5.076-1.128 7.896 0 6.11 1.692 10.904 5.076 14.382 3.478 3.478 9.353 5.217 17.625 5.217a66.12 66.12 0 0 0 8.46-.564c3.008-.376 5.734-.987 8.178-1.833l2.538 14.241c-2.444.94-5.405 1.692-8.883 2.256-3.478.658-7.567.987-12.267.987-6.768 0-12.596-.893-17.484-2.679-4.794-1.786-8.742-4.23-11.844-7.332-3.102-3.102-5.405-6.768-6.909-10.998-1.41-4.23-2.115-8.789-2.115-13.677Zm94.329 18.753V54.592c-2.632 1.88-5.64 3.572-9.024 5.076-3.384 1.41-6.251 2.538-8.601 3.384l-5.64-14.382c2.256-.846 4.7-1.927 7.332-3.243a110.08 110.08 0 0 0 7.896-4.371 86.694 86.694 0 0 0 7.473-5.076c2.35-1.786 4.371-3.619 6.063-5.499h11.844v72.897h17.343v14.382h-53.016v-14.382h18.33Zm66.834-25.662a179.902 179.902 0 0 0 5.076-6.204 118.303 118.303 0 0 0 5.076-6.909c1.692-2.35 3.29-4.606 4.794-6.768a109.152 109.152 0 0 0 3.948-6.345h20.868a221.125 221.125 0 0 1-5.781 7.05 442.276 442.276 0 0 1-6.768 7.755 179.854 179.854 0 0 1-6.909 7.614 1214.23 1214.23 0 0 0-6.204 6.768c2.35 2.256 4.888 4.982 7.614 8.178a210.613 210.613 0 0 1 8.037 9.729 134.552 134.552 0 0 1 7.191 10.152c2.256 3.384 4.042 6.392 5.358 9.024h-19.881c-1.128-2.35-2.632-4.982-4.512-7.896a108.096 108.096 0 0 0-5.781-8.742 117.208 117.208 0 0 0-6.345-8.178c-2.068-2.632-3.995-4.794-5.781-6.486v31.302h-17.484V22.867l17.484-2.961v57.81Zm92.073 28.905c0 4.136-1.269 7.285-3.807 9.447-2.538 2.162-5.452 3.243-8.742 3.243-1.692 0-3.29-.282-4.794-.846a13.766 13.766 0 0 1-4.089-2.397 15.055 15.055 0 0 1-2.82-3.948c-.658-1.598-.987-3.431-.987-5.499 0-1.974.329-3.713.987-5.217.752-1.598 1.692-2.914 2.82-3.948a13.766 13.766 0 0 1 4.089-2.397c1.504-.564 3.102-.846 4.794-.846 3.29 0 6.204 1.081 8.742 3.243 2.538 2.162 3.807 5.217 3.807 9.165Zm62.604-74.166c0 3.384-1.081 6.063-3.243 8.037-2.162 1.974-4.747 2.961-7.755 2.961s-5.593-.987-7.755-2.961c-2.068-1.974-3.102-4.653-3.102-8.037 0-3.478 1.034-6.204 3.102-8.178 2.162-1.974 4.747-2.961 7.755-2.961s5.593.987 7.755 2.961c2.162 1.974 3.243 4.7 3.243 8.178Zm25.803 82.485c-3.572 1.692-6.956 2.82-10.152 3.384-3.102.658-5.969.987-8.601.987-4.512 0-8.319-.658-11.421-1.974-3.008-1.316-5.452-3.196-7.332-5.64-1.786-2.538-3.055-5.593-3.807-9.165-.752-3.572-1.128-7.661-1.128-12.267V65.872h-18.33V51.49h35.673v41.031c0 3.948.705 6.909 2.115 8.883 1.504 1.88 3.948 2.82 7.332 2.82 1.598 0 3.525-.188 5.781-.564 2.256-.47 4.794-1.363 7.614-2.679l2.256 13.959Zm24.816-49.068h-15.087V51.49h15.087V34.852l17.343-2.82V51.49h27.777v14.382h-27.777v26.79c0 2.444.235 4.418.705 5.922.47 1.504 1.128 2.679 1.974 3.525.846.846 1.88 1.41 3.102 1.692 1.222.282 2.585.423 4.089.423 1.598 0 3.055-.047 4.371-.141a46.274 46.274 0 0 0 3.948-.423 31.12 31.12 0 0 0 3.948-1.128 41.46 41.46 0 0 0 4.512-1.833l2.397 14.946c-3.196 1.316-6.674 2.256-10.434 2.82-3.666.564-7.238.846-10.716.846-4.042 0-7.614-.329-10.716-.987-3.102-.658-5.734-1.927-7.896-3.807s-3.807-4.512-4.935-7.896c-1.128-3.478-1.692-7.99-1.692-13.536V65.872Z" transform="translate(-1)"/><path d="m482.552 109.25 13.91-3.732m-309.33-58.22c-6.814 0-12.81.971-17.964 2.991-4.978 1.894-9.205 4.576-12.578 8.047-3.355 3.358-5.867 7.362-7.486 11.916-1.604 4.51-2.4 9.318-2.4 14.373 0 5.128.742 9.963 2.243 14.467l.008.023.008.024c1.621 4.56 4.137 8.568 7.498 11.93 3.388 3.387 7.666 6.016 12.739 7.906l.008.002.008.004c5.237 1.913 11.354 2.83 18.341 2.83 4.798 0 9.014-.333 12.684-1.024 3.595-.584 6.71-1.366 9.363-2.386l1.926-.74-1.682-9.43-1.732-9.723-2.766.957c-2.252.78-4.808 1.357-7.67 1.715h-.01c-2.815.363-5.53.545-8.14.545-7.911 0-13.064-1.706-15.844-4.477-2.865-2.952-4.357-6.983-4.357-12.623 0-2.598.345-4.974 1.017-7.16.645-2.095 1.648-3.775 3.045-5.172l.024-.023.023-.026c1.382-1.458 3.212-2.612 5.627-3.445l.023-.008.024-.008c2.449-.898 5.644-1.398 9.572-1.398 3.44 0 6.182.283 8.137.771l.056.014.059.012c2.256.45 4.261.99 6.01 1.601l2.53.885 5.1-18.697-2.07-.828c-3.435-1.374-6.843-2.365-10.222-2.961-3.37-.595-7.084-.883-11.15-.883h-.001Zm0 5c3.83 0 7.26.274 10.282.807 2.319.41 4.711 1.225 7.107 2.063l-2.51 9.205c-1.314-.373-2.56-.782-4.034-1.078-2.548-.63-5.615-.91-9.293-.91-4.332 0-8.081.532-11.266 1.695-3.016 1.043-5.587 2.605-7.582 4.701-1.976 1.983-3.411 4.43-4.268 7.213-.831 2.702-1.24 5.589-1.24 8.631 0 6.572 1.891 12.123 5.785 16.125l.012.012.012.013c4.178 4.18 10.77 5.95 19.392 5.95 2.84 0 5.768-.198 8.78-.586h.002c2.124-.266 3.962-.816 5.851-1.315l.764 4.283.914 5.125c-1.911.606-3.852 1.207-6.418 1.623l-.031.006-.034.006c-3.275.62-7.217.944-11.8.944-6.545 0-12.082-.869-16.62-2.526-4.511-1.681-8.126-3.937-10.94-6.752-2.837-2.836-4.924-6.154-6.311-10.042-1.314-3.95-1.983-8.226-1.983-12.866 0-4.532.71-8.75 2.113-12.699 1.387-3.9 3.478-7.223 6.32-10.066l.014-.014.012-.014c2.825-2.91 6.391-5.202 10.799-6.877l.012-.004.011-.005c4.432-1.738 9.807-2.647 16.149-2.647Zm60.786-24.318-.744.829c-1.57 1.744-3.473 3.476-5.717 5.181a84.197 84.197 0 0 1-7.258 4.93 107.578 107.578 0 0 1-7.717 4.271c-2.568 1.284-4.935 2.33-7.091 3.14l-2.377.89 7.455 19.006 2.285-.823c2.407-.866 5.307-2.007 8.717-3.427l.027-.012.026-.012a58.326 58.326 0 0 0 5.51-2.822v41.748h-18.33v19.38h58.015v-19.38h-17.342V27.98h-15.459Zm2.062 5h8.397v72.899h17.342v9.38H227.703v-9.38h18.33V49.734l-3.953 2.825c-2.472 1.765-5.327 3.371-8.57 4.814-2.445 1.018-4.306 1.722-6.188 2.436l-3.853-9.825c1.704-.711 3.38-1.387 5.25-2.322a112.532 112.532 0 0 0 8.074-4.469 89.217 89.217 0 0 0 7.69-5.222c2.107-1.602 3.862-3.28 5.497-4.99Zm62.887-16.033-22.484 3.809v99.504h22.484V93.01c.46.554.838.869 1.317 1.478l.029.04.033.036a114.708 114.708 0 0 1 6.207 8.004l.012.016.012.016c2.02 2.755 3.901 5.6 5.646 8.539l.025.04.024.04c1.837 2.848 3.291 5.398 4.36 7.623l.68 1.418h25.5l-1.808-3.617c-1.378-2.756-3.214-5.84-5.506-9.278a137.095 137.095 0 0 0-7.308-10.316 213.093 213.093 0 0 0-8.133-9.844c-2.164-2.536-4.136-4.562-6.09-6.537 1.466-1.605 2.933-3.213 4.608-5.027l.035-.038a182.353 182.353 0 0 0 6.969-7.681 444.685 444.685 0 0 0 6.771-7.762c2.18-2.56 4.13-4.933 5.848-7.129l3.162-4.04h-27.45l-.718 1.259a106.846 106.846 0 0 1-3.848 6.18 509.187 509.187 0 0 1-4.754 6.713l-.02.027-.017.027a115.807 115.807 0 0 1-4.97 6.762l-.005.006-.004.006c-.202.259-.404.486-.607.742V16.947Zm-5 5.918v92.395h-12.484V24.979l12.484-2.114Zm22.781 31.125h14.198c-.817.99-1.5 1.87-2.4 2.928a439.844 439.844 0 0 1-6.731 7.71l-.016.02-.017.02a177.37 177.37 0 0 1-6.813 7.508l-.017.017-.018.02c-2.26 2.448-4.332 4.71-6.217 6.783l-1.637 1.8 1.756 1.684c2.273 2.182 4.757 4.851 7.442 7.999l.011.013.014.014a208.107 208.107 0 0 1 7.942 9.613 132.031 132.031 0 0 1 7.056 9.963l.014.022.014.019c1.3 1.95 2.083 3.465 3.052 5.137h-14.05c-1.08-2.101-2.255-4.263-3.83-6.707a110.635 110.635 0 0 0-5.89-8.903c-.001-.002-.001-.005-.003-.007a119.686 119.686 0 0 0-6.42-8.274c-2.113-2.687-4.104-4.928-6.002-6.726l-2.406-2.282 2.586-3.017c1.714-2 3.428-4.094 5.138-6.28l.008-.011a120.773 120.773 0 0 0 5.174-7.043c1.684-2.34 3.28-4.59 4.78-6.746l.013-.02.014-.021c1.233-1.85 2.261-3.553 3.256-5.233Zm59.192 37.723c-1.952.005-3.844.34-5.621 1.006a16.316 16.316 0 0 0-4.826 2.828l-.038.033-.037.033c-1.397 1.28-2.524 2.881-3.392 4.727l-.014.031-.014.031c-.819 1.872-1.197 3.967-1.197 6.219 0 2.333.373 4.502 1.176 6.451l.033.084.041.082a17.598 17.598 0 0 0 3.29 4.598l.073.074.078.066a16.322 16.322 0 0 0 4.827 2.83 16.092 16.092 0 0 0 5.672 1.004c3.837 0 7.419-1.332 10.363-3.84 3.137-2.672 4.685-6.702 4.685-11.35 0-4.483-1.575-8.418-4.685-11.067-2.944-2.508-6.526-3.84-10.363-3.84h-.051Zm.015 5h.036c2.742 0 4.989.83 7.12 2.646 1.967 1.675 2.928 3.85 2.928 7.262 0 3.625-.988 5.891-2.927 7.543-2.132 1.816-4.38 2.647-7.121 2.647-1.415 0-2.701-.23-3.916-.686a11.207 11.207 0 0 1-3.262-1.912 12.51 12.51 0 0 1-2.227-3.123c-.497-1.234-.785-2.697-.785-4.469 0-1.684.281-3.054.772-4.185.623-1.313 1.36-2.323 2.197-3.098a11.21 11.21 0 0 1 3.305-1.938c1.205-.451 2.48-.683 3.88-.687Zm64.192-77.897c-.268 0-.536.007-.8.02a13.718 13.718 0 0 0-8.642 3.596l-.021.017-.02.02c-2.598 2.48-3.875 6.013-3.875 9.986 0 3.893 1.291 7.38 3.875 9.846l.02.02.021.017c2.589 2.363 5.887 3.615 9.442 3.615 3.555 0 6.851-1.252 9.44-3.615 2.687-2.454 4.058-5.967 4.058-9.883 0-3.996-1.357-7.556-4.059-10.023-2.588-2.364-5.884-3.616-9.44-3.616Zm0 5c2.461 0 4.333.722 6.068 2.307 1.622 1.481 2.43 3.372 2.43 6.332 0 2.852-.793 4.697-2.43 6.191-1.735 1.585-3.607 2.307-6.068 2.307-2.449 0-4.315-.72-6.043-2.289-1.54-1.479-2.315-3.345-2.315-6.209 0-2.972.79-4.886 2.317-6.351 1.597-1.449 3.308-2.164 5.492-2.274.18-.009.363-.014.549-.014Zm-26.47 25.174v19.381h18.33v21.895c0 4.742.384 8.997 1.18 12.78.81 3.846 2.201 7.234 4.21 10.089l.03.045.032.04c2.141 2.784 4.96 4.94 8.313 6.407l.012.006.013.006c3.503 1.486 7.642 2.172 12.397 2.172 2.827 0 5.852-.356 9.078-1.037 3.447-.613 7.026-1.812 10.746-3.575l1.697-.804-.3-1.854-2.784-17.224-2.998 1.398c-2.677 1.25-5.032 2.065-7.03 2.484-2.127.352-3.922.526-5.308.526-2.881 0-4.39-.708-5.326-1.846-.933-1.343-1.621-3.753-1.621-7.357V48.99h-40.672Zm5 5h30.671v38.531c0 4.246.714 7.721 2.582 10.336l.04.055.042.053c2.076 2.594 5.436 3.76 9.283 3.76 1.794 0 3.843-.206 6.194-.598l.049-.01.048-.01c1.616-.337 3.35-.92 5.141-1.615l1.436 8.883c-2.75 1.172-5.38 2.058-7.803 2.486l-.041.008-.043.01c-2.962.628-5.659.931-8.082.931-4.263 0-7.733-.628-10.432-1.771-2.638-1.157-4.693-2.75-6.305-4.832-1.547-2.213-2.687-4.914-3.377-8.19-.707-3.36-1.074-7.282-1.074-11.751V63.37h-18.33v-9.38Zm102.929-24.898-22.344 3.635V48.99h-15.086v19.381h15.086v24.715c0 5.715.566 10.458 1.815 14.307l.004.01.002.01c1.235 3.706 3.108 6.766 5.668 8.992 2.5 2.173 5.558 3.631 9.017 4.365 3.318.704 7.057 1.041 11.234 1.041 3.614 0 7.313-.293 11.096-.875 3.936-.591 7.61-1.58 11.006-2.979l1.83-.753-1.465-9.13-1.771-11.052-3.008 1.416a39.012 39.012 0 0 1-4.238 1.723l-.024.008-.025.01a28.604 28.604 0 0 1-3.574 1.021c-1.12.17-2.332.301-3.649.389l-.006.002h-.006a59.27 59.27 0 0 1-4.191.135c-1.348 0-2.524-.128-3.527-.36-.812-.187-1.378-.505-1.897-1.023-.486-.486-.973-1.28-1.355-2.504-.355-1.136-.592-2.892-.592-5.176V68.371h27.777v-19.38h-27.777v-19.9Zm-5 5.88V53.99h27.777v9.381h-27.777v29.291c0 2.604.233 4.796.818 6.668.558 1.784 1.388 3.34 2.594 4.547a8.916 8.916 0 0 0 4.307 2.36c1.44.332 2.992.488 4.652.488 1.647 0 3.161-.05 4.549-.149a48.705 48.705 0 0 0 4.148-.445l.073-.012.072-.014a33.633 33.633 0 0 0 4.24-1.21c.548-.184 1.157-.482 1.725-.702l.75 4.674.816 5.092c-2.496.882-5.125 1.602-8.002 2.033l-.006.002h-.004c-3.548.546-6.993.817-10.336.817-3.906 0-7.31-.32-10.197-.932-2.745-.582-4.951-1.662-6.775-3.248-1.763-1.532-3.18-3.737-4.2-6.793-1.005-3.106-1.568-7.381-1.568-12.752V63.371h-15.086v-9.38h15.086V36.978l12.344-2.006Z"/></svg>'
  ],
  'favicon.png' => [
    'c' => 'image/png',
    'b' => true,
    'h' => 2592000, // 30 days
    'd' => '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'
  ]
];


// ---------- Helper functions ---------

/**
 * Check if a valid HTML Respose code is used
 *
 * 301 Moved Permanently
 * The URL of the requested resource has been changed permanently. The new URL is given in the response.
 *
 * 302 Found
 * This response code means that the URI of requested resource has been changed temporarily.
 * Further changes in the URI might be made in the future. Therefore, this same URI should be used by the client in future requests.
 *
 * 307 Temporary Redirect
 * The server sends this response to direct the client to get the requested resource at another URI with the same method that was used in the prior request.
 * This has the same semantics as the 302 response code, with the exception that the user agent must not change the HTTP method used.
 *
 * 308 Permanent Redirect
 * This means that the resource is now permanently located at another URI, specified.
 * This has the same semantics as the 301 response code, with the exception that the user agent must not change the HTTP method used.
 *
 * @param int $code The code requeted
 * @return int Returns the code requested if valid, else returns DEFAULT_REDIRECTION_CODE
*/
function validateRedirectionHtmlResponseCode($code) {
  return (in_array($code, array(301, 302, 307, 308))) ? $code : DEFAULT_REDIRECTION_CODE;
}

/**
 * Validates if a short URL is not a reserved command
 *
 * These are:
 * - `-` - show info page
 * - `@` - show QR-code
 * - `*` - show sitemap.xml, disabled if private
 * - `+` - pass data to a plugin
 * - `#` - alias to another short
 * - `e` - show error page
 * - `h` - "hello" or "home" page
 * - `f` - "file" fetch internal file, e.g. logo.png
 * - `u` - redirect URL
 * - `x` - settings missing, show "needs install" page
 */
// function validateShortURLisNotCommand($s_url) {
//   return !in_array($s_url, ['-', '@', '*', '+', 'e', 'h', 'f', 'u', 'x']);
// }

/**
 * Check if the second character of a string is an "=" character.
 *
 * @param string $str The string to check.
 * @return bool Returns true if the second character is "=", false otherwise.
 */
function isSecondCharAnEqual($str) {
  if (!is_string($str) || strlen($str) < 2) { return false; }
  return $str[1] === '=';
}

if (DEBUG) {
  /**
   * for DEGUG-ing
   */
  function var_dump_ret($mixed, $name = false, $echo = false) {
    ob_start();
    var_dump($mixed);
    $ret = htmlspecialchars(ob_get_contents(), ENT_QUOTES);
    ob_end_clean();

    $ret = (!$name ? '' : '<b>' . $name . '</b> = ') . '<code>' . $ret . '</code><br>';
    if (!$echo) return $ret;

    echo str_replace(['&quot;', '&gt;', '&lt;'], ['"', '>', '<'], strip_tags( $ret ));
  }

  function dump_globals($echo = true) {
    global $config, $command, $promise, $content, $short, $url, $error;
    var_dump_ret($command, '$command', $echo);
    var_dump_ret($promise, '$promise', $echo);
    var_dump_ret($content, '$content', $echo);
    var_dump_ret($short, '$short', $echo);
    var_dump_ret($url, '$url', $echo);
    var_dump_ret($error, '$error', $echo);
    var_dump_ret($config, '$config', $echo);
  }
}

function strip_trailing_slash($url) {
  return rtrim($url, '/');
}

function add_trailing_slash($url) {
  return strip_trailing_slash($url) . '/';
}

/**
 * Get the URL of the calling PHP file
 * !! not the best implementation, but PHP does not do this well
 */
function getCurrentUrl() {
  global $config;

  if (isset($config->base_url) && !empty($config->base_url)) {
    $base = strip_trailing_slash( $config->base_url );
  } else {
    $base = strip_trailing_slash( 'http' .
      ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') .
      '://' .
      $_SERVER['HTTP_HOST'] .
      dirname($_SERVER['PHP_SELF']) );
  }

  return add_trailing_slash($base) . basename($_SERVER['PHP_SELF']);
}

/**
 * Assume the query string of the calling PHP file
 * !! not the best way to do this, but hell!, what does work ???
 */
function getCurrentQuery() {
  $self = dirname($_SERVER['PHP_SELF']) . '/' . basename($_SERVER['PHP_SELF']);
  $ret = $_SERVER['REQUEST_URI'];

  $p = strpos($ret, $self);
  if (0 === $p) $ret = substr($ret, strlen($self));
  $p = strpos($ret, '?');
  if (false !== $p) {
    // get part after "?"
    $ret = substr($ret, $p + 1);
  } else {
    // strip "/" from stings incase this is installed in a sub-folder
    $ret = explode('/', $ret);
    $ret = $ret[ count($ret) - 1 ];  // get the last one
  }

  return $ret;
}

/**
 * find the `command=promise` pair in `$_REQUEST`
 */
function processQueryString($find) {
  global $command, $promise;
  if ($command !== false) return;  // already has one

  $find = strtolower($find);
  if (isset($_REQUEST[$find])) {
    $command = $find;
    $promise = $_REQUEST[$find];
  } else if (isset($_REQUEST[strtoupper($find)])) {
    $command = $find;
    $promise = $_REQUEST[strtoupper($find)];
  } else {
    $promise = false;
  }
  if (empty($promise)) $promise = false;
}

/**
 * Sets the HTTP response headers so that the client does not cache the result
 */
function http_response_cache_now() {
  $ts = time();
  header('Expires: ' . date(DATE_RFC822, $ts));
  header('Last-Modified: ' . date(DATE_RFC822, $ts));
  header('Cache-Control: no-cache, must-revalidate');
  header('Pragma: no-cache' );
}

/**
 * Sets the HTTP response headers so that the client won't ask for the resource again
 */
function http_response_cache_never() {
  header('Cache-Control: max-age=31536000, immutable');
}

/**
 * Sets the HTTP response header so that the client caches the result for `$secs` seconds
 *
 * @param int $secs The amount of seconds to add to now
 */
function http_response_cache_for($secs = 0) {
  if (0 == $secs) {
    http_response_cache_now();
    return;
  } else if (0 > $secs) {
    http_response_cache_never();
    return;
  }

  $ts = time();
  header('Expires: ' . date(DATE_RFC822, $ts + $secs));
  header('Retry-After: ' . date(DATE_RFC822, $ts + $secs - 1));
  header('Last-Modified: ' . date(DATE_RFC822, $ts));
  header('Cache-Control: max-age=' . $secs . ', must-revalidate');
  header('vary: User-Agent');
  header('ETag: W/"' . date('YmdHis', $ts) . '"');
}

/**
 * This is the main redirection code!!
 */
function http_response_redirection($url, $code, $cache_for) {
  if (DEBUG) {
    print '<html><body>';
    print 'Redirect to <code>' . $url . '</code><br>';
    print '<pre><code>';
    dump_globals();
    print '</code></pre>';
    print '</body></html>';
    die();
  }

  http_response_code($code);
  header('Location: ' . $url, TRUE, $code);
  http_response_cache_for($cache_for);
  header('Referer: ' . getCurrentUrl());  // be nice and tell the other server where you came from

  // just in case all goes wrong on the browser
  echo '<html><body>';
  echo 'Redirect to <a href="' . $url . '">' . mb_strimwidth($url, 0, 35, '...') . '</a>.';
  echo '</body></html>';
}

/**
 * A function that uses `{{` `}}`, like [Liquid](https://shopify.github.io/liquid/), to replace text.
 *
 * Usage: `liquefyStr("Hello {{name}}, welcome to {{place}}!", ['name' => 'Alice', 'place' => 'Wonderland']);`
 */
function liquefyStr($str, $replacements = []) {
  if (!empty($replacements && is_array($replacements))) {

    // Create the regex pattern
    $pattern = "/" . preg_quote('{{', '/') . "\s*(.*?)\s*" . preg_quote('}}', '/') . "/i";

    // Use a callback function to handle replacements
    $callback = function($matches) use ($replacements) {
        $key = strtolower(trim($matches[1]));
        return isset($replacements[$key]) ? $replacements[$key] : $matches[0];
    };

    // Perform the replacement
    $str = preg_replace_callback($pattern, $callback, $str);

  }
  return $str;
}

/**
 * generates the URL for the QR-Code API provider
 */
function generateQRCodeURL($url, $qr_code_engine) {
  return liquefyStr($qr_code_engine, ['url' => rawurlencode($url)]);
}

/**
 *
 */
function new_file_get_contents($url) {
  global $error;
  $error = $curl = $data = false;

  if (function_exists('curl_init')) $curl = @curl_init();  // Initialize cURL
  if (false !== $curl) {
    curl_setopt( $curl, CURLOPT_URL, $url );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true ); // Follow redirects (if any)
    curl_setopt( $curl, CURLOPT_MAXREDIRS, 10 );
    curl_setopt( $curl, CURLOPT_HEADER, false );
    curl_setopt( $curl, CURLOPT_BINARYTRANSFER, true );
    curl_setopt( $curl, CURLOPT_HTTPGET, true );
    curl_setopt( $curl, CURLOPT_TIMEOUT, DEFAULT_CURL_TIMEOUT ); // Set a timeout in seconds
    curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, DEFAULT_CURL_TIMEOUT );

    $data = curl_exec( $curl );
    if (false === $data) {  // fetch and dump
      $error = 'cURL Error ' . curl_errno($curl) . ': ' . curl_error($curl);
    }
    curl_close( $curl );  // close cURL resource, and free up system resources
  } else {
    // cURL is not avail.
    $opts = [ 'http' => [ 'method' => 'GET' ]];
    $context = stream_context_create($opts);
    $data = file_get_contents( $file_url, false, $context );
    if (false === $data) {
      $error = 'PHP `file_get_contents` Error: ' + error_get_last();
    }
  }
  return $data;
}

/**
 * fetches an external file with cURL or ``
 */
function http_get_and_print_remote_file($file_url, $content_type, $filename = false, $cache_for = DEFAULT_CACHE_TIME) {
  global $error;
  $error = false;

  header('Content-Type: ' . $content_type, true);
  if (false !== $filename) {
    header('Content-Disposition: inline; filename="' .  $filename . '"');
  }
  http_response_cache_for( $cache_for );

  $content = new_file_get_contents($file_url);
  if (false !== $content) {
    print( $content );
  } else {
    header('Content-Type: text/plain', true);
    echo $error;
  }
  return !!$content;  // cast as bool
}

/**
 * Translates the JSON error code to english
 */
function json_last_error_text() {
  global $json_exception;
  if (isset($json_exception)) {
    $r = $json_exception->getMessage();
  } else {
    $e = json_last_error();
    $r = $e;
    // @see: https://www.php.net/manual/en/function.json-last-error.php, for descriptions
    switch ($e) {
      case JSON_ERROR_DEPTH: $r = 'JSON_ERROR_DEPTH'; break;
      case JSON_ERROR_STATE_MISMATCH: $r = 'JSON_ERROR_STATE_MISMATCH'; break;
      case JSON_ERROR_CTRL_CHAR: $r = 'JSON_ERROR_CTRL_CHAR'; break;
      case JSON_ERROR_SYNTAX: $r = 'JSON_ERROR_SYNTAX'; break;
      case JSON_ERROR_UTF8: $r = 'JSON_ERROR_UTF8'; break;
      case JSON_ERROR_RECURSION: $r = 'JSON_ERROR_RECURSION'; break;
      case JSON_ERROR_INF_OR_NAN: $r = 'JSON_ERROR_INF_OR_NAN'; break;
      case JSON_ERROR_UNSUPPORTED_TYPE: $r = 'JSON_ERROR_UNSUPPORTED_TYPE'; break;
      case JSON_ERROR_INVALID_PROPERTY_NAME: $r = 'JSON_ERROR_INVALID_PROPERTY_NAME'; break;
      case JSON_ERROR_UTF16: $r = 'JSON_ERROR_UTF16'; break;
    }
  }
  return $r;
}

/**
 * Calls `json_decode` with the correct settings
 */
function json_decode_helper($string) {
  global $json_exception;
  try {
    $r = json_decode($string, true, JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_SUBSTITUTE | JSON_OBJECT_AS_ARRAY | JSON_THROW_ON_ERROR );
  } catch (Exception $e) {
    $json_exception = clone $e;
    $r = false;
  }
  return $r;
}

if (!function_exists('array_is_list')) {
  /**
   * Determines if the given array is a list.
   * An array is considered a list if its keys consist of consecutive numbers from 0 to count($array)-1.
   */
  function array_is_list(array $arr) {
      if ($arr === []) return true;
      return array_keys($arr) === range(0, count($arr) - 1);
  }
}

/**
 * Clean a string for use as a filename or function name
 */
function sanitize($string) {
  return preg_replace('/[^a-z0-9_]/', '', strtolower($string));
}

/**
 * Get / Generate a Unique Client ID
 */
function getUserID($visitorIp = flase) {
  global $uid, $dnt;

  if ($visitorIp === false) $visitorIp = $_SERVER['REMOTE_ADDR'];

  if (!isset($uid)) {
    $uid = (isset($_COOKIE['uid']) ? $_COOKIE['uid'] : false); // get prior uid from cookie
    if (($uid !== false) && (strlen($uid) != 16)) $uid = false; // invalid
    if ($uid === false) $uid = substr(md5($visitorIp . time()), 0, 16); // Unique Visitor ID
  }
  if (!isset($dnt)) {
    $dnt = (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] === '1') ? true : false;
    $dnt = !$dnt ? false : (isset($_COOKIE['dnt']) && ($_COOKIE['dnt'] === '1' || $_COOKIE['dnt'] === true));
  }

  if (!$dnt) setcookie('uid', $uid, strtotime('+1 year')); // Remember

  return $uid;
}

/**
 * Optional: Send data to a Matomo server for tracking
 *
 * Needs the following in your config section of the short_urls.json file:
 * "config": {
 *   "matomo": {
 *     "id": 1, // Replace with your site ID in Matomo
 *     "url": "https://your.matomo.url", // Replace with your Matomo URL, excluting the php file name
 *     "timeout": 5 // Timeout in secs for CuRL call
 *   }
 * }
 */
function recordToMatomo() {
  global $uid, $dnt, $config, $command, $short;

  if (!property_exists($config, 'matomo') || !isset($config->matomo)) {
    return false;
  }

  $siteId = isset($config->matomo['id']) ? $config->matomo['id'] : false;
  $matomoUrl = isset($config->matomo['url']) ? $config->matomo['url'] : false;
  if (!$siteId || !$matomoUrl) {
    return false;
  }
  $matomoUrl = add_trailing_slash($matomoUrl) . 'matomo.php'; // API call
  $timeOut = isset($config->matomo['timeout']) ? $config->matomo['timeout'] : DEFAULT_CURL_TIMEOUT;
  $visitorIp = $_SERVER['REMOTE_ADDR']; // Get visitor's IP
  $uid = getUserID($visitorIp);
  $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

  $params = [
    'idsite'      => $siteId,
    'rec'         => 1, // Required to record the visit
    'apiv'        => 1, // API version
    'action_name' => ('command=' . $command . ',short=' . $short ),
    'url'         => (getCurrentUrl() . $_SERVER['REQUEST_URI']),
    'rand'        => mt_rand(), // Random number to prevent caching
    'urlref'      => ($_SERVER['HTTP_REFERER'] ?? ''),
    'h'           => date('H'),
    'm'           => date('i'),
    's'           => date('s'),
    'lang'        => ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''),
    'ua'          => $userAgent,
    'uid'         => $uid, // Unique Visitor ID
  ];

  if (!isset($dnt) || $dnt !== true) {
    $params['cip'] = $visitorIp;
    $params['cdt'] = date("c");
    $params['token_auth'] = (isset($config->matomo['token_auth']) ? $config->matomo['token_auth'] : "");
  }

  $curl = curl_init();
  curl_setopt( $curl, CURLOPT_URL, $matomoUrl . '?' . http_build_query($params));
  curl_setopt( $curl, CURLOPT_USERAGENT, $userAgent);
  curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
  curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true ); // Follow redirects (if any)
  curl_setopt( $curl, CURLOPT_MAXREDIRS, 3 );
  curl_setopt( $curl, CURLOPT_HEADER, false );
  curl_setopt( $curl, CURLOPT_BINARYTRANSFER, true );
  curl_setopt( $curl, CURLOPT_HTTPGET, true );
  curl_setopt( $curl, CURLOPT_TIMEOUT, $timeOut ); // Set a timeout in seconds
  curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $timeOut );

  $data = curl_exec( $curl );
  if (false === $data) {  // fetch and dump
    $data = 'cURL Error ' . curl_errno($curl) . ': ' . curl_error($curl);
  }
  curl_close( $curl );  // close cURL resource, and free up system resources
  return $data;
}

function csv_encode($data) {
  if (is_object($data)) $data = (array) $data;
  $flatData = array_filter($data, function ($value) {
    return !is_array($value) && !is_object($value);
  });
  $temp = fopen('php://temp', 'r+');
  fputcsv($temp, array_values($flatData));
  rewind($temp);
  $csv = stream_get_contents($temp);
  fclose($temp);
  return $csv;
}

/**
 * Log to file
 * !! WARNING : Don't use this is a high volume site, log files are not managed
 */
function recordToLogFile() {
  global $logfile, $config, $command, $short;

  if (!property_exists($config, 'log') || !isset($config->log) || ($config->log === false)) {
    return false;
  }

  if (!isset($logfile)) {
    $logfile = $config->log;
    if ($logfile === true ) { $logfile = 'log_file.log'; }
    $logfile = add_trailing_slash(__DIR__) . $logfile;
  }

  $visitorIp = $_SERVER['REMOTE_ADDR'] ?? '-'; // Get visitor's IP
  $message = [
    'd'           => date("c"),
    'command'     => $command,
    'short'       => $short,
    'uri'         => $_SERVER['REQUEST_URI'],
    'uid'         => getUserID($visitorIp),
    'ip'          => $visitorIp,
    'lang'        => ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''),
    'referer'     => ($_SERVER['HTTP_REFERER'] ?? ''),
    'ua'          => ($_SERVER['HTTP_USER_AGENT'] ?? ''),
    'url'         => getCurrentUrl() // FIXME : Delete - only here as a quick debug for rouge links to the site
  ];
  $message = csv_encode($message);
  error_log($message, 3, $logfile);
}


/* ---------- End of helpers --------- */

/* ┌────────────────────────────────┐
 * │  The good stuff starts here!!  │
 * └────────────────────────────────┘
 */

if (!isset($GLOBALS['semaphore'])) {
  main();
}

function main() {
  global $config, $command, $promise, $content, $short, $url, $error;

  // Initialize Globals
  $command = $promise = $content = $short = $url = $expiry = false;
  $config = (object) $config;
  if (!isset($urls)) $urls = false;

  header('X-Powered-By: ClickIt-URL-Shortener', true);
  header('X-DNS-Prefetch-Control: off');

  processQueryString('f');  // Internal files, e.g. logo

  /*
  * Note: Data will always load from in-file '$config->json_data_filename',
  * but once loaded the content of the json may override the $config object.
  */
  if ($command === false) {
    if ((false === $urls) && file_exists($config->json_data_filename)) {
      $json_data = json_decode_helper( @file_get_contents($config->json_data_filename) );
      if ((null == $json_data) || (JSON_ERROR_NONE !== json_last_error()) ) {
        $command = 'e';
        $content = '<p>JSON error <b>' . json_last_error_text() . '</b> in file <code>' . $config->json_data_filename . '</code></p>';
        $promise = 500;
      } else {
        if (isset($json_data['urls'])) $urls = $json_data['urls'];
        if (isset($json_data['config'])) $config = (object) array_merge( (array) $config, $json_data['config'] );
      }
    } else {
      // JSON file not there, ask for it; a.k.a. needs install
      $command = 'x';
    }
  }

  if (DEBUG) {
    if (isset($config->base_url)) unset($config->base_url);
  }

  processQueryString('e');  // Errors
  if (('e' == $command) && !$promise) $promise = 405;

  processQueryString('u');  // URL
  /* Special case where .htaccess file 'generate's a query string like this `u=f=logo.svg` */
  if ( ('u' == $command) && (strlen($promise) > 2) && isSecondCharAnEqual($promise) )  {
    $command = strtolower( substr($promise, 0, 1) );
    $promise = substr($promise, 2);
  }

  processQueryString('@');  // asking for QR-code
  processQueryString('*');  // asking for sitemap.xml

  if (!$command) {
    // Command not set yet, so assume it's a redirection URL
    $command = 'u';
    $promise = getCurrentQuery();
  }

  // ----- Special cases -----
  if ($command == 'u') {
    if (($promise == '*') || ($promise == '@') || ($promise == '-')) {
      $command = $promise;
      $promise = false;
    }
  }

  /* ┌────────────┐
   * │  Lets go!  │
   * └────────────┘
   */
  if (in_array($command, ['u', '@', '-']))  {
    // no query situation - if there is a url named `0` then do that, else show hello screen ;)
    if (empty($promise)) $promise = '0';

    // Lets go! ... find the url in the datafile

    if (false !== $urls) {
      $promise = strtolower($promise);
      if (in_array($command, ['@', '-'])) {
        $special = $command;
      } else {
        $special = substr($promise, -1);
        if (in_array($special, ['@', '-'])) {
          $promise = substr($promise, 0, strlen($promise) - 1);
        } else {
          $special = false;
        }
      }

      $dest = isset($urls[$promise]) ? $urls[$promise] : false;

      // process Aliases ... they start with '#'

      function __is_hash($s) {
        return is_string($s) && strlen($s) > 0 && $s[0] === '#';
      }

      while ( __is_hash($dest) ) {
        $promise = substr($dest, 1);
        $dest = isset($urls[$promise]) ? $urls[$promise] : false;
      }

      if (false !== $dest) {
        $short = $promise;  // keep this for info and QR-code gen
        $url = false;
        $expiry = false;
        if (is_array($dest)) {

          if (array_is_list($dest)) {
            $url = isset($dest[0]) ? $dest[0] : false;
            $promise = isset($dest[1]) ? $dest[1] : DEFAULT_REDIRECTION_CODE;
            $expiry = isset($dest[2]) ? $dest[2] : false;
          } else {
            if (isset($dest['plugin']) && !empty($dest['plugin'])) {
              $url = sanitize($dest['plugin']);
              $promise = $dest;
              unset($promise['plugin']);
              $command = '+';
            } else {
              $url = isset($dest['url']) ? $dest['url'] : false;
              $promise = isset($dest['code']) ? $dest['code'] : DEFAULT_REDIRECTION_CODE;
            }
          }
        } else if (is_string($dest)) {
          if (strpos($dest, ',')) {
            $tmp = array_map('trim', explode(',', $dest));
            $url = isset($tmp[0]) ? $tmp[0] : false;
            $promise = isset($tmp[1]) ? $tmp[1] : DEFAULT_REDIRECTION_CODE;
            $expiry = isset($tmp[2]) ? $tmp[2] : false;
          } else {
            // just a simple string
            $url = $dest;
            $promise = DEFAULT_REDIRECTION_CODE;
          }
        }  // `$url == false` will handle the rest

        if (false == $url) {
          $command = 'e';
          $promise = 500;
        } else if (false !== $expiry) {
          $expiry = strtotime($expiry);
        }

        if (('u' == $command) && (false !== $special)) $command = $special;

      } else if ('0' == $promise) {
        // no default, so show hello
        $command = 'h';
      } else {
        // URL not found conditon!
        $command = 'e';
        $short = $promise;
        $promise = 404;
      }
    }
  }

  // all is good, but has it expired?
  if ((false !== $expiry) && ($expiry <= time())) {
    $command = 'e';
    $short = $promise;
    $promise = 418;
  }

  /* ┌─────────────────────────────────┐
   * │  This is the command processor  │
   * └─────────────────────────────────┘
   */

  $title = $config->default_title;
  $inc_fa = false;
  $inc_js = false;
  $inc_highlighter = false;
  $color = 'default';

  if (property_exists($config, 'matomo') && isset($config->matomo)) {
    recordToMatomo();
  }

  if (property_exists($config, 'log') && isset($config->log) && ($config->log !== false)) {
    recordToLogFile();
  }

  switch ($command) {

    case '-':
      // --- info page ---

      $heading = '<i class="fa fa-circle-info"></i> Info';
      $inc_fa = true;
      if (DEBUG) {
        $filename = getCurrentUrl() . '?u=' . $short . '@';
      } else {
        $filename = generateQRCodeURL($url, $config->qr_code_engine);
      }
      $content = '<p class="">';
      if (!empty($short)) $content .= 'Short URL = <code>' . $short . '</code><br>';
      if (!empty($url)) $content .= 'Destination URL = <code>' . $url . '</code><br>';
      if (!empty($promise)) $content .= 'Redirecting with code: <code>' . $promise . '</code>';
      $content .= '</p>';

      if (!empty($url)) $content .= '<p class="text-center"><img src="' . $filename . '" class="img-fluid img-thumbnail img-qrcode"></p>';
      if (!empty($url)) $content .= '<div class="text-center"><a class="btn btn-lg btn-outline-secondary" href="' . $url . '"' .
                  ' title="' . $url . '"' .
                  ' target="_blank"' .
                  '>' . mb_strimwidth($url, 0, 25, '...') . ' <i class="ml-2 fas fa-up-right-from-square"></i></a></div>';

      break;

    case '@':
      // --- QR-code ---

      // 'qr_content_type' => 'image/svg+xml',
      // 'qr_file_ext' => '.svg',

      $url = generateQRCodeURL($url, $config->qr_code_engine);

      if (isset($config->qr_content_type)) {
        $filename = isset($config->qr_file_ext) ? sanitize($short) . $config->qr_file_ext : false;
        http_get_and_print_remote_file($url, $config->qr_content_type, $filename);
      } else {
        http_response_redirection($url, 307, 0);  // always 307, always no-cache
      }
      die(); // !!!
      break;

    case '*':
      // --- SiteMap.XML ---

      $private = !empty($urls['0']);
      if (!$private) {

        header('Content-Type: text/xml', true);
        header('Content-Disposition: inline; filename="sitemap.xml"');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        // echo '<!-- This should never get called. The robots.txt file prohibits it. -->' . PHP_EOL;
        // echo '<?xml-stylesheet type="text/xsl" href="sitemap.xsl"' . '?' . '>' . PHP_EOL;
        echo '<urlset' .
          ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' .
          // ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' .
          // ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"' .
          '>' . PHP_EOL;

        if (!empty($urls)) {
          foreach ($urls as $short => $url) {
            echo "\t" .'<url>';
            echo '<loc>' . strip_trailing_slash(getCurrentUrl()) . '?u=' . rawurlencode($short) . '</loc>';
            // not doing `<lastmod>`
            echo '</url>' . PHP_EOL;
          }
        }
        echo '</urlset>';
        die(); // !!!

      } else {

        $command = 'e';
        $promise = 401;

      }
      break;

    case '+':
      // --- Plugin engine ---

      $fn = 'plugin_' . $url . '.php';
      if (file_exists($fn)) {

        global $semaphore;
        $semaphore = true;

        @include_once( $fn );  // load the plugin

        $cnf = 'plugin_' . $url;  // extract configuration from config
        if (isset($config->$cnf)) {
          $cnf = json_decode(json_encode($config->$cnf), true);  // nasty, but can go deep
        } else {
          $cnf = array();
        }

        $cnf['short'] = $short;
        $cnf['self'] = strip_trailing_slash(getCurrentUrl());

        $fn = 'f_' . $url . '_redirection';
        if (isset($special)) {
          switch ($special) {
            case '@':
              $fn .= '_at';
              $cnf['qr_code_engine'] = $config->qr_code_engine;
              if (isset($config->qr_content_type))
                $cnf['qr_content_type'] = $config->qr_content_type;
              if (isset($config->qr_file_ext))
                $cnf['qr_file_ext'] = $config->qr_file_ext;
              break;
            case '-':
              $fn .= '_dash';
              break;
            }
        }
        if (function_exists($fn)) {
          $result = $fn($promise, $cnf); // call the plugin function, with conf.
          if ($result) {
            die(); // !!! All good, plugin did its thing.
          } else {
            $command = 'e';
            if (!is_numeric($promise)) {
              $promise = 500;
            } elseif ($promise < 500 || $promise > 599) {
              $promise = 510;
            }
            if (isset($error) && !!$error) {
              $content = '<h2 class="text-center"><small class="text-muted">';
              $content .= '<tt>' . strtoupper($url) . '</tt> Plugin Error: ';
              $content .= '<i>' . $error . '</i>';
              $content .= '</small></h2>';
            }
          }
        } else {
          $command = 'e';
          $promise = 501;
          $url = $fn . '()';
        }
      } else {
        $command = 'e';
        $promise = 501;
      }
      break;

    case 'e':
      /* ┌─────────────────────────────────┐
       * │  Ignore for now, process later  │
       * └─────────────────────────────────┘
       */
      break;

    case 'h':
      $heading = '<i class="fas fa-handshake"></i> Hello';
      $inc_fa = true;
      $content = '<div class="text-center">My name is <tt><b>' . $_SERVER['SERVER_NAME'] . '</b></tt></div>';
      break;

    case 'f':
      // --- Include files, inline ---
      if ( !empty($images) ) {
        $promise = strtolower($promise);  // lowercase filenames!
        if (array_key_exists($promise, $images)) {

          $ct = isset($images[$promise]['c']) ? $images[$promise]['c'] : 'text/plain';

          if (isset($images[$promise]['d']) && !empty(isset($images[$promise]['d']))) {
            if (isset($images[$promise]['b']) && $images[$promise]['b']) {
              $content = base64_decode( $images[$promise]['d'] );
            } else {
              $content = $images[$promise]['d'];
            }
          } else {
            $content = '';
          }

          $hold = isset($images[$promise]['h']) ? intval($images[$promise]['h']) : DEFAULT_CACHE_TIME;

          header('Content-Type: ' . $ct, true);
          header('Content-Disposition: inline; filename="' . $promise . '"');
          http_response_cache_for( $hold );
          print( $content );

          die(); // !!!

        } else {
          $content = '<p class="h3 text-center">File <code>' . $promise . '</code> not found</p>';
          $promise = 404;
        }
      }

      $command = 'e';  // if you got here then you're in error
      break;

    case 'u':
      /* ┌───────────────────┐
       * │  URL redirection  │
       * └───────────────────┘
       */

      $promise = validateRedirectionHtmlResponseCode($promise);  // Just to make sure :)
      if ((302 == $promise) || (307 == $promise)) {
        http_response_cache_now();
      }

      if (DEBUG) {
        $heading = '<i class="fas fa-location-arrow"></i> ' . $promise . ' <small class="text-danger">DEBUG</small>';
        $inc_fa = true;
        $content = '<div class="text-center"><a class="btn btn-lg btn-outline-secondary" href="' . $url . '"' .
          ' title="' . $url . '"' .
          ' target="_blank"' .
          '>' . mb_strimwidth($url, 0, 35, '...') . ' <i class="ml-2 fas fa-up-right-from-square"></i></a></div>';
      } else {
        $cache_for = DEFAULT_CACHE_TIME;
        if (isset($expiry) && (false !== $expiry)) {
          $now = time();
          $delta = $expiry - $now;
          if ($delta < $cache_for) $cache_for = $delta;
        }

        http_response_redirection($url, $promise, $cache_for);  // !!! GETS GO!

        die(); // !!!
      }
      break;

    case 'x':
      // --- No JSON file ---

      $heading = '<b class="text-danger">Error:</b> Site not set up';

      $content = '<p class="card-text">File <code>' . $config->json_data_filename . '</code> not found.</p>
    <p class="card-text">Please create this file on the base folder of the server with the following content:<br>
    <pre class="border p-1 shadow"><code class="language-json">{
    "urls": {
      "x": "https://{your_url}",
      "y": [ "https://{your_url}", 302 ]
    }
  }</code></pre>
    </p>
    <p>Read the docs <a target="_blank" href="https://github.com/silvinor/clickit-url-shortener/blob/master/docs/readme.md">here</a>.</p>';

      $href = 'https://github.com/silvinor/clickit-url-shortener';
      $href = '';
      $btn_text = '<i class="fa-brands fa-github-alt"></i> View project';
      $color = 'info';
      $inc_highlighter = true;
      break;

    default:
      /* ┌────────────────────────────────┐
       * │  WTF?  You shouldn't be here!  │
       * └────────────────────────────────┘
       */
      $color = 'secondary';
      $heading = '<b>WTF</b> <i class="fas fa-question"></i>';
      $inc_fa = true;

      if (!isset($content)) $content = '';
      $content .= var_dump_ret($command, '$command');
      $content .= var_dump_ret($promise, '$promise');
      $content .= var_dump_ret($url, '$url');
      $content .= var_dump_ret($short, '$short');
      header('X-Server-Error: WTF?', true, 500);
      http_response_code(500);
      http_response_cache_now();
  }

  if ('e' == $command) { // Error page, common
    // ----- Error -----
    if (!is_numeric($promise) || ($promise < 100) || ($promise > 599)) $promise = 501;
    http_response_code($promise);
    header('X-Server-Error: ' . $promise, true, $promise);

    if ($promise >= 100 && $promise <= 199) {
      $_fa = 'circle-info';
      $color = 'info';
    } elseif ($promise >= 200 && $promise <= 299) {
      $_fa = 'thumbs-up';
      $color = 'success';
    } elseif ($promise >= 300 && $promise <= 399) {
      $_fa = 'location-arrow';
      $color = 'primary';
    } elseif ($promise >= 400 && $promise <= 499 && $promise <> 404) {
      $_fa = 'circle-exclamation';
      $color = 'warning';
    } else {
      $_fa = 'triangle-exclamation';
      $color = 'danger';
    }

    // Build string for some 4xx, and the 500 client response codes
    if (empty($content)) {
      $content .= '<h2 class="text-center"><small class="text-muted">';
      switch ($promise) {
        case 400: $content .= 'Bad Request'; break;
        case 401: $content .= 'Unauthorized'; break;
        case 402: $content .= 'Payment Required'; break;
        case 403: $content .= 'Forbidden'; break;
        case 404:
          if (!$short) {
            $content .= 'Not Found';
          } else {
            $content .= 'URL not found';
          }
          break;
        case 410: $content .= 'Gone'; break;
        case 418: $content .= 'Expired'; break;  // I'm a tea pot!!
        case 500: $content .= 'Internal Server Error'; break;
        case 501:
          $content .= 'Not Implemented';
          if (!empty($url)) $content .= ': <code>' . $url . '</code>';
          break;
        default: break;
      }
      $content .= '</small></h2>';
    }

    $heading = '<i class="fas fa-' . $_fa . '"></i> ' . $promise;
    $heading = '<span class="text-' . $color . '">' . $heading . '</span>';

    if (DEBUG) {
      echo '<!--' . PHP_EOL;
      var_dump_ret($command, '$command', true);
      var_dump_ret($promise, '$promise', true);
      var_dump_ret($url, '$url', true);
      var_dump_ret($short, '$short', true);
      echo '-->';
    }
    $inc_fa = true;

    if ((410 == $promise) || (418 == $promise)) {
      if (418 == $promise) header('HTTP/1.1 418 Expired', true, 418);  // Override I'm a teapot
      http_response_cache_never();
    } else {
      http_response_cache_now();
    }
  }

  /* ┌─────────────────────────────┐
   * │  HTML Goodness starts here  │
   * └─────────────────────────────┘
   */

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="<?= $config->bootstrap_css['url'] ?>" integrity="<?= $config->bootstrap_css['hash'] ?>" crossorigin="anonymous" media="all">
<?php if ($inc_fa) { ?>
  <link rel="stylesheet" href="<?= $config->fontawesome_css['url'] ?>" integrity="<?= $config->fontawesome_css['hash'] ?>" crossorigin="anonymous" media="all">
<?php } ?>
<?php if ($inc_highlighter) { ?>
  <link rel="stylesheet" href="<?= $config->highlight_css['url'] ?>" integrity="<?= $config->highlight_css['hash'] ?>" crossorigin="anonymous">
<?php } ?>
  <link rel="apple-touch-icon" href="<?= add_trailing_slash(getCurrentUrl()) . '?f=favicon.png' ?>">
  <link rel="icon" type="image/png" href="<?= add_trailing_slash(getCurrentUrl()) . '?f=favicon.png' ?>">
  <link rel="icon" type="image/svg+xml" href="<?= add_trailing_slash(getCurrentUrl()) . '?f=icon.svg' ?>" sizes="any">
  <title><?= $title ?></title>
  <style>
    .wrapper { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; }
    .dialog { display: flex; z-index: 100; max-width: 100%; }
    .logo { max-height: 64px; max-width: 100%; }
    .card-body p { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  </style>
<?php if (isset($config->extra_css) && !empty($config->extra_css)) { echo $config->extra_css . PHP_EOL; } ?>
</head>
<body>
  <div class="wrapper">

    <div class="dialog card border shadow-lg text-center border-<?= $color ?> rounded-4">
      <h5 class="card-header bg-<?= $color ?> px-5 py-3 border-<?= $color ?> rounded-top-4 bg-opacity-25"><img src="<?= add_trailing_slash(getCurrentUrl()) . '?f=logo.svg' ?>" class="logo"></h5>
      <div class="card-body text-left">
        <h1 class="card-title text-center"><?= $heading ?></h1>
        <?= $content ?>
      </div>
<?php if (!empty($href)) { ?>
      <div class="card-body text-center border-top">
        <a href="<?= $href ?>" class="btn btn-outline-primary"><?= $btn_text ?></a>
      </div>
<?php } ?>
<?php if (!empty($config->copyright)) { ?>
      <div class="card-footer text-muted small"><?= $config->copyright ?></div>
<?php } ?>
    </div>

  </div>
<?php if ($inc_js) { ?>
  <?php /* <script src="<?= $config->popper_js['url'] ?>" integrity="<?= $config->popper_js['hash'] ?>" crossorigin="anonymous"></script> */ ?>
  <script src="<?= $config->bootstrap_js['url'] ?>" integrity="<?= $config->bootstrap_js['hash'] ?>" crossorigin="anonymous"></script>
<?php } ?>
<?php if ($inc_highlighter) { ?>
  <script src="<?= $config->highlight_js['url'] ?>" integrity="<?= $config->highlight_js['hash'] ?>" crossorigin="anonymous"></script>
  <script>hljs.highlightAll();</script>
<?php } ?>
<?php if (isset($config->extra_js) && !empty($config->extra_js)) { echo $config->extra_js . PHP_EOL; } ?>

<?php
  // Either Matomo tracking or Log File are ON, AND cookie not set, AND the Do Not Track is NOT set
  // ... then show and handle the DMCA prompt
  global $dnt;
  if (isset($dnt) && !isset($_COOKIE['dnt']) && (!isset($_SERVER['HTTP_DNT']) || $_SERVER['HTTP_DNT'] !== '1')) {
?>
<div id="dmcaPrompt" class="alert alert-info position-fixed bottom-0 start-0 end-0 m-0 rounded-0 d-flex justify-content-between align-items-center">
  <div>
    <strong>Cookies:</strong> This site may use cookies for analytics.
  </div>
  <div>
    <button id="acceptBtn" class="btn btn-success btn-sm">Accept</button>
    <button id="declineBtn" class="btn btn-danger btn-sm">Decline</button>
  </div>
</div>
<script>
  function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + (days*24*60*60*1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
  }

  function getCookie(name) {
    const cname = name + "=";
    const decodedCookie = decodeURIComponent(document.cookie);
    const cookies = decodedCookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
      let c = cookies[i].trim();
      if (c.indexOf(cname) === 0) {
        return c.substring(cname.length, c.length);
      }
    }
    return false;
  }

  // DOMContentLoaded ensures it runs after the page is loaded
  document.addEventListener('DOMContentLoaded', function () {
    const dnt = getCookie('dnt');

    function hideDcmaPrompt() {
      const dmcaPrompt = document.getElementById('dmcaPrompt');
      dmcaPrompt.classList.add('d-none');
    }

    if (dnt !== false) { hideDcmaPrompt(); }

    document.getElementById('acceptBtn').addEventListener('click', function () {
      setCookie('dnt', '0', 365);
      hideDcmaPrompt();
    });
    document.getElementById('declineBtn').addEventListener('click', function () {
      setCookie('dnt', '1', 365);
      hideDcmaPrompt();
    });
  });
</script>
<?php
    }

    $analiticsHtml = __DIR__ . '/analitics.html';
    if (file_exists($analiticsHtml)) {
      $contents = @file_get_contents($analiticsHtml);
      if ($contents !== false) {
        echo $contents;
      } else {
        echo "<!-- Unable to read " . $analiticsHtml . " file. -->";
      }
    }
?>
</body>
</html>
<?php
}
