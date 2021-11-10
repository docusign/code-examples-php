<?php

namespace Example\Services\Examples\eSignature;

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
    # ***DS.snippet.0.start
    public static function idvAuthentication(array $args, $clientService): array
    {
        # 1. Create the envelope request object
        $envelope_definition = IDVAuthenticationService::make_envelope($args["envelope_args"], $clientService);

        # 2. call Envelopes::create API method
        # Exceptions will be caught by the calling function
        $envelope_api = $clientService->getEnvelopeApi();
        $envelopeResponse = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);

        return ['envelope_id' => $envelopeResponse->getEnvelopeId()];
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
    public static function make_envelope(array $args, $clientService): EnvelopeDefinition
    {
        # Retrieve the workflow ID
        $accounts_api = $clientService->getAccountsApi();
        $accounts_response = $accounts_api->getAccountIdentityVerification($_SESSION['ds_account_id']);
        $accounts_data = $accounts_response->getIdentityVerification();
        $accounts_id = $accounts_data[0]['workflow_id'];

        $envelopeAndSigner = SmsAuthenticationService::constructAnEnvelope();
        $envelope_definition = $envelopeAndSigner['envelopeDefinition'];
        $signer1Tabs = $envelopeAndSigner['signerTabs'];

        $signer1 = new Signer([
            'name' => $args['signer_name'],
            'email' => $args['signer_email'],
            'routing_order' => '1',
            'status' => 'created',
            'delivery_method' => 'Email',
            'recipient_id' => '1', # Represents your {RECIPIENT_ID}
            'tabs' => $signer1Tabs,

        ]);

        $wFObj = new RecipientIdentityVerification();
        $wFObj->setWorkflowId($accounts_id);

        $signer1->setIdentityVerification($wFObj);

        $recipients = new Recipients();
        $recipients->setSigners(array($signer1));
        $envelope_definition->setRecipients($recipients);

        return $envelope_definition;
    }
    # ***DS.snippet.0.end
}
