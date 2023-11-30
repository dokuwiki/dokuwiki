<?php

if (!defined('DOKU_INC')) define('DOKU_INC', __DIR__ . '/../../');
if (!defined('NOSESSION')) define('NOSESSION', true); // no session or auth required here

require_once(DOKU_INC . 'inc/init.php');
global $INPUT;

if ($INPUT->has('spec')) {
    header('Content-Type: application/json');
    $apigen = new \dokuwiki\Remote\OpenAPIGenerator();
    echo $apigen->generate();
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <script src="https://unpkg.com/openapi-explorer/dist/browser/openapi-explorer.min.js" type="module" defer=""></script>
    <style>
        body {
            font-family: sans-serif;
        }
    </style>
</head>
<body>
<openapi-explorer
    spec-url="<?php echo DOKU_URL ?>lib/exe/openapi.php?spec=1"
    hide-server-selection="true"
    default-schema-tab="body"
    use-path-in-nav-bar="true"
></openapi-explorer>
</body>
</html>


