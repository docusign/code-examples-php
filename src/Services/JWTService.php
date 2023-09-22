<?php

namespace DocuSign\Services;

use DocuSign\eSign\Api\AccountsApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Configuration;
use DocuSign\Controllers\Auth\DocuSign;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use DocuSign\Services\Utils;
use Throwable;

class JWTService
{
    const TOKEN_REPLACEMENT_IN_SECONDS = 600; # 10 minutes
    protected static $expires_in;
    protected static $access_token;
    protected static $expiresInTimestamp;
    protected static $account;
    protected static ApiClient $apiClient;

    public function __construct()
    {
        $config = new Configuration();
        self::$apiClient = new ApiClient($config);
    }

    /**
     * Checker for the JWT token
     */
    public function checkToken()
    {

        if (is_null($_SESSION['ds_access_token'])
            || (time() + self::TOKEN_REPLACEMENT_IN_SECONDS) > (int) $_SESSION['ds_expiration']
        ) {
            $this->login();
        }
    }


    /**
     * DocuSign login handler
     * @throws \DocuSign\eSign\Client\ApiException
     */
    public function login()
    {
        self::$access_token = $this->configureJwtAuthorizationFlowByKey();
        self::$expiresInTimestamp = time() + self::$expires_in;
        if (is_null(self::$account)) {
            self::$account = self::$apiClient->getUserInfo(self::$access_token->getAccessToken());
        }
        $cfr = new Utils();
        $_SESSION['cfr_enabled'] = $cfr->isCFR(
            self::$access_token["access_token"],
            self::$account[0]["accounts"][0]["account_id"],
            self::$account[0]["accounts"][0]["base_uri"] . "/restapi"
        );

        $redirectUrl = false;
        if (isset($_SESSION['eg'])) {
            $redirectUrl = $_SESSION['eg'];
        }

        $this->authCallback($redirectUrl);
    }

    /**
     * Get JWT auth by RSA key
     */
    private function configureJwtAuthorizationFlowByKey()
    {
        self::$apiClient->getOAuth()->setOAuthBasePath($GLOBALS['JWT_CONFIG']['authorization_server']);
        $privateKey = file_get_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/' . $GLOBALS['JWT_CONFIG']['private_key_file'],
            true
        );

        $scope = (new DocuSign())->getDefaultScopes()[0];
        $jwt_scope = $scope;

        try {
            $response = self::$apiClient->requestJWTUserToken(
                $aud = $GLOBALS['JWT_CONFIG']['ds_client_id'],
                $aud = $GLOBALS['JWT_CONFIG']['ds_impersonated_user_id'],
                $aud = $privateKey,
                $aud = $jwt_scope
            );

            return $response[0];    //code...
        } catch (Throwable $th) {
            // we found consent_required in the response body meaning first time consent is needed
            if (strpos($th->getMessage(), "consent_required") !== false) {
                $authorizationURL = 'https://account-d.docusign.com/oauth/auth?prompt=login&response_type=code&'
                . http_build_query(
                    [
                        'scope' => "impersonation+" . $jwt_scope,
                        'client_id' => $GLOBALS['JWT_CONFIG']['ds_client_id'],
                        'redirect_uri' => $GLOBALS['DS_CONFIG']['app_url'] . '/index.php?page=dsCallback'
                    ]
                );
                header('Location: ' . $authorizationURL);
            }
        }
    }


    /**
     * DocuSign login handler
     * @param $redirectUrl
     */
    public function authCallback($redirectUrl): void
    {
        // Check given state against previously stored one to mitigate CSRF attack
        if (!self::$access_token) {
            if (isset($_GET['code'])) {
                // we have obtained consent, let's shortcut and login the user
                $this->login();
            } else {
                exit('Invalid JWT state');
            }
        } else {
            try {
                // We have an access token, which we may use in authenticated
                // requests against the service provider's API.

                $this->flash('You have authenticated with DocuSign.');
                $_SESSION['ds_access_token'] = self::$access_token->getAccessToken();
                $_SESSION['ds_expiration'] = time() + (self::$access_token->getExpiresIn() * 60); # expiration time.

                // Using the access token, we may look up details about the
                // resource owner.
                $_SESSION['ds_user_name'] = self::$account[0]->getName();
                $_SESSION['ds_user_email'] = self::$account[0]->getEmail();

                $account_info = self::$account[0]->getAccounts();
                $base_uri_suffix = '/restapi';
                $_SESSION['ds_account_id'] = $account_info[0]->getAccountId();
                $_SESSION['ds_account_name'] = $account_info[0]->getAccountName();
                $_SESSION['ds_base_path'] = $account_info[0]->getBaseUri() . $base_uri_suffix;
            } catch (IdentityProviderException $e) {
                // Failed to get the access token or user details.
                exit($e->getMessage());
            }
            if (!$redirectUrl) {
                $redirectUrl = $GLOBALS['app_url'];
            }
            header('Location: ' . $redirectUrl);
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
}
