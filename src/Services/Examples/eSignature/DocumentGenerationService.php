<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Api\EnvelopesApi\UpdateEnvelopeDocGenFormFieldsOptions;
use DocuSign\eSign\Model\DateSigned;
use DocuSign\eSign\Model\DocGenFormField;
use DocuSign\eSign\Model\TemplateTabs;
use DocuSign\eSign\Model\DocGenFormFieldRequest;
use DocuSign\eSign\Model\DocGenFormFields;
use DocuSign\eSign\Model\Envelope;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\EnvelopeTemplate;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\TemplateRole;
use DocuSign\eSign\Model\Document;

class DocumentGenerationService
{
    public static function worker(array $args, $clientService, $documentPath): string
    {
        $templatesApi = $clientService->getTemplatesApi();

        $envelopeTemplate = DocumentGenerationService::makeTemplate();
        $templatesListResponse = $templatesApi->createTemplate($args['account_id'], $envelopeTemplate);
        $templateId = $templatesListResponse['template_id'];

        $templatesApi->updateDocument($args['account_id'], "1", $templateId, self::addDocumentTemplate($documentPath));
        $templatesApi->createTabs($args['account_id'], "1", $templateId, self::prepareTabs());

        $envelopeApi = $clientService->getEnvelopeApi();
        $envelopeResponse = $envelopeApi->createEnvelope(
            $args['account_id'],
            DocumentGenerationService::makeEnvelope($args["form_data"], $templateId)
        );
        $envelopeId = $envelopeResponse["envelope_id"];

        $documents = $envelopeApi->getEnvelopeDocGenFormFields($args['account_id'], $envelopeId);
        $documentId = $documents["doc_gen_form_fields"][0]["document_id"];

        $formFields = DocumentGenerationService::formFields($args["form_data"], $documentId);
        $envelopeApi->updateEnvelopeDocGenFormFields(
            $args['account_id'],
            $envelopeId,
            $formFields
        );

        $envelopeResponse = $envelopeApi->update(
            $args['account_id'],
            $envelopeId,
            new Envelope([
                'status' => 'sent'
            ])
        );

        return $envelopeResponse->getEnvelopeId();
    }

    public static function makeTemplate(): EnvelopeTemplate
    {
        $signer = new Signer([
             'role_name' => 'signer',
             'recipient_id' => '1',
             'routing_order' => '1',
        ]);

        return new EnvelopeTemplate(
            [
                'description' => 'Example template created via the API',
                'name' => 'Example Template',
                'email_subject' => 'Please sign this document',
                'recipients' => new Recipients([
                    'signers' => [$signer]
                ]),
                'status' => 'created',
                'shared' => 'false'
            ]
        );
    }

    public static function prepareTabs(): TemplateTabs
    {
        $signHere = new SignHere([
             'document_id' => '1',
             'page_number' => '1',
             'x_position' => '75',
             'y_position' => '415'
        ]);

        $dateSigned = new DateSigned([
             'document_id' => '1',
             'page_number' => '1',
             'x_position' => '290',
             'y_position' => '435'
        ]);

        return new TemplateTabs([
             'sign_here_tabs' => [$signHere],
             'date_signed_tabs' => [$dateSigned],
        ]);
    }

    public static function addDocumentTemplate(string $documentPath): EnvelopeDefinition
    {
        $documentFile = $GLOBALS['DS_CONFIG']['offer_doc_docx'];
        $contentBytes = file_get_contents($documentPath . $documentFile);
        $base64FileContent = base64_encode($contentBytes);

        $document = new Document([
             'document_base64' => $base64FileContent,
             'name' => 'OfferLetterDemo.docx',
             'file_extension' => 'docx',
             'document_id' => '1',
             'order' => '1',
             'pages' => '1',
        ]);

        return new EnvelopeDefinition([
                'documents' => [$document],
        ]);
    }

    public static function makeEnvelope(array $args, $templateId): EnvelopeDefinition
    {
        $signer = new TemplateRole([
            'email' => $args['candidate_email'],
            'name' => $args['candidate_name'],
            'role_name' => "signer"
        ]);

        return new EnvelopeDefinition(
            [
                'status' => "created",
                'template_roles' => [$signer],
                "template_id" => $templateId
            ]
        );
    }

    public static function formFields(array $args, $documentId): DocGenFormFieldRequest
    {
        return new DocGenFormFieldRequest(
            [
                'doc_gen_form_fields' => [
                    new DocGenFormFields([
                        'document_id' => $documentId,
                        'doc_gen_form_field_list' => [
                            new DocGenFormField([
                                'name' => 'Candidate_Name',
                                'value' => $args['candidate_name']
                            ]),
                            new DocGenFormField([
                                'name' => 'Manager_Name',
                                'value' => $args['manager_name']
                            ]),
                            new DocGenFormField([
                                'name' => 'Job_Title',
                                'value' => $args['job_title']
                            ]),
                            new DocGenFormField([
                                'name' => 'Salary',
                                'value' => $args['salary']
                            ]),
                            new DocGenFormField([
                                'name' => 'Start_Date',
                                'value' => $args['start_date']
                            ])
                        ]
                    ])
                ]
            ]
        );
    }
}
