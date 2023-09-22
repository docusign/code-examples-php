<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Model\EnvelopeFormData;

class EnvelopeTabDataService
{
    /**
     * Do the work of the example
     * 1. Get the envelope's data
     *
     * @param  $args array
     * @param $clientService
     * @return EnvelopeFormData
     */
    # ***DS.snippet.0.start
    public static function envelopeTabData(array $args, $clientService): EnvelopeFormData
    {
        # 1. call API method
        # Exceptions will be caught by the calling function
        $envelope_api = $clientService->getEnvelopeApi();
        return $envelope_api->getFormData($args['account_id'], $args['envelope_id']);
    }
    # ***DS.snippet.0.end
}
