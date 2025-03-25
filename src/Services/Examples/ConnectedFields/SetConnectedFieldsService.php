<?php

namespace DocuSign\Services\Examples\ConnectedFields;

use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Configuration;
use DocuSign\eSign\Model\Text;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

class SetConnectedFieldsService
{
    public static function getConnectedFieldsTabGroups(string $accountId, string $accessToken): ?array
    {
        $url = "https://api-d.docusign.com/v1/accounts/{$accountId}/connected-fields/tab-groups";
        $client = new Client();

        try {
            #ds-snippet-start:ConnectedFields1Step3
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => "Bearer $accessToken",
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ]);
            #ds-snippet-end:ConnectedFields1Step3

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            return $data;
        } catch (RequestException $e) {
            error_log("DocuSign API Request failed: " . $e->getMessage());
            return null;
        }
    }

    #ds-snippet-start:ConnectedFields1Step4
    public static function filterData(array $data): string
    {
        $filteredData = array_filter($data, function ($item) {
            if (!isset($item['tabs']) || !is_array($item['tabs'])) {
                return false;
            }

            foreach ($item['tabs'] as $tab) {
                if ((isset($tab['extensionData']['actionContract'])
                        && str_contains($tab['extensionData']['actionContract'], "Verify")) ||
                    (isset($tab['tabLabel']) && str_contains($tab['tabLabel'], "connecteddata"))) {
                    return true;
                }
            }

            return false;
        });

        return json_encode(array_values($filteredData));
    }
    #ds-snippet-end:ConnectedFields1Step4

    public static function sendEnvelope(
        $basePath,
        $accessToken,
        $accountId,
        $pdfDoc,
        $demoPath,
        $app,
        $signerName,
        $signerEmail
    ): string {
        #ds-snippet-start:ConnectedFields1Step2
        $config = new Configuration();
        $config->setHost($basePath);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $accessToken);
        #ds-snippet-end:ConnectedFields1Step2

        #ds-snippet-start:ConnectedFields1Step6
        $apiClient = new ApiClient($config);
        $envelopesApi = new EnvelopesApi($apiClient);
    
        $envelope = SetConnectedFieldsService::makeEnvelopes(
            $app,
            $signerName,
            $signerEmail,
            $pdfDoc,
            $demoPath
        );
    
        $options = new \DocuSign\eSign\Api\EnvelopesApi\CreateEnvelopeOptions();
        $results = $envelopesApi->createEnvelope($accountId, $envelope, $options);
        
        return $results->getEnvelopeId();
        #ds-snippet-end:ConnectedFields1Step6
    }
    
    #ds-snippet-start:ConnectedFields1Step5
    public static function makeEnvelopes($app, $signerName, $signerEmail, $pdfDoc, $demoPath): EnvelopeDefinition
    {
        $appId = $app['appId'] ?? "";
        $tabLabels = $app['tabs'];
       
        $contentBytes = file_get_contents($demoPath . $pdfDoc);
        $base64FileContent = base64_encode($contentBytes);
    
        $doc = new Document([
            'document_base64' => $base64FileContent,
            'name' => 'Lorem Ipsum',
            'file_extension' => 'pdf',
            'document_id' => '1'
        ]);
    
        $signer = new Signer([
            'email' => $signerEmail,
            'name' => $signerName,
            'recipient_id' => '1',
            "routing_order" => "1",
        ]);
    
        $signHere = new SignHere([
            'anchor_string' => '/sn1/',
            'anchor_y_offset' => '10',
            'anchor_units' => 'pixels',
            'anchor_x_offset' => '20'
        ]);

        $textTabs = [];

        foreach($tabLabels as $tab) {
            $connectionKey = $tab['extensionData']['connectionInstances'][0]['connectionKey'] ?? "";
            $connectionValue = $tab['extensionData']['connectionInstances'][0]['connectionValue'] ?? "";
            $extensionGroupId = $tab['extensionData']['extensionGroupId'] ??  "";
            $publisherName = $tab['extensionData']['publisherName'] ?? "";
            $applicationName = $tab['extensionData']['applicationName'] ?? "";
            $actionName = $tab['extensionData']['actionName'] ?? "";
            $actionInputKey = $tab['extensionData']['actionInputKey'] ?? "";
            $actionContract = $tab['extensionData']['actionContract'] ?? "";
            $extensionName = $tab['extensionData']['extensionName'] ?? "";
            $extensionContract = $tab['extensionData']['extensionContract'] ?? "";
            $requiredForExtension = $tab['extensionData']['requiredForExtension'] ?? "";
        
            $textTab = [
                "requireInitialOnSharedChange"=> false,
                "requireAll"=> false,
                "name"=> $applicationName,
                "required"=> true,
                "locked"=> false,
                "disableAutoSize"=> false,
                "maxLength"=> 4000,
                "tabLabel"=> $tab["tabLabel"],
                "font"=> "lucidaconsole",
                "fontColor"=> "black",
                "fontSize"=> "size9",
                "documentId"=> "1",
                "recipientId"=> "1",
                "pageNumber"=> "1",
                "xPosition"=> "273",
                "yPosition"=>  150 + 20 * count($textTabs),
                "width"=> "84",
                "height"=> "22",
                "templateRequired"=> false,
                "tabType"=> "text",
                "extensionData" => [
                    "extensionGroupId" => $extensionGroupId,
                    "publisherName"=> $publisherName,
                    "applicationId"=> $appId,
                    "applicationName"=> $applicationName,
                    "actionName"=> $actionName,
                    "actionContract"=> $actionContract,
                    "extensionName"=> $extensionName,
                    "extensionContract"=> $extensionContract,
                    "requiredForExtension"=> $requiredForExtension,
                    "actionInputKey"=> $actionInputKey,
                    "extensionPolicy"=> "MustVerifyToSign",
                    "connectionInstances"=> [
                        [
                            "connectionKey"=> $connectionKey,
                            "connectionValue"=> $connectionValue
                        ]
                    ]
                ]
            ];

            array_push($textTabs, $textTab);
        }
    
        $signerTabs = new Tabs([
            'sign_here_tabs' => [$signHere],
            'text_tabs' => $textTabs
        ]);
        $signer->setTabs($signerTabs);
    
        $recipients = new Recipients([
            'signers' => [$signer]
        ]);
    
        $envelope = new EnvelopeDefinition([
            'email_subject' => 'Please sign this document',
            'documents' => [$doc],
            'recipients' => $recipients,
            'status' => 'sent'
        ]);
    
        return $envelope;
    }
    #ds-snippet-end:ConnectedFields1Step5
}
