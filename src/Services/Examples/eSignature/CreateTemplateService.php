<?php

namespace Example\Services\Examples\eSignature;

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

class CreateTemplateService
{
    /**
     * Do the work of the example
     * 1. Check to see if the template already exists
     * 2. If not, create the template
     *
     * @param  $args array
     * @param $template_name
     * @param $demoDocsPath
     * @param $clientService
     * @return array
     */
    # ***DS.snippet.0.start
    public static function createTemplate(array $args, $template_name, $demoDocsPath, $clientService): array
    {
        # 1. call Templates::list API method
        # Exceptions will be caught by the calling function
        $templates_api = $clientService->getTemplatesApi();
        $options = new ListTemplatesOptions();
        $options->setSearchText($template_name);
        $templatesListResponse = $templates_api->listTemplates($args['account_id'], $options);

        if ($templatesListResponse['result_set_size'] > 0) {
            $template_id = $templatesListResponse['envelope_templates'][0]['template_id'];
            $results_template_name = $templatesListResponse['envelope_templates'][0]['name'];
        } else {
            # Template not found -- so create it
            # Step 2 create the template
            $template_req_object = CreateTemplateService::makeTemplateRequest($template_name, $demoDocsPath);
            #ds-snippet-start:eSign8Step3
            $templatesListResponse = $templates_api->createTemplate($args['account_id'], $template_req_object);
            #ds-snippet-end:eSign8Step3
            $template_id = $templatesListResponse['template_id'];
            $results_template_name = $templatesListResponse['name'];
        }

        return [
            'template_id' => $template_id,
            'template_name' => $results_template_name,
            'created_new_template' => !($templatesListResponse['result_set_size'] > 0)
        ];
    }

    /**
     * Create a template request object
     * @return mixed
     */

    #ds-snippet-start:eSign8Step2
    public static function makeTemplateRequest($template_name, $demoDocsPath): EnvelopeTemplate
    {
        # document 1 is a pdf
        #
        # The template has two recipient roles.
        # recipient 1 - signer
        # recipient 2 - cc
        #
        # Read the pdf from the disk
        # read files 2 and 3 from a local directory
        # The reads could raise an exception if the file is not available!
        $doc_file = 'World_Wide_Corp_fields.pdf';
        $content_bytes = file_get_contents($demoDocsPath . $doc_file);
        $base64_file_content = base64_encode($content_bytes);

        # Create the document model
        $document = new Document([  # create the DocuSign document object
            'document_base64' => $base64_file_content,
            'name' => 'Lorem Ipsum',  # can be different from actual file name
            'file_extension' => 'pdf',  # many different document types are accepted
            'document_id' => '1'  # a label used to reference the doc
        ]);

        # Create the signer recipient model
        # Since these are role definitions, no name/email:
        $signer = new Signer([
            'role_name' => 'signer', 'recipient_id' => "1", 'routing_order' => "1"]);
        # create a cc recipient to receive a copy of the documents
        $cc = new CarbonCopy([
            'role_name' => 'cc', 'recipient_id' => "2", 'routing_order' => "2"]);
        # Create fields using absolute positioning
        # Create a sign_here tab (field on the document)
        $sign_here = new SignHere(['document_id' => '1', 'page_number' => '1',
                                      'x_position' => '191',
                                      'y_position' => '148'
                                  ]);
        $check1 = new Checkbox(
            [
                'document_id' => '1',
                'page_number' => '1',
                'x_position' => '75',
                'y_position' => '417',
                'tab_label' => 'ckAuthorization'
            ]
        );
        $check2 = new Checkbox(
            [
                'document_id' => '1',
                'page_number' => '1',
                'x_position' => '75',
                'y_position' => '447',
                'tab_label' => 'ckAuthentication'
            ]
        );
        $check3 = new Checkbox(
            [
                'document_id' => '1',
                'page_number' => '1',
                'x_position' => '75',
                'y_position' => '478',
                'tab_label' => 'ckAgreement'
            ]
        );
        $check4 = new Checkbox(
            [
                'document_id' => '1',
                'page_number' => '1',
                'x_position' => '75',
                'y_position' => '508',
                'tab_label' => 'ckAcknowledgement'
            ]
        );

        $list1 = CreateTemplateService::createListOfButtonOptions();

        $numerical = new Numerical(
            [
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
            ]
        );
        $radio_group = new RadioGroup(
            [
                'document_id' => "1",
                'group_name' => "radio1",
                'radios' => [
                    new Radio(
                        [
                            'page_number' => "1",
                            'x_position' => "142",
                            'y_position' => "384",
                            'value' => "white",
                            'required' => "false"
                        ]
                    ),
                    new Radio(
                        [
                            'page_number' => "1",
                            'x_position' => "74",
                            'y_position' => "384",
                        'value' => "red",
                        'required' => "false"]
                    ),
                new Radio(['page_number' => "1", 'x_position' => "220", 'y_position' => "384",
                    'value' => "blue", 'required' => "false"])
                ]]
        );
        $text = new Text(['document_id' => "1", 'page_number' => "1",
            'x_position' => "153", 'y_position' => "230",
            'font' => "helvetica", 'font_size' => "size14", 'tab_label' => "text",
            'height' => "23", 'width' => "84", 'required' => "false"]);
        # Add the tabs model to the signer
        # The Tabs object wants arrays of the different field/tab types
        $signer->setTabs(new Tabs(['sign_here_tabs' => [$sign_here],
            'checkbox_tabs' => [$check1, $check2, $check3, $check4], 'list_tabs' => [$list1],
            'numerical_tabs' => [$numerical], 'radio_group_tabs' => [$radio_group], 'text_tabs' => [$text]
        ]));

        # Template object:
        return new EnvelopeTemplate(
            [
                'description' => "Example template created via the API",
                'name' => $template_name,
                'shared' => "false",
                'documents' => [$document],
                'email_subject' => "Please sign this document",
                'recipients' => new Recipients(
                    [
                        'signers' => [$signer],
                        'carbon_copies' => [$cc]
                    ]
                ),
                'status' => "created"
            ]
        );
    }
    #ds-snippet-end:eSign8Step2

    public static function createListOfButtonOptions(): ModelList
    {
        return new ModelList(
            [
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
            ]
        );
    }
}
