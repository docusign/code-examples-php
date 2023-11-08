<?php

namespace DocuSign\Tests;

use DocuSign\Services\Examples\eSignature\CreateBrandService;
use PHPUnit\Framework\TestCase;
use DocuSign\Services\ApiTypes;
use DocuSign\Services\SignatureClientService;

final class CreateBrandTest extends TestCase
{
    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    public function __construct($testConfig = null)
    {
        parent::__construct();
        $this->testConfig = $testConfig ?? new TestConfig();
    }

    public function testCreateBrand_CorrectInputValues_ReturnArray()
    {
        // Arrange
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $this->testConfig);

        $defaultLanguage = "en";
        $requestArguments = [
            'account_id' => $this->testConfig->getAccountId(),
            'base_path' => $this->testConfig->getBasePath(),
            'ds_access_token' => $this->testConfig->getAccessToken(),
            'brand_args' => [
                'brand_name' => substr(str_shuffle(MD5(microtime())), 0, 10),
                'default_language' => $defaultLanguage,
            ]
        ];

        $clientService = new SignatureClientService($requestArguments);

        // Act
        $brandId = CreateBrandService::createBrand($requestArguments, $clientService);
        $this->testConfig->setBrandId($brandId['brand_id']);

        // Assert
        $this->assertNotEmpty($brandId);
        $this->assertNotNull($brandId);
    }
}