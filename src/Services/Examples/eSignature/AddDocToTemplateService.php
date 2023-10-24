<?php

namespace Example\Services\Examples\eSignature;

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

class AddDocToTemplateService
{
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
     * @param $clientService
     * @return array ['redirect_url']
     */
    # ***DS.snippet.0.start
    public static function addDocToTemplate(array $args, $clientService): array
    {
        $envelope_args = $args["envelope_args"];
        # 1. Create the envelope request object
        $envelope_definition = AddDocToTemplateService::make_envelope($envelope_args, $clientService);

        return AddDocToTemplateService::sendCompositeTemplate($clientService, $args, $envelope_definition);
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
     * @param $clientService
     * @return mixed -- returns an envelope definition
     */
    #ds-snippet-start:eSign13Step2
    public static function make_envelope(array $args, $clientService): EnvelopeDefinition
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
            'role_name' => "cc", 'recipient_id' => "2"
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
            'anchor_x_offset' => '20']);
        $signer1_tabs = new Tabs([
            'sign_here_tabs' => [$sign_here1]]);

        # 4. Create Signer definition for the added document
        $signer1AddedDoc = new Signer([
            'email' => $args['signer_email'],
            'name' => $args['signer_name'],
                                          'role_name' => "signer",
                                          'recipient_id' => "1",
                                          'client_user_id' => $args['signer_client_id'],
                                          'tabs' => $signer1_tabs
                                      ]);

        # 5. The Recipients object for the added document.
        #    Using cc1 definition from above.
        $recipients_added_doc = new Recipients(
            [
                'carbon_copies' => [$cc1],
                'signers' => [$signer1AddedDoc]
            ]
        );

        # 6. Create the HTML document that will be added to the envelope
        $doc1_b64 = base64_encode($clientService->createDocumentForEnvelope($args));
        $doc1 = new Document(
            [
                'document_base64' => $doc1_b64,
                'name' => 'Appendix 1--Sales order', # can be different from
                # actual file name
                'file_extension' => 'html',
                'document_id' => '1'
            ]
        );

        # 6. create a composite template for the added document
        $comp_template2 = new CompositeTemplate(
            [
                'composite_template_id' => "2",
                # Add the recipients via an inlineTemplate
                'inline_templates' => [
                    new InlineTemplate(
                        [
                            'sequence' => "1",
                            'recipients' => $recipients_added_doc
                        ]
                    )
                ],
                'document' => $doc1
            ]
        );

        # 7. create the envelope definition with the composited templates
        return new EnvelopeDefinition(
            [
                'status' => "sent",
                'composite_templates' => [$comp_template1, $comp_template2]
            ]
        );
    }
   #ds-snippet-end:eSign13Step2

   public static function sendCompositeTemplate($clientService, $args, $envelope_definition): array
   {
       #ds-snippet-start:eSign13Step3
       # Call Envelopes::create API method
       # Exceptions will be caught by the calling function
       $envelope_api = $clientService->getEnvelopeApi();
       $envelopeResponse = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
       $envelope_id = $envelopeResponse->getEnvelopeId();
       #ds-snippet-end:eSign13Step3

       #ds-snippet-start:eSign13Step4
       # Create the Recipient View request object
       $authentication_method = 'None'; # How is this application authenticating
       # the signer? See the `authentication_method' definition
       # https://developers.docusign.com/esign-rest-api/reference/Envelopes/EnvelopeViews/createRecipient
       $recipient_view_request = $clientService->getRecipientViewRequest(
           $authentication_method,
           $args["envelope_args"]
       );

       # Obtain the recipient_view_url for the embedded signing
       # Exceptions will be caught by the calling function
       $recipientView = $clientService->getRecipientView($args['account_id'], $envelope_id, $recipient_view_request);
       
       return ['envelope_id' => $envelope_id, 'redirect_url' => $recipientView['url']];
       #ds-snippet-end:eSign13Step4
   }
}
