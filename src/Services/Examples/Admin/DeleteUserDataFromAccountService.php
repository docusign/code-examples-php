<?php

namespace DocuSign\Services\Examples\Admin;

use DateTime;
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
        $response = $accountsApi->redactIndividualMembershipDataWithHttpInfo($accountId, $membershipDataRedaction);
        $remaining = $response[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $response[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }
        return $response[0];
        #ds-snippet-end:Admin11Step4
    }
}
