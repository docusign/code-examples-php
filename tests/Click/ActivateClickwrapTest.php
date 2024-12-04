<?php

namespace DocuSign\Tests;

use DocuSign\Services\ClickApiClientService;
use DocuSign\Services\Examples\Click\ActivateClickwrapService;
use PHPUnit\Framework\TestCase;
use DocuSign\Services\ApiTypes;

final class ActivateClickwrapTest extends TestCase
{
    private string $templateName = 'Example Signer and CC template';

    private string $clickwrapName = "Clickwrap";

    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    public function testCreateClickwrap_CorrectInputValues_ReturnClickwrapVersionSummaryResponse()
    {
        // Arrange
        $testConfig = new TestConfig();
        
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $testConfig);
        $templateInformation = DocuSignHelpers::createTemplateMethod($this->templateName, $testConfig);
        $testConfig->setTemplateId($templateInformation["template_id"]);

        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::CLICK, $testConfig);
        
        $clickwrapVersionSummaryResponse = DocuSignHelpers::createClickwrapMethod($this->clickwrapName, $testConfig);

        $testConfig->setClickwrapId($clickwrapVersionSummaryResponse["clickwrap_id"]);
        $testConfig->setClickwrapVersionNumber($clickwrapVersionSummaryResponse["version_number"]);

        $activeStatus = "active";
        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken(),
            'clickwrap_id' => $testConfig->getClickwrapId(),
            'version_number' => $testConfig->getClickwrapVersionNumber()
        ];

        $clientService = new ClickApiClientService($requestArguments);

        // Act
        $clickwrapSummaryResponse = ActivateClickwrapService::activateClickwrap($requestArguments, $clientService);

        // Assert
        $this->assertNotEmpty($clickwrapSummaryResponse);
        $this->assertNotNull($clickwrapSummaryResponse);
        $this->assertEquals($clickwrapSummaryResponse["status"], $activeStatus);
    }
}