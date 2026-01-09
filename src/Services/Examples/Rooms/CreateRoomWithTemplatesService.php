<?php

namespace DocuSign\Services\Examples\Rooms;

use DateTime;
use DocuSign\Rooms\Client\ApiException;
use DocuSign\Rooms\Model\FieldDataForCreate;
use DocuSign\Rooms\Model\Room;
use DocuSign\Rooms\Model\RoomForCreate;

class CreateRoomWithTemplatesService
{
    /**
     * 1. Get available roles
     * 2. Create room object
     * 3. Submit newly created room based on selected template.
     *
     * @param  $args array
     * @param $clientService
     * @return Room
     */
    public static function createRoomWithTemplates(array $args, $clientService): Room
    {
        #Step 1. Create an API client with headers
        $rooms_api = $clientService->getRoomsApi();

        # Step 2. Get Default Admin role id
        $roles_api = $clientService->getRolesApi();

        try {
            $roles = $roles_api->getRolesWithHttpInfo($args["account_id"]);
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
        $role_id = $roles[0]['roles'][0]['role_id'];

        # Step 3. Create RoomForCreate object
        #ds-snippet-start:Rooms2Step4
        $room = new RoomForCreate(
            [
                'name'        => $args["room_name"],
                'role_id'     => $role_id,
                'template_id' => $args["template_id"],
                'field_data'  => new FieldDataForCreate(
                    ['data' =>
                        [
                            'address1'          => '111',
                            'city'              => 'Galaxian',
                            'state'             => 'US-HI',
                            'postalCode'        => '88888',
                        ]
                    ]
                )
            ]
        );
        #ds-snippet-end:Rooms2Step4

        # Step 4. Post the room using SDK
        #ds-snippet-start:Rooms2Step5
        try {
            $response = $rooms_api->createRoomWithHttpInfo($args['account_id'], $room);

            $remaining = $response[2]['x-ratelimit-remaining'] ?? null;
            $reset = $response[2]['x-ratelimit-reset'] ?? null;

            if ($remaining !== null && $reset !== null) {
                $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                error_log("API calls remaining: $remaining");
                error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
            }
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        #ds-snippet-end:Rooms2Step5
        return $response[0];
    }

    /**
     * Get existing template
     *
     * @param $clientService
     * @param $args
     * @param $routerService
     * @param $eg
     * @return array
     */
    public static function getRoomTemplates($clientService, $args, $routerService, $eg): array
    {
        #ds-snippet-start:Rooms2Step3
        $templates_api = $clientService->getRoomTemplatesApi();
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            try {
                $templates = $templates_api->getRoomTemplatesWithHttpInfo($args['account_id']);

                $remaining = $response[2]['x-ratelimit-remaining'] ?? null;
                $reset = $response[2]['x-ratelimit-reset'] ?? null;

                if ($remaining !== null && $reset !== null) {
                    $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                    error_log("API calls remaining: $remaining");
                    error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
                }
            } catch (ApiException $e) {
                return [];
            }
            return $templates[0]['room_templates'];
        } else {
            $clientService->needToReAuth($eg);
        }
        #ds-snippet-end:Rooms2Step3
    }
}
