<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Model\EnvelopeViewRecipientSettings;
use DocuSign\eSign\Model\EnvelopeViewDocumentSettings;
use DocuSign\eSign\Model\EnvelopeViewTaggerSettings;
use DocuSign\eSign\Model\EnvelopeViewTemplateSettings;
use DocuSign\eSign\Model\EnvelopeViewRequest;
use DocuSign\eSign\Model\EnvelopeViewSettings;

class EmbeddedSendingService
{
    /**
     * Do the work of the example
     * 1. Create the envelope with "created" (draft) status
     * 2. Send the envelope
     * 3. Get the SenderView url
     *
     * @param  $args array
     * @param $clientService
     * @param $demoDocsPath
     * @return array ['redirect_url']
     */
   
    public static function embeddedSending(array $args, $clientService, $demoDocsPath, $docxFile, $pdfFile): array
    {
        #ds-snippet-start:eSign11Step2
        # Create the envelope as a draft using eg002's worker
        # Exceptions will be caught by the calling function
        $args['envelope_args']['status'] = 'created';
        $demoDocsPath = SigningViaEmailService::signingViaEmail($args, $clientService, $demoDocsPath, $docxFile, $pdfFile);
        $envelope_id = $demoDocsPath['envelope_id'];
        #ds-snippet-end:eSign11Step2

        # Create sender view
        #ds-snippet-start:eSign11Step3
        $view_request = EmbeddedSendingService::prepareViewRequest($args['starting_view'], $args['ds_return_url']);
        $envelope_api = $clientService->getEnvelopeApi();
        $senderView = $envelope_api->createSenderView($args['account_id'], $envelope_id, $view_request);

        # Switch to the Recipients / Documents view if requested by the user in the form
        $url = $senderView['url'];
        if ($args['starting_view'] == "recipient") {
            $url = str_replace('send=1', 'send=0', $url);
        }

        return ['envelope_id' => $envelope_id, 'redirect_url' =>  $url];
        #ds-snippet-end:eSign11Step3
    }

    #ds-snippet-start:eSign11Step3
    private static function prepareViewRequest(string $startingView, string $returnUrl): EnvelopeViewRequest
    {
        $viewSettings = new EnvelopeViewSettings([
            'starting_screen' => $startingView,
            'send_button_action' => 'send',
            'show_back_button' => 'false',
            'back_button_action' => 'previousPage',
            'show_header_actions' => 'false',
            'show_discard_action' => 'false',
            'lock_token' => '',
            'recipient_settings' => new EnvelopeViewRecipientSettings([
                'show_edit_recipients' => 'false',
                'show_contacts_list' => 'false'
            ]),
            'document_settings' => new EnvelopeViewDocumentSettings([
                'show_edit_documents' => 'false',
                'show_edit_document_visibility' => 'false',
                'show_edit_pages' => 'false',
            ]),
            'tagger_settings' => new EnvelopeViewTaggerSettings([
                'palette_sections' => 'default',
                'palette_default' => 'custom'
            ]),
            'template_settings' => new EnvelopeViewTemplateSettings([
                'show_matching_templates_prompt' => 'true'
            ])
        ]);

        $viewRequest = new EnvelopeViewRequest([
            'return_url' => $returnUrl,
            'view_access' => 'envelope',
            'settings' => $viewSettings
        ]);

        return $viewRequest;
    }
    #ds-snippet-end:eSign11Step3
}
