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

        return SetTemplateTabValuesService::sendEnvelopeFromCreatedTemplate($clientService, $args, $envelope_definition);
    }

    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @param $demoDocsPath
     * @return mixed -- returns an envelope definition
     */
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

        # Salary that will be used.
        $salary = 123000;

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
            'text_tabs' => [$text_legal,
            $text_familiar,
            $text_salary]]
        ));

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
        return new EnvelopeDefinition([
            'email_subject' => "Please sign this document sent from the PHP SDK",
            'documents' => [$document],
            # The Recipients object wants arrays for each recipient type
            'recipients' => new Recipients(['signers' => [$signer]]),
            'status' => "sent", # requests that the envelope be created and sent.
            'custom_fields' => $custom_fields
        ]);
    }
    # ***DS.snippet.0.end
}
