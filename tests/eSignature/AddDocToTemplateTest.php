<?php

namespace Example\Tests;

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
use PHPUnit\Framework\TestCase;
use Example\Services\ApiTypes;
use Example\Services\Examples\eSignature\AddDocToTemplateService;
use Example\Services\SignatureClientService;

final class AddDocToTemplateTest extends TestCase
{
    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    public function testAddDocToTemplate_CorrectInputValues_ReturnEnvelopeId()
    {
        // Arrange
        $testConfig = TestConfig::getInstance();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::eSignature);
        (new CreateTemplateTest())->testCreateTemplate_CorrectInputValues_ReturnArray();

        $signerClientId = 1000;
        $ccEmail = 'cc@gmail.com';
        $ccName = 'CC';
        $envelopeStatus = 'sent';
        $itemName = 'kiwi';
        $itemQuantity = 1;

        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken(),
            'envelope_args' => [
                'signer_email' => $testConfig->getSignerEmail(),
                'signer_name' => $testConfig->getSignerName(),
                'signer_client_id' => $signerClientId,
                'cc_email' => $ccEmail,
                'cc_name' => $ccName,
                'status' => $envelopeStatus,
                'item' => $itemName,
                'quantity' => $itemQuantity,
                'template_id' => $testConfig->getTemplateId(),
                'ds_return_url' => $this::DEMO_DOCS_PATH
            ]
        ];

        $clientService = new SignatureClientService($requestArguments);

        // Act
        $envelopeTemplate = AddDocToTemplateService::addDocToTemplate(
            $requestArguments,
            $clientService
        );

        // Assert
        $this->assertNotEmpty($envelopeTemplate);
        $this->assertNotNull($envelopeTemplate);
        $this->assertNotNull($envelopeTemplate["envelope_id"]);
    }

    public function testMakeEnvelope_CorrectInputValues_ReturnEnvelopeDefinition()
    {
        // Arrange
        $testConfig = TestConfig::getInstance();
        (new CreateTemplateTest())->testCreateTemplate_CorrectInputValues_ReturnArray();

        $status = "sent";
        $ccEmail = 'cc@gmail.com';
        $ccName = 'CC';
        $item = 'kiwi';
        $itemQuantity = 1;
        $signerClientId = 1000;
        $signerRole = "signer";
        $defaultId = "1";
        $ccRole = "cc";
        $secondDefaultId = "2";
        $anchorString = '**signature_1**';
        $anchorOffsetY = '10';
        $anchorUnits = 'pixels';
        $anchorOffsetX = '20';
        $documentName = 'Appendix 1--Sales order';
        $fileExtension = 'html';

        $requestArguments = [
            'signer_email' => $testConfig->getSignerEmail(),
            'signer_name' => $testConfig->getSignerName(),
            'signer_client_id' => $signerClientId,
            'cc_email' => $ccEmail,
            'cc_name' => $ccName,
            'status' => $status,
            'item' => $item,
            'quantity' => $itemQuantity,
            'template_id' => $testConfig->getTemplateId(),
            'ds_return_url' => $this::DEMO_DOCS_PATH
        ];
        $clientService = new SignatureClientService($requestArguments);

        $signer = new Signer([
              'email' => $requestArguments['signer_email'],
              'name' => $requestArguments['signer_name'],
              'role_name' => $signerRole,
              'recipient_id' => $defaultId,
              'client_user_id' => $requestArguments['signer_client_id']
          ]);
        $carbonCopy = new CarbonCopy([
              'email' => $requestArguments['cc_email'],
              'name' => $requestArguments['cc_name'],
              'role_name' => $ccRole,
              'recipient_id' => $secondDefaultId
          ]);

        $recipients = new Recipients([
            'carbon_copies' => [$carbonCopy],
            'signers' => [$signer]]);

        $compositeTemplate = new CompositeTemplate([
            'composite_template_id' => $defaultId,
            'server_templates' => [
                new ServerTemplate([
                    'sequence' => $defaultId,
                    'template_id' => $requestArguments['template_id']])
            ],
            'inline_templates' => [
                new InlineTemplate([
                    'sequence' => $secondDefaultId,
                    'recipients' => $recipients])
            ]
        ]);

        $signHere = new SignHere([
           'anchor_string' => $anchorString,
           'anchor_y_offset' => $anchorOffsetY,
           'anchor_units' => $anchorUnits,
           'anchor_x_offset' => $anchorOffsetX
                                   ]);
        $signerTabs = new Tabs(['sign_here_tabs' => [$signHere]]);

        $signerAddedDoc = new Signer([
              'email' => $requestArguments['signer_email'],
              'name' => $requestArguments['signer_name'],
              'role_name' => $signerRole,
              'recipient_id' => $defaultId,
              'client_user_id' => $requestArguments['signer_client_id'],
              'tabs' => $signerTabs
          ]);

        $recipientsAddedDoc = new Recipients(
            [
                'carbon_copies' => [$carbonCopy],
                'signers' => [$signerAddedDoc]
            ]
        );

        $base64Document = base64_encode($clientService->createDocumentForEnvelope($requestArguments));
        $document = new Document(
            [
                'document_base64' => $base64Document,
                'name' => $documentName,
                'file_extension' => $fileExtension,
                'document_id' => $defaultId
            ]
        );

        $compositeTemplateWithDoc = new CompositeTemplate(
            [
                'composite_template_id' => $secondDefaultId,
                'inline_templates' => [
                    new InlineTemplate(
                        [
                            'sequence' => $defaultId,
                            'recipients' => $recipientsAddedDoc
                        ]
                    )
                ],
                'document' => $document
            ]
        );

        $expectedEnvelopeDefinition = new EnvelopeDefinition(
            [
                'status' => $status,
                'composite_templates' => [$compositeTemplate, $compositeTemplateWithDoc]
            ]
        );

        // Act
        $envelopeDefinition = AddDocToTemplateService::make_envelope(
            $requestArguments,
            $clientService
        );

        // Assert
        $this->assertNotNull($envelopeDefinition);
        $this->assertEquals($expectedEnvelopeDefinition, $envelopeDefinition);
    }
}