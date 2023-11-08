<?php

namespace DocuSign\Services\Examples\eSignature;

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
            $listRecipients = $envelope_api->listRecipients($args['account_id'], $args['envelope_id']);
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $listRecipients;
        #ds-snippet-end:eSign5Step2
    }
}
