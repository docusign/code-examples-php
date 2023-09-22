<?php

namespace DocuSign\Services\Examples\Click;

use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapRequest;
use DocuSign\Click\Model\ClickwrapVersionSummaryResponse;
use DocuSign\Click\Model\DisplaySettings;
use DocuSign\Click\Model\Document;
use DocuSign\Services\ClickApiClientService;
use DocuSign\Services\RouterService;

class CreateNewClickwrapVersionService
{
    /**
     * @param  $args array
     * @param string $demoDocsPath
     * @param ClickApiClientService $clientService
     * @return ClickwrapVersionSummaryResponse
     */
    public static function createNewClickwrapVersion(
        array $args,
        string $demoDocsPath,
        ClickApiClientService $clientService
    ): ClickwrapVersionSummaryResponse {

        #ds-snippet-start:Click3Step3
        $accountsApi = $clientService->accountsApi();

        # Build display settings
        $displaySettings = new DisplaySettings(
            [
                'consent_button_text' => 'Accept',
                'display_name' => $args['clickwrap_name'],
                'must_read' => true,
                'require_accept' => false,
                'document_display' => 'document',
                'downloadable' => false,
                'format' => 'modal',
                'send_to_email' => false,
            ]
        );
        # Read the PDF from the disk
        # The exception will be raised if the file doesn't exist
        $doc_file = 'World_Wide_Corp_fields.pdf';
        $content_bytes = file_get_contents($demoDocsPath . $doc_file);
        $base64_file_content = base64_encode($content_bytes);

        # Build array of documents.
        $documents = [
            new Document([  # create the DocuSign document object
                'document_base64' => $base64_file_content,
                'document_name' => 'Terms of Service',
                'file_extension' => 'pdf',
                'order' => '0'
            ])
        ];
        $clickwrap = new ClickwrapRequest(
            [
                'clickwrap_name' => $args["clickwrap_name"],
                'display_settings' => $displaySettings,
                'documents' => $documents,
                'require_reacceptance' => true,
                'status' => 'inactive',
            ]
        );
        #ds-snippet-end:Click3Step3

        try {
            #ds-snippet-start:Click3Step4
            $response = $accountsApi->createClickwrapVersion($args['account_id'], $args['clickwrap_id'], $clickwrap);
            #ds-snippet-end:Click3Step4
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $response;
    }

    public static function getClickwraps(
        RouterService $routerService,
        array $args,
        ClickApiClientService $clientService,
        string $eg
    ): array {
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            try {
                $apiClient = $clientService->accountsApi();
                return $apiClient->getClickwraps($args['account_id'])['clickwraps'];
            } catch (ApiException $e) {
                error_log($e);
                return [];
            }
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
