<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Api\EnvelopesApi\UpdateOptions;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Workflow;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG033UnpauseSignatureWorkflow extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg033"; # Reference (and URL) for this example

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
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 1. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $envelope_id = $this->worker($this->args);

            if ($envelope_id) {
                $_SESSION['pause_envelope_id'] = false;
                $this->clientService->showDoneTemplate(
                    "Envelope unpaused",
                    "Envelope unpaused",
                    "The envelope workflow has been resumed and the envelope
                             has been sent to a second recipient!<br/>
                             Envelope ID {$envelope_id}."
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @return string
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    
    public function worker($args): string
    {
        # Step 3 Start
        $env = new EnvelopeDefinition([
            'workflow' => new Workflow(['workflow_status' => 'in_progress'])
        ]);
        $envelope_api = $this->clientService->getEnvelopeApi();
        $envelope_option = new UpdateOptions();

        # Update resend envelope parameter
        $envelope_option -> setResendEnvelope('true');
        # Step 3 End

        # Step 4 Start
        # Call Envelopes::update API method to unpause signature workflow
        $envelope = $envelope_api->update(
            $args['account_id'],
            $args['pause_envelope_id'],
            $envelope=$env,
            $options=$envelope_option
        );
        $envelope_id = $envelope['envelope_id'];
        # Step 4 End

        return  $envelope_id;
    }

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
            'pause_envelope_id' => $_SESSION['pause_envelope_id']
        ];

        return $args;
    }
}
