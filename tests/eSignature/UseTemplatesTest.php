<?php

namespace DocuSign\Tests;

use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\TemplateRole;
use PHPUnit\Framework\TestCase;
use DocuSign\Services\ApiTypes;
use DocuSign\Services\Examples\eSignature\UseTemplateService;
use DocuSign\Services\SignatureClientService;

final class UseTemplatesTest extends TestCase
{
    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    private string $templateName = 'Example Signer and CC template';
    
    public function testUseTemplate_CorrectInputValues_ReturnsArray()
    {
        // Arrange
        $testConfig = new TestConfig();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $testConfig);
        $templateInformation = DocuSignHelpers::createTemplateMethod($this->templateName, $testConfig);
        $testConfig->setTemplateId($templateInformation["template_id"]);

        $ccEmail = "CC@gmail.com";
        $ccName = "CC";
        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken(),
            'envelope_args' => [
                'signer_email' => $testConfig->getSignerEmail(),
                'signer_name' => $testConfig->getSignerName(),
                'cc_email' => $ccEmail,
                'cc_name' => $ccName,
                'template_id' => $testConfig->getTemplateId()
            ]
        ];

        $clientService = new SignatureClientService($requestArguments);

        // Act
        $envelopeId = UseTemplateService::useTemplate($requestArguments, $clientService);

        // Assert
        $this->assertNotEmpty($envelopeId);
        $this->assertNotNull($envelopeId);
    }

    public function testMakeEnvelope_CorrectInputValues_ReturnsEnvelopeDefinition()
    {
        // Arrange
        $testConfig = new TestConfig();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $testConfig);
        $templateInformation = DocuSignHelpers::createTemplateMethod($this->templateName, $testConfig);
        $testConfig->setTemplateId($templateInformation["template_id"]);

        $ccMail = "CC@gmail.com";
        $ccName = "CC";
        $envelopeStatus = 'sent';
        $signerRole = 'signer';
        $ccRole = 'cc';
        $requestArguments = [
            'signer_email' => $testConfig->getSignerEmail(),
            'signer_name' => $testConfig->getSignerName(),
            'cc_email' => $ccMail,
            'cc_name' => $ccName,
            'template_id' => $testConfig->getTemplateId()
        ];

        $expectedEnvelopeDefinition = new EnvelopeDefinition([
            'status' => $envelopeStatus, 'template_id' => $requestArguments['template_id']
        ]);

        $signer = new TemplateRole([
           'email' => $requestArguments['signer_email'],
           'name' => $requestArguments['signer_name'],
           'role_name' => $signerRole
        ]);

        $cc = new TemplateRole([
           'email' => $requestArguments['cc_email'],
           'name' => $requestArguments['cc_name'],
           'role_name' => $ccRole
        ]);

        $expectedEnvelopeDefinition->setTemplateRoles([$signer, $cc]);

        // Act
        $envelopeDefinition = UseTemplateService::makeEnvelope($requestArguments);

        // Assert
        $this->assertNotNull($envelopeDefinition);
        $this->assertEquals($expectedEnvelopeDefinition, $envelopeDefinition);
    }
}