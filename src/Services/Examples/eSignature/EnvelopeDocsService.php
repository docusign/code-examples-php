<?php

namespace DocuSign\Services\Examples\eSignature;

use DateTime;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\EnvelopeDocumentsResult;

class EnvelopeDocsService
{
    /**
     * Do the work of the example
     * Call the envelope documents list method
     *
     * @param  $args array
     * @param $clientService
     * @return EnvelopeDocumentsResult
     */

    public static function envelopeDocs(array $args, $clientService): EnvelopeDocumentsResult
    {
        # Call API method
        # Exceptions will be caught by the calling function
        #ds-snippet-start:eSign6Step3
        $envelope_api = $clientService->getEnvelopeApi();
        try {
            $listDocuments = $envelope_api->listDocumentsWithHttpInfo($args['account_id'], $args['envelope_id']);

            $remaining = $listDocuments[2]['X-RateLimit-Remaining'] ?? null;
            $reset = $listDocuments[2]['X-RateLimit-Reset'] ?? null;

            if ($remaining !== null && $reset !== null) {
                $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                error_log("API calls remaining: $remaining");
                error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
            }
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        #ds-snippet-end:eSign6Step3
        return $listDocuments[0];
    }
}
