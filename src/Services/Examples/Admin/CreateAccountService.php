<?php

namespace DocuSign\Services\Examples\Admin;

use DocuSign\Admin\Api\ProvisionAssetGroupApi;
use DocuSign\Admin\Model\OrganizationSubscriptionResponse;
use DocuSign\Admin\Model\SubAccountCreateRequest;
use DocuSign\Admin\Model\SubAccountCreateRequestSubAccountCreationSubscription;
use DocuSign\Admin\Model\SubAccountCreateRequestSubAccountCreationTargetAccountAdmin;
use DocuSign\Admin\Model\SubAccountCreateRequestSubAccountCreationTargetAccountDetails;
use DocuSign\Admin\Model\SubscriptionProvisionModelAssetGroupWorkResult;

class CreateAccountService
{
    private const DEFAULT_ACCOUNT_NAME = 'CreatedThroughAPI';
    private const DEFAULT_COUNTRY_CODE = 'US';

    #ds-snippet-start:Admin13Step3
    public static function getFirstPlanItem(
        ProvisionAssetGroupApi $provisionAssetGroupApi,
        string $orgId
    ): ?OrganizationSubscriptionResponse {
        $planItems = $provisionAssetGroupApi->getOrganizationPlanItems($orgId);
        return $planItems[0] ?? null;
    }
    #ds-snippet-end:Admin13Step3

    public static function createAccountBySubscription(
        ProvisionAssetGroupApi $provisionAssetGroupApi,
        string $orgId,
        string $email,
        string $firstName,
        string $lastName,
        string $subscriptionId,
        string $planId
    ): SubscriptionProvisionModelAssetGroupWorkResult {
        $subAccountRequest = self::buildSubAccountRequest(
            $email,
            $firstName,
            $lastName,
            $subscriptionId,
            $planId
        );
        
        #ds-snippet-start:Admin13Step5
        return $provisionAssetGroupApi->createAssetGroupAccount($orgId, $subAccountRequest);
        #ds-snippet-end:Admin13Step5
    }

    #ds-snippet-start:Admin13Step4
    private static function buildSubAccountRequest(
        string $email,
        string $firstName,
        string $lastName,
        string $subscriptionId,
        string $planId
    ): SubAccountCreateRequest {
        $uuidList = [];

        $subscriptionDetails = new SubAccountCreateRequestSubAccountCreationSubscription();
        $subscriptionDetails->setId($subscriptionId);
        $subscriptionDetails->setPlanId($planId);
        $subscriptionDetails->setModules($uuidList);

        $targetAccount = new SubAccountCreateRequestSubAccountCreationTargetAccountDetails();
        $targetAccount->setName(self::DEFAULT_ACCOUNT_NAME);
        $targetAccount->setCountryCode(self::DEFAULT_COUNTRY_CODE);

        $admin = new SubAccountCreateRequestSubAccountCreationTargetAccountAdmin();
        $admin->setEmail($email);
        $admin->setFirstName($firstName);
        $admin->setLastName($lastName);
        $admin->setLocale(SubAccountCreateRequestSubAccountCreationTargetAccountAdmin::LOCALE_EN);

        $targetAccount->setAdmin($admin);

        $subAccountRequest = new SubAccountCreateRequest();
        $subAccountRequest->setSubscriptionDetails($subscriptionDetails);
        $subAccountRequest->setTargetAccount($targetAccount);

        return $subAccountRequest;
    }
    #ds-snippet-end:Admin13Step4
}
