<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use Example\Services\SignatureClientService;

class FocusedViewService
{
    public static function worker(array $args, SignatureClientService $client_service, string $demo_path, string $pdf_file): array
    {
        #ds-snippet-start:eSign44Step3
        $envelope_definition = FocusedViewService::makeEnvelope($args['envelope_args'], $demo_path, $pdf_file);
        $envelope_api = $client_service->getEnvelopeApi();

        try {
            $envelope_summary = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        } catch (ApiException $e) {
            $client_service->showErrorTemplate($e);
            exit;
        }
        $envelope_id = $envelope_summary->getEnvelopeId();
        #ds-snippet-end:eSign44Step3

        #ds-snippet-start:eSign44Step4
        $authentication_method = 'None';

        $recipient_view_request = $client_service->getRecipientViewRequest(
            $authentication_method,
            $args['envelope_args']
        );

        $recipient_view_request->setFrameAncestors(['http://localhost:8080/public', 'https://apps-d.docusign.com']);
        $recipient_view_request->setMessageOrigins(['https://apps-d.docusign.com']);
        #ds-snippet-end:eSign44Step4

        #ds-snippet-start:eSign44Step5
        $view_url = $client_service->getRecipientView($args['account_id'], $envelope_id, $recipient_view_request);

        return ['envelope_id' => $envelope_id, 'redirect_url' => $view_url['url']];
        #ds-snippet-end:eSign44Step5
    }

    #ds-snippet-start:eSign44Step2
    public static function makeEnvelope(array $args, string $demo_path, string $pdf_file): EnvelopeDefinition
    {
        $content_bytes = file_get_contents($demo_path . $pdf_file);
        $base64_file_content = base64_encode($content_bytes);

        $document = new Document(
            [
                'document_base64' => $base64_file_content,
                'name' => 'Example document',
                'file_extension' => 'pdf',
                'document_id' => 1
            ]
        );

        $signer = new Signer(
            [
                'email' => $args['signer_email'],
                'name' => $args['signer_name'],
                'recipient_id' => '1',
                'routing_order' => '1',
                'client_user_id' => $args['signer_client_id']
            ]
        );

        $sign_here = new SignHere(
            [
                'anchor_string' => '/sn1/',
                'anchor_units' => 'pixels',
                'anchor_y_offset' => '10',
                'anchor_x_offset' => '20'
            ]
        );

        $signer->settabs(new Tabs(['sign_here_tabs' => [$sign_here]]));

        return new EnvelopeDefinition(
            [
                'email_subject' => 'Please sign this document sent from the PHP SDK',
                'documents' => [$document],
                'recipients' => new Recipients(['signers' => [$signer]]),
                'status' => 'sent'
            ]
        );
    }
    #ds-snippet-end:eSign44Step2
}
