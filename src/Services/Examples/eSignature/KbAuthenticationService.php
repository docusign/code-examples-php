<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;

class KbAuthenticationService
{
    /**
     * Do the work of the example
     * Get the envelope's data
     *
     * @param  $args array
     * @param $clientService
     * @return array ['envelope_id']
     */
    public static function kbAuthentification(array $args, $clientService, $demoDocsPath): array
    {
        # Create the envelope request object
        $envelope_definition = KbAuthenticationService::makeEnvelope($args["envelope_args"], $demoDocsPath);

        # Call Envelopes::create API method
        # Exceptions will be caught by the calling function
        #ds-snippet-start:eSign22Step4
        $envelope_api = $clientService->getEnvelopeApi();
        $envelopeResponse = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);

        return ['envelope_id' => $envelopeResponse->getEnvelopeId()];
        #ds-snippet-end:eSign22Step4
    }

    /**
     * This function creates the envelope definition for the
     * order form.
     * Parameters for the envelope: signer_email, signer_name
     *
     * @param  $args array
     * @return mixed -- returns an envelope definition
     */
    #ds-snippet-start:eSign22Step3
    public static function makeEnvelope(array $args, $demoDocsPath): EnvelopeDefinition
    {
        $envelopeAndSigner = RecipientAuthenticationService::constructAnEnvelope($demoDocsPath);
        $envelope_definition = $envelopeAndSigner['envelopeDefinition'];
        $signer1Tabs = $envelopeAndSigner['signerTabs'];

        $signer1 = new Signer([
            'name' => $args['signer_name'],
            'email' => $args['signer_email'],
            'routing_order' => '1',
            'status' => 'created',
            'delivery_method' => 'Email',
            'recipient_id' => '1', #represents your {RECIPIENT_ID}
            'tabs' => $signer1Tabs,
            'id_check_configuration_name' => 'ID Check'
        ]);

        $recipients = new Recipients();
        $recipients->setSigners(array($signer1));

        $envelope_definition->setRecipients($recipients);

        return $envelope_definition;
    }
    #ds-snippet-end:eSign22Step3
}
