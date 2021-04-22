<?php
/**
 * Example 013: Use embedded signing from template with added document
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\CompositeTemplate;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\InlineTemplate;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\ServerTemplate;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG013AddDocToTemplate extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg013";  # reference (and url) for this example

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
                # Redirect the user to the signing
                # Don't use an iFrame!
                # State can be stored/recovered using the framework's session or a
                # query parameter on the returnUrl
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
     * Create the envelope and the embedded signing
     * 1. Create the envelope request object using composite template to
     *    add the new document
     * 2. Send the envelope
     * 3. Make the recipient view request object
     * 4. Get the recipient view (embedded signing) url
     *
     * @param  $args array
     * @return array ['redirect_url']
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker(array $args): array
    {
        $envelope_args = $args["envelope_args"];
        # 1. Create the envelope request object
        $envelope_definition = $this->make_envelope($envelope_args);

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

        # 4. Obtain the recipient_view_url for the signing
        # Exceptions will be caught by the calling function
        $results = $this->clientService->getRecipientView($args['account_id'], $envelope_id, $recipient_view_request);

        return ['envelope_id' => $envelope_id, 'redirect_url' => $results['url']];
    }

    /**
     * Creates the envelope definition
     * Uses compositing templates to add a new document to the existing template
     * The envelope request object uses Composite Template to
     * include in the envelope:
     * 1. A template stored on the DocuSign service
     * 2. An additional document which is a custom HTML source document
     * Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return mixed -- returns an envelope definition
     */
    private function make_envelope(array $args): EnvelopeDefinition
    {
        # 1. Create Recipients for server template. Note that Recipients object
        #    is used, not TemplateRole
        #
        # Create a signer recipient for the signer role of the server template
        $signer1 = new Signer([
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'role_name' => "signer", 'recipient_id' => "1",
            # Adding clientUserId transforms the template recipient
            # into an embedded recipient:
            'client_user_id' => $args['signer_client_id']
        ]);
        # Create the cc recipient
        $cc1 = new CarbonCopy([
            'email' => $args['cc_email'], 'name' => $args['cc_name'],
            'role_name' => "cc", 'recipient_id' =>"2"
        ]);
        # Recipients object:
        $recipients_server_template = new Recipients([
            'carbon_copies' => [$cc1], 'signers' => [$signer1]]);

        # 2. create a composite template for the Server template + roles
        $comp_template1 = new CompositeTemplate([
            'composite_template_id' => "1",
            'server_templates' => [
                new ServerTemplate([
                    'sequence' => "1", 'template_id' => $args['template_id']])
            ],
            # Add the roles via an inlineTemplate
            'inline_templates' => [
                new InlineTemplate([
                    'sequence' => "2",
                    'recipients' => $recipients_server_template])
            ]
        ]);

        # Next, create the second composite template that will
        # include the new document.
        #
        # 3. Create the signer recipient for the added document
        #    starting with the tab definition:
        $sign_here1 = new SignHere([
            'anchor_string' => '**signature_1**',
            'anchor_y_offset' => '10', 'anchor_units' => 'pixels',
            'anchor_x_offset' =>'20']);
        $signer1_tabs = new Tabs([
            'sign_here_tabs' => [$sign_here1]]);

        # 4. Create Signer definition for the added document
        $signer1AddedDoc = new Signer([
            'email' => $args['signer_email'],
            'name' => $args['signer_name'],
            'role_name' => "signer", 'recipient_id' => "1",
            'client_user_id' => $args['signer_client_id'],
            'tabs' => $signer1_tabs]);

        # 5. The Recipients object for the added document.
        #    Using cc1 definition from above.
        $recipients_added_doc = new Recipients([
            'carbon_copies' => [$cc1], 'signers' => [$signer1AddedDoc]]);

        # 6. Create the HTML document that will be added to the envelope
        $doc1_b64 = base64_encode($this->clientService->createDocumentForEnvelope($args));
        $doc1 = new Document([
            'document_base64' => $doc1_b64,
            'name' => 'Appendix 1--Sales order', # can be different from
                                                 # actual file name
            'file_extension' => 'html', 'document_id' =>'1']);

        # 6. create a composite template for the added document
        $comp_template2 = new CompositeTemplate([
            'composite_template_id' => "2",
            # Add the recipients via an inlineTemplate
            'inline_templates' => [
                new InlineTemplate([
                    'sequence' => "1", 'recipients' => $recipients_added_doc])
            ],
            'document' => $doc1]);

        # 7. create the envelope definition with the composited templates
        $envelope_definition = new EnvelopeDefinition([
            'status' => "sent",
            'composite_templates' => [$comp_template1, $comp_template2]
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
        $template_id = isset($_SESSION['template_id']) ? $_SESSION['template_id'] : false;
        $signer_name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name' ]);
        $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email']);
        $cc_name      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name'     ]);
        $cc_email     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email'    ]);
        $item         = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['item'        ]);
        $quantity     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['quantity'    ]);
        $quantity = intval($quantity);
        $envelope_args = [
            'signer_email' => $signer_email,
            'signer_name' => $signer_name,
            'signer_client_id' => 1000,
            'cc_email' => $cc_email,
            'cc_name' => $cc_name,
            'item' => $item,
            'quantity' => $quantity,
            'template_id' => $template_id,
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
