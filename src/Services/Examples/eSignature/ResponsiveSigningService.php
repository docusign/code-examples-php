<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\DocumentHtmlDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use Example\Services\SignatureClientService;

class ResponsiveSigningService
{
    // Step 3 start
    public static function worker(array $args, SignatureClientService $clientService, string $demoPath): string
    {
        $envelopeDefinition = ResponsiveSigningService::makeEnvelope($args["envelope_args"], $demoPath);
        $envelopeApi = $clientService->getEnvelopeApi();

        try {
            $envelopeSummary = $envelopeApi->createEnvelope($args['account_id'], $envelopeDefinition);
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        $envelopeId = $envelopeSummary->getEnvelopeId();

        $authenticationMethod = 'None';
        $recipientViewRequest = $clientService->getRecipientViewRequest(
            $authenticationMethod,
            $args["envelope_args"]
        );

        $viewUrl = $clientService->getRecipientView($args['account_id'], $envelopeId, $recipientViewRequest);

        return $viewUrl['url'];
    }
    // Step 3 end

    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, cc_name, cc_email, signer_client_id
     * @param  $args array
     * @return EnvelopeDefinition -- returns an envelope definition
     */

    // Step 2 start
    private static function makeEnvelope(array $args, string $demoPath): EnvelopeDefinition
    {
        $signer = new Signer(
            [
                'email' => $args['signer_email'],
                'name' => $args['signer_name'],
                'recipient_id' => "1",
                'routing_order' => "1",
                'client_user_id' => $args['signer_client_id'],
                'role_name' => 'Signer'
            ]
        );

        $cc = new CarbonCopy(
            [
                'email' => $args['cc_email'],
                'name' => $args['cc_name'],
                'recipient_id' => "2",
            ]
        );

        $htmlMarkupFileName = 'order_form.html';
        $htmlMarkup = file_get_contents($demoPath . $htmlMarkupFileName);

        $htmlWithData = str_replace(
            ['{signer_name}', '{signer_email}', '{cc_name}', '{cc_email}', '/sn1/', '/l1q/', '/l2q/'],
            [$args['signer_name'], $args['signer_email'], $args['cc_name'], $args['cc_email'],
            '<ds-signature data-ds-role="Signer"/>', ' <input data-ds-type="number"/>', '<input data-ds-type="number"/>'],
            $htmlMarkup
        );

        return new EnvelopeDefinition(
            [
                'email_subject' => "Example Signing Document",
                'documents' => [
                    new Document(
                        [
                            'name' => 'Lorem Ipsum', # can be different from actual file name
                            'document_id' => 1, # a label used to reference the doc,
                            'html_definition' => new DocumentHtmlDefinition(
                                [
                                    'source' => $htmlWithData
                                ]
                            )
                        ]
                    )
                ],
                'recipients' => new Recipients(['signers' => [$signer], 'carbon_copies' => [$cc]]),
                'status' => "sent" # requests that the envelope be created and sent.
            ]
        );
    }
    // Step 2 end
}
