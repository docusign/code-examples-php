<?php

namespace DocuSign\Services\Examples\Maestro;

use DocuSign\Maestro\Api\WorkflowInstanceManagementApi;
use DocuSign\Maestro\Client\ApiException;
use DocuSign\Maestro\Model\WorkflowInstance;

class GetWorkflowStatusService
{
    /**
     * Get workflow definition
     * @param WorkflowInstanceManagementApi $workflowInstanceApi
     * @param string $accountId
     * @param string $workflowId
     * @param string $instanceId
     * @return WorkflowInstance
     * @throws ApiException
     */
    #ds-snippet-start:Maestro3Step2
    public static function getWorkflowInstance(
        WorkflowInstanceManagementApi $workflowInstanceApi,
        string $accountId,
        string $workflowId,
        string $instanceId
    ): WorkflowInstance {
        return $workflowInstanceApi->getWorkflowInstance(
            $accountId,
            $workflowId,
            $instanceId
        );
    }
    #ds-snippet-end:Maestro3Step2
}
