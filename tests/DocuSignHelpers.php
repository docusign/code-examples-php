<?php
namespace DocuSign\Tests;

use DocuSign\Click\Model\ClickwrapVersionSummaryResponse;
use DocuSign\Services\ClickApiClientService;
use DocuSign\Services\Examples\eSignature\CreateBrandService;
use DocuSign\Services\Examples\Click\CreateClickwrapService;
use DocuSign\Services\Examples\eSignature\CreateTemplateService;
use DocuSign\Services\SignatureClientService;

final class DocuSignHelpers
{
    private const DEMO_DOCS_PATH = __DIR__ . '/../public/demo_documents/';

    public static function createClickwrapMethod(string $clickwrapName, TestConfig $testConfig): ClickwrapVersionSummaryResponse
    {
        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken(),
            'clickwrap_name' => $clickwrapName,
        ];

        $clientService = new ClickApiClientService($requestArguments);

        return CreateClickwrapService::createClickwrap(
            $requestArguments,
            self::DEMO_DOCS_PATH,
            $clientService
        );
    }

    public static function createTemplateMethod(string $templateName, TestConfig $testConfig): array
    {
        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken()
        ];

        $clientService = new SignatureClientService($requestArguments);

        return CreateTemplateService::createTemplate(
            $requestArguments,
            $templateName,
            self::DEMO_DOCS_PATH,
            $clientService
        );
    }

    public static function createBrand(TestConfig $testConfig): array {
        $defaultLanguage = "en";
        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken(),
            'brand_args' => [
                'brand_name' => substr(str_shuffle(MD5(microtime())), 0, 10),
                'default_language' => $defaultLanguage,
            ]
        ];

        $clientService = new SignatureClientService($requestArguments);

        return CreateBrandService::createBrand($requestArguments, $clientService);
    }
}