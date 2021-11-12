<?php

namespace Example\Services\Examples\eSignature;

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
        # 1. Create the envelope request object
        $envelope_definition = PhoneAuthenticationService::make_envelope($args["envelope_args"], $demoDocsPath);

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
     * @return mixed -- returns an envelope definition
     */
    public static function make_envelope(array $args, $demoDocsPath): EnvelopeDefinition
    {

        $envelopeAndSigner = RecipientAuthenticationService::constructAnEnvelope($demoDocsPath);
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
        $identityVerification->setWorkflowId('c368e411-1592-4001-a3df-dca94ac539ae');
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

        return $envelope_definition;
    }
    # ***DS.snippet.0.end
}
