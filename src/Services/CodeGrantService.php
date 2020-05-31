<?php


namespace Example\Services;

use Example\Controllers\Auth\DocuSign;

class CodeGrantService
{
    /**
     * Set flash for the current user session
     * @param $msg string
     */
    public function flash(string $msg): void
    {
        if (! isset($_SESSION['flash'])) {$_SESSION['flash'] = [];}
        array_push($_SESSION['flash'], $msg);
    }

    /**
     * Checker for the CSRF token
     */
    function checkToken(): void
    {
        if ( ! (isset($_POST['csrf_token']) &&
            hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))) {
            # trouble!
            $this->flash('CSRF token problem!');
            header('Location: ' . $GLOBALS['app_url']);
            exit;
        }
    }

    /**
     * Get OAUTH provider
     * @return DocuSign $provider
     */
    function get_oauth_provider(): DocuSign
    {
        return new DocuSign([
            'clientId' => $GLOBALS['DS_CONFIG']['ds_client_id'],
            'clientSecret' => $GLOBALS['DS_CONFIG']['ds_client_secret'],
            'redirectUri' => $GLOBALS['DS_CONFIG']['app_url'] . '/index.php?page=ds_callback',
            'authorizationServer' => $GLOBALS['DS_CONFIG']['authorization_server'],
            'allowSilentAuth' => $GLOBALS['DS_CONFIG']['allow_silent_authentication']
        ]);
    }

    /**
     * DocuSign login handler
     */
    function login(): void
    {
        $provider = $this->get_oauth_provider();
        $authorizationUrl = $provider->getAuthorizationUrl();
        // Get the state generated for you and store it to the session.
        $_SESSION['oauth2state'] = $provider->getState();
        // Redirect the user to the authorization URL.
        header('Location: ' . $authorizationUrl);
        exit;
    }

    /**
     * DocuSign login handler
     * @param $redirectUrl
     */
    function authCallback($redirectUrl): void
    {
        $provider = $this->get_oauth_provider();
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

                $this->flash('You have authenticated with DocuSign.');
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
            if (! $redirectUrl) {
                $redirectUrl = $GLOBALS['app_url'];
            }
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
}