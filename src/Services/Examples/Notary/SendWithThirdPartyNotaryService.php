<?php

namespace DocuSign\Services\Examples\Notary;

use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\NotaryRecipient;
use DocuSign\eSign\Model\NotarySeal;
use DocuSign\eSign\Model\RecipientSignatureProvider;
use DocuSign\eSign\Model\RecipientSignatureProviderOptions;

class SendWithThirdPartyNotaryService
{
    #ds-snippet-start:Notary4Step4
    public static function sendWithNotary($signerEmail, $signerName, $envelopesApi, $accountId, $demoPath): mixed
    {
        $env = SendWithThirdPartyNotaryService::makeEnvelope($signerEmail, $signerName, $demoPath);
        
        $results = $envelopesApi->createEnvelope($accountId, $env);
        return $results;
    }
    #ds-snippet-end:Notary4Step4
    
    #ds-snippet-start:Notary4Step3
    private static function makeEnvelope($signerEmail, $signerName, $demoPath): EnvelopeDefinition
    {
        $env = new EnvelopeDefinition();
        $env->setEmailSubject("Please sign this document set");
        
        $env->setDocuments(SendWithThirdPartyNotaryService::getDocuments($signerEmail, $signerName, $demoPath));
        
        $recipients = new Recipients();
        $recipients->setSigners(SendWithThirdPartyNotaryService::getSigners($signerEmail, $signerName));
        $recipients->setNotaries(SendWithThirdPartyNotaryService::getNotaryRecipients());
        
        $env->setRecipients($recipients);
        $env->setStatus("sent");
        
        return $env;
    }
    
    private static function getDocuments($signerEmail, $signerName, $demoPath): array
    {
        $doc = new Document();
        $b64 = base64_encode(SendWithThirdPartyNotaryService::getDocumentExample($signerEmail, $signerName, $demoPath));
        
        $doc->setDocumentBase64($b64);
        $doc->setName("Order acknowledgement");
        $doc->setFileExtension("html");
        $doc->setDocumentId("1");
        
        return [$doc];
    }
    
    private static function getDocumentExample($signerEmail, $signerName, $demoPath): string
    {
        $htmlMarkupFileName = "order_form.html";
        $htmlMarkup = file_get_contents($demoPath . $htmlMarkupFileName);
        $htmlWithData = str_replace(
            [
                "{signer_name}",
                "{signer_email}"
            ],
            [
                $signerName,
                $signerEmail
            ],
            $htmlMarkup
        );
        return $htmlWithData;
    }
    
    private static function getSigners($signerEmail, $signerName)
    {
        $signer = new Signer();
        $signer->setClientUserId("1000");
        $signer->setEmail($signerEmail);
        $signer->setName($signerName);
        $signer->setRecipientId("2");
        $signer->setRoutingOrder("1");
        $signer->setNotaryId("1");
        
        $signHere = new SignHere();
        $signHere->setDocumentId("1");
        $signHere->setXPosition("200");
        $signHere->setYPosition("235");
        $signHere->setPageNumber("1");

        $signHereWithStamp = new SignHere();
        $signHereWithStamp->setDocumentId("1");
        $signHereWithStamp->setXPosition("200");
        $signHereWithStamp->setYPosition("150");
        $signHereWithStamp->setPageNumber("1");
        $signHereWithStamp->setStampType("stamp");
        
        $tabs = new Tabs();
        $tabs->setSignHereTabs([$signHere, $signHereWithStamp]);
        $signer->setTabs($tabs);
        
        return [$signer];
    }
    
    private static function getNotaryRecipients(): array
    {
        $notarySeal = new NotarySeal();
        $notarySeal->setXPosition("300");
        $notarySeal->setYPosition("235");
        $notarySeal->setDocumentId("1");
        $notarySeal->setPageNumber("1");

        $signHere = new SignHere();
        $signHere->setDocumentId("1");
        $signHere->setXPosition("300");
        $signHere->setYPosition("150");
        $signHere->setPageNumber("1");

        $tabs = new Tabs();
        $tabs->setSignHereTabs([$signHere]);
        $tabs->setNotarySealTabs([$notarySeal]);
        
        $notaryRecipient = new NotaryRecipient();
        $notaryRecipient->setEmail("");
        $notaryRecipient->setName("Notary");
        $notaryRecipient->setRecipientId("1");
        $notaryRecipient->setRoutingOrder("1");
        $notaryRecipient->setNotaryType("remote");
        $notaryRecipient->setNotarySourceType("thirdparty");
        $notaryRecipient->setNotaryThirdPartyPartner("onenotary");
        $notaryRecipient->setTabs($tabs);

        $signatureProvider = new RecipientSignatureProvider();
        $signatureProvider->setSignatureProviderName("ds_authority_idv");
        $signatureProvider->setSignatureProviderOptions(new RecipientSignatureProviderOptions());
        
        $notaryRecipient->setRecipientSignatureProviders([$signatureProvider]);

        return [$notaryRecipient];
    }
    #ds-snippet-end:Notary4Step3
}
