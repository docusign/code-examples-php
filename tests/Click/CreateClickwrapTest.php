<?php

namespace DocuSign\Tests;

use DocuSign\Services\ClickApiClientService;
use DocuSign\Services\Examples\Click\CreateClickwrapService;
use PHPUnit\Framework\TestCase;
use DocuSign\Services\ApiTypes;

final class CreateClickwrapTest extends TestCase
{
    private string $templateName = 'Example Signer and CC template';

    private string $clickwrapName = "Clickwrap";

    public function testCreateClickwrap_CorrectInputValues_ReturnClickwrapVersionSummaryResponse()
    {
        // Arrange
        $testConfig = new TestConfig();
        
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $testConfig);
        $templateInformation = DocuSignHelpers::createTemplateMethod($this->templateName, $testConfig);
        $testConfig->setTemplateId($templateInformation["template_id"]);

        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::CLICK, $testConfig);
        
        // Act
        $clickwrapVersionSummaryResponse = DocuSignHelpers::createClickwrapMethod($this->clickwrapName, $testConfig);

        $testConfig->setClickwrapId($clickwrapVersionSummaryResponse["clickwrap_id"]);
        $testConfig->setClickwrapVersionNumber($clickwrapVersionSummaryResponse["version_number"]);

        // Assert
        $this->assertNotEmpty($clickwrapVersionSummaryResponse);
        $this->assertNotNull($clickwrapVersionSummaryResponse);
        $this->assertEquals($this->clickwrapName, $clickwrapVersionSummaryResponse["clickwrap_name"]);
    }
}