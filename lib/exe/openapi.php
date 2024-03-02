<?php

use dokuwiki\Remote\OpenApiDoc\OpenAPIGenerator;

if (!defined('DOKU_INC')) define('DOKU_INC', __DIR__ . '/../../');
require_once(DOKU_INC . 'inc/init.php');
global $INPUT;

if ($INPUT->has('spec')) {
    header('Content-Type: application/json');
    $apigen = new OpenAPIGenerator();
    echo $apigen->generate();
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DokuWiki API Explorer</title>
    <script src="https://unpkg.com/openapi-explorer/dist/browser/openapi-explorer.min.js" type="module"
            defer=""></script>
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
    use-path-in-nav-bar="true"
>
    <div slot="overview-api-description">
        <p>
            This is an auto generated description and OpenAPI specification for the
            <a href="https://www.dokuwiki.org/devel/jsonrpc">DokuWiki JSON-RPC API</a>.
            It is generated from the source code and the inline documentation.
        </p>

        <p>
            <a href="<?php echo DOKU_BASE ?>/lib/exe/openapi.php?spec=1" download="dokuwiki.json">Download
                the API Spec</a>
        </p>

        <h3>Error Codes</h3>

        <p>
            The following error codes are currently used in the core methods. This list may be incomplete
            or change in the future.
        </p>

        <table>
            <tr><th>Code</th><th>Message</th></tr>
            <tr><td>0</td><td>Success</td></tr>
            <?php
            $apigen = new OpenAPIGenerator();
            $last = 0;
            foreach ($apigen->getErrorCodes() as $code) {
                // duplicate codes are only shown with debug
                if ($code['code'] === $last && !$INPUT->has('debug')) continue;
                $last = $code['code'];
                echo '<tr><td>' . $code['code'] . '</td><td>' . hsc($code['message']) . '</td></tr>';
            }
            ?>
        </table>
    </div>

    <div slot="authentication-footer">
        <p>
            <?php
            if ($INPUT->server->has('REMOTE_USER')) {
                echo 'You are currently logged in as <strong>' . hsc($INPUT->server->str('REMOTE_USER')) . '</strong>.';
                echo '<br>API calls in this tool will be automatically executed with your permissions.';
            } else {
                echo 'You are currently not logged in.<br>';
                echo 'You can provide credentials above.';
            }
            ?>
        </p>
    </div>
</openapi-explorer>
</body>
</html>


