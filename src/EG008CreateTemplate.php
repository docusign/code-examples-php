<?php
/**
 * Example 008: create a template if it doesn't already exist
 */

namespace Example;
class EG008CreateTemplate
{
    private $eg = "eg008";  # reference (and url) for this example
    private $template_name = 'Example Signer and CC template';

    public function controller()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController();
        };
        if ($method == 'POST') {
            check_csrf();
            $this->createController();
        };
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     */
    private function createController()
    {
        $minimum_buffer_min = 3;
        if (ds_token_ok($minimum_buffer_min)) {
            # 2. Call the worker method
            $args = [
                'account_id' => $_SESSION['ds_account_id'],
                'base_path' => $_SESSION['ds_base_path'],
                'ds_access_token' => $_SESSION['ds_access_token'],
            ];

            try {
                $results = $this->worker($args);

            } catch (\DocuSign\eSign\ApiException $e) {
                $error_code = $e->getResponseBody()->errorCode;
                $error_message = $e->getResponseBody()->message;
                $GLOBALS['twig']->display('error.html', [
                        'error_code' => $error_code,
                        'error_message' => $error_message]
                );
                exit();
            }
            if ($results) {
                $_SESSION["template_id"] = $results["template_id"]; # Save for use by other examples
                $msg = $results['created_new_template'] ? "The template has been created!" :
                            "Done. The template already existed in your account.";

                $GLOBALS['twig']->display('example_done.html', [
                    'title' => "Template results",
                    'h1' => "Template results",
                    'message' => "{$msg}<br/>Template name: {$results['template_name']}, 
                        ID {$results['template_id']}."
                ]);
                exit;
            }
        } else {
            flash('Sorry, you need to re-authenticate.');
            # We could store the parameters of the requested operation
            # so it could be restarted automatically.
            # But since it should be rare to have a token issue here,
            # we'll make the user re-enter the form data after
            # authentication.
            $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $this->eg;
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
        }
    }


    /**
     * Do the work of the example
     * 1. Check to see if the template already exists
     * 2. If not, create the template
     * @param $args
     * @return array
     * @throws \DocuSign\eSign\ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker($args)
    {
        # 1. call Templates::list API method
        # Exceptions will be caught by the calling function
        $config = new \DocuSign\eSign\Configuration();
        $config->setHost($args['base_path']);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $args['ds_access_token']);
        $api_client = new \DocuSign\eSign\ApiClient($config);
        $templates_api = new \DocuSign\eSign\Api\TemplatesApi($api_client);
        $options = new \DocuSign\eSign\Api\TemplatesApi\ListTemplatesOptions();
        $options->setSearchText($this->template_name);
        $results = $templates_api->listTemplates($args['account_id'], $options);
        $created_new_template = false;
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
            $created_new_template = true;
        }
        return [
            'template_id' => $template_id,
            'template_name' => $results_template_name,
            'created_new_template' => $created_new_template];
    }


    /**
     * Create a template request object
     * @return mixed
     */
    private function make_template_req()
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
        $demo_docs_path = __DIR__ . '/../public/demo_documents/';
        $doc_file = 'World_Wide_Corp_fields.pdf';
        $content_bytes = file_get_contents($demo_docs_path . $doc_file);
        $base64_file_content = base64_encode($content_bytes);

        # Create the document model
        $document = new \DocuSign\eSign\Model\Document([  # create the DocuSign document object
            'document_base64' => $base64_file_content,
            'name' => 'Lorem Ipsum',  # can be different from actual file name
            'file_extension' => 'pdf',  # many different document types are accepted
            'document_id' => '1'  # a label used to reference the doc
        ]);

        # Create the signer recipient model
        # Since these are role definitions, no name/email:
        $signer = new \DocuSign\eSign\Model\Signer([
            'role_name' => 'signer', 'recipient_id' => "1", 'routing_order' => "1"]);
        # create a cc recipient to receive a copy of the documents
        $cc = new \DocuSign\eSign\Model\CarbonCopy([
            'role_name' => 'cc', 'recipient_id' => "2", 'routing_order' => "2"]);
        # Create fields using absolute positioning
        # Create a sign_here tab (field on the document)
        $sign_here = new \DocuSign\eSign\Model\SignHere(['document_id' => '1', 'page_number' => '1',
            'x_position' => '191', 'y_position' => '148']);
        $check1 = new \DocuSign\eSign\Model\Checkbox(['document_id' => '1', 'page_number' => '1',
            'x_position' => '75', 'y_position' => '417', 'tab_label' => 'ckAuthorization']);
        $check2 = new \DocuSign\eSign\Model\Checkbox(['document_id' => '1', 'page_number' => '1',
            'x_position' => '75', 'y_position' => '447', 'tab_label' => 'ckAuthentication']);
        $check3 = new \DocuSign\eSign\Model\Checkbox(['document_id' => '1', 'page_number' => '1',
            'x_position' => '75', 'y_position' => '478', 'tab_label' => 'ckAgreement']);
        $check4 = new \DocuSign\eSign\Model\Checkbox(['document_id' => '1', 'page_number' => '1',
            'x_position' => '75', 'y_position' => '508', 'tab_label' => 'ckAcknowledgement']);

        # Unfortunately there is currently a bug in the PHP SDK such that creating
        # a list tab is not yet supported via the SDK. You can use the API directly if need be, see eg010 file.
        # See https://github.com/docusign/docusign-php-client/issues/58
        # Once the bug is fixed something similar to the following will be used:
        /* $list1 = new \DocuSign\eSign\Model\ListModel (['document_id' => "1", 'page_number' => "1",
                'x_position' => "142", 'y_position' => "291", 'font' => "helvetica", 'font_size' => "size14",
                'tab_label' => "list", 'required' => "false",
                'list_items' => [
                    new \DocuSign\eSign\Model\ListItem(['text' => "Red"   , 'value' => "red"   ]),
                    new \DocuSign\eSign\Model\ListItem(['text' => "Orange", 'value' => "orange"]),
                    new \DocuSign\eSign\Model\ListItem(['text' => "Yellow", 'value' => "yellow"]),
                    new \DocuSign\eSign\Model\ListItem(['text' => "Green" , 'value' => "green" ]),
                    new \DocuSign\eSign\Model\ListItem(['text' => "Blue"  , 'value' => "blue"  ]),
                    new \DocuSign\eSign\Model\ListItem(['text' => "Indigo", 'value' => "indigo"]),
                    new \DocuSign\eSign\Model\ListItem(['text' => "Violet", 'value' => "violet"]),
                ])*/
        $number1 = new \DocuSign\eSign\Model\Number(['document_id' => "1", 'page_number' => "1",
            'x_position' => "163", 'y_position' => "260",
            'font' => "helvetica", 'font_size' => "size14", 'tab_label' => "numbersOnly",
            'width' => "84", 'required' => "false"]);
        $radio_group = new \DocuSign\eSign\Model\RadioGroup(['document_id' => "1", 'group_name' => "radio1",
            'radios' => [
                new \DocuSign\eSign\Model\Radio(['page_number' => "1", 'x_position' => "142", 'y_position' => "384",
                    'value' => "white", 'required' => "false"]),
                new \DocuSign\eSign\Model\Radio(['page_number' => "1", 'x_position' => "74", 'y_position' => "384",
                    'value' => "red", 'required' => "false"]),
                new \DocuSign\eSign\Model\Radio(['page_number' => "1", 'x_position' => "220", 'y_position' => "384",
                    'value' => "blue", 'required' => "false"])
            ]]);
        $text = new \DocuSign\eSign\Model\Text(['document_id' => "1", 'page_number' => "1",
            'x_position' => "153", 'y_position' => "230",
            'font' => "helvetica", 'font_size' => "size14", 'tab_label' => "text",
            'height' => "23", 'width' => "84", 'required' => "false"]);
        # Add the tabs model to the signer
        # The Tabs object wants arrays of the different field/tab types
        $signer->setTabs(new \DocuSign\eSign\Model\Tabs(['sign_here_tabs' => [$sign_here],
            'checkbox_tabs' => [$check1, $check2, $check3, $check4], # 'list_tabs' => [$list1],
            'number_tabs' => [$number1], 'radio_group_tabs' => [$radio_group], 'text_tabs' => [$text]
        ]));
        # Create top two objects
        $envelope_template_definition = new \DocuSign\eSign\Model\EnvelopeTemplateDefinition([
            'description' => "Example template created via the API",
            'name' => $this->template_name,
            'shared' => "false"
        ]);
        # Top object:
        $template_request = new \DocuSign\eSign\Model\EnvelopeTemplate([
            'documents' => [$document], 'email_subject' => "Please sign this document",
            'envelope_template_definition' => $envelope_template_definition,
            'recipients' => new \DocuSign\eSign\Model\Recipients([
                'signers' => [$signer], 'carbon_copies' => [$cc]]),
            'status' => "created"
        ]);

        return $template_request;
    }
    # ***DS.snippet.0.end


    /**
     * Show the example's form page
     */
    private function getController()
    {
        if (ds_token_ok()) {
            $basename = basename(__FILE__);
            $GLOBALS['twig']->display('eg008_create_template.html', [
                'title' => "Create a template",
                'source_file' => $basename,
                'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
                'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $this->eg,
                'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
            ]);
        } else {
            # Save the current operation so it will be resumed after authentication
            $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $this->eg;
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
        }
    }
}


