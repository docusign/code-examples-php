<?php

namespace DocuSign\Services\Examples\WebForms;

use DocuSign\eSign\Api\TemplatesApi;
use DocuSign\eSign\Api\TemplatesApi\ListTemplatesOptions;
use DocuSign\eSign\Model\Checkbox;
use DocuSign\eSign\Model\DateSigned;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeTemplate;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\Text;
use DocuSign\Services\Examples\eSignature\CreateTemplateService;
use DocuSign\Services\SignatureClientService;
use DocuSign\WebForms\Api\FormInstanceManagementApi;
use DocuSign\WebForms\Api\FormManagementApi;
use DocuSign\WebForms\Client\ApiException;
use DocuSign\WebForms\Model\CreateInstanceRequestBody;
use DocuSign\WebForms\Model\WebFormInstance;
use DocuSign\WebForms\Model\WebFormSummaryList;
use DocuSign\WebForms\Model\WebFormValues;
use Exception;

class CreateAndEmbedFormService
{
    /**
     * Get forms
     * @param FormManagementApi $formManagementApi
     * @param string $accountId
     * @return WebFormSummaryList
     * @throws ApiException
     */
    public static function getForms(
        FormManagementApi $formManagementApi,
        string            $accountId
    ): WebFormSummaryList {
        $formName = "Web Form Example Template";

        $listFormsOptions = new FormManagementApi\ListFormsOptions();
        $listFormsOptions->setSearch($formName);

        return $formManagementApi->listForms($accountId, $listFormsOptions);
    }

    /**
     * Add template ID to form
     * @param string $fileLocation
     * @param string $templateId
     * @return void
     */
    #ds-snippet-start:WebForms1Step3
    public static function addTemplateIdToForm(string $fileLocation, string $templateId): void
    {
        $targetString = "template-id";

        try {
            $fileContent = file_get_contents($fileLocation);
            $modifiedContent = str_replace($targetString, $templateId, $fileContent);
            file_put_contents($fileLocation, $modifiedContent);
        } catch (Exception $ex) {
            echo "An error occurred: " . $ex->getMessage();
        }
    }
    #ds-snippet-end:WebForms1Step3

    /**
     * @param FormInstanceManagementApi $formInstanceApi
     * @param string $accountId
     * @param string $formId
     * @return WebFormInstance
     * @throws ApiException
     */
    public static function createInstance(
        FormInstanceManagementApi $formInstanceApi,
        string                    $accountId,
        string                    $formId
    ): WebFormInstance {
        #ds-snippet-start:WebForms1Step4
        $formValues = new WebFormValues([
            ["PhoneNumber" => "555-555-5555"],
            ["Yes" => ["Yes"]],
            ["Company" => "Tally"],
            ["JobTitle" => "Programmer Writer"]
        ]);

        $options = new CreateInstanceRequestBody([
            'client_user_id' => '1234-5678-abcd-ijkl',
            'form_values' => $formValues,
            'expiration_offset' => 3600,
        ]);
        #ds-snippet-end:WebForms1Step4

        #ds-snippet-start:WebForms1Step5
        return $formInstanceApi->createInstance($accountId, $formId, $options);
        #ds-snippet-end:WebForms1Step5
    }

