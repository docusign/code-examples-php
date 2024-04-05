<?php

namespace DocuSign\Services\Examples\Maestro;

use DocuSign\Maestro\Api\WorkflowInstanceManagementApi;
use DocuSign\Maestro\Client\ApiException;
use DocuSign\Maestro\Model\CancelResponse;

class CancelMaestroWorkflowService
{
    /**
     * Get workflow instance and check is status is in progress
     * @param WorkflowInstanceManagementApi $workflowInstanceApi
     * @param string $accountId
     * @param string $workflowId
     * @param string $instanceId
     * @return bool
     */
    public static function getWorkflowInstanceAndCheckItsStatus(
        WorkflowInstanceManagementApi $workflowInstanceApi,
        string $accountId,
        string $workflowId,
        string $instanceId
    ): bool {
        try {
            $instance = $workflowInstanceApi->getWorkflowInstance(
                $accountId,
                $workflowId,
                $instanceId
            );

            if ($instance->getInstanceState() != "In progress") {
                return true;
            }

            return false;
        } catch (ApiException $exception) {
            return true;
        }
    }

    /**
     * Cancel workflow instance
     * @param WorkflowInstanceManagementApi $workflowInstanceApi
     * @param string $accountId
     * @param string $instanceId
     * @return CancelResponse
     */
    #ds-snippet-start:Maestro2Step3
    public static function cancelWorkflowInstance(
        WorkflowInstanceManagementApi $workflowInstanceApi,
        string $accountId,
        string $instanceId
    ): CancelResponse {
        return $workflowInstanceApi->cancelWorkflowInstance($accountId, $instanceId);
    }
    #ds-snippet-end:Maestro2Step3
}
