<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\InPersonSigner;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use Example\Services\SignatureClientService;

class InPersonSigningService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     * 3. Create the Recipient View request object
     * 4. Obtain the recipient_view_url for the embedded signing
     * @return string
     */
    public static function worker(
        string $accountId,
        string $hostName,
        SignatureClientService $clientService,
        string $demoPath
    ): string {

        # Step 3 Start
        $envelopeDefinition = InPersonSigningService::_prepareEnvelope($hostName, $demoPath);
        $envelopeApi = $clientService->getEnvelopeApi();

        try {
            $envelopeSummary = $envelopeApi->createEnvelope($accountId, $envelopeDefinition);
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        # Step 3 end

        # Step 5 start
        $authentication_method = 'None';

        $envelopeArguments = [
            'signer_email' => $GLOBALS['DS_CONFIG']['signer_email'],
            'signer_name' => $GLOBALS['DS_CONFIG']['signer_name'],
            'ds_return_url' => $GLOBALS['app_url'] . 'index.php?page=ds_return'
        ];

        $recipientViewRequest = $clientService->getRecipientViewRequest(
            $authentication_method,
            $envelopeArguments
        );

        $viewUrl = $clientService->getRecipientView($accountId, $envelopeSummary->getEnvelopeId(), $recipientViewRequest);

        return $viewUrl['url'];
    }
        # Step 5 end
    
    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return EnvelopeDefinition -- returns an envelope definition
     */

    # Step 2 start
    private static function _prepareEnvelope(string $hostName, string $demoPath): EnvelopeDefinition
    {
        $file_content_in_bytes = file_get_contents($demoPath . $GLOBALS['DS_CONFIG']['doc_pdf']);
        $document = new Document(
            [
                'document_base64' => base64_encode($file_content_in_bytes),
                'name' => 'Lorem Ipsum',
                'file_extension' => 'pdf',
                'document_id' => '1'
            ]
        );

        $inPersonSigner = new InPersonSigner(
            [
                'host_email' => $GLOBALS['DS_CONFIG']['signer_email'],
                'host_name' => $GLOBALS['DS_CONFIG']['signer_name'],
                'signer_name' => $hostName,
                'recipient_id' => "1",
                'routing_order' => "1"
            ]
        );

        $sign_here = new SignHere(
            [
                'anchor_string' => '/sn1/',
                'anchor_units' => 'pixels',
                'anchor_y_offset' => '10',
                'anchor_x_offset' => '20'
            ]
        );

        $inPersonSigner->settabs(new Tabs(['sign_here_tabs' => [$sign_here]]));

        return new EnvelopeDefinition(
            [
                'email_subject' => "Please host this in-person signing session",
                'documents' => [$document],
                'recipients' => new Recipients(['in_person_signers' => [$inPersonSigner]]),
                'status' => "sent"
            ]
        );
    }
    #Step 2 end
}
