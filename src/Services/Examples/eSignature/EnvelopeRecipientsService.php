<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Recipients;

class EnvelopeRecipientsService
{
    /**
     * Do the work of the example
     * 1. Call the envelope recipients list method
     *
     * @param  $args array
     * @param $clientService
     * @return Recipients
     */
    # ***DS.snippet.0.start
    public static function envelopeRecipients(array $args, $clientService): Recipients
    {
        # 1. call API method
        # Exceptions will be caught by the calling function
        $envelope_api = $clientService->getEnvelopeApi();
        try {
            $results = $envelope_api->listRecipients($args['account_id'], $args['envelope_id']);
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $results;
    }
    # ***DS.snippet.0.end
}
