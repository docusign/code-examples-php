<?php

namespace DocuSign\Services\Examples\eSignature;

use DateTime;
use DocuSign\eSign\Model\CustomFieldsEnvelope;

class EnvelopeCustomFieldDataService
{
    /**
     * Do the work of the example
     * 1. Get the envelope's data
     *
     * @param  $args array
     * @param $clientService
     * @return CustomFieldsEnvelope
     */
    public static function envelopeCustomFieldData(array $args, $clientService): CustomFieldsEnvelope
    {
        # Call API method
        # Exceptions will be caught by the calling function
        #ds-snippet-start:eSign18Step3
        $envelope_api = $clientService->getEnvelopeApi();
        $response = $envelope_api->listCustomFieldsWithHttpInfo($args['account_id'], $args['envelope_id']);

        $remaining = $response[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $response[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }
        return $response[0];
        #ds-snippet-end:eSign18Step3
    }
}
