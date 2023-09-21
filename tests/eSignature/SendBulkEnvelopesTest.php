<?php

namespace Example\Tests;

use DocuSign\eSign\Model\BulkSendingCopy;
use DocuSign\eSign\Model\BulkSendingCopyRecipient;
use DocuSign\eSign\Model\BulkSendingList;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use PHPUnit\Framework\TestCase;
use Example\Services\ApiTypes;
use Example\Services\Examples\eSignature\BulkSendEnvelopesService;
use Example\Services\SignatureClientService;

final class SendBulkEnvelopesTest extends TestCase
{
    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    public function testBulkSendEnvelopes_CorrectInputValues_ReturnString()
    {
        // Arrange
        $testConfig = new TestConfig();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $testConfig);

        $ccEmail = "cc@gmail.com";
        $ccName = "cc";
        $signer2Email = "signer2@gmail.com";
        $signer2Name = "signer 2";
        $cc2Email = "cc2@gmail.com";
        $cc2Name = "cc 2";
        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken(),
            'signers' => [
                [
                    'signer_email' => $testConfig->getSignerEmail(),
                    'signer_name' => $testConfig->getSignerName(),
                    'cc_email' => $ccEmail,
                    'cc_name' => $ccName
                ],
                [
                    'signer_email' => $signer2Email,
                    'signer_name' => $signer2Name,
                    'cc_email' => $cc2Email,
                    'cc_name' => $cc2Name
                ]
            ]
        ];

        $clientService = new SignatureClientService($requestArguments);

        // Act
        $bulkSendBatchStatus = BulkSendEnvelopesService::bulkSendEnvelopes(
            $requestArguments,
            $clientService,
            self::DEMO_DOCS_PATH,
            "World_Wide_Corp_lorem.pdf"
        );

        // Assert
        $this->assertNotEmpty($bulkSendBatchStatus);
        $this->assertNotNull($bulkSendBatchStatus);
    }

    public function testCreateBulkSendingList_CorrectInputValues_ReturnBulkSendingList()
    {
        // Arrange
        $testConfig = new TestConfig();

        $ccEmail = "cc@gmail.com";
        $ccRole = "cc";
        $signer2Email = "signer2@gmail.com";
        $signer2Name = "signer 2";
        $cc2Email = "cc2@gmail.com";
        $cc2Name = "cc 2";
        $signerRole = "signer";
        $bulkSendingListName = "sample";
        $signers = [
            [
                'signer_email' => $testConfig->getSignerEmail(),
                'signer_name' => $testConfig->getSignerName(),
                'cc_email' => $ccEmail,
                'cc_name' => $ccRole
            ],
            [
                'signer_email' => $signer2Email,
                'signer_name' => $signer2Name,
                'cc_email' => $cc2Email,
                'cc_name' => $cc2Name
            ]
        ];

        $copies = [];

        foreach ($signers as $signer) {
            $recipient = new BulkSendingCopyRecipient(
                [
                    "role_name" => $signerRole,
                    "tabs" => [],
                    "name" => $signer["signer_name"],
                    "email" => $signer["signer_email"]
                ]
            );

            $recipient2 = new BulkSendingCopyRecipient(
                [
                    "role_name" => $ccRole,
                    "tabs" => [],
                    "name" => $signer["cc_name"],
                    "email" => $signer["cc_email"]
                ]
            );

            $bulkCopy = new BulkSendingCopy(
                [
                    "recipients" => [$recipient, $recipient2],
                    "custom_fields" => []
                ]
            );

            array_push($copies, $bulkCopy);
        }

        $expectedBulkSendingList = new BulkSendingList(["name" => $bulkSendingListName]);
        $expectedBulkSendingList->setBulkCopies($copies);

        // Act
        $bulkSendingList = BulkSendEnvelopesService::createBulkSendingList(
            $signers
        );

        // Assert
        $this->assertNotNull($bulkSendingList);
        $this->assertEquals($expectedBulkSendingList, $bulkSendingList);
    }

    public function testMakeEnvelope_CorrectInputValues_ReturnEnvelopeDefinition()
    {
        // Arrange
        $contentBytes = file_get_contents(self::DEMO_DOCS_PATH . "World_Wide_Corp_lorem.pdf");
        $base64 = base64_encode($contentBytes);

        $defaultId = 1;
        $defaultIdString = "1";
        $envelopeStatus = "created";
        $signer = "signer";
        $secondDefaultId = "2";
        $carbonCopy = "cc";
        $emailSubject = "Please sign this document sent from the PHP SDK";

        $document = new Document(
            [
                'document_base64' => $base64,
                'name' => 'Example document',
                'file_extension' => 'pdf',
                'document_id' => $defaultId
            ]
        );

        $signer = new Signer(
            [
                'email' => "multiBulkRecipients-signer@docusign.com",
                'name' => "Multi Bulk Recipient::signer",
                'recipient_id' => $defaultIdString,
                'routing_order' => $defaultIdString,
                'recipient_type' => $signer,
                'delievery_method' => "email",
                'status' => $envelopeStatus,
                'role_name' => $signer
            ]
        );

        $cc = new CarbonCopy(
            [
                'email' => "multiBulkRecipients-cc@docusign.com",
                'name' => "Multi Bulk Recipient::cc",
                'recipient_id' => $secondDefaultId,
                'routing_order' => $secondDefaultId,
                'recipient_type' => $carbonCopy,
                'delievery_method' => "email",
                'status' => $envelopeStatus,
                'role_name' => $carbonCopy
            ]
        );

        $signHere = new SignHere(
            [
                'tab_label' => "signHere1",
                'anchor_string' => '/sn1/',
                'anchor_units' => 'pixels',
                'anchor_y_offset' => '10',
                'anchor_x_offset' => '20'
            ]
        );

        $signer->settabs(new Tabs(['sign_here_tabs' => [$signHere]]));

        $expectedEnvelopeDefinition = new EnvelopeDefinition(
            [
                'email_subject' => $emailSubject,
                'documents' => [$document],
                'recipients' => ['signers' => [$signer], 'carbonCopies' => [$cc]],
                'status' => $envelopeStatus
            ]
        );

        // Act
        $envelopeDefinition = BulkSendEnvelopesService::makeEnvelope(
            self::DEMO_DOCS_PATH,
            "World_Wide_Corp_lorem.pdf"
        );

        // Assert
        $this->assertNotNull($envelopeDefinition);
        $this->assertEquals($expectedEnvelopeDefinition, $envelopeDefinition);
    }
}