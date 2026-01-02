<?php

namespace DocuSign\Services\Examples\Rooms;

use DateTime;
use DocuSign\Rooms\Client\ApiException;
use DocuSign\Rooms\Model\FormForAdd;
use DocuSign\Rooms\Model\RoomDocument;

class AddFormsToRoomService
{
    /**
     * 1. Create FormForAdd for selected form
     * 2. Add created FormForAdd to selected room
     * 3. Return RoomDocument
     *
     * @param  $args array
     * @param $clientService
     * @return RoomDocument
     */
    public static function addFormsToRoom(array $args, $clientService): RoomDocument
    {
        $rooms_api = $clientService->getRoomsApi();

        try {
            #ds-snippet-start:Rooms4Step4
            $form_for_add = new FormForAdd($args);
            $response = $rooms_api->addFormToRoomWithHttpInfo($args['room_id'], $args["account_id"], $form_for_add);

            $remaining = $response[2]['x-ratelimit-remaining'] ?? null;
            $reset = $response[2]['x-ratelimit-reset'] ?? null;

            if ($remaining !== null && $reset !== null) {
                $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                error_log("API calls remaining: $remaining");
                error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
            }
            #ds-snippet-end:Rooms4Step4
        } catch (ApiException $e) {
            if ($e) {
                error_log($e);
            }
            $clientService->showErrorTemplate($e);
            exit;
        }
        return $response[0];
    }

    /**
     * Get available Rooms
     *
     * @param $clientService
     * @param $eg
     * @param $args
     * @param $routerService
     * @return array
     */
    public static function getRooms($clientService, $eg, $args, $routerService): array
    {
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            try {
                $rooms = $clientService->getRooms($args);
            } catch (ApiException $e) {
                return [];
            }
            return $rooms;
        } else {
            $clientService->needToReAuth($eg);
        }
    }

    /**
     * Get Form Libraries
     *
     * @param $args
     * @param $routerService
     * @param $clientService
     * @param $eg
     * @return array
     */
    public static function getFormLibraries($args, $routerService, $clientService, $eg): array
    {
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            return $clientService->getFormLibraries($args);
        } else {
            $clientService->needToReAuth($eg);
        }
    }

    /**
     * Get available Forms
     *
     * @param $libraryID
     * @param $routerService
     * @param $clientService
     * @param $args
     * @param $eg
     * @return array
     */
    public static function getForms($libraryID, $routerService, $clientService, $args, $eg): array
    {
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            return $clientService->getFormLibraryForms($libraryID, $args['account_id']);
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
