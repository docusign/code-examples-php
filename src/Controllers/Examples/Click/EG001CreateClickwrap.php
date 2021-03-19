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

class EG001CreateClickwrap extends ClickApiBaseController
{
    private ClickApiClientService $clientService;
    private RouterService $routerService;
    private array $args;
    private string $eg = "ceg001";  # reference (and URL) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new ClickApiClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return created clickwrap data
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
                $clickwrap_name = $results['clickwrapName'];
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Creating a clickwrap example",
                    "Creating a clickwrap example",
                    "Clickwrap $clickwrap_name has been created!",
                    json_encode(json_encode($results))
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * 1. Build the display settings
     * 2. Build array of documents
     * 3. Build clickwrap
     * 4. Submit clickwrap using SDK
     * @param  $args array
     * @return ClickwrapVersionSummaryResponse
     * @throws ApiException
     */
    public function worker(array $args)
    {

        # Step 3 Start
        $accountsApi = $this->clientService->accountsApi();
        # Build the display settings
        $displaySettings = new DisplaySettings(
            [
                'consent_button_text' => 'I Agree',
                'display_name' => 'Terms of Service',
                'downloadable' => true,
                'format' => 'modal',
                'has_decline_button' => true,
                'must_read' => true,
                'must_view' => true,
                'require_accept' => true,
                'document_display' => 'document'
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
        # Step 3 End



        try {
            # Step 4 Start
            $response =  $accountsApi->createClickwrap($this->args['account_id'], $clickwrap);
            # Step 4 End
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            exit;
        }
        return $response;
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $clickwrap_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['clickwrap_name']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'clickwrap_name' => $clickwrap_name
        ];
    }
}
