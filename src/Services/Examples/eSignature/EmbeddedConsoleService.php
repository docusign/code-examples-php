<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Model\ConsoleViewRequest;

class EmbeddedConsoleService
{
    /**
     * Do the work of the example
     * Set the url where you want the recipient to go once they are done
     * with the NDSE. It is usually the case that the
     * user will never "finish" with the NDSE.
     * Assume that control will not be passed back to your app.
     *
     * @param  $args array
     * @param $clientService
     * @return array ['redirect_url']
     */
    # ***DS.snippet.0.start
    public static function embeddedConsole(array $args, $clientService): array
    {
        # Step 1. Create the NDSE view request object
        # Exceptions will be caught by the calling function
        $view_request = new ConsoleViewRequest(['return_url' => $args['ds_return_url']]);
        if ($args['starting_view'] == "envelope" && $args['envelope_id']) {
            $view_request->setEnvelopeId($args['envelope_id']);
        }

        # 2. Call the API method
        $envelope_api = $clientService->getEnvelopeApi();
        $results = $envelope_api->createConsoleView($args['account_id'], $view_request);
        $url = $results['url'];

        return ['redirect_url' =>  $url];
    }
    # ***DS.snippet.0.end
}
