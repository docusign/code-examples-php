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

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$GLOBALS['app_url'] = $GLOBALS['DS_CONFIG']['app_url'] . '/';
$loader = new Twig_Loader_Filesystem(__DIR__ . '/../templates');
$twig = new Twig_Environment($loader);
$twig->addGlobal('app_url', $GLOBALS['app_url']);
$twig->addGlobal('session', $_SESSION);
$twig_function = new Twig_Function('get_flash', function () {
    if (! isset($_SESSION['flash'])) {$_SESSION['flash'] = [];}
    $msgs = $_SESSION['flash'];
    $_SESSION['flash'] = [];
    return $msgs;
});
$twig->addFunction($twig_function);
# Add csrf_token func. See https://stackoverflow.com/a/31683058/64904
$twig_function = new Twig_Function('csrf_token', function($lock_to = null) {
    if (empty($_SESSION['csrf_token'])) {$_SESSION['csrf_token'] = bin2hex(random_bytes(32));}
    if (empty($_SESSION['csrf_token2'])) {$_SESSION['csrf_token2'] = random_bytes(32);}
    if (empty($lock_to)) {return $_SESSION['csrf_token'];}
    return hash_hmac('sha256', $lock_to, $_SESSION['csrf_token2']);
});
$twig->addFunction($twig_function);
$GLOBALS['twig'] = $twig;

function flash($msg) {
    if (! isset($_SESSION['flash'])) {$_SESSION['flash'] = [];}
    array_push($_SESSION['flash'], $msg);
}

function router() {
    $routes = [
        'ds_return' => 'DsReturn',
        'must_authenticate' => 'MustAuthenticate',
        'eg001' => 'EG001EmbeddedSigning',
        'eg002' => 'EG002SigningViaEmail',
        'eg003' => 'EG003ListEnvelopes',
        'eg004' => 'EG004EnvelopeInfo',
        'eg005' => 'EG005EnvelopeRecipients',
        'eg006' => 'EG006EnvelopeDocs',
        'eg007' => 'EG007EnvelopeGetDoc',
        'eg008' => 'EG008CreateTemplate',
        'eg009' => 'EG009UseTemplate',
        'eg010' => 'EG010SendBinaryDocs',
        'eg011' => 'EG011EmbeddedSending',
        'eg012' => 'EG012EmbeddedConsole',
        'eg013' => 'EG013AddDocToTemplate',
        'eg014' => 'EG014CollectPayment',
        'eg015' => 'EG015EnvelopeFieldData',
        'eg016' => 'EG016SetFieldValues',
        'eg017' => 'EG017SetTemplateFieldValues',
    ];


    if (! isset($_GET['page'])) {
        $controller = 'Example\Home';
    } elseif ($_GET['page'] == 'ds_login') {
        ds_login(); // See below in oauth section
        exit();
    } elseif ($_GET['page'] == 'ds_callback') {
        ds_callback(); // See below in oauth section
        exit();
    } elseif ($_GET['page'] == 'ds_logout') {
        ds_logout(); // See below in oauth section
        exit();
    } else {
        $page = $_GET['page'];
        $controller = 'Example\\' . $routes[$page];
    }
    $c = new $controller();
    $c->controller();
}

/**
 * Check that the csrf token is present and correct.
 * If not, return to home page.
 * See https://stackoverflow.com/a/31683058/64904
 */
function check_csrf(){
    if ( ! (isset($_POST['csrf_token']) &&
            hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))) {
        # trouble!
        flash('CSRF token problem!');
        header('Location: ' . $GLOBALS['app_url']);
        exit;
    }
}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
//
// OAuth support
// Using the https://github.com/thephpleague/oauth2-client with a locally
// stored DocuSign provider
//

/**
 * @param int $buffer_min buffer time needed in minutes
 * @return boolean $ok true iff the user has an access token that will be good for another buffer min
 */
function ds_token_ok($buffer_min=60)
{
    $ok = isset($_SESSION['ds_access_token']) && isset($_SESSION['ds_expiration']);
    $ok = $ok && (($_SESSION['ds_expiration'] - ($buffer_min * 60)) > time());
    return $ok;
}


