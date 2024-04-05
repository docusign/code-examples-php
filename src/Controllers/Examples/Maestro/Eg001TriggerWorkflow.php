<?php

namespace DocuSign\Controllers\Examples\Maestro;

use DocuSign\Controllers\MaestroApiBaseController;
use DocuSign\Maestro\Client\ApiException;
use DocuSign\Services\Examples\Maestro\TriggerMaestroWorkflowService;
use DocuSign\Services\ManifestService;
use DocuSign\Maestro\Model\TriggerWorkflowViaPostResponse;

class Eg001TriggerWorkflow extends MaestroApiBaseController
{
    const EG = 'mae001'; # reference (and url) for this example

    const FILE = __FILE__;

    /**
     * Create a new controller instance
     *
     * @return void
     * @throws ApiException
     */
    public function __construct()
    {
        parent::__construct();
        $this->checkDsToken();
        $this->codeExampleText = $this->getPageText(static::EG);
        $workflowName = 'Example workflow - send invite to signer';
        $accountId = $this->args['account_id'];
        $workflowManagementApi = $this->clientService->workflowManagementApi();

        try {
            $workflowDefinitions = TriggerMaestroWorkflowService::getWorkflowDefinitions(
                $workflowManagementApi,
                $accountId
            );

            $this->selectNewestWorkflowByName($workflowDefinitions, $workflowName);
        } catch (ApiException $e) {
            if ($e->getCode() == 403) {
                $this->contactSupportToEnableFeature($e);
            }
        }

        if ($_COOKIE["template_id"] != null && $_SESSION["workflow_id"] === null) {
            try {
                $createdWorkflowDefinition = TriggerMaestroWorkflowService::createWorkflow(
                    $workflowManagementApi,
                    $accountId,
                    $_COOKIE["template_id"]
                );

                $_SESSION["workflow_id"] = $createdWorkflowDefinition->getWorkflowDefinitionId();

                $publishWorkflowUrl = TriggerMaestroWorkflowService::publishWorkflow(
                    $workflowManagementApi,
                    $accountId,
                    $_SESSION["workflow_id"]
                );

                $_SESSION["workflow_published"] = true;
                $this->openPublishWorkflowPage($publishWorkflowUrl);
            } catch (ApiException $e) {
                if ($e->getCode() == 403) {
                    $this->contactSupportToEnableFeature($e);
                }
            }
        }

        if ($_SESSION['workflow_published']) {
            $publishWorkflowUrl = TriggerMaestroWorkflowService::publishWorkflow(
                $workflowManagementApi,
                $accountId,
                $_SESSION["workflow_id"]
            );

            if ($publishWorkflowUrl == null) {
                $_SESSION["workflow_published"] = false;
            } else {
                $this->openPublishWorkflowPage($publishWorkflowUrl);
            }
        }

        parent::controller();
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws ApiException
     */
    public function createController(): void
    {
        $this->getTemplateArgs();

        $workflowId = $_SESSION["workflow_id"];
        $workflowManagementApi = $this->clientService->workflowManagementApi();

        $trigger = new TriggerWorkflowViaPostResponse();
        try {
            $workflowDefinition = TriggerMaestroWorkflowService::getWorkflowDefinition(
                $workflowManagementApi,
                $this->args['account_id'],
                $workflowId
            );

            $triggerUrl = $workflowDefinition->getTriggerUrl();

            $queryParams = parse_url($triggerUrl, PHP_URL_QUERY);
            parse_str($queryParams, $params);

            $mtid = $params['mtid'];
            $mtsec = $params['mtsec'];

            $triggerApi = $this->clientService->workflowTriggerApi();

            $trigger = TriggerMaestroWorkflowService::triggerWorkflow(
                $triggerApi,
                $this->args['account_id'],
                $this->args['envelope_args']['instance_name'],
                $this->args['envelope_args']['signer_name'],
                $this->args['envelope_args']['signer_email'],
                $this->args['envelope_args']['cc_name'],
                $this->args['envelope_args']['cc_email'],
                $mtid,
                $mtsec
            );
            $_SESSION['instance_id'] = $trigger->getInstanceId();
        } catch (ApiException $e) {
            if ($e->getCode() == 403) {
                $this->contactSupportToEnableFeature($e);
            }
        }
        $this->clientService->showDoneTemplateFromManifest(
            $this->codeExampleText,
            json_encode($trigger->__toString())
        );
    }

    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        $envelope_args = [
            'instance_name' => $_POST['instance_name'],
            'signer_email' => $_POST['signer_email'],
            'signer_name' => $_POST['signer_name'],
            'cc_email' => $_POST['cc_email'],
            'cc_name' => $_POST['cc_name'],
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }

    private function contactSupportToEnableFeature($e)
    {
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

    private function selectNewestWorkflowByName($workflowDefinitions, $workflowName)
    {
        if ($workflowDefinitions['count'] > 0) {
            $filteredWorkflows = array_filter(
                $workflowDefinitions['value'],
                function ($workflow) use ($workflowName) {
                    return $workflow['name'] === $workflowName;
                }
            );

            usort($filteredWorkflows, function ($wf1, $wf2) {
                return strtotime($wf2['lastUpdatedDate']) - strtotime($wf1['lastUpdatedDate']);
            });

            $workflow = reset($filteredWorkflows);

            if ($workflow) {
                $_SESSION["workflow_id"] = $workflow['id'];
            }
        }
    }

    private function openPublishWorkflowPage($publishWorkflowUrl)
    {
        $GLOBALS['twig']->display("maestro/eg001_publish_workflow.html", [
            'title' => $this->routerService->getTitle(static::EG),
            'consent_url' => ManifestService::replacePlaceholders(
                '{0}',
                $publishWorkflowUrl,
                $this->codeExampleText['AdditionalPage'][0]['ResultsPageText']
            ),
            'code_example_text' => $this->codeExampleText,
            'common_texts' => $this->getCommonText()
        ]);
        exit();
    }
}
