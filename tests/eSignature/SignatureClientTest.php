<?php

namespace DocuSign\Tests;

use DocuSign\eSign\Api\AccountsApi;
use DocuSign\eSign\Api\BulkEnvelopesApi;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Api\GroupsApi;
use DocuSign\eSign\Api\TemplatesApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Configuration;
use DocuSign\Services\ApiTypes;
use PHPUnit\Framework\TestCase;
use DocuSign\Services\SignatureClientService;

final class SignatureClientTest extends TestCase
{
    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    public function testGetTemplatesApiMethod_CorrectInputValues_ReturnsTemplatesApi()
    {
        // Arrange
        $testConfig = new TestConfig();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $testConfig);

        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken()
        ];

        $config = new Configuration();
        $config->setHost($testConfig->getBasePath());
        $config->addDefaultHeader('Authorization', 'Bearer ' . $testConfig->getAccessToken());
        $apiClient = new ApiClient($config);
        $expectedTemplatesApi = new TemplatesApi($apiClient);

        // Act
        $templateApi = (new SignatureClientService($requestArguments))->getTemplatesApi();

        // Assert
        $this->assertNotEmpty($templateApi);
        $this->assertEquals($expectedTemplatesApi, $templateApi);
    }

    public function testGetBulkEnvelopesApiMethod_CorrectInputValues_ReturnsBulkEnvelopesApi()
    {
        // Arrange
        $testConfig = new TestConfig();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $testConfig);

        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken()
        ];

        $config = new Configuration();
        $config->setHost($testConfig->getBasePath());
        $config->addDefaultHeader('Authorization', 'Bearer ' . $testConfig->getAccessToken());
        $apiClient = new ApiClient($config);
        $expectedBulkEnvelopesApi = new BulkEnvelopesApi($apiClient);

        // Act
        $bulkEnvelopesApi = (new SignatureClientService($requestArguments))->getBulkEnvelopesApi();

        // Assert
        $this->assertNotEmpty($bulkEnvelopesApi);
        $this->assertEquals($expectedBulkEnvelopesApi, $bulkEnvelopesApi);
    }

    public function testGetEnvelopeApiMethod_CorrectInputValues_ReturnsEnvelopeApi()
    {
        // Arrange
        $testConfig = new TestConfig();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $testConfig);

        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken()
        ];

        $config = new Configuration();
        $config->setHost($testConfig->getBasePath());
        $config->addDefaultHeader('Authorization', 'Bearer ' . $testConfig->getAccessToken());
        $apiClient = new ApiClient($config);
        $expectedEnvelopesApi = new EnvelopesApi($apiClient);

        // Act
        $envelopesApi = (new SignatureClientService($requestArguments))->getEnvelopeApi();

        // Assert
        $this->assertNotEmpty($envelopesApi);
        $this->assertEquals($expectedEnvelopesApi, $envelopesApi);
    }

    public function testGetAccountsApiMethod_CorrectInputValues_ReturnsAccountsApi()
    {
        // Arrange
        $testConfig = new TestConfig();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $testConfig);

        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken()
        ];

        $config = new Configuration();
        $config->setHost($testConfig->getBasePath());
        $config->addDefaultHeader('Authorization', 'Bearer ' . $testConfig->getAccessToken());
        $apiClient = new ApiClient($config);
        $expectedAccountsApi = new AccountsApi($apiClient);

        // Act
        $accountsApi = (new SignatureClientService($requestArguments))->getAccountsApi();

        // Assert
        $this->assertNotEmpty($accountsApi);
        $this->assertEquals($expectedAccountsApi, $accountsApi);
    }

    public function testGetGroupsApiMethod_CorrectInputValues_ReturnsGroupsApi()
    {
        // Arrange
        $testConfig = new TestConfig();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $testConfig);

        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken()
        ];

        $config = new Configuration();
        $config->setHost($testConfig->getBasePath());
        $config->addDefaultHeader('Authorization', 'Bearer ' . $testConfig->getAccessToken());
        $apiClient = new ApiClient($config);
        $expectedGroupsApi = new GroupsApi($apiClient);

        // Act
        $groupsApi = (new SignatureClientService($requestArguments))->getGroupsApi();

        // Assert
        $this->assertNotEmpty($groupsApi);
        $this->assertEquals($expectedGroupsApi, $groupsApi);
    }
}
