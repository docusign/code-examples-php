<?php

namespace DocuSign\Controllers\Examples\Maestro;

use DocuSign\Controllers\MaestroApiBaseController;
use DocuSign\Maestro\Client\ApiException;
use DocuSign\Services\Examples\Maestro\CancelMaestroWorkflowService;
use DocuSign\Services\ManifestService;

class Eg002CancelWorkflow extends MaestroApiBaseController
{
    const EG = 'mae002'; # reference (and url) for this example

    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        parent::controller();
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws \DocuSign\Maestro\Client\ApiException
     */
    public function createController(): void
    {
        $accountId = $_SESSION['ds_account_id'];
        $instanceId = $_SESSION['instance_id'];
        $workflowId = $_SESSION["workflow_id"];
        $workflowInstanceApi = $this->clientService->workflowInstanceManagementApi();

        try {
            $isRedirectNeeded = CancelMaestroWorkflowService::getWorkflowInstanceAndCheckItsStatus(
                $workflowInstanceApi,
                $accountId,
                $workflowId,
                $instanceId
            );
    
            if (!$isRedirectNeeded) {
                header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=mae001');
            }
    
            $result = CancelMaestroWorkflowService::cancelWorkflowInstance(
                $workflowInstanceApi,
                $accountId,
                $instanceId
            );
        } catch (ApiException $e) {
            if ($e->getCode() == 403) {
                $GLOBALS['twig']->display('error.html', [
                    'error_code' => $e->getCode(),
                    'error_message' => ManifestService::replacePlaceholders(
                        '{0}',
                        'Maestro',
                        ManifestService::getCommonTexts()['ContactSupportToEnableFeature']
                    ),
                    'common_texts' => ManifestService::getCommonTexts()
                ]);
                exit;
            }
        }

        $this->clientService->showDoneTemplateFromManifest(
            $this->codeExampleText,
            json_encode($result->__toString()),
            ManifestService::replacePlaceholders('{0}', $instanceId, $this->codeExampleText['ResultsPageText'])
        );
    }

    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];
    }
}
