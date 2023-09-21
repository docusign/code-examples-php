<?php

namespace Example\Tests;

use Example\Services\ClickApiClientService;
use Example\Services\Examples\Click\CreateClickwrapService;
use PHPUnit\Framework\TestCase;
use Example\Services\ApiTypes;

final class CreateClickwrapTest extends TestCase
{
    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    protected $testConfig;

    public function __construct($testConfig = null)
    {
        parent::__construct();
        $this->testConfig = $testConfig ?? new TestConfig();
    }

    public function testCreateClickwrap_CorrectInputValues_ReturnClickwrapVersionSummaryResponse()
    {
        // Arrange
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::CLICK, $this->testConfig);
        (new CreateTemplateTest())->testCreateTemplate_CorrectInputValues_ReturnArray();

        $clickwrapName = "Clickwrap";
        $requestArguments = [
            'account_id' => $this->testConfig->getAccountId(),
            'base_path' => $this->testConfig->getBasePath(),
            'ds_access_token' => $this->testConfig->getAccessToken(),
            'clickwrap_name' => $clickwrapName,
        ];

        $clientService = new ClickApiClientService($requestArguments);

        // Act
        $clickwrapVersionSummaryResponse = CreateClickwrapService::createClickwrap(
            $requestArguments,
            self::DEMO_DOCS_PATH,
            $clientService
        );

        $this->testConfig->setClickwrapId($clickwrapVersionSummaryResponse["clickwrap_id"]);
        $this->testConfig->setClickwrapVersionNumber($clickwrapVersionSummaryResponse["version_number"]);

        // Assert
        $this->assertNotEmpty($clickwrapVersionSummaryResponse);
        $this->assertNotNull($clickwrapVersionSummaryResponse);
        $this->assertEquals($clickwrapName, $clickwrapVersionSummaryResponse["clickwrap_name"]);
    }
}