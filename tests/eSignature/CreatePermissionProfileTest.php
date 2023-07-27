<?php

namespace Example\Tests;

use PHPUnit\Framework\TestCase;
use Example\Services\ApiTypes;
use Example\Services\Examples\eSignature\PermissionCreateService;
use Example\Services\SignatureClientService;

final class CreatePermissionProfileTest extends TestCase
{
    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    private const SETTINGS = [
        "useNewDocuSignExperienceInterface" => "optional",
        "allowBulkSending" => "true",
        "allowEnvelopeSending" => "true",
        "allowSignerAttachments" => "true",
        "allowTaggingInSendAndCorrect" => "true",
        "allowWetSigningOverride" => "true",
        "allowedAddressBookAccess" => "personalAndShared",
        "allowedTemplateAccess" => "share",
        "enableRecipientViewingNotifications" => "true",
        "enableSequentialSigningInterface" => "true",
        "receiveCompletedSelfSignedDocumentsAsEmailLinks" => "false",
        "signingUiVersion" => "v2",
        "useNewSendingInterface" => "true",
        "allowApiAccess" => "true",
        "allowApiAccessToAccount" => "true",
        "allowApiSendingOnBehalfOfOthers" => "true",
        "allowApiSequentialSigning" => "true",
        "enableApiRequestLogging" => "true",
        "allowDocuSignDesktopClient" => "false",
        "allowSendersToSetRecipientEmailLanguage" => "true",
        "allowVaulting" => "false",
        "allowedToBeEnvelopeTransferRecipient" => "true",
        "enableTransactionPointIntegration" => "false",
        "powerFormRole" => "admin",
        "vaultingMode" => "none"
    ];

    public function testPermisssionCreate_CorrectInputValues_ReturnPermissionProfile()
    {
        // Arrange
        $testConfig = new TestConfig();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::eSignature, $testConfig);

        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken(),
            'permission_args' => [
                'permission_profile_name' => substr(str_shuffle(MD5(microtime())), 0, 10),
                'settings' => self::SETTINGS,
            ]
        ];

        $clientService = new SignatureClientService($requestArguments);

        // Act
        $permissionProfile = PermissionCreateService::permisssionCreate($requestArguments, $clientService);

        // Assert
        $this->assertNotEmpty($permissionProfile);
        $this->assertNotNull($permissionProfile);
        $this->assertEquals($requestArguments['permission_args']['permission_profile_name'], $permissionProfile['permission_profile_name']);
    }
}