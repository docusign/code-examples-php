<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\TemplateRole;

class UseTemplateService
{
    /**
     * Do the work of the example
     * Create the envelope request object
     * Send the envelope
     *
     * @param  $args array
     * @param $clientService
     * @return array ['redirect_url']
     */
    public static function useTemplate(array $args, $clientService): array
    {
        #ds-snippet-start:eSign9Step3
        # Create the envelope request object
        $envelope_definition = UseTemplateService::make_envelope($args["envelope_args"]);

        # Call Envelopes::create API method
        # Exceptions will be caught by the calling function
        $envelope_api = $clientService->getEnvelopeApi();
        $envelopeResponse = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);

        return ['envelope_id' => $envelopeResponse->getEnvelopeId()];
        #ds-snippet-end:eSign9Step3
    }

    /**
     * Creates envelope definition using a template
     * Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return mixed -- returns an envelope definition
     */

    #ds-snippet-start:eSign9Step2
     public static function make_envelope(array $args): EnvelopeDefinition
    {
        # create the envelope definition with the template_id
        $envelope_definition = new EnvelopeDefinition([
           'status' => 'sent', 'template_id' => $args['template_id']
        ]);
        # Create the template role elements to connect the signer and cc recipients
        # to the template
        $signer = new TemplateRole([
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'role_name' => 'signer'
        ]);
        # Create a cc template role.
        $cc = new TemplateRole([
            'email' => $args['cc_email'], 'name' => $args['cc_name'],
            'role_name' => 'cc'
        ]);

        # Add the TemplateRole objects to the envelope object
        $envelope_definition->setTemplateRoles([$signer, $cc]);

        return $envelope_definition;
    }
    #ds-snippet-end:eSign9Step2
}
