<?php

namespace Example\Tests;

use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use Example\Services\Examples\eSignature\ApplyBrandToEnvelopeService;
use Example\Services\Examples\eSignature\CreateAnEnvelopeFunctionService;
use PHPUnit\Framework\TestCase;
use Example\Services\ApiTypes;
use Example\Services\SignatureClientService;

final class ApplyBrandToEnvelopeTest extends TestCase
{
    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    public function testApplyBrandToEnvelope_CorrectInputValues_ReturnArray()
    {
        // Arrange
        $testConfig = new TestConfig();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::eSignature, $testConfig);
        (new CreateBrandTest($testConfig))->testCreateBrand_CorrectInputValues_ReturnArray();

        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken(),
            'envelope_args' => [
                'signer_email' => $testConfig->getSignerEmail(),
                'signer_name' => $testConfig->getSignerName(),
                'brand_id' => $testConfig->getBrandId(),
            ]
        ];

        $clientService = new SignatureClientService($requestArguments);

        // Act
        $envelopeID = ApplyBrandToEnvelopeService::applyBrandToEnvelope(
            $requestArguments,
            static::DEMO_DOCS_PATH,
            $clientService
        );

        // Assert
        $this->assertNotEmpty($envelopeID);
        $this->assertNotNull($envelopeID);
    }

    public function testMakeEnvelope_CorrectInputValues_ReturnEnvelopeDefinition()
    {
        // Arrange
        $testConfig = new TestConfig();
        (new CreateBrandTest($testConfig))->testCreateBrand_CorrectInputValues_ReturnArray();

        $status = 'sent';
        $defaultId = '1';
        $anchorY = '10';
        $anchorX = '20';
        $anchorUnits = 'pixels';

        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken(),
            'envelope_args' => [
                'signer_email' => $testConfig->getSignerEmail(),
                'signer_name' => $testConfig->getSignerName(),
                'brand_id' => $testConfig->getBrandId(),
            ]
        ];
        $clientService = new SignatureClientService($requestArguments);

        $expectedEnvelopeDefinition = CreateAnEnvelopeFunctionService::make_envelope(
            $requestArguments,
            $clientService,
            static::DEMO_DOCS_PATH
        );

        $expectedEnvelopeDefinition->setStatus($status);

        $signer = new Signer([
             'name' => $requestArguments['signer_name'],
             'email' => $requestArguments['signer_email'],
             'routing_order' => $defaultId,
             'recipient_id' => $defaultId,
        ]);

        $signHere = new SignHere([
           'anchor_string' => '**signature_1**', 'anchor_units' => $anchorUnits,
           'anchor_y_offset' => $anchorY, 'anchor_x_offset' => $anchorX
        ]);
        $signHere2 = new SignHere([
           'anchor_string' => '/sn1/', 'anchor_units' => $anchorUnits,
           'anchor_y_offset' => $anchorY, 'anchor_x_offset' => $anchorX
        ]);

        $signer->setTabs(new Tabs(['sign_here_tabs' => [$signHere, $signHere2]]));
        $recipients = new Recipients(['signers' => [$signer],]);

        $expectedEnvelopeDefinition->setRecipients($recipients);
        $expectedEnvelopeDefinition->setBrandId($requestArguments['brand_id']);

        // Act
        $envelopeDefinition = ApplyBrandToEnvelopeService::make_envelope(
            $requestArguments,
            $clientService,
            ApplyBrandToEnvelopeTest::DEMO_DOCS_PATH
        );

        // Assert
        $this->assertNotNull($envelopeDefinition);
        $this->assertEquals($expectedEnvelopeDefinition, $envelopeDefinition);
    }
}