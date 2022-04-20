<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;

class RecipientAuthenticationService
{
  
    /**
     * This function creates the envelope definition for the
     * order form.
     * Parameters for the envelope: signer_email, signer_name
     *
     * @param  $args array
     * @return mixed -- returns an envelope definition
     */
    public static function make_envelope(array $args,  $demoDocsPath): EnvelopeDefinition
    {
        $envelopeAndSigner = RecipientAuthenticationService::constructAnEnvelope($demoDocsPath);
        $envelope_definition = $envelopeAndSigner['envelopeDefinition'];
        $signer1Tabs = $envelopeAndSigner['signerTabs'];
        $signer1 = new Signer(
            [
                'name' => $args['signer_name'],
                'email' => $args['signer_email'],
                'routing_order' => '1',
                'status' => 'created',
                'delivery_method' => 'Email',
                'recipient_id' => '1', # represents your {RECIPIENT_ID}
                'tabs' => $signer1Tabs,
                'require_id_lookup' => 'true',
            ]
        );

        $recipients = new Recipients();
        $recipients->setSigners(array($signer1));

        $envelope_definition->setRecipients($recipients);

        return $envelope_definition;
    }

    public static function constructAnEnvelope($demoDocsPath): array
    {
        # Construct your envelope JSON body
        $envelope_definition = new EnvelopeDefinition(
            [
                'email_subject' => 'Please Sign',
                'envelope_id_stamping' => 'true',
                'email_blurb' => 'Sample text for email body',
                'status' => 'sent'
            ]
        );


        $content_bytes = file_get_contents($demoDocsPath . $GLOBALS['DS_CONFIG']['doc_pdf']);
        $doc_b64 = base64_encode($content_bytes);

        # Add a document
        $document1 = new Document(
            [
                'document_base64' => $doc_b64,
                'document_id' => '1',
                'file_extension' => 'pdf',
                'name' => 'Lorem'
            ]
        );

        $envelope_definition->setDocuments(array($document1));

        # Create your signature tab
        $signHere1 = new SignHere(
            [
                'name' => 'SignHereTab',
                'x_position' => '200',
                'y_position' => '160',
                'tab_label' => 'SignHereTab',
                'page_number' => '1',
                'document_id' => '1',
                # A 1- to 8-digit integer or 32-character GUID to match recipient IDs on your own systems.
                # This value is referenced in the Tabs element below to assign tabs on a per-recipient basis.
                'recipient_id' => '1'
                # represents your {RECIPIENT_ID}
            ]
        );

        # Tabs are set per recipient/signer
        $signer1Tabs = new Tabs();
        $signer1Tabs->setSignHereTabs(array($signHere1));

        return array(
            'signerTabs' => $signer1Tabs,
            'envelopeDefinition' => $envelope_definition
        );
    }
    # ***DS.snippet.0.end
}
