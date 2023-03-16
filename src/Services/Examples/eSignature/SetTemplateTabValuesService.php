<?php

namespace Example\Services\Examples\eSignature;

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

class SetTemplateTabValuesService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     * 3. Create the Recipient View request object
     * 4. Obtain the recipient_view_url for the embedded signing
     *
     * @param  $args array
     * @param $clientService
     * @return array ['redirect_url']
     */
    # ***DS.snippet.0.start
    public static function setTemplateTabValues(array $args, $clientService): array
    {
        # 1. Create the envelope request object
        $envelope_definition = SetTemplateTabValuesService::make_envelope($args["envelope_args"]);

        return SetTemplateTabValuesService::sendEnvelopeFromCreatedTemplate($clientService, $args, $envelope_definition);
    }

    public static function sendEnvelopeFromCreatedTemplate($clientService, $args, $envelope_definition): array
    {
        # Step 4 start
        # Call Envelopes::create API method
        # Exceptions will be caught by the calling function
        $envelope_api = $clientService->getEnvelopeApi();
        $envelopeResponse = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        $envelope_id = $envelopeResponse->getEnvelopeId();
        # Step 4 end

        # Step 5 start
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
        # Step 5 end
        return ['envelope_id' => $envelope_id, 'redirect_url' => $recipientView['url']];
    }

    /**
     * Creates envelope definition using a template.
     * The signer role will include values for the fields
     * Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return mixed -- returns an envelope definition
     */
    public static function make_envelope(array $args): EnvelopeDefinition
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
}
