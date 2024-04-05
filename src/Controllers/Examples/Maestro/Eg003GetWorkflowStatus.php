<?php

namespace DocuSign\Controllers\Examples\Maestro;

use DocuSign\Controllers\MaestroApiBaseController;
use DocuSign\Maestro\Client\ApiException;
use DocuSign\Services\Examples\Maestro\GetWorkflowStatusService;
use DocuSign\Services\ManifestService;

class Eg003GetWorkflowStatus extends MaestroApiBaseController
{
    const EG = 'mae003'; # reference (and url) for this example

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
     */
    public function createController(): void
    {
        $this->getTemplateArgs();
        $instanceId = $_SESSION['instance_id'];
        $workflowId = $_SESSION["workflow_id"];
        $workflowInstanceApi = $this->clientService->workflowInstanceManagementApi();

        try {
            $workflowInstance = GetWorkflowStatusService::getWorkflowInstance(
                $workflowInstanceApi,
                $this->args['account_id'],
                $workflowId,
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
            json_encode($workflowInstance->__toString()),
            ManifestService::replacePlaceholders(
                '{0}',
                $workflowInstance->getInstanceState(),
                $this->codeExampleText['ResultsPageText']
            )
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
            'ds_access_token' => $_SESSION['ds_access_token']
        ];
    }
}
