<?php

namespace Example\Controllers\Examples\Click;

use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapRequest;
use DocuSign\Click\Model\ClickwrapVersionSummaryResponse;
use DocuSign\Click\Model\DisplaySettings;
use DocuSign\Click\Model\Document;
use Example\Controllers\ClickApiBaseController;
use Example\Services\ClickApiClientService;
use Example\Services\RouterService;

class EG003CreateClickwrapVersion extends ClickApiBaseController
{
    private ClickApiClientService $clientService;
    private RouterService $routerService;
    private array $args;
    private string $eg = "ceg003";  # reference (and URL) for this example

    /**
     * 1. Get available clickwraps
     * 2. Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new ClickApiClientService($this->args);
        $this->routerService = new RouterService();
        # Get available clickwraps
        $clickwraps = $this->getClickwraps();
        parent::controller($this->eg, $this->routerService, basename(__FILE__), ['clickwraps' => $clickwraps]);
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return created clickwrap version
     *
     * @return void
     * @throws ApiException
     */
    function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = $this->worker($this->args);

            if ($results) {
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Creating a new clickwrap version example",
                    "Creating a new clickwrap version example",
                    "Clickwrap version has been created!",
                    json_encode(json_encode($results))
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }

    }

    /**
     * @param  $args array
     * @return ClickwrapVersionSummaryResponse
     * @throws ApiException
     */
    public function worker(array $args)
    {

        # Step 3 Start
        $accountsApi = $this->clientService->accountsApi();

        # Build display settings
        $displaySettings = new DisplaySettings(
            [
                'consent_button_text' => 'Accept',
                'display_name' => $args['clickwrap_name'],
                'must_read' => true,
                'must_view' => false,
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
        $content_bytes = file_get_contents(self::DEMO_DOCS_PATH . $doc_file);
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
        # Step 3 End

        
        try {
            # Step 4 Start
            $response = $accountsApi->createClickwrapVersion($this->args['account_id'], $this->args['clickwrap_id'], $clickwrap);
            # Step 4 End
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            exit;
        }
        
        return $response;

    }

    private function getTemplateArgs(): array
    {
        $clickwrap_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['clickwrap_name']);
        $clickwrap_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['clickwrap_id']);

        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'clickwrap_name' => $clickwrap_name,
            'clickwrap_id' => $clickwrap_id,
        ];
    }

    private function getClickwraps(): array
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            try {
                $apiClient = $this->clientService->accountsApi();
                return $apiClient->getClickwraps($this->args['account_id'])['clickwraps'];
            } catch (ApiException $e) {
                error_log($e);
                return [];
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
}
