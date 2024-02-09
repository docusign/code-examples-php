<?php
namespace DocuSign\Tests;
use DocuSign\Services\ApiTypes;
use DocuSign\eSign\Client\ApiClient;

final class JWTLoginMethod
{
    const OAUTH_BASEPATH = "account-d.docusign.com";
    const BASEPATH = "https://demo.docusign.net/restapi";
    const REDIRECT_URL = "https://developers.docusign.com/platform/auth/consent";
    const RESPONSE_TYPE = 'code';
    const EXPIRES_IN = 3600;
    function open($target)
    {
        switch (PHP_OS) {
            case 'Darwin':
                $opener = 'open';
                break;
            case 'WINNT':
                $opener = 'start ""';
                break;
            default:
                $opener = 'xdg-open';
        }
        exec(sprintf('%s %s', $opener, escapeshellcmd($target)));
    }
    public static function jwtAuthenticationMethod(string $apiType, TestConfig $testConfig): void
    {
        if ($apiType == ApiTypes::ROOMS) {
            $scopes = "room_forms dtr.rooms.read dtr.rooms.write dtr.documents.read dtr.documents.write "
                . "dtr.profile.read dtr.profile.write dtr.company.read dtr.company.write";
        } elseif ($apiType == ApiTypes::CLICK) {
            $scopes = "signature click.manage click.send";
        } elseif ($apiType == ApiTypes::ADMIN) {
            $scopes = "signature user_write group_read organization_read permission_read user_read "
            . "account_read domain_read identity_provider_read user_data_redact"
            . "asset_group_account_read asset_group_account_clone_write asset_group_account_clone_read";
        } elseif ($_SESSION['api_type'] == ApiTypes::WEBFORMS) {
            $scopes = "signature webforms_read webforms_instance_read webforms_instance_write";
        } else {
            $scopes = "signature";
        }
        try {
            $apiClient = new ApiClient();
            $apiClient->getOAuth()->setOAuthBasePath(self::OAUTH_BASEPATH);
            $response = $apiClient->requestJWTUserToken(
                $testConfig->getClientId(),
                $testConfig->getImpersonatedUserId(),
                $testConfig->getPrivateKey(),
                $scopes,
                self::EXPIRES_IN
            );
            if (isset($response)) {
                $access_token = $response[0]['access_token'];
                // retrieve our API account Id
                $info = $apiClient->getUserInfo($access_token);
                $account_id = $info[0]["accounts"][0]["account_id"];
                $testConfig->setBasePath(self::BASEPATH);
                $testConfig->setAccountId($account_id);
                $testConfig->setAccessToken($access_token);
            }
        } catch (\Throwable $th) {
            if (strpos($th->getMessage(), "consent_required") !== false) {
                $authorizationURL = 'https://account-d.docusign.com/oauth/auth?' .
                    http_build_query([
                        'scope' => $scopes,
                        'redirect_uri' => self::REDIRECT_URL,
                        'client_id' => $testConfig->getClientId(),
                        'response_type' => self::RESPONSE_TYPE
                    ]);
                echo "It appears that you are using this integration key for the first time." .
                    "Opening the following link in a browser window:\n";
                echo $authorizationURL . "\n\n";
                open($authorizationURL);
                exit;
            }
        }
    }
}