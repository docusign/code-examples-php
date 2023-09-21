<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Api\EnvelopesApi\UpdateOptions;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Workflow;
use DocuSign\eSign\Model\EnvelopeUpdateSummary;

class UnpauseSignatureWorkflowService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @param $clientService
     * @return string
     */

    public static function unpauseSignatureWorkflow(array $args, $clientService): EnvelopeUpdateSummary
    {
        # Step 3 Start
        $env = new EnvelopeDefinition([
            'workflow' => new Workflow(['workflow_status' => 'in_progress'])
        ]);
        $envelope_api = $clientService->getEnvelopeApi();
        $envelope_option = new UpdateOptions();

        # Update resend envelope parameter
        $envelope_option->setResendEnvelope('true');
        # Step 3 End

        # Step 4 Start
        # Call Envelopes::update API method to unpause signature workflow
        $envelope = $envelope_api->update(
            $args['account_id'],
            $args['pause_envelope_id'],
            $env,
            $envelope_option
        );
        # Step 4 End

        return $envelope;
    }
}
