<?php

namespace DocuSign\Controllers\Examples\ConnectedFields;

use DocuSign\eSign\Client\ApiException;
use DocuSign\Controllers\ConnectedFieldsApiBaseController;
use DocuSign\Services\Examples\ConnectedFields\SetConnectedFieldsService;
use DocuSign\Services\ManifestService;

class Cf001SetConnectedFields extends ConnectedFieldsApiBaseController
{
    const EG = 'cf001'; # reference (and url) for this example

    const FILE = __FILE__;

    private string $orgId;

    /**
     * Create a new controller instance.
     * @return void
     * @throws ApiException
     */
    public function __construct()
    {
        parent::__construct();
    
        $this->codeExampleText = $this->getPageText(static::EG);
        $this->checkDsToken();

        $accessToken = $_SESSION['ds_access_token'];
        $accountId = $_SESSION['ds_account_id'];

        $filteredAppsJson = $this->getFilteredAppsJson($accountId, $accessToken);

        if ($filteredAppsJson === null || empty($filteredAppsJson)) {
            $this->clientService->showDoneTemplate(
                $this->codeExampleText["ExampleName"],
                $this->codeExampleText["ExampleName"],
                $this->codeExampleText["AdditionalPage"][0]["ResultsPageText"]
            );
        } else {
            $_SESSION["apps"] = $filteredAppsJson;
            parent::controller(['apps' => $filteredAppsJson]);
        }
    }

    private function getFilteredAppsJson(string $accountId, string $accessToken): ?array
    {
        $apps = SetConnectedFieldsService::getConnectedFieldsTabGroups($accountId, $accessToken);
        $filteredApps = SetConnectedFieldsService::filterData($apps);
        return json_decode($filteredApps, true);
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws ApiException for API problems.
     */
    public function createController(): void
    {
        $this->checkDsToken();

        $args = $this->getTemplateArgs();
    
        $accessToken = $_SESSION['ds_access_token'];
        $accountId = $_SESSION['ds_account_id'];
        $pdfDoc = $GLOBALS['DS_CONFIG']['doc_pdf'];
        $selectedAppId = $args['selected_app_id'];
        $basePath = self::DEMO_DOCS_PATH;
        $app = $this->getAppById($selectedAppId);
    
        $envelopeId = SetConnectedFieldsService::sendEnvelope(
            $_SESSION['ds_base_path'],
            $accessToken,
            $accountId,
            $pdfDoc,
            $basePath,
            $app,
            $args['signer_name'],
            $args['signer_email']
        );
    
        if ($envelopeId) {
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                null,
                ManifestService::replacePlaceholders(
                    "{0}",
                    $envelopeId,
                    $this->codeExampleText["ResultsPageText"]
                )
            );
        }
    }

    private function getAppById(string $selectedAppId): array
    {
        return array_values(array_filter($_SESSION["apps"], function ($item) use ($selectedAppId) {
            return isset($item['appId']) && $item['appId'] === $selectedAppId;
        }))[0];
    }

    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return [
            'signer_email' => $this->checkInputValues($_POST['signer_email']),
            'signer_name' => $this->checkInputValues($_POST['signer_name']),
            'selected_app_id' => $_POST['appId'],
        ];
    }
}
