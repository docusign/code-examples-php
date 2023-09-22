<?php

namespace DocuSign\Tests;

use DocuSign\Services\ClickApiClientService;
use DocuSign\Services\Examples\Click\ActivateClickwrapService;
use PHPUnit\Framework\TestCase;
use DocuSign\Services\ApiTypes;

final class ActivateClickwrapTest extends TestCase
{
    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    public function testCreateClickwrap_CorrectInputValues_ReturnClickwrapVersionSummaryResponse()
    {
        // Arrange
        $testConfig = new TestConfig();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::CLICK, $testConfig);
        (new CreateClickwrapTest($testConfig))->testCreateClickwrap_CorrectInputValues_ReturnClickwrapVersionSummaryResponse();

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