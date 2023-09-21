<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\TemplateRole;

class ApplyBrandToTemplateService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @param $clientService
     * @return array ['envelope_id']
     */
    # ***DS.snippet.0.start
    public static function applyBrandToTemplate(array $args, $clientService): array
    {
        # Step 3. Construct the request body
        $envelope_definition = ApplyBrandToTemplateService::makeEnvelope($args["envelope_args"]);

        # Step 4. Call the eSignature REST API
        $envelope_api = $clientService->getEnvelopeApi();
        $createdEnvelope = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);

        return ['envelope_id' => $createdEnvelope->getEnvelopeId()];
    }

    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, cc_name, cc_email, template_id, brand_id
     * @param  $args array
     * @return EnvelopeDefinition -- returns an envelope definition
     */
    public static function makeEnvelope(array $args): EnvelopeDefinition
    {
        $signer = new TemplateRole([
            'name' => $args['signer_name'],
            'email' => $args['signer_email'],
            'role_name' => 'signer'
        ]);
        $cc = new TemplateRole([
                'name' => $args['cc_name'],
                'email' => $args['cc_email'],
                'role_name' => 'cc'
        ]);
        # Next, create the top-level envelope definition and populate it
        return new EnvelopeDefinition([
            'template_id' => $args['template_id'],
            'template_roles' => [$signer, $cc],
            'status' => "sent", # Request that the envelope be created and sent
            'brand_id' => $args["brand_id"], # Apply selected Brand to envelope
        ]);
    }
    # ***DS.snippet.0.end
}
