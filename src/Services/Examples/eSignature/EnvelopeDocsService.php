<?php

namespace Example\Services\Examples\eSignature;

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
            $listDocuments = $envelope_api->listDocuments($args['account_id'], $args['envelope_id']);
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        #ds-snippet-end:eSign6Step3
        return $listDocuments;
    }
}
