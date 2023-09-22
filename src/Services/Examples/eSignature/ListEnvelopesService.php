<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Api\EnvelopesApi\ListStatusChangesOptions;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\EnvelopesInformation;

class ListEnvelopesService
{
    /**
     * Do the work of the example
     * 1. List the envelopes that have changed in the last 10 days
     *
     * @param  $args array
     * @param $clientService
     * @return EnvelopesInformation
     */
    # ***DS.snippet.0.start
    public static function listEnvelopes(array $args, $clientService): EnvelopesInformation
    {
        # 1. call API method
        # Exceptions will be caught by the calling function
        # The Envelopes::listStatusChanges method has many options
        # See https://developers.docusign.com/esign-rest-api/reference/Envelopes/Envelopes/listStatusChanges
        # The list status changes call requires at least a from_date OR
        # a set of envelope_ids. Here we filter using a from_date.
        # Here we set the from_date to filter envelopes for the last 10 days
        # Use ISO 8601 date format
        $envelope_api = $clientService->getEnvelopeApi();
        $from_date = date("c", (time() - (10 * 24 * 60 * 60)));
        $options = new ListStatusChangesOptions();
        $options->setFromDate($from_date);
        try {
            $statusChanges = $envelope_api->listStatusChanges($args['account_id'], $options);
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $statusChanges;
    }
    # ***DS.snippet.0.end
}
