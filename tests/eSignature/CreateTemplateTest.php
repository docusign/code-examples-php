<?php

namespace DocuSign\Tests;

use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Checkbox;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeTemplate;
use DocuSign\eSign\Model\ModelList;
use DocuSign\eSign\Model\Numerical;
use DocuSign\eSign\Model\Radio;
use DocuSign\eSign\Model\RadioGroup;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\Text;
use PHPUnit\Framework\TestCase;
use DocuSign\Services\ApiTypes;
use DocuSign\Services\Examples\eSignature\CreateTemplateService;
use DocuSign\Services\SignatureClientService;

final class CreateTemplateTest extends TestCase
{
    private string $templateName = 'Example Signer and CC template';

    protected const DEMO_DOCS_PATH = __DIR__ . '/../../public/demo_documents/';

    protected $testConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testConfig = new TestConfig();
    }

    public function testCreateTemplate_CorrectInputValues_ReturnArray()
    {
        // Arrange
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $this->testConfig);

        $requestArguments = [
            'account_id' => $this->testConfig->getAccountId(),
            'base_path' => $this->testConfig->getBasePath(),
            'ds_access_token' => $this->testConfig->getAccessToken()
        ];

        $clientService = new SignatureClientService($requestArguments);

        // Act
        $templateInformation = CreateTemplateService::createTemplate(
            $requestArguments,
            $this->templateName,
            $this::DEMO_DOCS_PATH,
            $clientService
        );
        $this->testConfig->setTemplateId($templateInformation["template_id"]);

        // Assert
        $this->assertNotEmpty($templateInformation);
        $this->assertNotNull($templateInformation["template_id"]);
        $this->assertNotNull($templateInformation["created_new_template"]);
    }

    public function testMakeTemplateRequest__CorrectInputValues_ReturnEnvelopeDefinition()
    {
        // Arrange
        $doc_file = 'World_Wide_Corp_fields.pdf';
        $emailSubject = "Please sign this document";
        $description = "Example template created via the API";
        $status = "created";
        $falseString = "false";
        $signerRole = 'signer';
        $ccRole = 'cc';
        $defaultIdTwo = "2";
        $defaultIdOne = "1";
        $contentBytes = file_get_contents($this::DEMO_DOCS_PATH. $doc_file);
        $base64FileContent = base64_encode($contentBytes);

        $document = new Document([
             'document_base64' => $base64FileContent,
             'name' => 'Lorem Ipsum',
             'file_extension' => 'pdf',
             'document_id' => $defaultIdOne
        ]);

        $signer = new Signer([
            'role_name' => $signerRole,
            'recipient_id' => $defaultIdOne,
            'routing_order' => $defaultIdOne
         ]);
        $cc = new CarbonCopy([
            'role_name' => $ccRole,
            'recipient_id' => $defaultIdTwo,
            'routing_order' => $defaultIdTwo
        ]);
        $signHere = new SignHere([
            'document_id' => $defaultIdOne,
            'page_number' => $defaultIdOne,
            'x_position' => '191',
            'y_position' => '148'
        ]);

        $check1 = new Checkbox([
            'document_id' => $defaultIdOne,
            'page_number' => $defaultIdOne,
            'x_position' => '75',
            'y_position' => '417',
            'tab_label' => 'ckAuthorization'
        ]);
        $check2 = new Checkbox([
            'document_id' => $defaultIdOne,
            'page_number' => $defaultIdOne,
            'x_position' => '75',
            'y_position' => '447',
            'tab_label' => 'ckAuthentication'
        ]);
        $check3 = new Checkbox([
            'document_id' => $defaultIdOne,
            'page_number' => $defaultIdOne,
            'x_position' => '75',
            'y_position' => '478',
            'tab_label' => 'ckAgreement'
        ]);
        $check4 = new Checkbox([
            'document_id' => $defaultIdOne,
            'page_number' => $defaultIdOne,
            'x_position' => '75',
            'y_position' => '508',
            'tab_label' => 'ckAcknowledgement'
        ]);

        $listOfButtonOptions = CreateTemplateService::createListOfButtonOptions();

        $numerical = new Numerical([
            'document_id' => $defaultIdOne,
            'page_number' => $defaultIdOne,
            'validation_type' => "Currency",
            'x_position' => "163",
            'y_position' => "260",
            'font' => "helvetica",
            'font_size' => "size14",
            'tab_label' => "numericalCurrency",
            'width' => "84",
            'required' => $falseString
        ]);
        $radioGroup = new RadioGroup([
                'document_id' => $defaultIdOne,
                'group_name' => "radio1",
                'radios' => [
                    new Radio([
                        'page_number' => $defaultIdOne,
                        'x_position' => "142",
                        'y_position' => "384",
                        'value' => "white",
                        'required' => $falseString
                    ]),
                    new Radio([
                        'page_number' => $defaultIdOne,
                        'x_position' => "74",
                        'y_position' => "384",
                        'value' => "red",
                        'required' => $falseString
                    ]),
                    new Radio([
                        'page_number' => $defaultIdOne, 'x_position' => "220",
                        'y_position' => "384",
                        'value' => "blue", 'required' => $falseString
                    ])
                ]
        ]);
        $text = new Text([
             'document_id' => $defaultIdOne, 'page_number' => $defaultIdOne,
             'x_position' => "153", 'y_position' => "230",
             'font' => "helvetica", 'font_size' => "size14", 'tab_label' => "text",
             'height' => "23", 'width' => "84", 'required' => $falseString
        ]);
        $signer->setTabs(new Tabs([
            'sign_here_tabs' => [$signHere],
            'checkbox_tabs' => [$check1, $check2, $check3, $check4],
            'list_tabs' => [$listOfButtonOptions],
            'numerical_tabs' => [$numerical],
            'radio_group_tabs' => [$radioGroup],
            'text_tabs' => [$text]
        ]));

        $expectedTemplate =  new EnvelopeTemplate([
                'description' => $description,
                'name' => $this->templateName,
                'shared' => $falseString,
                'documents' => [$document],
                'email_subject' => $emailSubject,
                'recipients' => new Recipients(
                    [
                        'signers' => [$signer],
                        'carbon_copies' => [$cc]
                    ]
                ),
                'status' => $status
            ]);

        // Act
        $template = CreateTemplateService::makeTemplateRequest(
            $this->templateName,
            $this::DEMO_DOCS_PATH
        );

        // Assert
        $this->assertNotNull($template);
        $this->assertEquals($expectedTemplate, $template);
    }

    public function testCreateListOfButtonOptions_CorrectInputValues_ReturnModelList()
    {
        // Arrange
        $font = "helvetica";
        $fontSize = "size11";
        $required= "true";
        $tabLabel = "l1q";
        $anchorString = '/l1q/';
        $anchorUnits = 'pixels';
        $anchorOffsetY = '-10';
        $anchorOffsetX = '0';
        $expectedListOfButtonOptions = new ModelList(
            [
                'font' => $font,
                'font_size' => $fontSize,
                'anchor_string' => $anchorString,
                'anchor_y_offset' => $anchorOffsetY,
                'anchor_units' => $anchorUnits,
                'anchor_x_offset' => $anchorOffsetX,
                'list_items' => [
                    ['text' => "Red", 'value' => "red"],
                    ['text' => "Orange", 'value' => "orange"],
                    ['text' => "Yellow", 'value' => "yellow"],
                    ['text' => "Green", 'value' => "green"],
                    ['text' => "Blue", 'value' => "blue"],
                    ['text' => "Indigo", 'value' => "indigo"]
                ],
                'required' => $required,
                'tab_label' => $tabLabel
            ]
        );
        // Act
        $listOfButtonOptions = CreateTemplateService::createListOfButtonOptions();

        // Assert
        $this->assertNotNull($listOfButtonOptions);
        $this->assertEquals($expectedListOfButtonOptions, $listOfButtonOptions);
    }
}