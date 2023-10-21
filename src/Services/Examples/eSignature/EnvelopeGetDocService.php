<?php

namespace Example\Services\Examples\eSignature;

class EnvelopeGetDocService
{
    /**
     * Do the work of the example
     * Call the envelope documents list method
     *
     * @param  $args array
     * @param $clientService
     * @return array
     */
    public static function envelopeGetDoc(array $args, $clientService): array
    {
        # Call API method
        # Exceptions will be caught by the calling function
        #ds-snippet-start:eSign7Step3
        $envelope_api = $clientService->getEnvelopeApi();

        # An SplFileObject is returned. See http://php.net/manual/en/class.splfileobject.php
        $temp_file = $envelope_api->getDocument($args['account_id'], $args['document_id'], $args['envelope_id']);
        #ds-snippet-end:eSign7Step3
        # find the matching document information item
        $doc_item = false;
        foreach ($args['envelope_documents']['documents'] as $item) {
            if ($item['document_id'] ==  $args['document_id']) {
                $doc_item = $item;
                break;
            }
        }
        $doc_name = $doc_item['name'];
        $has_pdf_suffix = strtoupper(substr($doc_name, -4)) == '.PDF';
        $pdf_file = $has_pdf_suffix;
        # Add ".pdf" if it's a content or summary doc and doesn't already end in .pdf
        if ($doc_item["type"] == "content" || ($doc_item["type"] == "summary" && ! $has_pdf_suffix)) {
            $doc_name .= ".pdf";
            $pdf_file = true;
        }
        # Add .zip as appropriate
        if ($doc_item["type"] == "zip") {
            $doc_name .= ".zip";
        }
        # Return the file information
        if ($pdf_file) {
            $mimetype = 'application/pdf';
        } elseif ($doc_item["type"] == 'zip') {
            $mimetype = 'application/zip';
        } else {
            $mimetype = 'application/octet-stream';
        }

        return ['mimetype' => $mimetype, 'doc_name' => $doc_name, 'data' => $temp_file];
    }
}
