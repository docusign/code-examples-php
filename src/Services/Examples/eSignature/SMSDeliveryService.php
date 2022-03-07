<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\RecipientAdditionalNotification;
use DocuSign\eSign\Model\RecipientPhoneNumber;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;

class SMSDeliveryService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @param $clientService
     * @param $demoDocsPath
     * @return array ['redirect_url']
     */
    # ***DS.snippet.0.start
    public static function smsDelivery(array $args, $clientService, $demoDocsPath): array
    {
        # Step 2. Create the envelope definition
        $envelope_definition = SMSDeliveryService::make_envelope($args["envelope_args"], $clientService, $demoDocsPath);
        $envelope_api = $clientService->getEnvelopeApi();


        # Call Envelopes::create API method
        # Exceptions will be caught by the calling function
        try {
            # Step 3. Create and send the envelope
            $envelopeResponse = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return ['envelope_id' => $envelopeResponse->getEnvelopeId()];
    }

    /**
     * Creates envelope definition
     * Document 1: An HTML document.
     * Document 2: A Word .docx document.
     * Document 3: A PDF document.
     * DocuSign will convert all of the documents to the PDF format.
     * The recipients' field tags are placed using <b>anchor</b> strings.
     *
     * Parameters for the envelope: signer_name, signer_client_id
     *
     * @param  $args array
     * @param $clientService
     * @param $demoDocsPath
     * @return EnvelopeDefinition -- returns an envelope definition
     */
    public static function make_envelope(array $args, $clientService, $demoDocsPath): EnvelopeDefinition
    {
        $envelope_definition = CreateAnEnvelopeFunctionService::make_envelope($args, $clientService, $demoDocsPath);

        $signerPhone = new RecipientPhoneNumber([
            'country_code' => $args['signer_country_code'],
            'number' => $args['signer_phone_number']
        ]);

        # Create the signer recipient model
        $signer1 = new Signer([
            'name' => $args['signer_name'], 'phone_number' => $signerPhone,
            'recipient_id' => "1", 'routing_order' => "1"
            ]);
        # routingOrder (lower means earlier) determines the order of deliveries
        # to the recipients. Parallel routing order is supported by using the
        # same integer as the order for two or more recipients.

        $CCsignerPhone = new RecipientPhoneNumber([
            'country_code' => $args['cc_country_code'],
            'number' => $args['cc_phone_number']
        ]);

        # Create a CC recipient to receive a copy of the documents
        $CC = new CarbonCopy([
            'name' => $args['cc_name'], 'phone_number' => $CCsignerPhone,
            'recipient_id' => "2", 'routing_order' => "2"
            ]);

        return SMSDeliveryService::addSignersToTheDelivery($signer1, $CC, $envelope_definition, $args);
    }

    public static function addSignersToTheDelivery($signer1, $CC, $envelope_definition, $args)
    {
        # Create signHere fields (also known as tabs) on the documents,
        # We're using anchor (autoPlace) positioning
        #
        # The DocuSign platform searches throughout your envelope's
        # documents for matching anchor strings. So the
        # signHere2 tab will be used in both document 2 and 3 since they
        #  use the same anchor string for their "signer 1" tabs.
        $sign_here1 = new SignHere([
                                       'anchor_string' => '**signature_1**', 'anchor_units' => 'pixels',
                                       'anchor_y_offset' => '10', 'anchor_x_offset' => '20']);
        $sign_here2 = new SignHere([
                                       'anchor_string' => '/sn1/', 'anchor_units' =>  'pixels',
                                       'anchor_y_offset' => '10', 'anchor_x_offset' => '20']);

        # Add the tabs model (including the sign_here tabs) to the signer
        # The Tabs object wants arrays of the different field/tab types
        $signer1->setTabs(new Tabs([
                                       'sign_here_tabs' => [$sign_here1, $sign_here2]]));

        # Add the recipients to the envelope object
        $recipients = new Recipients([
                                         'signers' => [$signer1], 'carbon_copies' => [$CC]]);
        $envelope_definition->setRecipients($recipients);

        # Request that the envelope be sent by setting |status| to "sent".
        # To request that the envelope be created as a draft, set to "created"
        $envelope_definition->setStatus($args["status"]);

        return $envelope_definition;
    }
    # ***DS.snippet.0.end
}
