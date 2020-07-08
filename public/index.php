<?php
/**
 * Created by PhpStorm.
 * User: larry.kluger
 * Date: 11/21/18
 * Time: 8:46 PM
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/docusign/esign-client/autoload.php';
require_once __DIR__ . '/../ds_config.php';

use Example\Services\RouterService;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$GLOBALS['app_url'] = $GLOBALS['DS_CONFIG']['app_url'] . '/';
$loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new Twig\Environment($loader);
$twig->addGlobal('app_url', $GLOBALS['app_url']);
$twig->addGlobal('session', $_SESSION);
$twig_function = new Twig\TwigFunction('get_flash', function () {
    if (! isset($_SESSION['flash'])) {$_SESSION['flash'] = [];}
    $msgs = $_SESSION['flash'];
    $_SESSION['flash'] = [];
    return $msgs;
});
$twig->addFunction($twig_function);
# Add csrf_token func. See https://stackoverflow.com/a/31683058/64904
$twig_function = new Twig\TwigFunction('csrf_token', function($lock_to = null) {
    if (empty($_SESSION['csrf_token'])) {$_SESSION['csrf_token'] = bin2hex(random_bytes(32));}
    if (empty($_SESSION['csrf_token2'])) {$_SESSION['csrf_token2'] = random_bytes(32);}
    if (empty($lock_to)) {return $_SESSION['csrf_token'];}
    return hash_hmac('sha256', $lock_to, $_SESSION['csrf_token2']);
});
$twig->addFunction($twig_function);
$GLOBALS['twig'] = $twig;
$router = new RouterService();

$router->router();