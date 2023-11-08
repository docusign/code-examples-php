<?php

namespace DocuSign\Services\Examples\eSignature;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

use function GuzzleHttp\json_decode;

class SendBinaryDocsService
{
    /**
     * Do the work of the example
     * This function does the work of creating the envelope by using
     * the API directly with multipart mime
     *
     * @param $args
     * @param $demoDocsPath
     * @param $clientService
     * @return array ['redirect_url']
     * @throws GuzzleException
     */
    #ds-snippet-start:eSign10Step3
    public static function sendBinaryDocs($args, $demoDocsPath, $clientService): array
    {
        $envelope_args = $args["envelope_args"];
        # 1. Create the envelope JSON request object
        $envelope_JSON = SendBinaryDocsService::makeEnvelopeJson($envelope_args);

        # 2. Gather documents and their headers
        # Read files 2 and 3 from a local directory
        # The reads could raise an exception if the file is not available!
        # Note: the files are not binary encoded!
        $doc2_docx_bytes = file_get_contents($demoDocsPath . $GLOBALS['DS_CONFIG']['doc_docx']);
        $doc3_pdf_bytes = file_get_contents($demoDocsPath . $GLOBALS['DS_CONFIG']['doc_pdf']);

        $documents = [
            ['mime' => "text/html", 'filename' => $envelope_JSON['documents'][0]['name'],
             'document_id' => $envelope_JSON['documents'][0]['documentId'],
             'bytes' => $clientService->createDocumentForEnvelope($envelope_args)],
            ['mime' => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
             'filename' => $envelope_JSON['documents'][1]['name'],
             'document_id' => $envelope_JSON['documents'][1]['documentId'],
             'bytes' => $doc2_docx_bytes],
            ['mime' => "application/pdf", 'filename' => $envelope_JSON['documents'][2]['name'],
             'document_id' => $envelope_JSON['documents'][2]['documentId'],
             'bytes' => $doc3_pdf_bytes]
        ];

        # Step 3. Create the multipart body
        $CRLF = "\r\n";
        $boundary = "multipartboundary_multipartboundary";
        $hyphens = "--";

        $req_body = ""
            . $hyphens . $boundary
            . $CRLF . "Content-Type: application/json"
            . $CRLF . "Content-Disposition: form-data"
            . $CRLF
            . $CRLF . json_encode($envelope_JSON, JSON_PRETTY_PRINT);

        # Loop to add the documents.
        # See section Multipart Form Requests on page
        # https://developers.docusign.com/esign-rest-api/guides/requests-and-responses
        foreach ($documents as $d) {
            $content_disposition = "Content-Disposition: file; filename=\"{$d['filename']}\";" .
                "documentid={$d['document_id']}";
            $req_body .= $CRLF . $hyphens . $boundary
                . $CRLF . "Content-Type: {$d['mime']}"
                . $CRLF . $content_disposition
                . $CRLF
                . $CRLF . $d['bytes'];
        }
        # Add closing $boundary
        $req_body .= $CRLF . $hyphens . $boundary . $hyphens . $CRLF;
        #ds-snippet-end:eSign10Step3

        # Step 2. call Envelopes::create API method
        # using Guzzle https://guzzle.readthedocs.io/en/latest/index.html
        #ds-snippet-start:eSign10Step4
        $client = new Client();
        $uri = "{$args['base_path']}/v2.1/accounts/{$args['account_id']}/envelopes";
        $responseInterface = $client->request('POST', $uri, [
            'headers' => [
                'Authorization' => "bearer {$args['ds_access_token']}",
                'Accept' => 'application/json',
                'Content-Type' => "multipart/form-data; boundary=" . $boundary
            ],
            'body' => $req_body
        ]);
        $responseInterfaceToJson = json_decode((string)$responseInterface->getBody());

        return ['envelope_id' => $responseInterfaceToJson->envelopeId];
        #ds-snippet-end:eSign10Step4
    }

    /**
     * Creates envelope definition JSON as an associative array
     * Document 1: An HTML document.
     * Document 2: A Word .docx document.
     * Document 3: A PDF document.
     * DocuSign will convert all of the documents to the PDF format.
     * The recipients' field tags are placed using <b>anchor</b> strings.
     *
     * @param  $args array
     * @return array {Envelope} An envelope definition
     */
    #ds-snippet-start:eSign10Step3
    public static function makeEnvelopeJson(array $args): array
    {
        # create the envelope definition
        $env_json = [];
        $env_json['emailSubject'] = 'Please sign this document set';

        # add the documents
        $doc1 = [
            'name' => 'Order acknowledgement', # can be different from actual file name
            'fileExtension' => 'html', # Source data format. Signed docs are always pdf.
            'documentId' => '1' ]; # a label used to reference the doc
        $doc2 = [
            'name' => 'Battle Plan', 'fileExtension' => 'docx', 'documentId' => '2'];
        $doc3 = [
            'name' => 'Lorem Ipsum', 'fileExtension' => 'pdf', 'documentId' => '3'];
        # The order in the docs array determines the order in the envelope
        $env_json['documents'] = [$doc1, $doc2, $doc3];

        # create a signer recipient to sign the document, identified by name and email
        $signer1 = [
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'recipientId' => '1', 'routingOrder' => '1'];
            # routingOrder (lower means earlier) determines the order of deliveries
            # to the recipients. Parallel routing order is supported by using the
            # same integer as the order for two or more recipients.

        # create a cc recipient to receive a copy of the documents, identified by name and email
        $cc1 = [
            'email' => $args['cc_email'], 'name' => $args['cc_name'],
            'routingOrder' => '2', 'recipientId' => '2'];

        # Create signHere fields (also known as tabs) on the documents,
        # We're using anchor (autoPlace) positioning
        #
        # The DocuSign platform searches throughout your envelope's
        # documents for matching anchor strings. So the
        # signHere2 tab will be used in both document 2 and 3 since they
        # use the same anchor string for their "signer 1" tabs.
        $sign_here1 = [
            'anchorString' => '**signature_1**', 'anchorYOffset' => '10', 'anchorUnits' => 'pixels',
            'anchorXOffset' => '20'];
        $sign_here2 = [
            'anchorString' => '/sn1/', 'anchorYOffset' => '10', 'anchorUnits' => 'pixels',
            'anchorXOffset' => '20'];

        # Tabs are set per recipient / signer
        $signer1_tabs = ['signHereTabs' => [$sign_here1, $sign_here2]];
        $signer1["tabs"] = $signer1_tabs;

        # Add the recipients to the envelope object
        $recipients = ['signers' => [$signer1], 'carbonCopies' => [$cc1]];
        $env_json['recipients'] = $recipients;

        # Request that the envelope be sent by setting |status| to "sent".
        # To request that the envelope be created as a draft, set to "created"
        $env_json['status'] = 'sent';

        return $env_json;
    }
    #ds-snippet-end:eSign10Step3
}
