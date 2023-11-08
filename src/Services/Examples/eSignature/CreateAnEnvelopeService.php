<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\Services\SignatureClientService;

class CreateAnEnvelopeService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     * 3. Create the Recipient View request object
     * 4. Obtain the recipient_view_url for the embedded signing
     *
     * @param  $args array
     * @param string $demoDocsPath
     * @param SignatureClientService $clientService
     * @return array ['redirect_url']
     */
    # ***DS.snippet.0.start
    public static function createAnEnvelope(array $args, string $demoDocsPath, SignatureClientService $clientService): array
    {

        # 1. Create the envelope request object
        $envelope_definition = CreateAnEnvelopeService::makeEnvelope($args["envelope_args"], $demoDocsPath);
        $envelope_api = $clientService->getEnvelopeApi();

        # 2. call Envelopes::create API method
        # Exceptions will be caught by the calling function
        try {
            $envelopeSummary = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        $envelope_id = $envelopeSummary->getEnvelopeId();

        # 3. Create the Recipient View request object
        $authentication_method = 'None'; # How is this application authenticating
        # the signer? See the `authentication_method' definition
        # https://developers.docusign.com/esign-rest-api/reference/Envelopes/EnvelopeViews/createRecipient
        $recipient_view_request = $clientService->getRecipientViewRequest(
            $authentication_method,
            $args["envelope_args"]
        );

        # 4. Obtain the recipient_view_url for the embedded signing
        # Exceptions will be caught by the calling function
        $envelopeSummary = $clientService->getRecipientView($args['account_id'], $envelope_id, $recipient_view_request);

        return ['envelope_id' => $envelope_id, 'redirect_url' => $envelopeSummary['url']];
    }

    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @param string $demoDocsPath
     * @return EnvelopeDefinition -- returns an envelope definition
     */
    public static function makeEnvelope(array $args, string $demoDocsPath): EnvelopeDefinition
    {
        $envelopeAndSigner = ApplyBrandToTemplateService::defineAnEnvelopeAndSigner($args, $demoDocsPath);
        $document = $envelopeAndSigner["document"];
        $signer = $envelopeAndSigner["signer"];

        # Next, create the top level envelope definition and populate it.
        return new EnvelopeDefinition([
            'email_subject' => "Please sign this document sent from the PHP SDK",
            'documents' => [$document],
            # The Recipients object wants arrays for each recipient type
            'recipients' => new Recipients(['signers' => [$signer]]),
            'status' => "sent" # requests that the envelope be created and sent.
        ]);
    }
    # ***DS.snippet.0.end
}
