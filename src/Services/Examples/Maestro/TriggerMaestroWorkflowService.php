<?php

namespace DocuSign\Services\Examples\Maestro;

use DocuSign\Maestro\Api\WorkflowManagementApi;
use DocuSign\Maestro\Api\WorkflowTriggerApi;
use DocuSign\Maestro\Api\WorkflowTriggerApi\TriggerWorkflowOptions;
use DocuSign\Maestro\Client\ApiException;
use DocuSign\Maestro\Model\DeployRequest;
use DocuSign\Maestro\Model\NewOrUpdatedWorkflowDefinitionResponse;
use DocuSign\Maestro\Model\TriggerPayload;
use DocuSign\Maestro\Model\TriggerWorkflowViaPostResponse;
use DocuSign\Maestro\Model\WorkflowDefinitionWithId;
use DocuSign\Maestro\Model\WorkflowDefinitionList;
use DocuSign\Maestro\Api\WorkflowManagementApi\GetWorkflowDefinitionsOptions;
use Exception;
use stdClass;

class TriggerMaestroWorkflowService
{
    /**
     * Get workflow definition
     * @param WorkflowManagementApi $managementApi
     * @param string $accountId
     * @param string $workflowId
     * @return WorkflowDefinitionWithId
     * @throws ApiException
     */
    #ds-snippet-start:Maestro1Step3
    public static function getWorkflowDefinition(
        WorkflowManagementApi $managementApi,
        string $accountId,
        string $workflowId
    ): WorkflowDefinitionWithId {
        return $managementApi->getWorkflowDefinition($accountId, $workflowId);
    }
    #ds-snippet-end:Maestro1Step3

    /**
     * Get workflow definitions
     * @param WorkflowManagementApi $managementApi
     * @param string $accountId
     * @return WorkflowDefinitionList
     * @throws ApiException
     */
    public static function getWorkflowDefinitions(
        WorkflowManagementApi $managementApi,
        string $accountId
    ): WorkflowDefinitionList {
        $workflowDefinitionsOptions = new GetWorkflowDefinitionsOptions();
        $workflowDefinitionsOptions->setStatus("active");

        return $managementApi->getWorkflowDefinitions($accountId, $workflowDefinitionsOptions);
    }

    /**
     * Trigger workflow
     * @param WorkflowTriggerApi $workflowTriggerApi
     * @param string $accountId
     * @param string $instanceName
     * @param string $signerName
     * @param string $signerEmail
     * @param string $ccName
     * @param string $ccEmail
     * @param string $mtid
     * @param string $mtsec
     * @return TriggerWorkflowViaPostResponse
     * @throws ApiException
     */
    public static function triggerWorkflow(
        WorkflowTriggerApi $workflowTriggerApi,
        string $accountId,
        string $instanceName,
        string $signerName,
        string $signerEmail,
        string $ccName,
        string $ccEmail,
        string $mtid,
        string $mtsec,
        string $workflowId
    ): TriggerWorkflowViaPostResponse {
        #ds-snippet-start:Maestro1Step4
        $triggerPayload = new TriggerPayload();
        $triggerPayload->setInstanceName($instanceName);
        $triggerPayload->setParticipants(new stdClass());
        $triggerPayload->setPayload(
            (object)array(
                "signerEmail" => $signerEmail,
                "signerName" => $signerName,
                "ccEmail" => $ccEmail,
                "ccName" => $ccName
            )
        );
        $triggerPayload->setMetadata(new stdClass());

        $triggerOptions = new TriggerWorkflowOptions();
        $triggerOptions->setMtid($mtid);
        $triggerOptions->setMtsec($mtsec);
        #ds-snippet-end:Maestro1Step4

        #ds-snippet-start:Maestro1Step5
        return $workflowTriggerApi->triggerWorkflow($accountId, $workflowId, $triggerPayload, $triggerOptions);
        #ds-snippet-end:Maestro1Step5
    }

    /**
     * Trigger workflow
     * @return WorkflowDefinitionWithId
     * @throws ApiException
     */
    public static function publishWorkflow(
        WorkflowManagementApi $managementApi,
        string $accountId,
        string $workflowId
    ): string {
        try {
            $managementApi->publishOrUnPublishWorkflowDefinition(
                $accountId,
                $workflowId,
                new DeployRequest()
            );
            return "";
        } catch (ApiException $exception) {
            $isConsentRequired = str_contains(
                strtolower($exception->getResponseBody()->message),
                'consent required'
            );

            if ($isConsentRequired) {
                return $exception->getResponseBody()->consentUrl;
            } else {
                throw $exception;
            }
        }
    }

