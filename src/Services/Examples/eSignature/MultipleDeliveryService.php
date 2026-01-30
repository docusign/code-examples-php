<?php

namespace DocuSign\Services\Examples\eSignature;

use DateTime;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\RecipientPhoneNumber;
use DocuSign\eSign\Model\RecipientAdditionalNotification;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;

class MultipleDeliveryService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object to send via multiple
     * delivery channels (Email and SMS/WhatsApp).
     * 2. Send the envelope
     *
     * @param  $args array
     * @param $clientService
     * @param $demoDocsPath
     * @return array ['envelope_id']
     */
    
    #ds-snippet-start:eSign46Step3
    public static function multipleDelivery(
        array $args,
        $clientService,
        $demoDocsPath,
        $docDocx,
        $docPDF
    ): array {
        $envelopeDefinition = self::makeEnvelope(
            $args["envelope_args"],
            $clientService,
            $demoDocsPath,
            $docDocx,
            $docPDF
        );

        $envelopeApi = $clientService->getEnvelopeApi();

        $envelopeResponse = $envelopeApi->createEnvelopeWithHttpInfo($args['account_id'], $envelopeDefinition);

        $remaining = $envelopeResponse[2]['X-RateLimit-Remaining'] ?? null;
        $reset =  $envelopeResponse[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }

        return ['envelope_id' => $envelopeResponse[0]->getEnvelopeId()];
    }
    #ds-snippet-end:eSign46Step3

    #ds-snippet-start:eSign46Step2
    private static function makeEnvelope(
        array $args,
        $clientService,
        $demoDocsPath,
        $docDocx,
        $docPDF
    ): EnvelopeDefinition {
        $envelopeDefinition = CreateAnEnvelopeFunctionService::makeEnvelope(
            $args,
            $clientService,
            $demoDocsPath,
            $docDocx,
            $docPDF
        );

        $signer = new Signer([
            'name' => $args['signer_name'],
            'email' => $args['signer_email'],
            'additional_notifications' => [
                self::buildAdditionalNotification(
                    $args['signer_country_code'],
                    $args['signer_phone_number'],
                    $args['deliveryMethod']
                )
            ],
            'recipient_id' => "1",
            'routing_order' => "1",
            'delivery_method' => "Email"
        ]);

        $cc = new CarbonCopy([
            'name' => $args['cc_name'],
            'email' => $args['cc_email'],
            'additional_notifications' => [
                self::buildAdditionalNotification(
                    $args['cc_country_code'],
                    $args['cc_phone_number'],
                    $args['deliveryMethod']
                )
            ],
            'recipient_id' => "2",
            'routing_order' => "2",
            'delivery_method' => "Email"
            ]);

        return self::addSignersToTheDelivery($signer, $cc, $envelopeDefinition, $args);
    }

    private static function addSignersToTheDelivery($signer, $cc, $envelopeDefinition, $args)
    {
        $signHere = new SignHere([
            'anchor_string' => '**signature_1**',
            'anchor_units' => 'pixels',
            'anchor_y_offset' => '10',
            'anchor_x_offset' => '20'
        ]);

        $signHere2 = new SignHere([
            'anchor_string' => '/sn1/',
            'anchor_units' =>  'pixels',
            'anchor_y_offset' => '10',
            'anchor_x_offset' => '20'
        ]);

        $signer->setTabs(new Tabs([
            'sign_here_tabs' => [$signHere, $signHere2]
        ]));

        $recipients = new Recipients([
            'signers' => [$signer],
            'carbon_copies' => [$cc]
        ]);

        $envelopeDefinition->setRecipients($recipients);
        $envelopeDefinition->setStatus($args["status"]);

        return $envelopeDefinition;
    }
    #ds-snippet-end:eSign46Step2

    private static function buildAdditionalNotification(
        string $countryCode,
        string $phoneNumber,
        string $deliveryMethod
    ): RecipientAdditionalNotification {
        $phone = new RecipientPhoneNumber([
            'country_code' => $countryCode,
            'number' => $phoneNumber
        ]);

        return new RecipientAdditionalNotification([
            'secondary_delivery_method' => $deliveryMethod,
            'phone_number' => $phone
        ]);
    }
}
