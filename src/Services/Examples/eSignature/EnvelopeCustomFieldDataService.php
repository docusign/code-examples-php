<?php

namespace DocuSign\Services\Examples\eSignature;

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
    # ***DS.snippet.0.start
    public static function envelopeCustomFieldData(array $args, $clientService): CustomFieldsEnvelope
    {
        # 1. call API method
        # Exceptions will be caught by the calling function
        $envelope_api = $clientService->getEnvelopeApi();
        return $envelope_api->listCustomFields($args['account_id'], $args['envelope_id']);
    }
    # ***DS.snippet.0.end
}