    /**
     * Generate random guid
     * @throws Exception
     */
    private static function generateSecureGuid(): string
    {
        mt_srand((double)microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = '-';
        $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return $uuid;
    }

    /**
     * Create workflow
     * @param WorkflowManagementApi $managementApi
     * @param string $accountId
     * @param string $templateId
     * @return NewOrUpdatedWorkflowDefinitionResponse
     * @throws ApiException
     * @throws Exception
     */
    public static function createWorkflow(
        WorkflowManagementApi $managementApi,
        string $accountId,
        string $templateId
    ): NewOrUpdatedWorkflowDefinitionResponse {
        $signerId = TriggerMaestroWorkflowService::generateSecureGuid();
        $ccId = TriggerMaestroWorkflowService::generateSecureGuid();
        $triggerId = "wfTrigger";

        $body = '{
            "workflowDefinition": {
                "workflowName": "Example workflow - send invite to signer",
                "workflowDescription": "",
                "accountId": "' . $accountId . '",
                "documentVersion": "1.0.0",
                "schemaVersion": "1.0.0",
                "participants": {
                    "' . $signerId . '": {
                        "participantRole": "Signer"
                    },
                    "' . $ccId . '": {
                        "participantRole": "CC"
                    }
                },
                "trigger": {
                    "name": "Get_URL",
                    "type": "Http",
                    "httpType": "Get",
                    "id": "' . $triggerId . '",
                    "input": {
                        "metadata": {
                            "customAttributes": {}
                        },
                        "payload": {
                            "dacId_' . $triggerId . '": {
                                "source": "step",
                                "propertyName": "dacId",
                                "stepId": "'. $triggerId . '"
                            },
                            "id_' . $triggerId .  '": {
                                "source": "step",
                                "propertyName": "id",
                                "stepId": "' . $triggerId .  '"
                            },
                            "signerName_' . $triggerId .  '": {
                                "source": "step",
                                "propertyName": "signerName",
                                "stepId": "' . $triggerId .  '"
                            },
                            "signerEmail_' . $triggerId .  '": {
                                "source": "step",
                                "propertyName": "signerEmail",
                                "stepId": "' . $triggerId .  '"
                            },
                            "ccName_' . $triggerId .  '": {
                                "source": "step",
                                "propertyName": "ccName",
                                "stepId": "' . $triggerId .  '"
                            },
                            "ccEmail_' . $triggerId .  '": {
                                "source": "step",
                                "propertyName": "ccEmail",
                                "stepId": "'. $triggerId . '"
                            }
                        },
                        "participants": {}
                    },
                    "output": {
                        "dacId_'. $triggerId . '": {
                            "source": "step",
                            "propertyName": "dacId",
                            "stepId": "'. $triggerId . '"
                        }
                    }
                },
                "variables": {
                    "dacId_'. $triggerId . '": {
                        "source": "step",
                        "propertyName": "dacId",
                        "stepId": "'. $triggerId . '"
                    },
                    "id_'. $triggerId . '": {
                        "source": "step",
                        "propertyName": "id",
                        "stepId": "'. $triggerId . '"
                    },
                    "signerName_'. $triggerId . '": {
                        "source": "step",
                        "propertyName": "signerName",
                        "stepId": "'. $triggerId . '"
                    },
                    "signerEmail_'. $triggerId . '": {
                        "source": "step",
                        "propertyName": "signerEmail",
                        "stepId": "'. $triggerId . '"
                    },
                    "ccName_'. $triggerId . '": {
                        "source": "step",
                        "propertyName": "ccName",
                        "stepId": "'. $triggerId . '"
                    },
                    "ccEmail_'. $triggerId . '": {
                        "source": "step",
                        "propertyName": "ccEmail",
                        "stepId": "'. $triggerId . '"
                    },
                    "envelopeId_step2": {
                        "source": "step",
                        "propertyName": "envelopeId",
                        "stepId": "step2",
                        "type": "String"
                    },
                    "combinedDocumentsBase64_step2": {
                        "source": "step",
                        "propertyName": "combinedDocumentsBase64",
                        "stepId": "step2",
                        "type": "File"
                    },
                    "fields.signer.text.value_step2": {
                        "source": "step",
                        "propertyName": "fields.signer.text.value",
                        "stepId": "step2",
                        "type": "String"
                    }
                },
                "steps": [
                    {
                        "id": "step1",
                        "name": "Set Up Invite",
                        "moduleName": "Notification-SendEmail",
                        "configurationProgress": "Completed",
                        "type": "DS-EmailNotification",
                        "config": {
                            "templateType": "WorkflowParticipantNotification",
                            "templateVersion": 1,
                            "language": "en",
                            "sender_name": "DocuSign Orchestration",
                            "sender_alias": "Orchestration",
                            "participantId": "'. $signerId .'"
                        },
                        "input": {
                            "recipients": [
                                {
                                    "name": {
                                        "source": "step",
                                        "propertyName": "signerName",
                                        "stepId": "'. $triggerId . '"
                                    },
                                    "email": {
                                        "source": "step",
                                        "propertyName": "signerEmail",
                                        "stepId": "'. $triggerId . '"
                                    }
                                }
                            ],
                            "mergeValues": {
                                "CustomMessage": "Follow this link to access and complete the workflow.",
                                "ParticipantFullName": {
                                    "source": "step",
                                    "propertyName": "signerName",
                                    "stepId": "'. $triggerId . '"
                                }
                            }
                        },
                        "output": {}
                    },
                    {
                        "id": "step2",
                        "name": "Get Signatures",
                        "moduleName": "ESign",
                        "configurationProgress": "Completed",
                        "type": "DS-Sign",
                        "config": {
                            "participantId": "'. $signerId .'"
                        },
                        "input": {
                            "isEmbeddedSign": true,
                            "documents": [
                                {
                                    "type": "FromDSTemplate",
                                    "eSignTemplateId": "'. $templateId.'"
                                }
                            ],
                            "emailSubject": "Please sign this document",
                            "emailBlurb": "",
                            "recipients": {
                                "signers": [
                                    {
                                        "defaultRecipient": "false",
                                        "tabs": {
                                            "signHereTabs": [
                                                {
                                                    "stampType": "signature",
                                                    "name": "SignHere",
                                                    "tabLabel": "Sign Here",
                                                    "scaleValue": "1",
                                                    "optional": "false",
                                                    "documentId": "1",
                                                    "recipientId": "1",
                                                    "pageNumber": "1",
                                                    "xPosition": "191",
                                                    "yPosition": "148",
                                                    "tabId": "1",
                                                    "tabType": "signhere"
                                                }
                                            ],
                                            "textTabs": [
                                                {
                                                    "requireAll": "false",
                                                    "value": "",
                                                    "required": "false",
                                                    "locked": "false",
                                                    "concealValueOnDocument": "false",
                                                    "disableAutoSize": "false",
                                                    "tabLabel": "text",
                                                    "font": "helvetica",
                                                    "fontSize": "size14",
                                                    "localePolicy": {},
                                                    "documentId": "1",
                                                    "recipientId": "1",
                                                    "pageNumber": "1",
                                                    "xPosition": "153",
                                                    "yPosition": "230",
                                                    "width": "84",
                                                    "height": "23",
                                                    "tabId": "2",
                                                    "tabType": "text"
                                                }
                                            ],
                                            "checkboxTabs": [
                                                {
                                                    "name": "",
                                                    "tabLabel": "ckAuthorization",
                                                    "selected": "false",
                                                    "selectedOriginal": "false",
                                                    "requireInitialOnSharedChange": "false",
                                                    "required": "true",
                                                    "locked": "false",
                                                    "documentId": "1",
                                                    "recipientId": "1",
                                                    "pageNumber": "1",
                                                    "xPosition": "75",
                                                    "yPosition": "417",
                                                    "width": "0",
                                                    "height": "0",
                                                    "tabId": "3",
                                                    "tabType": "checkbox"
                                                },
                                                {
                                                    "name": "",
                                                    "tabLabel": "ckAuthentication",
                                                    "selected": "false",
                                                    "selectedOriginal": "false",
                                                    "requireInitialOnSharedChange": "false",
                                                    "required": "true",
                                                    "locked": "false",
                                                    "documentId": "1",
                                                    "recipientId": "1",
                                                    "pageNumber": "1",
                                                    "xPosition": "75",
                                                    "yPosition": "447",
                                                    "width": "0",
                                                    "height": "0",
                                                    "tabId": "4",
                                                    "tabType": "checkbox"
                                                },
                                                {
                                                    "name": "",
                                                    "tabLabel": "ckAgreement",
                                                    "selected": "false",
                                                    "selectedOriginal": "false",
                                                    "requireInitialOnSharedChange": "false",
                                                    "required": "true",
                                                    "locked": "false",
                                                    "documentId": "1",
                                                    "recipientId": "1",
                                                    "pageNumber": "1",
                                                    "xPosition": "75",
                                                    "yPosition": "478",
                                                    "width": "0",
                                                    "height": "0",
                                                    "tabId": "5",
                                                    "tabType": "checkbox"
                                                },
                                                {
                                                    "name": "",
                                                    "tabLabel": "ckAcknowledgement",
                                                    "selected": "false",
                                                    "selectedOriginal": "false",
                                                    "requireInitialOnSharedChange": "false",
                                                    "required": "true",
                                                    "locked": "false",
                                                    "documentId": "1",
                                                    "recipientId": "1",
                                                    "pageNumber": "1",
                                                    "xPosition": "75",
                                                    "yPosition": "508",
                                                    "width": "0",
                                                    "height": "0",
                                                    "tabId": "6",
                                                    "tabType": "checkbox"
                                                }
                                            ],
                                            "radioGroupTabs": [
                                                {
                                                    "documentId": "1",
                                                    "recipientId": "1",
                                                    "groupName": "radio1",
                                                    "radios": [
                                                        {
                                                            "pageNumber": "1",
                                                            "xPosition": "142",
                                                            "yPosition": "384",
                                                            "value": "white",
                                                            "selected": "false",
                                                            "tabId": "7",
                                                            "required": "false",
                                                            "locked": "false",
                                                            "bold": "false",
                                                            "italic": "false",
                                                            "underline": "false",
                                                            "fontColor": "black",
                                                            "fontSize": "size7"
                                                        },
                                                        {
                                                            "pageNumber": "1",
                                                            "xPosition": "74",
                                                            "yPosition": "384",
                                                            "value": "red",
                                                            "selected": "false",
                                                            "tabId": "8",
                                                            "required": "false",
                                                            "locked": "false",
                                                            "bold": "false",
                                                            "italic": "false",
                                                            "underline": "false",
                                                            "fontColor": "black",
                                                            "fontSize": "size7"
                                                        },
                                                        {
                                                            "pageNumber": "1",
                                                            "xPosition": "220",
                                                            "yPosition": "384",
                                                            "value": "blue",
                                                            "selected": "false",
                                                            "tabId": "9",
                                                            "required": "false",
                                                            "locked": "false",
                                                            "bold": "false",
                                                            "italic": "false",
                                                            "underline": "false",
                                                            "fontColor": "black",
                                                            "fontSize": "size7"
                                                        }
                                                    ],
                                                    "shared": "false",
                                                    "requireInitialOnSharedChange": "false",
                                                    "requireAll": "false",
                                                    "tabType": "radiogroup",
                                                    "value": "",
                                                    "originalValue": ""
                                                }
                                            ],
                                            "listTabs": [
                                                {
                                                    "listItems": [
                                                        {
                                                            "text": "Red",
                                                            "value": "red",
                                                            "selected": "false"
                                                        },
                                                        {
                                                            "text": "Orange",
                                                            "value": "orange",
                                                            "selected": "false"
                                                        },
                                                        {
                                                            "text": "Yellow",
                                                            "value": "yellow",
                                                            "selected": "false"
                                                        },
                                                        {
                                                            "text": "Green",
                                                            "value": "green",
                                                            "selected": "false"
                                                        },
                                                        {
                                                            "text": "Blue",
                                                            "value": "blue",
                                                            "selected": "false"
                                                        },
                                                        {
                                                            "text": "Indigo",
                                                            "value": "indigo",
                                                            "selected": "false"
                                                        },
                                                        {
                                                            "text": "Violet",
                                                            "value": "violet",
                                                            "selected": "false"
                                                        }
                                                    ],
                                                    "value": "",
                                                    "originalValue": "",
                                                    "required": "false",
                                                    "locked": "false",
                                                    "requireAll": "false",
                                                    "tabLabel": "list",
                                                    "font": "helvetica",
                                                    "fontSize": "size14",
                                                    "localePolicy": {},
                                                    "documentId": "1",
                                                    "recipientId": "1",
                                                    "pageNumber": "1",
                                                    "xPosition": "142",
                                                    "yPosition": "291",
                                                    "width": "78",
                                                    "height": "0",
                                                    "tabId": "10",
                                                    "tabType": "list"
                                                }
                                            ],
                                            "numericalTabs": [
                                                {
                                                    "validationType": "currency",
                                                    "value": "",
                                                    "required": "false",
                                                    "locked": "false",
                                                    "concealValueOnDocument": "false",
                                                    "disableAutoSize": "false",
                                                    "tabLabel": "numericalCurrency",
                                                    "font": "helvetica",
                                                    "fontSize": "size14",
                                                    "localePolicy": {
                                                        "cultureName": "en-US",
                                                        "currencyPositiveFormat": "csym_1_comma_234_comma_567_period_89",
                                                        "currencyNegativeFormat":
                                                            "opar_csym_1_comma_234_comma_567_period_89_cpar",
                                                        "currencyCode": "usd"
                                                    },
                                                    "documentId": "1",
                                                    "recipientId": "1",
                                                    "pageNumber": "1",
                                                    "xPosition": "163",
                                                    "yPosition": "260",
                                                    "width": "84",
                                                    "height": "0",
                                                    "tabId": "11",
                                                    "tabType": "numerical"
                                                }
                                            ]
                                        },
                                        "signInEachLocation": "false",
                                        "agentCanEditEmail": "false",
                                        "agentCanEditName": "false",
                                        "requireUploadSignature": "false",
                                        "name": {
                                            "source": "step",
                                            "propertyName": "signerName",
                                            "stepId": "'. $triggerId . '"
                                        },
                                        "email": {
                                            "source": "step",
                                            "propertyName": "signerEmail",
                                            "stepId": "'. $triggerId . '"
                                        },
                                        "recipientId": "1",
                                        "recipientIdGuid": "00000000-0000-0000-0000-000000000000",
                                        "accessCode": "",
                                        "requireIdLookup": "false",
                                        "routingOrder": "1",
                                        "note": "",
                                        "roleName": "signer",
                                        "completedCount": "0",
                                        "deliveryMethod": "email",
                                        "templateLocked": "false",
                                        "templateRequired": "false",
                                        "inheritEmailNotificationConfiguration": "false",
                                        "recipientType": "signer"
                                    }
                                ],
                                "carbonCopies": [
                                    {
                                        "agentCanEditEmail": "false",
                                        "agentCanEditName": "false",
                                        "name": {
                                            "source": "step",
                                            "propertyName": "ccName",
                                            "stepId": "'. $triggerId . '"
                                        },
                                        "email": {
                                            "source": "step",
                                            "propertyName": "ccEmail",
                                            "stepId": "'. $triggerId . '"
                                        },
                                        "recipientId": "2",
                                        "recipientIdGuid": "00000000-0000-0000-0000-000000000000",
                                        "accessCode": "",
                                        "requireIdLookup": "false",
                                        "routingOrder": "2",
                                        "note": "",
                                        "roleName": "cc",
                                        "completedCount": "0",
                                        "deliveryMethod": "email",
                                        "templateLocked": "false",
                                        "templateRequired": "false",
                                        "inheritEmailNotificationConfiguration": "false",
                                        "recipientType": "carboncopy"
                                    }
                                ],
                                "certifiedDeliveries": []
                            }
                        },
                        "output": {
                            "envelopeId_step2": {
                                "source": "step",
                                "propertyName": "envelopeId",
                                "stepId": "step2",
                                "type": "String"
                            },
                            "combinedDocumentsBase64_step2": {
                                "source": "step",
                                "propertyName": "combinedDocumentsBase64",
                                "stepId": "step2",
                                "type": "File"
                            },
                            "fields.signer.text.value_step2": {
                                "source": "step",
                                "propertyName": "fields.signer.text.value",
                                "stepId": "step2",
                                "type": "String"
                            }
                        }
                    },
                    {
                        "id": "step3",
                        "name": "Show a Confirmation Screen",
                        "moduleName": "ShowConfirmationScreen",
                        "configurationProgress": "Completed",
                        "type": "DS-ShowScreenStep",
                        "config": {
                            "participantId": "'. $signerId .'"
                        },
                        "input": {
                            "httpType": "Post",
                            "payload": {
                                "participantId": "'. $signerId .'",
                                "confirmationMessage": {
                                    "title": "Tasks complete",
                                    "description": "You have completed all your workflow tasks."
                                }
                            }
                        },
                        "output": {}
                    }
                ]
            }
        }';

        return $managementApi->createWorkflowDefinition($_SESSION['ds_account_id'], $body);
    }
}
