<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;

class ApplyBrandToEnvelopeService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @param $demoDocsPath
     * @param $clientService
     * @return array ['redirect_url']
     */
    # ***DS.snippet.0.start
    public static function applyBrandToEnvelope(array $args, $demoDocsPath, $clientService, $docDocx, $docPDF): array
    {
        # Step 3. Construct the request body
        #ds-snippet-start:eSign29Step3
        $envelope_definition = ApplyBrandToEnvelopeService::makeEnvelope(
            $args["envelope_args"],
            $clientService,
            $demoDocsPath,
            $docDocx,
            $docPDF
        );
        #ds-snippet-end:eSign29Step3

        # Step 4. Call the eSignature REST API
        #ds-snippet-start:eSign29Step4
        $envelope_api = $clientService->getEnvelopeApi();
        $createdEnvelope = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        #ds-snippet-end:eSign29Step4

        return ['envelope_id' => $createdEnvelope->getEnvelopeId()];
    }

    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, brand_id
     * @param  $args array
     * @return EnvelopeDefinition -- returns an envelope definition
     */
    public static function makeEnvelope(array $args, $clientService, $demoDocsPath, $docDocx, $docPDF): EnvelopeDefinition
    {
        $envelope_definition = CreateAnEnvelopeFunctionService::makeEnvelope(
            $args,
            $clientService,
            $demoDocsPath,
            $docDocx,
            $docPDF
        );
        $envelope_definition->setStatus('sent');

        $signer = new Signer([
            'name' => $args['signer_name'],
            'email' => $args['signer_email'],
            'routing_order' => '1',
            'recipient_id' => '1',

        ]);
        $sign_here1 = new SignHere([
            'anchor_string' => '**signature_1**', 'anchor_units' => 'pixels',
            'anchor_y_offset' => '10', 'anchor_x_offset' => '20'
        ]);
        $sign_here2 = new SignHere([
            'anchor_string' => '/sn1/', 'anchor_units' =>  'pixels',
            'anchor_y_offset' => '10', 'anchor_x_offset' => '20'
        ]);

        # Add the tabs model (including the sign_here tabs) to the signer
        # The Tabs object wants arrays of the different field/tab types
        $signer->setTabs(new Tabs([
            'sign_here_tabs' => [$sign_here1, $sign_here2]
        ]));

        # Add the recipients to the envelope object
        $recipients = new Recipients([
            'signers' => [$signer],
        ]);
        $envelope_definition->setRecipients($recipients);
        $envelope_definition->setBrandId($args['brand_id']);


        return $envelope_definition;
    }
}
