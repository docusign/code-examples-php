<?php
/**
 * Example 016: Set optional and locked field values and an envelope custom field value
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\CustomFields;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\Text;
use DocuSign\eSign\Model\TextCustomField;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG016SetTabValues extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg016";  # reference (and url) for this example
    private $signer_client_id = 1000; # Used to indicate that the signer will use embedded
                                      # signing. Represents the signer's userId within your application.

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
     * 3. Redirect the user to the signing
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = $this->worker($this->args);

            if ($results) {
                $_SESSION["envelope_id"] = $results["envelope_id"]; # Save for use by other examples
                                                                    # which need an envelope_id

                # Redirect the user to the embedded signing
                # Don't use an iFrame!
                # State can be stored/recovered using the framework's session or a
                # query parameter on the returnUrl (see the makerecipient_view_request method)
                header('Location: ' . $results["redirect_url"]);
                exit;
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }


    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     * 3. Create the Recipient View request object
     * 4. Obtain the recipient_view_url for the embedded signing
     *
     * @param  $args array
     * @return array ['redirect_url']
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker(array $args): array
    {
        # 1. Create the envelope request object
        $envelope_definition = $this->make_envelope($args["envelope_args"]);

        # 2. call Envelopes::create API method
        # Exceptions will be caught by the calling function
        $envelope_api = $this->clientService->getEnvelopeApi();
        $results = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        $envelope_id= $results->getEnvelopeId();

        # 3. Create the Recipient View request object
        $authentication_method = 'None'; # How is this application authenticating
        # the signer? See the `authentication_method' definition
        # https://developers.docusign.com/esign-rest-api/reference/Envelopes/EnvelopeViews/createRecipient
        $recipient_view_request = $this->clientService->getRecipientViewRequest(
            $authentication_method,
            $args["envelope_args"]
        );

        # 4. Obtain the recipient_view_url for the embedded signing
        # Exceptions will be caught by the calling function
        $results = $this->clientService->getRecipientView($args['account_id'], $envelope_id, $recipient_view_request);

        return ['envelope_id' => $envelope_id, 'redirect_url' => $results['url']];
    }

    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return mixed -- returns an envelope definition
     */
    private function make_envelope(array $args): EnvelopeDefinition
    {
        # document 1 (pdf) has tags
        # /sn1/ - signature field
        # /salary/ - yearly salary
        # /legal/ - legal name
        # /familiar/ - person's familiar name
        #
        # The envelope has one recipient.
        # recipient 1 - signer
        #
        # The salary is set both as a readable number in the
        # /salary/ text field, and as a pure number in a
        # custom field ('salary') in the envelope.

        # Salary that will be used.
        $salary = 123000;

        # Read the file
        $doc_name = 'World_Wide_Corp_salary.docx';
        $content_bytes = file_get_contents(self::DEMO_DOCS_PATH . $doc_name);
        $base64_file_content = base64_encode($content_bytes);

        # Create the document model
        $document = new Document([ # create the DocuSign document object
            'document_base64' => $base64_file_content,
            'name' => 'Salary action', # can be different from actual file name
            'file_extension' => 'docx', # many different document types are accepted
            'document_id' => 1 # a label used to reference the doc
        ]);

        # Create the signer recipient model
        $signer = new Signer([ # The signer
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'recipient_id' => "1", 'routing_order' => "1",
            # Setting the client_user_id marks the signer as embedded
            'client_user_id' => $args['signer_client_id']
        ]);

        # Create a sign_here tab (field on the document)
        $sign_here = new SignHere([ # DocuSign SignHere field/tab
            'anchor_string' => '/sn1/', 'anchor_units' => 'pixels',
            'anchor_y_offset' => '10', 'anchor_x_offset' => '20'
        ]);

        # Create the legal and familiar text fields.
        # Recipients can update these values if they wish to.
        $text_legal = new Text([
            'anchor_string' => '/legal/', 'anchor_units' => 'pixels',
            'anchor_y_offset' => '-9', 'anchor_x_offset' => '5',
            'font' => "helvetica", 'font_size' => "size11",
            'bold' => 'true', 'value' => $args['signer_name'],
            'locked' => 'false', 'tab_id' => 'legal_name',
            'tab_label' => 'Legal name']);
        $text_familiar = new Text([
            'anchor_string' => '/familiar/', 'anchor_units' => 'pixels',
            'anchor_y_offset' => '-9', 'anchor_x_offset' => '5',
            'font' => "helvetica", 'font_size' => "size11",
            'bold' => 'true', 'value' => $args['signer_name'],
            'locked' => 'false', 'tab_id' => 'familiar_name',
            'tab_label' => 'Familiar name']);

        # Create the salary field. It should be human readable, so
        # add a comma before the thousands number, a currency indicator, etc.
        $salary_readable = '$' . number_format($salary);
        $text_salary = new Text([
            'anchor_string' => '/salary/', 'anchor_units' => 'pixels',
            'anchor_y_offset' => '-9', 'anchor_x_offset' => '5',
            'font' => "helvetica", 'font_size' => "size11",
            'bold' => 'true', 'value' => $salary_readable,
            'locked' => 'true', # mark the field as readonly
            'tab_id' => 'salary', 'tab_label' => 'Salary'
        ]);

        # Add the tabs model (including the sign_here tab) to the signer
        # The Tabs object wants arrays of the different field/tab types
        $signer->settabs(new Tabs(
            ['sign_here_tabs' => [$sign_here],
            'text_tabs' => [$text_legal, $text_familiar, $text_salary]]));

        # Create an envelope custom field to save the "real" (numeric)
        # version of the salary
        $salary_custom_field = new TextCustomField([
            'name' => 'salary',
            'required' => 'false',
            'show' => 'true', # Yes, include in the CoC
            'value' => $salary]);
        $custom_fields = new CustomFields([
            'text_custom_fields' => [$salary_custom_field]]);

        # Next, create the top level envelope definition and populate it.
        $envelope_definition = new EnvelopeDefinition([
            'email_subject' => "Please sign this document sent from the PHP SDK",
            'documents' => [$document],
            # The Recipients object wants arrays for each recipient type
            'recipients' => new Recipients(['signers' => [$signer]]),
            'status' => "sent", # requests that the envelope be created and sent.
            'custom_fields' => $custom_fields
        ]);

        return $envelope_definition;
    }
    # ***DS.snippet.0.end

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $signer_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name']);
        $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email']);
        $envelope_args = [
            'signer_email' => $signer_email,
            'signer_name' => $signer_name,
            'signer_client_id' => $this->signer_client_id,
            'ds_return_url' => $GLOBALS['app_url'] . 'index.php?page=ds_return'
        ];
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];

        return $args;
    }
}