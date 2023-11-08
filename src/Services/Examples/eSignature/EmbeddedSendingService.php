<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Model\ReturnUrlRequest;

class EmbeddedSendingService
{
    /**
     * Do the work of the example
     * 1. Create the envelope with "created" (draft) status
     * 2. Send the envelope
     * 3. Get the SenderView url
     *
     * @param  $args array
     * @param $clientService
     * @param $demoDocsPath
     * @return array ['redirect_url']
     */
   
    public static function embeddedSending(array $args, $clientService, $demoDocsPath, $docxFile, $pdfFile): array
    {
        #ds-snippet-start:eSign11Step2
        # Create the envelope as a draft using eg002's worker
        # Exceptions will be caught by the calling function
        $args['envelope_args']['status'] = 'created';
        $demoDocsPath = SigningViaEmailService::signingViaEmail($args, $clientService, $demoDocsPath, $docxFile, $pdfFile);
        $envelope_id = $demoDocsPath['envelope_id'];
        #ds-snippet-end:eSign11Step2

        # Create sender view
        #ds-snippet-start:eSign11Step3
        $view_request = new ReturnUrlRequest(['return_url' => $args['ds_return_url']]);
        $envelope_api = $clientService->getEnvelopeApi();
        $senderView = $envelope_api->createSenderView($args['account_id'], $envelope_id, $view_request);

        # Switch to the Recipients / Documents view if requested by the user in the form
        $url = $senderView['url'];
        if ($args['starting_view'] == "recipient") {
            $url = str_replace('send=1', 'send=0', $url);
        }

        return ['envelope_id' => $envelope_id, 'redirect_url' =>  $url];
        #ds-snippet-end:eSign11Step3
    }
}
