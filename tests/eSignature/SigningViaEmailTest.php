<?php

namespace Example\Tests;

use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use PHPUnit\Framework\TestCase;
use Example\Services\ApiTypes;
use Example\Services\Examples\eSignature\SigningViaEmailService;
use Example\Services\SignatureClientService;

final class SigningViaEmailTest extends TestCase
{
    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    public function testSigningViaEmail_CorrectInputValues_ReturnsArray()
    {
        // Arrange
        $testConfig = TestConfig::getInstance();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::eSignature);

        $ccEmail = 'cc@gmail.com';
        $ccName = 'CC';
        $status = 'sent';
        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken(),
            'envelope_args' => [
                'signer_email' => $testConfig->getSignerEmail(),
                'signer_name' => $testConfig->getSignerName(),
                'cc_email' => $ccEmail,
                'cc_name' => $ccName,
                'status' => $status
            ]
        ];

        $clientService = new SignatureClientService($requestArguments);

        // Act
        $envelopeIdAndReturnUrl = SigningViaEmailService::signingViaEmail(
            $requestArguments,
            $clientService,
            $this::DEMO_DOCS_PATH
        );

        // Assert
        $this->assertNotEmpty($envelopeIdAndReturnUrl);
        $this->assertNotNull($envelopeIdAndReturnUrl["envelope_id"]);
    }

    public function testMakeEnvelope_CorrectInputValues_ReturnsEnvelopeDefinition()
    {
        // Arrange
        $testConfig = TestConfig::getInstance();

        $ccEmail = 'cc@gmail.com';
        $ccName = 'CC';
        $status = 'sent';
        $defaultIdOne = "1";
        $defaultIdTwo = '2';
        $defaultIdThree = '3';
        $anchorUnits = 'pixels';
        $emailSubject = 'Please sign this document set';

        $requestArguments = [
            'signer_email' => $testConfig->getSignerEmail(),
            'signer_name' => $testConfig->getSignerName(),
            'cc_email' => $ccEmail,
            'cc_name' => $ccName,
            'status' => $status
        ];

        $clientService = new SignatureClientService($requestArguments);
        $expectedEnvelopeDefinition = new EnvelopeDefinition([
              'email_subject' => $emailSubject
        ]);
        $documentOneBase64 = base64_encode(
            $clientService->createDocumentForEnvelope($requestArguments));
        $contentBytes = file_get_contents(
            $this::DEMO_DOCS_PATH . $GLOBALS['DS_CONFIG']['doc_docx']);
        $documentTwoBase64 = base64_encode($contentBytes);
        $contentBytes = file_get_contents(
            $this::DEMO_DOCS_PATH . $GLOBALS['DS_CONFIG']['doc_pdf']);
        $documentThreeBase64 = base64_encode($contentBytes);

        $document1 = new Document([
            'document_base64' => $documentOneBase64,
            'name' => 'Order acknowledgement',
            'file_extension' => 'html',
            'document_id' => $defaultIdOne
        ]);
        $document2 = new Document([
            'document_base64' => $documentTwoBase64,
            'name' => 'Battle Plan',
            'file_extension' => 'docx',
            'document_id' => $defaultIdTwo
        ]);
        $document3 = new Document([
            'document_base64' => $documentThreeBase64,
            'name' => 'Lorem Ipsum',
            'file_extension' => 'pdf',
            'document_id' => $defaultIdThree
        ]);
        $expectedEnvelopeDefinition->setDocuments([$document1, $document2, $document3]);

        $signer = new Signer([
          'email' => $requestArguments['signer_email'],
          'name' => $requestArguments['signer_name'],
          'recipient_id' => $defaultIdOne, 'routing_order' => $defaultIdOne
        ]);
        $CC = new CarbonCopy([
          'email' => $requestArguments['cc_email'],
          'name' => $requestArguments['cc_name'],
          'recipient_id' => $defaultIdTwo, 'routing_order' => $defaultIdTwo
        ]);
        $signHere1 = new SignHere([
           'anchor_string' => '**signature_1**', 'anchor_units' => $anchorUnits,
           'anchor_y_offset' => '10', 'anchor_x_offset' => '20']);
        $signHere2 = new SignHere([
           'anchor_string' => '/sn1/', 'anchor_units' => $anchorUnits,
           'anchor_y_offset' => '10', 'anchor_x_offset' => '20']);

        $signer->setTabs(new Tabs(['sign_here_tabs' => [$signHere1, $signHere2]]));

        $recipients = new Recipients(['signers' => [$signer], 'carbon_copies' => [$CC]]);
        $expectedEnvelopeDefinition->setRecipients($recipients);

        $expectedEnvelopeDefinition->setStatus($requestArguments["status"]);

        // Act
        $envelope = SigningViaEmailService::make_envelope(
            $requestArguments,
            $clientService,
            $this::DEMO_DOCS_PATH
        );

        // Assert
        $this->assertNotNull($envelope);
        $this->assertEquals($expectedEnvelopeDefinition, $envelope);
    }
}