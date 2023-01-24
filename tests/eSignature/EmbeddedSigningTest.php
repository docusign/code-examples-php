<?php

namespace Example\Tests;

use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use PHPUnit\Framework\TestCase;
use Example\Services\ApiTypes;
use Example\Services\Examples\eSignature\EmbeddedSigningService;
use Example\Services\SignatureClientService;

final class EmbeddedSigningTest extends TestCase
{
    private int $signer_client_id = 1000;

    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    private string $redirectUrl = "https://developers.docusign.com/platform/auth/consent";

    public function testWorkerMethod_CorrectInputValues_ReturnsArray()
    {
        // Arrange
        $testConfig = TestConfig::getInstance();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::eSignature);

        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken(),
            'envelope_args' => [
                'signer_email' => $testConfig->getSignerEmail(),
                'signer_name' => $testConfig->getSignerName(),
                'signer_client_id' => $this->signer_client_id,
                'ds_return_url' => $this->redirectUrl
            ]
        ];

        $clientService = new SignatureClientService($requestArguments);

        // Act
        $envelopeIdAndReturnUrl = EmbeddedSigningService::worker(
            $requestArguments,
            $clientService,
            $this::DEMO_DOCS_PATH
        );

        // Assert
        $this->assertNotEmpty($envelopeIdAndReturnUrl);
        $this->assertNotNull($envelopeIdAndReturnUrl["envelope_id"]);
        $this->assertNotNull($envelopeIdAndReturnUrl["redirect_url"]);
    }

    public function testMakeEnvelope_CorrectInputValues_ReturnsEnvelopeDefinition()
    {
        // Arrange
        $testConfig = TestConfig::getInstance();

        $requestArguments = [
            'signer_email' => $testConfig->getSignerEmail(),
            'signer_name' => $testConfig->getSignerName(),
            'signer_client_id' => $this->signer_client_id,
            'ds_return_url' => $this->redirectUrl
        ];

        $documentName = 'World_Wide_Corp_lorem.pdf';
        $envelopeStatus = "sent";
        $emailSubject = "Please sign this document sent from the PHP SDK";
        $fileExtension = 'pdf';
        $documentNaming = 'Example document';
        $defaultDocumentId = 1;
        $defaultId = "1";
        $anchorXOffset = '20';
        $anchorYOffset = '10';
        $anchorUnits = 'pixels';
        $anchorString = '/sn1/';
        $contentBytes = file_get_contents($this::DEMO_DOCS_PATH . $documentName);
        $base64FileContent = base64_encode($contentBytes);

        $document = new Document(
            [
                'document_base64' => $base64FileContent,
                'name' => $documentNaming,
                'file_extension' => $fileExtension,
                'document_id' => $defaultDocumentId
            ]
        );
        $signer = new Signer(
            [
                'email' => $testConfig->getSignerEmail(),
                'name' => $testConfig->getSignerName(),
                'recipient_id' => $defaultId,
                'routing_order' => $defaultId,
                'client_user_id' => $this->signer_client_id,
            ]
        );

        $signHere = new SignHere(
            [
                'anchor_string' => $anchorString,
                'anchor_units' => $anchorUnits,
                'anchor_y_offset' => $anchorYOffset,
                'anchor_x_offset' => $anchorXOffset
            ]
        );

        $signer->settabs(new Tabs(['sign_here_tabs' => [$signHere]]));

        $expectedEnvelope = new EnvelopeDefinition(
            [
                'email_subject' => $emailSubject,
                'documents' => [$document],
                'recipients' => new Recipients(['signers' => [$signer]]),
                'status' => $envelopeStatus
            ]
        );

        // Act
        $envelope = EmbeddedSigningService::make_envelope(
            $requestArguments,
            $this::DEMO_DOCS_PATH
        );

        // Assert
        $this->assertNotNull($envelope);
        $this->assertEquals($expectedEnvelope, $envelope);
    }
}