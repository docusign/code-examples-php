<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Api\AccountsApi;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Api\EnvelopesApi\ListStatusChangesOptions;
use DocuSign\eSign\Api\UsersApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Client\Auth\OAuth;
use DocuSign\eSign\Configuration;
use DocuSign\eSign\Model\AuthorizationUser;
use DocuSign\eSign\Model\EnvelopesInformation;
use DocuSign\eSign\Model\NewUsersDefinition;
use DocuSign\eSign\Model\NewUsersSummary;
use DocuSign\eSign\Model\UserAuthorizationCreateRequest;
use DocuSign\eSign\Model\UserInformation;

class SharedAccessService
{
    const BASEPATH = "account-d.docusign.com";

    public static function getCurrentUserInfo(
        string $basePath,
        string $accessToken
    ): array {
        #ds-snippet-start:eSign43Step2
        $config = new Configuration();
        $config->setHost($basePath);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $accessToken);

        $oauth = new OAuth();
        $oauth->setOAuthBasePath(self::BASEPATH);

        $apiClient = new ApiClient($config, $oauth);
        #ds-snippet-end:eSign43Step2

        return $apiClient->getUserInfo($accessToken);
    }

    public static function getUserInfo(
        UsersApi $usersApi,
        string $accountId,
        string $agentEmail
    ): ?UserInformation {
        $userInformation = null;
        $activeStatus = 'Active';

        $callListOptions = new UsersApi\ListOptions();
        $callListOptions->setEmail($agentEmail);

        try {
            $informationList = $usersApi->callList($accountId, $callListOptions);

            if (intval($informationList->getResultSetSize()) > 0) {
                $users = $informationList->getUsers();

                foreach ($users as $user) {
                    if ($user['user_status'] == $activeStatus) {
                        $userInformation = $user;
                    }
                }
            }
        } catch (ApiException $e) {
            error_log($e->getMessage());
        }

        return $userInformation;
    }

    /**
     * @throws ApiException
     */
    public static function shareAccess(
        UsersApi $usersApi,
        string $accountId,
        string $agentEmail,
        string $agentName,
        string $activationCode
    ): NewUsersSummary {
        #ds-snippet-start:eSign43Step3
        $newUser = new NewUsersDefinition();
        $newUser->setNewUsers([
            new UserInformation([
                "activation_access_code" => $activationCode,
                "email" => $agentEmail,
                "user_name" => $agentName])
        ]);

        return $usersApi->create($accountId, $newUser);
        #ds-snippet-end:eSign43Step3
    }

    #ds-snippet-start:eSign43Step4
    public static function createUserAuthorization(
        string $basePath,
        string $accessToken,
        string $accountId,
        string $userId,
        string $agentUserId
    ): void {
        $config = new Configuration();
        $config->setHost($basePath);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $accessToken);

        $oauth = new OAuth();
        $oauth->setOAuthBasePath(self::BASEPATH);

        $apiClient = new ApiClient($config, $oauth);
        $accountsApi = new AccountsApi($apiClient);

        $managePermission = "manage";

        $options = new AccountsApi\GetAgentUserAuthorizationsOptions();
        $options->setPermissions($managePermission);
        $userAuthorizations = $accountsApi->getAgentUserAuthorizations($accountId, $userId);

        if ($userAuthorizations->getAuthorizations() === null) {
            $authRequest = new UserAuthorizationCreateRequest();
            $authRequest->setPermission($managePermission);
            $authRequest->setAgentUser(new AuthorizationUser([
                'account_id' => $accountId,
                'user_id' => $agentUserId
             ]));

            $accountsApi->createUserAuthorization($accountId, $userId, $authRequest);
        }
    }
    #ds-snippet-end:eSign43Step4
    public static function listEnvelopes(ApiClient $apiClient, string $accountId, string $userId): ?EnvelopesInformation
    {
        #ds-snippet-start:eSign43Step5
        $apiClient->getConfig()->addDefaultHeader("X-DocuSign-Act-On-Behalf", $userId);
        $envelopeApi = new EnvelopesApi($apiClient);

        $fromDate = date("c", (time() - (10 * 24 * 60 * 60)));
        $options = new ListStatusChangesOptions();
        $options->setFromDate($fromDate);

        try {
            $statusChanges = $envelopeApi->listStatusChanges($accountId, $options);
        } catch (ApiException $e) {
            error_log($e->getMessage());
        }

        return $statusChanges;
        #ds-snippet-end:eSign43Step5
    }
}
