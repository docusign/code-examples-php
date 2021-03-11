<?php
/**
 * Example 008: create a template if it doesn't already exist
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Api\TemplatesApi\ListTemplatesOptions;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Checkbox;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeTemplate;
use DocuSign\eSign\Model\ModelList;
use DocuSign\eSign\Model\Number;
use DocuSign\eSign\Model\Radio;
use DocuSign\eSign\Model\RadioGroup;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\Text;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG008CreateTemplate extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg008";  # reference (and url) for this example
    private $template_name = 'Example Signer and CC template';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 2. Call the worker method
            $results = $this->worker($this->args);
            if ($results) {
                $_SESSION["template_id"] = $results["template_id"]; # Save for use by other examples
                $msg = $results['created_new_template'] ? "The template has been created!" :
                            "Done. The template already existed in your account.";

                $this->clientService->showDoneTemplate(
                    "Template results",
                    "Template results",
                    "{$msg}<br/>Template name: {$results['template_name']}, 
                                ID {$results['template_id']}."
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }


    /**
     * Do the work of the example
     * 1. Check to see if the template already exists
     * 2. If not, create the template
     *
     * @param  $args array
     * @return array
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker(array $args): array
    {
        # 1. call Templates::list API method
        # Exceptions will be caught by the calling function
        $templates_api = $this->clientService->getTemplatesApi();
        $options = new ListTemplatesOptions();
        $options->setSearchText($this->template_name);
        $results = $templates_api->listTemplates($args['account_id'], $options);

        if ($results['result_set_size'] > 0) {
            $template_id = $results['envelope_templates'][0]['template_id'];
            $results_template_name = $results['envelope_templates'][0]['name'];
        } else {
            # Template not found -- so create it
            # Step 2 create the template
            $template_req_object = $this->make_template_req();
            $results = $templates_api->createTemplate($args['account_id'], $template_req_object);
            $template_id = $results['template_id'];
            $results_template_name = $results['name'];
        }

        return [
            'template_id' => $template_id,
            'template_name' => $results_template_name,
            'created_new_template' => $results['result_set_size'] > 0 ? false : true
        ];
    }


    /**
     * Create a template request object
     * @return mixed
     */
    private function make_template_req(): EnvelopeTemplate
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
        $content_bytes = file_get_contents(self::DEMO_DOCS_PATH . $doc_file);
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
            'x_position' => '191', 'y_position' => '148']);
        $check1 = new Checkbox(['document_id' => '1', 'page_number' => '1',
            'x_position' => '75', 'y_position' => '417', 'tab_label' => 'ckAuthorization']);
        $check2 = new Checkbox(['document_id' => '1', 'page_number' => '1',
            'x_position' => '75', 'y_position' => '447', 'tab_label' => 'ckAuthentication']);
        $check3 = new Checkbox(['document_id' => '1', 'page_number' => '1',
            'x_position' => '75', 'y_position' => '478', 'tab_label' => 'ckAgreement']);
        $check4 = new Checkbox(['document_id' => '1', 'page_number' => '1',
            'x_position' => '75', 'y_position' => '508', 'tab_label' => 'ckAcknowledgement']);

        $list1 = new ModelList([
            'font' => "helvetica",
            'font_size' => "size11",
            'anchor_string' => '/l1q/',
            'anchor_y_offset' => '-10', 'anchor_units' => 'pixels',
            'anchor_x_offset' => '0',
            'list_items' => [
                    ['text' => "Red"   , 'value' => "red"   ], ['text' => "Orange", 'value' => "orange"],
                    ['text' => "Yellow", 'value' => "yellow"], ['text' => "Green" , 'value' => "green" ],
                    ['text' => "Blue"  , 'value' => "blue"  ], ['text' => "Indigo", 'value' => "indigo"]
                ],
            'required' => "true",
            'tab_label' => "l1q"
        ]);

        $number1 = new Number(['document_id' => "1", 'page_number' => "1",
            'x_position' => "163", 'y_position' => "260",
            'font' => "helvetica", 'font_size' => "size14", 'tab_label' => "numbersOnly",
            'width' => "84", 'required' => "false"]);
        $radio_group = new RadioGroup(['document_id' => "1", 'group_name' => "radio1",
            'radios' => [
                new Radio(['page_number' => "1", 'x_position' => "142", 'y_position' => "384",
                    'value' => "white", 'required' => "false"]),
                new Radio(['page_number' => "1", 'x_position' => "74", 'y_position' => "384",
                    'value' => "red", 'required' => "false"]),
                new Radio(['page_number' => "1", 'x_position' => "220", 'y_position' => "384",
                    'value' => "blue", 'required' => "false"])
            ]]);
        $text = new Text(['document_id' => "1", 'page_number' => "1",
            'x_position' => "153", 'y_position' => "230",
            'font' => "helvetica", 'font_size' => "size14", 'tab_label' => "text",
            'height' => "23", 'width' => "84", 'required' => "false"]);
        # Add the tabs model to the signer
        # The Tabs object wants arrays of the different field/tab types
        $signer->setTabs(new Tabs(['sign_here_tabs' => [$sign_here],
            'checkbox_tabs' => [$check1, $check2, $check3, $check4], 'list_tabs' => [$list1],
            'number_tabs' => [$number1], 'radio_group_tabs' => [$radio_group], 'text_tabs' => [$text]
        ]));

        # Template object:
        $template_request = new EnvelopeTemplate([
            'description' => "Example template created via the API",
            'name' => $this->template_name,
            'shared' => "false",
            'documents' => [$document], 'email_subject' => "Please sign this document",
            'recipients' => new Recipients([
            'signers' => [$signer], 'carbon_copies' => [$cc]]),
            'status' => "created"
        ]);

        return $template_request;
    }
    # ***DS.snippet.0.end

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];

        return $args;
    }
}


