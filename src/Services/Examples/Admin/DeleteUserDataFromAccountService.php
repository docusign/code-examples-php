<?php

namespace DocuSign\Services\Examples\Admin;

use DocuSign\Admin\Api\AccountsApi;
use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\IndividualMembershipDataRedactionRequest;
use DocuSign\Admin\Model\IndividualUserDataRedactionResponse;

class DeleteUserDataFromAccountService
{
    /**
     * Delete user data from account.
     *
     * @param AccountsApi $accountsApi
     * @param string      $accountId
     * @param string      $userId
     * @return IndividualUserDataRedactionResponse
     * @throws ApiException
     */
    public static function deleteUserDataFromAccount(
        AccountsApi $accountsApi,
        string $accountId,
        string $userId
    ): IndividualUserDataRedactionResponse {
        #ds-snippet-start:Admin11Step3
        $membershipDataRedaction = new IndividualMembershipDataRedactionRequest();
        $membershipDataRedaction->setUserId($userId);
        #ds-snippet-end:Admin11Step3

        #ds-snippet-start:Admin11Step4
        return $accountsApi->redactIndividualMembershipData($accountId, $membershipDataRedaction);
        #ds-snippet-end:Admin11Step4
    }
}
