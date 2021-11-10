<?php

namespace Example\Services\Examples\eSignature;

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
    public static function applyBrandToEnvelope(array $args, $demoDocsPath, $clientService): array
    {
        # Step 3. Construct the request body
        $envelope_definition = ApplyBrandToTemplateService::make_envelope($args["envelope_args"], $demoDocsPath);

        # Step 4. Call the eSignature REST API
        $envelope_api = $clientService->getEnvelopeApi();
        $createdEnvelope = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);

        return ['envelope_id' => $createdEnvelope->getEnvelopeId()];
    }
    # ***DS.snippet.0.end
}
