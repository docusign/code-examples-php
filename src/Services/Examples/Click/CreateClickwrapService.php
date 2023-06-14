<?php

namespace Example\Services\Examples\Click;

use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapRequest;
use DocuSign\Click\Model\ClickwrapVersionSummaryResponse;
use DocuSign\Click\Model\DisplaySettings;
use DocuSign\Click\Model\Document;
use Example\Services\ClickApiClientService;

class CreateClickwrapService
{
    /**
     * 1. Build the display settings
     * 2. Build array of documents
     * 3. Build clickwrap
     * 4. Submit clickwrap using SDK
     * @param  $args array
     * @param string $demoDocsPath
     * @param ClickApiClientService $clientService
     * @return ClickwrapVersionSummaryResponse
     */
    public static function createClickwrap(
        array $args,
        string $demoDocsPath,
        ClickApiClientService $clientService
    ): ClickwrapVersionSummaryResponse {
        #ds-snippet-start:Rooms1Step3
        $accountsApi = $clientService->accountsApi();
        # Build the display settings
        $displaySettings = new DisplaySettings(
            [
                'consent_button_text' => 'I Agree',
                'display_name' => 'Terms of Service',
                'downloadable' => true,
                'format' => 'modal',
                'has_decline_button' => true,
                'must_read' => true,
                'require_accept' => true,
                'document_display' => 'document'
            ]
        );
        # Read the PDF from the disk
        # The exception will be raised if the file doesn't exist
        $doc_file = 'TermsOfService.pdf';
        $content_bytes = file_get_contents($demoDocsPath . $doc_file);
        $base64_file_content = base64_encode($content_bytes);

        # Build array of documents.
        $documents = [
            new Document([  # create the DocuSign document object
                'document_base64' => $base64_file_content,
                'document_name' => 'Lorem Ipsum',
                'file_extension' => 'pdf',
                'order' => '1'
            ])
        ];

        # Build ClickwrapRequest
        $clickwrap = new ClickwrapRequest(
            [
                'clickwrap_name' => $args["clickwrap_name"],
                'display_settings' => $displaySettings,
                'documents' => $documents,
                'require_reacceptance' => true
            ]
        );
        #ds-snippet-end:Rooms1Step3

        try {
            #ds-snippet-start:Rooms1Step4
            $response =  $accountsApi->createClickwrap($args['account_id'], $clickwrap);
            #ds-snippet-end:Rooms1Step4
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        return $response;
    }
}
