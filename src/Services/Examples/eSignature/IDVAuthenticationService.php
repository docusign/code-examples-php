<?php

namespace DocuSign\Services\Examples\eSignature;

use DateTime;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\RecipientIdentityVerification;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;

class IDVAuthenticationService
{
    /**
     * Do the work of the example
     * 1. Get the envelope's data
     *
     * @param  $args array
     * @param $clientService
     * @return array ['envelope_id']
     */
   
    public static function idvAuthentication(array $args, $clientService, $demoDocsPath, string $fixingInstructions): array
    {
        # Create the envelope request object
        $envelope_definition = IDVAuthenticationService::makeEnvelope(
            $args["envelope_args"],
            $clientService,
            $demoDocsPath,
            $fixingInstructions
        );

        # Call Envelopes::create API method
        # Exceptions will be caught by the calling function
        #ds-snippet-start:eSign23Step5
        $envelope_api = $clientService->getEnvelopeApi();
        $envelopeResponse = $envelope_api->createEnvelopeWithHttpInfo(
            $args['account_id'],
            $envelope_definition
        );

        $remaining = $envelopeResponse[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $envelopeResponse[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }

        return ['envelope_id' => $envelopeResponse[0]->getEnvelopeId()];
        #ds-snippet-end:eSign23Step5
    }

    /**
     * This function creates the envelope definition for the
     * order form.
     * Parameters for the envelope: signer_email, signer_name
     *
     * @param  $args array
     * @param $clientService
     * @return mixed -- returns an envelope definition
     */
    public static function makeEnvelope(array $args, $clientService, $demoDocsPath, string $fixingInstruction): EnvelopeDefinition
    {
        # Retrieve the workflow ID
        #ds-snippet-start:eSign23Step3
        $accounts_api = $clientService->getAccountsApi();
        $accounts_response = $accounts_api->getAccountIdentityVerificationWithHttpInfo($_SESSION['ds_account_id']);

        $remaining = $accounts_response[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $accounts_response[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }

        $workflows_data = $accounts_response[0]->getIdentityVerification();
        $workflow_id = '';
        foreach ($workflows_data as $workflow) {
            if ($workflow['default_name'] == 'DocuSign ID Verification') {
                $workflow_id = $workflow['workflow_id'];
            }
        }
        #ds-snippet-end:eSign23Step3

        if ($workflow_id == '') {
            throw new ApiException($fixingInstruction);
        }

        $envelopeAndSigner = RecipientAuthenticationService::constructAnEnvelope(
            $demoDocsPath
        );
        #ds-snippet-start:eSign23Step4
        $envelope_definition = $envelopeAndSigner['envelopeDefinition'];
        $signer1Tabs = $envelopeAndSigner['signerTabs'];

        $signer1 = new Signer(
            [
                'name' => $args['signer_name'],
                'email' => $args['signer_email'],
                'routing_order' => '1',
                'status' => 'created',
                'delivery_method' => 'Email',
                'recipient_id' => '1', # Represents your {RECIPIENT_ID}
                'tabs' => $signer1Tabs,
            ]
        );

        $wFObj = new RecipientIdentityVerification();
        $wFObj->setWorkflowId($workflow_id);

        $signer1->setIdentityVerification($wFObj);

        $recipients = new Recipients();
        $recipients->setSigners(array($signer1));
        $envelope_definition->setRecipients($recipients);
        #ds-snippet-end:eSign23Step4

        return $envelope_definition;
    }
}
