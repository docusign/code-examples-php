<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Model\CustomFields;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\Text;
use DocuSign\eSign\Model\TextCustomField;
use DocuSign\eSign\Model\Numerical;
use DocuSign\eSign\Model\LocalePolicyTab;

class SetTabValuesService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     * 3. Create the Recipient View request object
     * 4. Obtain the recipient_view_url for the embedded signing
     *
     * @param  $args array
     * @param $demoDocsPath
     * @param $clientService
     * @return array ['redirect_url']
     */
    # ***DS.snippet.0.start
    public static function setTabValues(array $args, $demoDocsPath, $clientService): array
    {
        # 1. Create the envelope request object
        $envelope_definition = SetTabValuesService::make_envelope($args["envelope_args"], $demoDocsPath);

        return SetTabValuesService::sendEnvelope($clientService, $args, $envelope_definition);
    }

    public static function sendEnvelope($clientService, $args, $envelope_definition): array
    {
        # Call Envelopes::create API method
        # Exceptions will be caught by the calling function
        #ds-snippet-start:eSign16Step4
        $envelope_api = $clientService->getEnvelopeApi();
        $envelopeResponse = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        $envelope_id = $envelopeResponse->getEnvelopeId();
        #ds-snippet-end:eSign16Step4

        # Create the Recipient View request object
        #ds-snippet-start:eSign16Step5
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
        #ds-snippet-end:eSign16Step5
        
        return ['envelope_id' => $envelope_id, 'redirect_url' => $recipientView['url']];
    }

    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @param $demoDocsPath
     * @return mixed -- returns an envelope definition
     */

    #ds-snippet-start:eSign16Step3
    public static function make_envelope(array $args, $demoDocsPath): EnvelopeDefinition
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


        # Read the file
        $doc_name = 'World_Wide_Corp_salary.docx';
        $content_bytes = file_get_contents($demoDocsPath . $doc_name);
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
            'anchor_x_offset' => '5',
            'anchor_y_offset' => '-6',
            'font' => "helvetica", 'font_size' => "size11",
            'bold' => 'true', 'value' => $args['signer_name'],
            'locked' => 'false', 'tab_id' => 'legal_name',
            'tab_label' => 'Legal name']);
        $text_familiar = new Text([
            'anchor_string' => '/familiar/', 'anchor_units' => 'pixels',
            'anchor_y_offset' => '-6', 'anchor_x_offset' => '5',
            'font' => "helvetica", 'font_size' => "size11",
            'bold' => 'true', 'value' => $args['signer_name'],
            'locked' => 'false', 'tab_id' => 'familiar_name',
            'tab_label' => 'Familiar name']);

        # Create the salary field. It should be human readable, so
        # add a comma before the thousands number, a currency indicator, etc.
        $salary = 123000;
        
        $locale_policy_tab = new LocalePolicyTab([
            "culture_name" => "en-US",
            "currency_code" => "usd",
            "currency_positive_format" => "csym_1_comma_234_comma_567_period_89",
            "currency_negative_format" => "minus_csym_1_comma_234_comma_567_period_89",
            "use_long_currency_format" => "true"
        ]);
        
        $numerical_salary = new Numerical([
            'page_number' => '1',
            'document_id' => '1',
            'x_position' => '210',
            'y_position' => '233',
            'height' => "20",
            'width' => "70",
            'min_numerical_value' => "0",
            'max_numerical_value' => "1000000",
            'validation_type' => 'Currency',
            'font' => "helvetica", 'font_size' => "size11",
            'bold' => 'true',
            'tab_id' => 'salary', 'tab_label' => 'Salary',
            'numerical_value' => strval($salary),
            'locale_policy' => $locale_policy_tab
        ]);

        # Add the tabs model (including the sign_here tab) to the signer
        # The Tabs object wants arrays of the different field/tab types
        $signer->settabs(new Tabs(
            ['sign_here_tabs' => [$sign_here],
            'text_tabs' => [$text_legal, $text_familiar],
            'numerical_tabs'=>[$numerical_salary]]
        ));

        # Create an envelope custom field to save the "real" (numeric)
        # version of the salary
        $salary_custom_field = new TextCustomField([
            'name' => 'salary',
            'required' => 'false',
            'show' => 'true', # Yes, include in the CoC
            'value' => strval($salary)]);
        $custom_fields = new CustomFields([
            'text_custom_fields' => [$salary_custom_field]]);


        # Next, create the top level envelope definition and populate it.
        return new EnvelopeDefinition([
            'email_subject' => "Please sign this document sent from the PHP SDK",
            'documents' => [$document],
            # The Recipients object wants arrays for each recipient type
            'recipients' => new Recipients(['signers' => [$signer]]),
            'status' => "sent", # requests that the envelope be created and sent.
            'custom_fields' => $custom_fields
        ]);
    }
    #ds-snippet-end:eSign16Step3

}