    /**
     * @param TemplatesApi $templatesApi
     * @param string $templateName
     * @param string $accountId
     * @return array
     */
    public static function getTemplatesByName(
        TemplatesApi $templatesApi,
        string       $templateName,
        string       $accountId
    ): mixed {
        $listTemplateOptions = new ListTemplatesOptions();
        $listTemplateOptions->setSearchText($templateName);

        try {
            $templates = $templatesApi->listTemplates($accountId, $listTemplateOptions);

            return $templates->getEnvelopeTemplates();
        } catch (\DocuSign\eSign\Client\ApiException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Do the work of the example
     * 1. Check to see if the template already exists
     * 2. If not, create the template
     *
     * @param  $args array
     * @param string $template_name
     * @param string $demoDocsPath
     * @param SignatureClientService $clientService
     * @return array
     * @throws \DocuSign\eSign\Client\ApiException
     */
    public static function createTemplate(
        array                  $args,
        string                 $template_name,
        string                 $demoDocsPath,
        SignatureClientService $clientService
    ): array {
        $templatesApi = $clientService->getTemplatesApi();
        $options = new ListTemplatesOptions();
        $options->setSearchText($template_name);
        $templatesListResponse = $templatesApi->listTemplates(
            $args['account_id'],
            $options
        );

        if ($templatesListResponse['result_set_size'] > 0) {
            $templateId = $templatesListResponse['envelope_templates'][0]['template_id'];
            $resultsTemplateName = $templatesListResponse['envelope_templates'][0]['name'];
        } else {
            $templateObject = CreateAndEmbedFormService::makeTemplateRequest(
                $template_name,
                $demoDocsPath
            );
            $templatesListResponse = $templatesApi->createTemplate(
                $args['account_id'],
                $templateObject
            );

            $templateId = $templatesListResponse['template_id'];
            $resultsTemplateName = $templatesListResponse['name'];
        }

        return [
            'template_id' => $templateId,
            'template_name' => $resultsTemplateName,
            'created_new_template' => !($templatesListResponse['result_set_size'] > 0)
        ];
    }

    /**
     * Create a template request object
     * @param string $template_name
     * @param string $demoDocsPath
     * @return mixed
     */

    public static function makeTemplateRequest(
        string $template_name,
        string $demoDocsPath
    ): EnvelopeTemplate {
        $docName = 'World_Wide_Corp_Web_Form.pdf';
        $contentBytes = file_get_contents($demoDocsPath . $docName);
        $base64FileContent = base64_encode($contentBytes);

        $document = new Document([
            'document_base64' => $base64FileContent,
            'name' => 'World_Wide_Web_Form',
            'file_extension' => 'pdf',
            'document_id' => '1'
        ]);

        $signer = new Signer([
            'role_name' => 'signer',
            'recipient_id' => "1",
            'routing_order' => "1"
        ]);
        $signHere = new SignHere([
            'document_id' => '1',
            'anchor_string' => '/SignHere/',
            'anchor_units' => 'pixels',
            'anchor_x_offset' => '20',
            'anchor_y_offset' => '10',
            'tab_label' => 'Signature'
        ]);
        $checkbox = new Checkbox([
            'document_id' => '1',
            'anchor_string' => '/SMS/',
            'anchor_units' => 'pixels',
            'anchor_x_offset' => '0',
            'anchor_y_offset' => '0',
            'tab_label' => 'Yes'
        ]);
        $text = new Text([
            'document_id' => "1",
            'tab_label' => "FullName",
            'anchor_string' => "/FullName/",
            'anchor_units' => "pixels",
            'anchor_x_offset' => "0",
            'anchor_y_offset' => "0"
        ]);
        $text2 = new Text([
            'document_id' => "1",
            'tab_label' => "PhoneNumber",
            'anchor_string' => "/PhoneNumber/",
            'anchor_units' => "pixels",
            'anchor_x_offset' => "0",
            'anchor_y_offset' => "0"
        ]);
        $text3 = new Text([
            'document_id' => "1",
            'tab_label' => "Company",
            'anchor_string' => "/Company/",
            'anchor_units' => "pixels",
            'anchor_x_offset' => "0",
            'anchor_y_offset' => "0"
        ]);
        $text4 = new Text([
            'document_id' => "1",
            'tab_label' => "JobTitle",
            'anchor_string' => "/Title/",
            'anchor_units' => "pixels",
            'anchor_x_offset' => "0",
            'anchor_y_offset' => "0"
        ]);

        $dateSignedTabs = new DateSigned([
            'document_id' => "1",
            'tab_label' => "DateSigned",
            'anchor_string' => "/Date/",
            'anchor_units' => "pixels",
            'anchor_x_offset' => "0",
            'anchor_y_offset' => "0"
        ]);

        $signer->setTabs(new Tabs([
            'sign_here_tabs' => [$signHere],
            'checkbox_tabs' => [$checkbox],
            'text_tabs' => [$text, $text2, $text3, $text4],
            'date_signed_tabs' => [$dateSignedTabs]
        ]));

        return new EnvelopeTemplate(
            [
                'description' => "Example template created via the eSignature API",
                'name' => $template_name,
                'shared' => "false",
                'documents' => [$document],
                'email_subject' => "Please sign this document",
                'recipients' => new Recipients([
                    'signers' => [$signer]
                ]),
                'status' => "created"
            ]
        );
    }
}
