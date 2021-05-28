<?php
/**
 * Example 010: Send binary docs with multipart mime: Remote signer, cc; the envelope has three documents
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use GuzzleHttp\Exception\GuzzleException;

class EG010SendBinaryDocs extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg010";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     * @throws GuzzleException
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = $this->worker($this->args);

            if ($results) {
                $_SESSION["envelope_id"] = $results["envelope_id"]; # Save for use by other examples
                                                                    # which need an envelope_id
                $this->clientService->showDoneTemplate(
                    "Envelope sent",
                    "Envelope sent",
                    "The envelope has been created and sent!<br/>
                        Envelope ID {$results["envelope_id"]}."
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }


    /**
     * Do the work of the example
     * This function does the work of creating the envelope by using
     * the API directly with multipart mime
     *
     * @param $args
     * @return array ['redirect_url']
     * @throws GuzzleException
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker($args): array
    {
        $envelope_args = $args["envelope_args"];
        # 1. Create the envelope JSON request object
        $envelope_JSON = $this->make_envelope_JSON($envelope_args);

        # 2. Gather documents and their headers
        # Read files 2 and 3 from a local directory
        # The reads could raise an exception if the file is not available!
        # Note: the files are not binary encoded!
        $doc2_docx_bytes = file_get_contents(self::DEMO_DOCS_PATH . $GLOBALS['DS_CONFIG']['doc_docx']);
        $doc3_pdf_bytes = file_get_contents(self::DEMO_DOCS_PATH . $GLOBALS['DS_CONFIG']['doc_pdf']);

        $documents = [
            ['mime' => "text/html", 'filename' => $envelope_JSON['documents'][0]['name'],
             'document_id' => $envelope_JSON['documents'][0]['documentId'],
             'bytes' => $this->clientService->createDocumentForEnvelope($envelope_args)],
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

        # Step 2. call Envelopes::create API method
        # using Guzzle https://guzzle.readthedocs.io/en/latest/index.html
        $client = new \GuzzleHttp\Client();
        $uri = "{$args['base_path']}/v2.1/accounts/{$args['account_id']}/envelopes";
        $results = $client->request('POST', $uri, [
            'headers' => [
                'Authorization' => "bearer {$args['ds_access_token']}",
                'Accept' => 'application/json',
                'Content-Type' => "multipart/form-data; boundary={$boundary}"
            ],
            'body' => $req_body
        ]);
        $json_results = json_decode((string)$results->getBody());

        return ['envelope_id' => $json_results->envelope_id];
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
     * @return mixed {Envelope} An envelope definition
     */
    private function make_envelope_JSON(array $args)
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
    # ***DS.snippet.0.end

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $signer_name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name' ]);
        $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email']);
        $cc_name      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name'     ]);
        $cc_email     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email'    ]);
        $envelope_args = [
            'signer_email' => $signer_email,
            'signer_name' => $signer_name,
            'cc_email' => $cc_email,
            'cc_name' => $cc_name,
        ];
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];

        return $args;
    }
}

