<?php

namespace DocuSign\Services\Examples\WebForms;

use DocuSign\eSign\Api\TemplatesApi;
use DocuSign\eSign\Api\TemplatesApi\ListTemplatesOptions;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Checkbox;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeTemplate;
use DocuSign\eSign\Model\ModelList;
use DocuSign\eSign\Model\Numerical;
use DocuSign\eSign\Model\Radio;
use DocuSign\eSign\Model\RadioGroup;
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
    ): WebFormSummaryList
    {
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
    ): WebFormInstance
    {
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

        return $formInstanceApi->createInstance($accountId, $formId, $options);
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
    ): array
    {
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
    ): array
    {
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
            $templateObject = CreateTemplateService::makeTemplateRequest(
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
    ): EnvelopeTemplate
    {
        $docName = 'World_Wide_Corp_fields.pdf';
        $contentBytes = file_get_contents($demoDocsPath . $docName);
        $base64FileContent = base64_encode($contentBytes);

        $document = new Document([
            'document_base64' => $base64FileContent,
            'name' => 'Lorem Ipsum',
            'file_extension' => 'pdf',
            'document_id' => '1'
        ]);

        $signer = new Signer([
            'role_name' => 'signer',
            'recipient_id' => "1",
            'routing_order' => "1"
        ]);
        $cc = new CarbonCopy([
            'role_name' => 'cc',
            'recipient_id' => "2",
            'routing_order' => "2"
        ]);
        $signHere = new SignHere([
            'document_id' => '1',
            'page_number' => '1',
            'x_position' => '191',
            'y_position' => '148'
        ]);
        $checkbox = new Checkbox([
            'document_id' => '1',
            'page_number' => '1',
            'x_position' => '75',
            'y_position' => '417',
            'tab_label' => 'ckAuthorization'
        ]);
        $checkboxTwo = new Checkbox([
            'document_id' => '1',
            'page_number' => '1',
            'x_position' => '75',
            'y_position' => '447',
            'tab_label' => 'ckAuthentication'
        ]);
        $checkboxThree = new Checkbox([
            'document_id' => '1',
            'page_number' => '1',
            'x_position' => '75',
            'y_position' => '478',
            'tab_label' => 'ckAgreement'
        ]);
        $checkboxFour = new Checkbox([
            'document_id' => '1',
            'page_number' => '1',
            'x_position' => '75',
            'y_position' => '508',
            'tab_label' => 'ckAcknowledgement'
        ]);

        $list = CreateAndEmbedFormService::createListOfButtonOptions();

        $numerical = new Numerical([
            'document_id' => "1",
            'validation_type' => "Currency",
            'page_number' => "1",
            'x_position' => "163",
            'y_position' => "260",
            'font' => "helvetica",
            'font_size' => "size14",
            'tab_label' => "numericalCurrency",
            'width' => "84",
            'required' => "false"
        ]);

        $radioGroup = new RadioGroup(
            [
                'document_id' => "1",
                'group_name' => "radio1",
                'radios' => [
                    new Radio([
                        'page_number' => "1",
                        'x_position' => "142",
                        'y_position' => "384",
                        'value' => "white",
                        'required' => "false"
                    ]),
                    new Radio([
                        'page_number' => "1",
                        'x_position' => "74",
                        'y_position' => "384",
                        'value' => "red",
                        'required' => "false"
                    ]),
                    new Radio([
                        'page_number' => "1",
                        'x_position' => "220",
                        'y_position' => "384",
                        'value' => "blue",
                        'required' => "false"
                    ])
                ]]
        );
        $text = new Text([
            'document_id' => "1",
            'page_number' => "1",
            'x_position' => "153",
            'y_position' => "230",
            'font' => "helvetica",
            'font_size' => "size14",
            'tab_label' => "text",
            'height' => "23",
            'width' => "84",
            'required' => "false"
        ]);

        $signer->setTabs(new Tabs([
            'sign_here_tabs' => [$signHere],
            'checkbox_tabs' => [$checkbox, $checkboxTwo, $checkboxThree, $checkboxFour],
            'list_tabs' => [$list],
            'numerical_tabs' => [$numerical],
            'radio_group_tabs' => [$radioGroup],
            'text_tabs' => [$text]
        ]));

        return new EnvelopeTemplate(
            [
                'description' => "Example template created via the API",
                'name' => $template_name,
                'shared' => "false",
                'documents' => [$document],
                'email_subject' => "Please sign this document",
                'recipients' => new Recipients([
                    'signers' => [$signer],
                    'carbon_copies' => [$cc]
                ]),
                'status' => "created"
            ]
        );
    }

    public static function createListOfButtonOptions(): ModelList
    {
        return new ModelList([
            'font' => "helvetica",
            'font_size' => "size11",
            'anchor_string' => '/l1q/',
            'anchor_y_offset' => '-10',
            'anchor_units' => 'pixels',
            'anchor_x_offset' => '0',
            'list_items' => [
                ['text' => "Red", 'value' => "red"],
                ['text' => "Orange", 'value' => "orange"],
                ['text' => "Yellow", 'value' => "yellow"],
                ['text' => "Green", 'value' => "green"],
                ['text' => "Blue", 'value' => "blue"],
                ['text' => "Indigo", 'value' => "indigo"]
            ],
            'required' => "true",
            'tab_label' => "l1q"
        ]);
    }
}
