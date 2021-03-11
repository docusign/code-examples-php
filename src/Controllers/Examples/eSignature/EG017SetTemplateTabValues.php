<?php
/**
 * Example 017: Set template field (tab) values and an envelope custom field value
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Checkbox;
use DocuSign\eSign\Model\CustomFields;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Number;
use DocuSign\eSign\Model\Radio;
use DocuSign\eSign\Model\RadioGroup;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\TemplateRole;
use DocuSign\eSign\Model\Text;
use DocuSign\eSign\Model\TextCustomField;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG017SetTemplateTabValues extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg017";  # reference (and url) for this example
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
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        $template_id = $this->args['envelope_args']['template_id'];
        $token_ok = $this->routerService->ds_token_ok($minimum_buffer_min);
        if ($token_ok && $template_id) {
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
        } elseif (! $token_ok) {
            $this->clientService->needToReAuth($this->eg);
        } elseif (! $template_id) {
            $this->clientService->envelopeNotCreated(
                basename(__FILE__),
                $this->routerService->getTemplate($this->eg),
                $this->routerService->getTitle($this->eg),
                $this->eg,
                ['template_ok' => false]
            );
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
     * Creates envelope definition using a template.
     * The signer role will include values for the fields
     * Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return mixed -- returns an envelope definition
     */
    private function make_envelope(array $args): EnvelopeDefinition
    {
        # create the envelope definition with the template_id
        $envelope_definition = new EnvelopeDefinition([
            'status' => 'sent', 'template_id' => $args['template_id']
        ]);

        # Set the values for the fields in the template
        $check1 = new Checkbox([
            'tab_label' => 'ckAuthorization', 'selected' => "true"]);
        $check3 = new Checkbox([
            'tab_label' => 'ckAgreement', 'selected' => "true"]);
        $number1 = new Number([
            'tab_label' => "numbersOnly", 'value' => '54321']);
        $radio_group = new RadioGroup(['group_name' => "radio1",
            # You only need to provide the radio entry for the entry you're selecting
            'radios' => [
                new Radio(['value' => "white", 'selected' => "true"]),
            ]]);
        $text = new Text([
            'tab_label' => "text", 'value' => "Jabberwocky!"]);

        # We can also add a new field to the ones already in the template:
        $text_extra = new Text([
            'document_id' => "1", 'page_number' => "1",
            'x_position' => "280", 'y_position' => "172",
            'font' => "helvetica", 'font_size' => "size14", 'tab_label' => "added text field",
            'height' => "23", 'width' => "84", 'required' => "false",
            'bold' => 'true', 'value' => $args['signer_name'],
            'locked' => 'false', 'tab_id' => 'name']);

        # Pull together the existing and new tabs in a Tabs object:
        $tabs = new Tabs([
            'checkbox_tabs' => [$check1, $check3], 'number_tabs' => [$number1],
            'radio_group_tabs' => [$radio_group], 'text_tabs' => [$text, $text_extra]]);

        # Create the template role elements to connect the signer and cc recipients
        # to the template
        $signer = new TemplateRole([
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'role_name' => 'signer',
            'client_user_id' => $args['signer_client_id'], # change the signer to be embedded
            'tabs' => $tabs # Set tab values
        ]);
        # Create a cc template role.
        $cc = new TemplateRole([
            'email' => $args['cc_email'], 'name' => $args['cc_name'],
            'role_name' => 'cc'
        ]);

        # Add the TemplateRole objects to the envelope object
        $envelope_definition->setTemplateRoles([$signer, $cc]);

        # Create an envelope custom field to save the our application's
        # data about the envelope
        $custom_field = new TextCustomField([
            'name' => 'app metadata item',
            'required' => 'false',
            'show' => 'true', # Yes, include in the CoC
            'value' => '1234567']);
        $custom_fields = new CustomFields([
            'text_custom_fields' => [$custom_field]]);
        $envelope_definition->setCustomFields($custom_fields);

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
        $template_id = isset($_SESSION['template_id']) ? $_SESSION['template_id'] : false;
        $signer_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name']);
        $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email']);
        $cc_name      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name'     ]);
        $cc_email     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email'    ]);
        $envelope_args = [
            'signer_email' => $signer_email,
            'signer_name' => $signer_name,
            'signer_client_id' => $this->signer_client_id,
            'cc_email' => $cc_email,
            'cc_name' => $cc_name,
            'ds_return_url' => $GLOBALS['app_url'] . 'index.php?page=ds_return',
            'template_id' => $template_id
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