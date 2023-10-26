<?php

namespace Example\Services\Examples\Admin;

use DocuSign\Admin\Api\ProvisionAssetGroupApi;
use DocuSign\Admin\Api\ProvisionAssetGroupApi\GetAssetGroupAccountsOptions;
use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\AssetGroupAccountClone;
use DocuSign\Admin\Model\AssetGroupAccountCloneSourceAccount;
use DocuSign\Admin\Model\AssetGroupAccountCloneTargetAccount;
use DocuSign\Admin\Model\AssetGroupAccountCloneTargetAccountAdmin;
use DocuSign\Admin\Model\AssetGroupAccountsResponse;

class CloneAccountService
{
    /**
     * Get all accounts in asset groups for the organization.
     * @param ProvisionAssetGroupApi $provisionAssetGroupApi
     * @param string                 $organizationId
     * @return AssetGroupAccountsResponse
     * @throws ApiException
     */
    public static function getAccounts(
        ProvisionAssetGroupApi $provisionAssetGroupApi,
        string $organizationId
    ): AssetGroupAccountsResponse {
        #ds-snippet-start:Admin12Step3
        $options = new GetAssetGroupAccountsOptions();
        $options->setCompliant(true);

        return $provisionAssetGroupApi->getAssetGroupAccounts($organizationId, $options);
        #ds-snippet-end:Admin12Step3
    }

    /**
     * Clones an existing DocuSign account to a new DocuSign account
     * @param ProvisionAssetGroupApi $provisionAssetGroupApi
     * @param string $organizationId
     * @param string $sourceAccountId
     * @param string $targetAccountName
     * @param string $targetAccountFirstName
     * @param string $targetAccountLastName
     * @param string $targetAccountEmail
     * @return AssetGroupAccountClone
     * @throws ApiException
     */
    public static function cloneAccount(
        ProvisionAssetGroupApi $provisionAssetGroupApi,
        string $organizationId,
        string $sourceAccountId,
        string $targetAccountName,
        string $targetAccountFirstName,
        string $targetAccountLastName,
        string $targetAccountEmail
    ): AssetGroupAccountClone {
        #ds-snippet-start:Admin12Step4
        $countryCode = "US";

        $accountData = new AssetGroupAccountClone([
            'source_account' => new AssetGroupAccountCloneSourceAccount(
                [
                    'id' => $sourceAccountId,
                ]
            ),
            'target_account' => new AssetGroupAccountCloneTargetAccount(
                [
                    'name' => $targetAccountName,
                    'country_code' => $countryCode,
                    'admin' => new AssetGroupAccountCloneTargetAccountAdmin(
                        [
                            'first_name' => $targetAccountFirstName,
                            'last_name' => $targetAccountLastName,
                            'email' => $targetAccountEmail,
                        ]
                    ),
                ]
            ),
        ]);
        #ds-snippet-end:Admin12Step4

        #ds-snippet-start:Admin12Step5
        return $provisionAssetGroupApi->cloneAssetGroupAccount($organizationId, $accountData);
        #ds-snippet-end:Admin12Step5
    }
}