function get_oauth_provider()
{
    $provider = new \DocuSign\OAuth2\Client\Provider\DocuSign([
                   'clientId' => $GLOBALS['DS_CONFIG']['ds_client_id'],
               'clientSecret' => $GLOBALS['DS_CONFIG']['ds_client_secret'],
                'redirectUri' => $GLOBALS['DS_CONFIG']['app_url'] . '/index.php?page=ds_callback',
        'authorizationServer' => $GLOBALS['DS_CONFIG']['authorization_server'],
            'allowSilentAuth' => $GLOBALS['DS_CONFIG']['allow_silent_authentication']
    ]);
    return $provider;
}

function ds_login()
{
    $provider = get_oauth_provider();
    $authorizationUrl = $provider->getAuthorizationUrl();
    // Get the state generated for you and store it to the session.
    $_SESSION['oauth2state'] = $provider->getState();
    // Redirect the user to the authorization URL.
    header('Location: ' . $authorizationUrl);
    exit;
}

/*
 * Called via a redirect from DocuSign authentication service
 */
function ds_callback()
{
    # Save the redirect eg if present
    $redirect_url = false;
    if (isset($_SESSION['eg'])) {
        $redirect_url = $_SESSION['eg'];
    }
    # reset the session
    ds_logout_internal();

    $provider = get_oauth_provider();
    // Check given state against previously stored one to mitigate CSRF attack
    if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
        if (isset($_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
        }
        exit('Invalid OAuth state');
    } else {
        try {
            // Try to get an access token using the authorization code grant.
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            flash('You have authenticated with DocuSign.');
            // We have an access token, which we may use in authenticated
            // requests against the service provider's API.
            $_SESSION['ds_access_token'] = $accessToken->getToken();
            $_SESSION['ds_refresh_token'] = $accessToken->getRefreshToken();
            $_SESSION['ds_expiration'] = $accessToken->getExpires(); # expiration time.

            // Using the access token, we may look up details about the
            // resource owner.
            $user = $provider->getResourceOwner($accessToken);
            $_SESSION['ds_user_name'] = $user->getName();
            $_SESSION['ds_user_email'] = $user->getEmail();

            $account_info = $user->getAccountInfo();
            $base_uri_suffix = '/restapi';
            $_SESSION['ds_account_id'] = $account_info["account_id"];
            $_SESSION['ds_account_name'] = $account_info["account_name"];
            $_SESSION['ds_base_path'] = $account_info["base_uri"] . $base_uri_suffix;
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token or user details.
            exit($e->getMessage());
        }
        if (! $redirect_url) {
            $redirect_url = $GLOBALS['app_url'];
        }
        header('Location: ' . $redirect_url);
        exit;
    }
}

/**
 * ds_logout
 */
function ds_logout()
{
    ds_logout_internal();
    flash('You have logged out from DocuSign.');
    header('Location: ' . $GLOBALS['app_url']);
    exit;
}

/**
 * Unset all items from the session
 */
function ds_logout_internal()
{
    if (isset($_SESSION['ds_access_token'   ])) {unset($_SESSION['ds_access_token'   ]);}
    if (isset($_SESSION['ds_refresh_token'  ])) {unset($_SESSION['ds_refresh_token'  ]);}
    if (isset($_SESSION['ds_user_email'     ])) {unset($_SESSION['ds_user_email'     ]);}
    if (isset($_SESSION['ds_user_name'      ])) {unset($_SESSION['ds_user_name'      ]);}
    if (isset($_SESSION['ds_expiration'     ])) {unset($_SESSION['ds_expiration'     ]);}
    if (isset($_SESSION['ds_account_id'     ])) {unset($_SESSION['ds_account_id'     ]);}
    if (isset($_SESSION['ds_account_name'   ])) {unset($_SESSION['ds_account_name'   ]);}
    if (isset($_SESSION['ds_base_path'      ])) {unset($_SESSION['ds_base_path'      ]);}
    if (isset($_SESSION['envelope_id'       ])) {unset($_SESSION['envelope_id'       ]);}
    if (isset($_SESSION['eg'                ])) {unset($_SESSION['eg'                ]);}
    if (isset($_SESSION['envelope_documents'])) {unset($_SESSION['envelope_documents']);}
    if (isset($_SESSION['template_id'       ])) {unset($_SESSION['template_id'       ]);}
}


router();
