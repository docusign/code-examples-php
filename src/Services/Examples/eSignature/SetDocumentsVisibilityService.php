<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\Services\SignatureClientService;

class SetDocumentsVisibilityService
{
    const CASE_FOR_INSTRUCTIONS = "ACCOUNT_LACKS_PERMISSIONS";
    const FIXING_INSTRUCTIONS_FOR_PERMISSIONS = "See " .
        "<a href=\"https://developers.docusign.com/docs/esign-rest-api/how-to/set-document-visibility\">" .
        "How to set document visibility for envelope recipients</a> in the DocuSign Developer Center " .
        "for instructions on how to enable document visibility in your developer account.";

    public static function worker(
        string $signer1Email,
        string $signer1Name,
        string $signer2Email,
        string $signer2Name,
        string $ccEmail,
        string $ccName,
        string $docPdf,
        string $docDocx,
        string $docHTML,
        string $accountId,
        SignatureClientService $clientService,
        string $demoPath
    ): string {
        $envelope_definition = SetDocumentsVisibilityService::makeEnvelope(
            $signer1Email,
            $signer1Name,
            $signer2Email,
            $signer2Name,
            $ccEmail,
            $ccName,
            $docPdf,
            $docDocx,
            $docHTML,
            $demoPath
        );
        $envelope_api = $clientService->getEnvelopeApi();

        try {
            #ds-snippet-start:eSign40Step4
            $envelopeSummary = $envelope_api->createEnvelope(
                $accountId,
                $envelope_definition
            );
            #ds-snippet-end:eSign40Step4
        } catch (ApiException $e) {
            $error_code = $e->getResponseBody()->errorCode;

            if (strpos($error_code, self::CASE_FOR_INSTRUCTIONS) !== false) {
                $clientService->showErrorTemplate(
                    $e,
                    self::FIXING_INSTRUCTIONS_FOR_PERMISSIONS
                );
                exit;
            }

            $clientService->showErrorTemplate($e);
            exit;
        }

        return $envelopeSummary->getEnvelopeId();
    }
    #ds-snippet-start:eSign40Step3
    private static function makeEnvelope(
        string $signer1Email,
        string $signer1Name,
        string $signer2Email,
        string $signer2Name,
        string $ccEmail,
        string $ccName,
        string $docPdf,
        string $docDocx,
        string $docHTML,
        string $demoPath
    ): EnvelopeDefinition {
        $carbonCopy = new CarbonCopy(
            [
                'email' => $ccEmail,
                'name' => $ccName,
                'recipient_id' => "3",
                'routing_order' => "2",
            ]
        );

        $envelope_definition = new EnvelopeDefinition(
            [
                'email_subject' => "Please sign this document set",
                'documents' => SetDocumentsVisibilityService::_prepareDocuments(
                    $docPdf,
                    $docDocx,
                    $docHTML,
                    $demoPath
                ),
                'recipients' => new Recipients(
                    [
                        'signers' => SetDocumentsVisibilityService::_prepareSigners(
                            $signer1Email,
                            $signer1Name,
                            $signer2Email,
                            $signer2Name
                        ),
                        'carbon_copies' => [$carbonCopy]
                    ]
                ),
                'status' => "sent"
            ]
        );

        return $envelope_definition;
    }

    private static function _prepareSigners(
        string $signer1Email,
        string $signer1Name,
        string $signer2Email,
        string $signer2Name
    ): array {
        $signer1 = new Signer(
            [
                'email' => $signer1Email,
                'name' => $signer1Name,
                'recipient_id' => "1",
                'routing_order' => "1",
                'excluded_documents' => ["2", "3"],
                'tabs' => new Tabs(
                    [
                        'sign_here_tabs' =>  [
                            new SignHere(
                                [
                                    'anchor_string' => '**signature_1**',
                                    'anchor_units' => 'pixels',
                                    'anchor_y_offset' => '10',
                                    'anchor_x_offset' => '20'
                                ]
                            )
                        ]
                    ]
                )
            ]
        );

        $signer2 = new Signer(
            [
                'email' => $signer2Email,
                'name' => $signer2Name,
                'recipient_id' => "2",
                'routing_order' => "1",
                'excluded_documents' => ["1"],
                'tabs' => new Tabs(
                    [
                        'sign_here_tabs' =>  [
                            new SignHere(
                                [
                                    'anchor_string' => '/sn1/',
                                    'anchor_units' => 'pixels',
                                    'anchor_y_offset' => '10',
                                    'anchor_x_offset' => '20'
                                ]
                            )
                        ]
                    ]
                )
            ]
        );

        return [$signer1, $signer2];
    }

    private static function _prepareDocuments(
        string $docPdf,
        string $docDocx,
        string $docHTML,
        string $demoPath
    ): array {
        $contentBytesPdf = file_get_contents($demoPath . $docPdf);
        $contentBytesDocx = file_get_contents($demoPath . $docDocx);
        $contentBytesHtml = file_get_contents($demoPath . $docHTML);

        $documentHTML = new Document(
            [
                'document_base64' => base64_encode($contentBytesHtml),
                'name' => 'Order acknowledgement',
                'file_extension' => 'html',
                'document_id' => "1"
            ]
        );

        $documentDOCX = new Document(
            [
                'document_base64' => base64_encode($contentBytesDocx),
                'name' => 'Battle Plan',
                'file_extension' => 'docx',
                'document_id' => "2"
            ]
        );

        $documentPDF = new Document(
            [
                'document_base64' => base64_encode($contentBytesPdf),
                'name' => 'Lorem Ipsum',
                'file_extension' => 'pdf',
                'document_id' => "3"
            ]
        );

        return [$documentHTML, $documentDOCX, $documentPDF];
    }
    #ds-snippet-end:eSign40Step3
}
