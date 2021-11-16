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
    # ***DS.snippet.0.start
    public static function phone_authentication(array $args, $demoDocsPath, $clientService): array
    {
        # Create the envelope request object
        $envelope_definition = PhoneAuthenticationService::make_envelope($args["envelope_args"], $clientService, $demoDocsPath);

        # call Envelopes::create API method
        # Exceptions will be caught by the calling function
        # Step 5 start
        $envelope_api = $clientService->getEnvelopeApi();
        $envelopeResponse = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        # Step 5 end

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
    public static function make_envelope(array $args, $clientService, $demoDocsPath): EnvelopeDefinition
    {

        # Retrieve the workflow ID
        # Step 3 start
        $accounts_api = $clientService->getAccountsApi();
        $accounts_response = $accounts_api->getAccountIdentityVerification($_SESSION['ds_account_id']);
        $workflows_data = $accounts_response->getIdentityVerification();
        $workflow_id = '';
        foreach ($workflows_data as $workflow) {
            if ($workflow['default_name'] == 'Phone Authentication')
                $workflow_id = $workflow['workflow_id'];  
        }
        # step 3 end
        if ($workflow_id == '') 
          throw new ApiException('Please contact <a href="https://support.docusign.com">DocuSign Support</a> to enable Phone Auth in your account.');
        
        $envelopeAndSigner = RecipientAuthenticationService::constructAnEnvelope($demoDocsPath);
        # step 4 start
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
        # Step 4 end

        return $envelope_definition;
    }
    # ***DS.snippet.0.end
}
