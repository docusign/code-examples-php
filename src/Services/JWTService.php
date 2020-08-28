<?php


namespace Example\Services;

use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Configuration;


class JWTService
{
    const TOKEN_REPLACEMENT_IN_SECONDS = 600; # 10 minutes
    protected static $expires_in;
    protected static $access_token;
    protected static $expiresInTimestamp;
    protected static $account;
    protected static $apiClient;

    public function __construct()
    {
        $config = new Configuration();
        self::$apiClient = new ApiClient($config);
    }

    /**
     * Checker for the JWT token
     */
    protected function checkToken()
    {
        if (
            is_null(self::$access_token)
            || (time() +  self::TOKEN_REPLACEMENT_IN_SECONDS) > self::$expiresInTimestamp
        ) {
            $this->login();
        }
    }

    /**
     * DocuSign login handler
     */
    public function login()
    {
        self::$access_token = $this->configureJwtAuthorizationFlowByKey();
        self::$expiresInTimestamp = time() + self::$expires_in;

        if (is_null(self::$account)) {
            self::$account = self::$apiClient->getUserInfo(self::$access_token->getAccessToken());
        }

        $redirectUrl = false;
        if (isset($_SESSION['eg'])) {
            $redirectUrl = $_SESSION['eg'];
        }

        self::authCallback($redirectUrl);
    }

    /**
     * Get JWT auth by RSA key
     */
    private function configureJwtAuthorizationFlowByKey()
    {
        self::$apiClient->getOAuth()->setOAuthBasePath($GLOBALS['JWT_CONFIG']['authorization_server']);
        $privateKey = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $GLOBALS['JWT_CONFIG']['private_key_file'], true);


        try {
            $response = self::$apiClient->requestJWTUserToken(
                $aud = $GLOBALS['JWT_CONFIG']['ds_client_id'],
                $aud = $GLOBALS['JWT_CONFIG']['ds_impersonated_user_id'],
                $aud = $privateKey,
                $aud = $GLOBALS['JWT_CONFIG']['jwt_scope']
            );

            return $response[0];    //code...
        } catch (\Throwable $th) {

            // we found consent_required in the response body meaning 1st time consent is needed
            if (strpos($th->getMessage(), "consent_required") !== false) {

                $authorizationURL = 'https://account-d.docusign.com/oauth/auth?' . http_build_query([
                    'scope'         => 'signature impersonation',
                    'redirect_uri'  => $GLOBALS['DS_CONFIG']['app_url'] . '/index.php?page=ds_callback',
                    'client_id'     => $GLOBALS['JWT_CONFIG']['ds_client_id'],
                    'state'         => $_SESSION['oauth2state'],
                    'response_type' => 'code'
                ]);
                header('Location: ' . $authorizationURL);
            }
        }
    }


    /**
     * DocuSign login handler
     * @param $redirectUrl
     */
    function authCallback($redirectUrl): void
    {
        // Check given state against previously stored one to mitigate CSRF attack
        if (!self::$access_token) {
            exit('Invalid JWT state');
        } else {
            try {
                // Try to get an access token using the authorization code grant.

                $this->flash('You have authenticated with DocuSign.');
                // We have an access token, which we may use in authenticated
                // requests against the service provider's API.
                $_SESSION['ds_access_token'] = self::$access_token->getAccessToken();
                $_SESSION['ds_refresh_token'] = self::$access_token->getRefreshToken();
                $_SESSION['ds_expiration'] = time() + (self::$access_token->getExpiresIn() * 1000); # expiration time.

                // Using the access token, we may look up details about the
                // resource owner.
                $_SESSION['ds_user_name'] = self::$account[0]->getName();
                $_SESSION['ds_user_email'] = self::$account[0]->getEmail();

                $account_info = self::$account[0]->getAccounts();
                $base_uri_suffix = '/restapi';
                $_SESSION['ds_account_id'] = $account_info[0]->getAccountId();
                $_SESSION['ds_account_name'] = $account_info[0]->getAccountName();
                $_SESSION['ds_base_path'] = $account_info[0]->getBaseUri() . $base_uri_suffix;
            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
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
