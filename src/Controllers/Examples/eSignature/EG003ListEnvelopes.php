<?php
/**
 * Example 003: List envelopes whose status has changed in the last 10 days
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Api\EnvelopesApi\ListStatusChangesOptions;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\EnvelopesInformation;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG003ListEnvelopes extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg003";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 2. Call the worker method
            $results = $this->worker($this->args);

            if ($results) {
                # results is an object that implements ArrayAccess. Convert to a regular array:
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Envelope list",
                    "List envelopes results",
                    "Results from the Envelopes::listStatusChanges method:",
                    json_encode(json_encode($results))
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }


    /**
     * Do the work of the example
     * 1. List the envelopes that have changed in the last 10 days
     *
     * @param  $args array
     * @return EnvelopesInformation
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker(array $args): EnvelopesInformation
    {
        # 1. call API method
        # Exceptions will be caught by the calling function
        # The Envelopes::listStatusChanges method has many options
        # See https://developers.docusign.com/esign-rest-api/reference/Envelopes/Envelopes/listStatusChanges
        # The list status changes call requires at least a from_date OR
        # a set of envelope_ids. Here we filter using a from_date.
        # Here we set the from_date to filter envelopes for the last 10 days
        # Use ISO 8601 date format
        $envelope_api = $this->clientService->getEnvelopeApi();
        $from_date = date("c", (time() - (10 * 24 * 60 * 60)));
        $options = new ListStatusChangesOptions();
        $options->setFromDate($from_date);
        try {
            $results = $envelope_api->listStatusChanges($args['account_id'], $options);
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            exit;
        }

        return $results;
    }
    # ***DS.snippet.0.end

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];

        return $args;
    }
}
