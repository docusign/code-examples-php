<?php

namespace Example\Tests;

use DocuSign\eSign\Model\CustomFields;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\Text;
use DocuSign\eSign\Model\TextCustomField;
use PHPUnit\Framework\TestCase;
use Example\Services\ApiTypes;
use Example\Services\Examples\eSignature\SetTabValuesService;
use Example\Services\SignatureClientService;

final class SetTabValuesTest extends TestCase
{
    private int $signer_client_id = 1000;

    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    private string $redirectUrl = "https://developers.docusign.com/platform/auth/consent";

    public function testSetTabValues_CorrectInputValues_ReturnsArray()
    {
        // Arrange
        $testConfig = TestConfig::getInstance();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::eSignature);

        $requestArguments = [
            'account_id' => $testConfig->getAccountId(),
            'base_path' => $testConfig->getBasePath(),
            'ds_access_token' => $testConfig->getAccessToken(),
            'envelope_args' => [
                'signer_email' => $testConfig->getSignerEmail(),
                'signer_name' => $testConfig->getSignerName(),
                'signer_client_id' => $this->signer_client_id,
                'ds_return_url' => $this->redirectUrl
            ]
        ];

        $clientService = new SignatureClientService($requestArguments);

        // Act
        $envelopeResponse = SetTabValuesService::setTabValues(
            $requestArguments,
            $this::DEMO_DOCS_PATH,
            $clientService
        );

        // Assert
        $this->assertNotEmpty($envelopeResponse);
        $this->assertNotNull($envelopeResponse["envelope_id"]);
    }

    public function testMakeEnvelope_CorrectInputValues_ReturnsEnvelopeDefinition()
    {
        // Arrange
        $testConfig = TestConfig::getInstance();

        $requestArguments = [
            'signer_email' => $testConfig->getSignerEmail(),
            'signer_name' => $testConfig->getSignerName(),
            'signer_client_id' => $this->signer_client_id,
            'ds_return_url' => $this->redirectUrl
        ];

        $salary = 123000;
        $documentName = 'World_Wide_Corp_salary.docx';
        $documentNaming = 'Salary action';
        $fileExtension = 'docx';
        $defaultDocumentId = 1;
        $defaultId = "1";
        $emailSubject = "Please sign this document sent from the PHP SDK";
        $emailStatus = "sent";
        $anchorUnits = 'pixels';
        $font = "helvetica";
        $fontSize = "size11";
        $trueString = 'true';
        $falseString = 'false';
        $contentBytes = file_get_contents($this::DEMO_DOCS_PATH . $documentName);
        $base64FileContent = base64_encode($contentBytes);

        $document = new Document([
             'document_base64' => $base64FileContent,
             'name' => $documentNaming,
             'file_extension' => $fileExtension,
             'document_id' => $defaultDocumentId
        ]);
        $signer = new Signer([
            'email' => $requestArguments['signer_email'],
            'name' => $requestArguments['signer_name'],
            'recipient_id' => $defaultId,
            'routing_order' => $defaultId,
            'client_user_id' => $requestArguments['signer_client_id']
        ]);
        $signHere = new SignHere([
            'anchor_string' => '/sn1/', 'anchor_units' => $anchorUnits,
            'anchor_y_offset' => '10', 'anchor_x_offset' => '20'
        ]);
        $textLegal = new Text([
           'anchor_string' => '/legal/', 'anchor_units' => $anchorUnits,
           'anchor_y_offset' => '-9', 'anchor_x_offset' => '5',
           'font' => $font, 'font_size' => $fontSize,
           'bold' => $trueString, 'value' => $requestArguments['signer_name'],
           'locked' => $falseString, 'tab_id' => 'legal_name',
           'tab_label' => 'Legal name']);

        $textFamiliar = new Text([
          'anchor_string' => '/familiar/', 'anchor_units' => $anchorUnits,
          'anchor_y_offset' => '-9', 'anchor_x_offset' => '5',
          'font' => $font, 'font_size' => $fontSize,
          'bold' => $trueString, 'value' => $requestArguments['signer_name'],
          'locked' => $falseString, 'tab_id' => 'familiar_name',
          'tab_label' => 'Familiar name']);

        $salaryReadable = '$' . number_format($salary);
        $textSalary = new Text([
            'anchor_string' => '/salary/', 'anchor_units' => $anchorUnits,
            'anchor_y_offset' => '-9', 'anchor_x_offset' => '5',
            'font' => $font, 'font_size' => $fontSize,
            'bold' => $trueString, 'value' => $salaryReadable,
            'locked' => $trueString,
            'tab_id' => 'salary', 'tab_label' => 'Salary'
        ]);

        $signer->settabs(new Tabs([
            'sign_here_tabs' => [$signHere],
             'text_tabs' => [$textLegal, $textFamiliar, $textSalary]
        ]));

        $salaryCustomField = new TextCustomField([
           'name' => 'salary',
           'required' => $falseString,
           'show' => $trueString,
           'value' => $salary]);
        $customFields = new CustomFields(['text_custom_fields' => [$salaryCustomField]]);

        $expectedEnvelopeDefinition = new EnvelopeDefinition([
             'email_subject' => $emailSubject,
             'documents' => [$document],
             'recipients' => new Recipients(['signers' => [$signer]]),
             'status' => $emailStatus,
             'custom_fields' => $customFields
        ]);

        // Act
        $envelope = SetTabValuesService::make_envelope(
            $requestArguments,
            $this::DEMO_DOCS_PATH
        );

        // Assert
        $this->assertNotNull($envelope);
        $this->assertEquals($expectedEnvelopeDefinition, $envelope);
    }
}