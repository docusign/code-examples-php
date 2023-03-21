<?php

namespace Example\Services\Examples\Rooms;

use DocuSign\Rooms\Client\ApiException;
use DocuSign\Rooms\Model\ExternalFormFillSessionForCreate;
use DocuSign\Rooms\Model\Room;

class CreateExternalFormFillSessionService
{
    public static function createExternalFormFillSession($args, $clientService)
    {
        $form_session_api = $clientService->getExternalFormFillSessionsApi();
        try {
            $form_for_add = new ExternalFormFillSessionForCreate($args);
            $response = $form_session_api->createExternalFormFillSession($args["account_id"], $form_for_add);
        } catch (ApiException $e) {
            error_log($e);
            $clientService->showErrorTemplate($e);
            exit;
        }
        return $response;
    }

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
        $minimumBufferMin = 3;
        if ($routerService->ds_token_ok($minimumBufferMin)) {
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
        $minimumBufferMin = 3;
        if ($routerService->ds_token_ok($minimumBufferMin)) {
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
        $minimumBufferMin = 3;
        if ($routerService->ds_token_ok($minimumBufferMin)) {
            return $clientService->getDocuments($room_id, $args['account_id']);
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
