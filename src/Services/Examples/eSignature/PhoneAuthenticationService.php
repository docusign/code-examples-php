<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\RecipientIdentityPhoneNumber;
use DocuSign\eSign\Model\RecipientIdentityInputOption;
use DocuSign\eSign\Model\RecipientIdentityVerification;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;

class PhoneAuthenticationService
{
    /**
     * Do the work of the example
     * 1. Get the envelope's data
     *
     * @param  $args array
     * @param $clientService
     * @return array ['envelope_id']
     */
    public static function phoneAuthentication(array $args, $demoDocsPath, $clientService, string $fixingInstructions): array
    {
        # Create the envelope request object
        $envelope_definition = PhoneAuthenticationService::makeEnvelope(
            $args["envelope_args"],
            $clientService,
            $demoDocsPath,
            $fixingInstructions
        );

        # call Envelopes::create API method
        # Exceptions will be caught by the calling function
        #ds-snippet-start:eSign20Step5
        $envelope_api = $clientService->getEnvelopeApi();
        $envelopeResponse = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        #ds-snippet-end:eSign20Step5

        return ['envelope_id' => $envelopeResponse->getEnvelopeId()];
    }

    /**
     * This function creates the envelope definition for the
     * order form.
     * Parameters for the envelope: signer_email, signer_name
     *
     * @param  $args array
     * @return mixed -- returns an envelope definition
     */
    public static function makeEnvelope(
        array $args,
        $clientService,
        $demoDocsPath,
        string $fixingInstructions
    ): EnvelopeDefinition {
        # Retrieve the workflow ID
        #ds-snippet-start:eSign20Step3
        $accounts_api = $clientService->getAccountsApi();
        $accounts_response = $accounts_api->getAccountIdentityVerification($_SESSION['ds_account_id']);
        $workflows_data = $accounts_response->getIdentityVerification();
        $workflow_id = '';
        foreach ($workflows_data as $workflow) {
            if ($workflow['default_name'] == 'Phone Authentication') {
                $workflow_id = $workflow['workflow_id'];
            }
        }
        #ds-snippet-end:eSign20Step3
        if ($workflow_id == '') {
            throw new ApiException($fixingInstructions);
        }
        
        $envelopeAndSigner = RecipientAuthenticationService::constructAnEnvelope($demoDocsPath);
        #ds-snippet-start:eSign20Step4
        $envelope_definition = $envelopeAndSigner['envelopeDefinition'];
        $signer1Tabs = $envelopeAndSigner['signerTabs'];

        $phoneNumber = new RecipientIdentityPhoneNumber;
        $phoneNumber->setCountryCode($args['signer_country_code']);
        $phoneNumber->setNumber($args['signer_phone_number']);

        $inputOption = new RecipientIdentityInputOption;
        $inputOption->setName('phone_number_list');
        $inputOption->setValueType('PhoneNumberList');
        $inputOption->setPhoneNumberList(array($phoneNumber));

        $identityVerification = new RecipientIdentityVerification;
        $identityVerification->setWorkflowId($workflow_id);
        $identityVerification->setInputOptions(array($inputOption));

        $signer1 = new Signer([
            'name' => $args['signer_name'],
            'email' => $args['signer_email'],
            'routing_order' => '1',
            'status' => 'created',
            'delivery_method' => 'Email',
            'recipient_id' => '1', # represents your {RECIPIENT_ID}
            'tabs' => $signer1Tabs,
            'identity_verification' => $identityVerification
        ]);


        $recipients = new Recipients();
        $recipients->setSigners(array($signer1));

        $envelope_definition->setRecipients($recipients);
        #ds-snippet-end:eSign20Step4

        return $envelope_definition;
    }
}
