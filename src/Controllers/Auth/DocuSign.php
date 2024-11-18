<?php

/**
 * Created by PhpStorm.
 * User: larry.kluger
 * Date: 11/22/18
 * Time: 9:40 PM
 */

namespace DocuSign\Controllers\Auth;

use DocuSign\Services\ApiTypes;
use Exception;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class DocuSign extends AbstractProvider
{
    /**
     * We use additional options that must be supplied when constructing
     * the object:
     *   authorizationServer => https://account-d.docusign.com or
     *                          https://account.docusign.com
     *   allowSilentAuth => (optional) default is true
     *   targetAccountId => (optional) default is false which means the
     *                                 default account will be used.
     */
    public $authorizationServer = null;
    public $allowSilentAuth = true;
    public $targetAccountId = false;

    use BearerAuthorizationTrait;

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     * @throws Exception
     */
    public function getBaseAuthorizationUrl(): string
    {
        $url = $this->getAuthServer();
        if ($this->allowSilentAuth) {
            $url .= '/oauth/auth';
        } else {
            $url .= '/oauth/auth?prompt=login';
        }
        return $url;
    }

    /**
     * Returns the DocuSign authorization server url
     * @return string authorization server url
     * @throws Exception
     */
    private function getAuthServer(): string
    {
        $url = $this->authorizationServer;
        if ($url == null) {
            throw new Exception('authorizationServer not set.');
        }
        return $url;
    }

    /**
     * Get access token url to retrieve token
     *
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        $url = $this->getAuthServer();
        $url .= '/oauth/token';
        return $url;
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     * @throws Exception
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        $url = $this->getAuthServer();
        $url .= '/oauth/userinfo';
        return $url;
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface! Note that the signature scope
     * is necessary to check against cfrPt11 compatibility and may not be
     * required if your making non eSignature API calls
     *
     * @return array
     */
    public function getDefaultScopes(): array
    {
        if ($_SESSION['api_type'] == ApiTypes::ROOMS) {
            return [
                "room_forms dtr.rooms.read dtr.rooms.write dtr.documents.read dtr.documents.write "
                . "dtr.profile.read dtr.profile.write dtr.company.read dtr.company.write"
            ];
        } elseif ($_SESSION['api_type'] == ApiTypes::CLICK) {
            return [
                "signature click.manage click.send"
            ];
        } elseif ($_SESSION['api_type'] == ApiTypes::ADMIN) {
            return [
                "signature user_write group_read organization_read permission_read user_read"
                . " account_read domain_read identity_provider_read user_data_redact asset_group_account_read"
                . " asset_group_account_clone_write asset_group_account_clone_read "
                . "organization_sub_account_write organization_sub_account_read"
            ];
        } elseif ($_SESSION['api_type'] == ApiTypes::MAESTRO) {
            return [
                "signature aow_manage"
            ];
        } elseif ($_SESSION['api_type'] == ApiTypes::WEBFORMS) {
            return [
                "signature webforms_read webforms_instance_read webforms_instance_write"
            ];
        } else {
            return [
                "signature"
            ];
        }
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            if (!empty($response)) {
                throw new IdentityProviderException(
                    $data['error'],
                    0,
                    $response
                );
            }
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return DocuSignResourceOwner
     * @throws Exception
     */
    protected function createResourceOwner(array $response, AccessToken $token): DocuSignResourceOwner
    {
        $r = new DocuSignResourceOwner($response);
        $r->target_account_id = $this->targetAccountId;
        return $r;
    }
}
