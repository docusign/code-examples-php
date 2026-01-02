<?php

namespace DocuSign\Services\Examples\eSignature;

use DateTime;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Recipients;

class EnvelopeRecipientsService
{
    /**
     * Do the work of the example
     * Call the envelope recipients list method
     *
     * @param  $args array
     * @param $clientService
     * @return Recipients
     */
    
    public static function envelopeRecipients(array $args, $clientService): Recipients
    {
        # Call API method
        # Exceptions will be caught by the calling function
        #ds-snippet-start:eSign5Step2
        $envelope_api = $clientService->getEnvelopeApi();
        try {
            $listRecipients = $envelope_api->listRecipientsWithHttpInfo($args['account_id'], $args['envelope_id']);

            $remaining = $listRecipients[2]['X-RateLimit-Remaining'] ?? null;
            $reset = $listRecipients[2]['X-RateLimit-Reset'] ?? null;

            if ($remaining !== null && $reset !== null) {
                $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                error_log("API calls remaining: $remaining");
                error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
            }
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $listRecipients[0];
        #ds-snippet-end:eSign5Step2
    }
}
