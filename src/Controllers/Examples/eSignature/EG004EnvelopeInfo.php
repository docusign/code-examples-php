<?php
/**
 * Example 004: Get an envelope's basic information and status
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Envelope;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG004EnvelopeInfo extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg004";  # reference (and url) for this example

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
     * 1. Check the token and check we have an envelope_id
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        $envelope_id= $this->args['envelope_id'];
        $token_ok = $this->routerService->ds_token_ok($minimum_buffer_min);
        if ($token_ok && $envelope_id) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = $this->worker($this->args);

            if ($results) {
                # results is an object that implements ArrayAccess. Convert to a regular array:
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Envelope status results",
                    "Envelope status results",
                    "Results from the Envelopes::get method:",
                    json_encode(json_encode($results))
                );
            }
        } elseif (! $token_ok) {
            $this->clientService->needToReAuth($this->eg);
        } elseif (! $envelope_id) {
            $this->clientService->envelopeNotCreated(
                basename(__FILE__),
                $this->routerService->getTemplate($this->eg),
                $this->routerService->getTitle($this->eg),
                $this->eg,
                ['envelope_ok' => false]
            );
        }
    }


    /**
     * Do the work of the example
     * 1. Get the envelope's data
     *
     * @param  $args array
     * @return Envelope
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker(array $args): Envelope
    {
        # 1. call API method
        # Exceptions will be caught by the calling function
        $envelope_api = $this->clientService->getEnvelopeApi();
        try {
            $results = $envelope_api->getEnvelope($args['account_id'], $args['envelope_id']);
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
        $envelope_id= isset($_SESSION['envelope_id']) ? $_SESSION['envelope_id'] : false;
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_id' => $envelope_id
        ];

        return $args;
    }
}

