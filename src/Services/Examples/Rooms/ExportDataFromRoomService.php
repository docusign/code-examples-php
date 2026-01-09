<?php

namespace DocuSign\Services\Examples\Rooms;

use DateTime;
use DocuSign\Rooms\Client\ApiException;
use DocuSign\Rooms\Model\FieldData;

class ExportDataFromRoomService
{
    /**
     * 1. Get RoomFieldData for selected room
     *
     * @param  $args array
     * @param $clientService
     * @return FieldData
     */
    public static function exportDataFromRoom(array $args, $clientService): FieldData
    {
        #ds-snippet-start:Rooms3Step3
        $rooms_api = $clientService->getRoomsApi();
        try {
            $room_details = $rooms_api->getRoomFieldDataWithHttpInfo($args['room_id'], $args["account_id"]);

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
        #ds-snippet-end:Rooms3Step3
        return $room_details[0];
    }

    /**
     * Get available Rooms
     *
     * @param $routerService
     * @param $clientService
     * @param $args
     * @param $eg
     * @return array
     */
    public static function getRooms($routerService, $clientService, $args, $eg): array
    {
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            return $clientService->getRooms($args);
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
