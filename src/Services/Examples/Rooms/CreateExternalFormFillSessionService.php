<?php

namespace DocuSign\Services\Examples\Rooms;

use DateTime;
use DocuSign\Rooms\Client\ApiException;
use DocuSign\Rooms\Model\ExternalFormFillSessionForCreate;
use DocuSign\Rooms\Model\Room;

class CreateExternalFormFillSessionService
{
    #ds-snippet-start:Rooms6Step4
    public static function createExternalFormFillSession($args, $clientService)
    {
        $form_session_api = $clientService->getExternalFormFillSessionsApi();
        try {
            $form_for_add = new ExternalFormFillSessionForCreate($args);
            $response = $form_session_api->createExternalFormFillSessionWithHttpInfo($args["account_id"], $form_for_add);

            $remaining = $response[2]['x-ratelimit-remaining'] ?? null;
            $reset = $response[2]['x-ratelimit-reset'] ?? null;

            if ($remaining !== null && $reset !== null) {
                $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                error_log("API calls remaining: $remaining");
                error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
            }
        } catch (ApiException $e) {
            error_log($e);
            $clientService->showErrorTemplate($e);
            exit;
        }
        return $response[0];
    }
    #ds-snippet-end:Rooms6Step4

    /**
     * Get available Rooms
     *
     * @param $clientService
     * @param $routerService
     * @param $args
     * @param $eg
     * @return array
     */
    public static function getRooms($clientService, $routerService, $args, $eg): array
    {
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            return $clientService->getRooms($args);
        } else {
            $clientService->needToReAuth($eg);
        }
    }

    /**
     * Get Room details
     *
     * @param $room_id
     * @param $routerService
     * @param $clientService
     * @param $args
     * @param $eg
     * @return Room
     */
    public static function getRoom($room_id, $routerService, $clientService, $args, $eg): Room
    {
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            return $clientService->getRoom($room_id, $args['account_id']);
        } else {
            $clientService->needToReAuth($eg);
        }
    }

    /**
     * Get form documents
     *
     * @param $room_id
     * @param $routerService
     * @param $clientService
     * @param $args
     * @param $eg
     * @return array
     */
    public static function getDocuments($room_id, $routerService, $clientService, $args, $eg): array
    {
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            return $clientService->getDocuments($room_id, $args['account_id']);
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
