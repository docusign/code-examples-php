<?php

namespace Example\Services\Examples\eSignature;

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
        return $envelope_api->listCustomFields($args['account_id'], $args['envelope_id']);
        #ds-snippet-end:eSign18Step3
    }
}
