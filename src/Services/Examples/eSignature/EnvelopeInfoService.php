<?php

namespace DocuSign\Services\Examples\eSignature;

use DateTime;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Envelope;

class EnvelopeInfoService
{
    /**
     * Do the work of the example
     * 1. Get the envelope's data
     *
     * @param  $args array
     * @param $clientService
     * @return Envelope
     */
    #ds-snippet-start:eSign4Step2
    public static function envelopeInfo(array $args, $clientService): Envelope
    {
        # Call API method
        # Exceptions will be caught by the calling function
        $envelope_api = $clientService->getEnvelopeApi();
        try {
            $envelopeId = $envelope_api->getEnvelopeWithHttpInfo($args['account_id'], $args['envelope_id']);

            $remaining = $envelopeId[2]['X-RateLimit-Remaining'] ?? null;
            $reset = $envelopeId[2]['X-RateLimit-Reset'] ?? null;

            if ($remaining !== null && $reset !== null) {
                $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                error_log("API calls remaining: $remaining");
                error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
            }
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $envelopeId[0];
    }
    #ds-snippet-end:eSign4Step2
}
