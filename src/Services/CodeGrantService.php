<?php

namespace DocuSign\Services;

use DocuSign\Controllers\Auth\DocuSign;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use DocuSign\Services\Utils;

class CodeGrantService
{
    public function generateCodeVerifier()
    {
        return bin2hex(random_bytes(32));
    }
    
    public function generateCodeChallenge($code_verifier)
    {
        return rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');
    }

    /**
     * Checker for the CSRF token
     */
    public function checkToken(): void
    {
        if (!(isset($_POST['csrf_token']) &&
            hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
        ) {
            # trouble!
            $this->flash('CSRF token problem!');
            header('Location: ' . $GLOBALS['app_url']);
            exit;
        }
    }

    /**
     * Set flash for the current user session
     * @param $msg string
     */
    public function flash(string $msg): void
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        array_push($_SESSION['flash'], $msg);
    }

    public function login(): void
    {
        if (isset($_SESSION['pkce_failed']) && $_SESSION['pkce_failed'] === true) {
            $provider = $this->getOauthProvider();
            $authorizationUrl = $provider->getAuthorizationUrl();
        } else {
            $codeVerifier = $this->generateCodeVerifier();
            $codeChallenge = $this->generateCodeChallenge($codeVerifier);
            $_SESSION['code_verifier'] = $codeVerifier;

            $provider = $this->getOauthProvider();
            $authorizationUrl = $provider->getAuthorizationUrl([
                'code_challenge' => $codeChallenge,
                'code_challenge_method' => 'S256'
            ]);
        }
        $_SESSION['oauth2state'] = $provider->getState();
        // Redirect the user to the authorization URL.
        header('Location: ' . $authorizationUrl);
        exit;
    }
    
    /**
     * Get OAUTH provider
     * @return DocuSign $provider
     */
    public function getOauthProvider(): DocuSign
    {
        return new DocuSign(
            [
                'clientId' => $GLOBALS['DS_CONFIG']['ds_client_id'],
                'clientSecret' => $GLOBALS['DS_CONFIG']['ds_client_secret'],
                'redirectUri' => $GLOBALS['DS_CONFIG']['app_url'] . '/index.php?page=ds_callback',
                'authorizationServer' => $GLOBALS['DS_CONFIG']['authorization_server'],
                'allowSilentAuth' => $GLOBALS['DS_CONFIG']['allow_silent_authentication']
            ]
        );
    }

    /**
     * DocuSign login handler
     * @param $redirectUrl
     */
    public function authCallback($redirectUrl): void
    {
        $provider = $this->getOauthProvider();
        // Check given state against previously stored one to mitigate CSRF attack
        if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }
            exit('Invalid OAuth state');
        } else {
            $tokenRequestOptions = [
                'code' => $_GET['code']
            ];
            
            // Add the code_verifier only for PKCE authorization
            if (!isset($_SESSION['pkce_failed']) || $_SESSION['pkce_failed'] === false) {
                $tokenRequestOptions['code_verifier'] = $_SESSION['code_verifier'];
                unset($_SESSION['pkce_failed']);
            }
            
            try {
                // Try to get an access token using the authorization code grant.
                $accessToken = $provider->getAccessToken('authorization_code', $tokenRequestOptions);
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

                $cfr = new Utils();
                $_SESSION['cfr_enabled'] = $cfr->isCFR(
                    $_SESSION['ds_access_token'],
                    $account_info["account_id"],
                    $account_info["base_uri"] . $base_uri_suffix
                );
                $_SESSION['ds_account_id'] = $account_info["account_id"];
                $_SESSION['ds_account_name'] = $account_info["account_name"];
                $_SESSION['ds_base_path'] = $account_info["base_uri"] . $base_uri_suffix;
            } catch (IdentityProviderException $e) {
                // Failed to get the access token or user details.
                $_SESSION['pkce_failed'] = true;
                $this->login();
                exit;
            }
            if (!$redirectUrl) {
                $redirectUrl = $GLOBALS['app_url'];
            }
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
}
