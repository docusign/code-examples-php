<?php

namespace Example\Services\Examples\Rooms;

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
            $roles = $roles_api->getRoles($args["account_id"]);
        } catch (ApiException $e) {
            error_log($e);
            $clientService->showErrorTemplate($e);
            exit;
        }
        $role_id = $roles['roles'][0]['role_id'];

        # Step 3. Create RoomForCreate object
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

        # Step 4. Post the room using SDK
        try {
            $response = $rooms_api->createRoom($args['account_id'], $room);
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        return $response;
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
        $templates_api = $clientService->getRoomTemplatesApi();
        if ($routerService->ds_token_ok($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            try {
                $templates = $templates_api->getRoomTemplates($args['account_id']);
            } catch (ApiException $e) {
                return [];
            }
            return $templates['room_templates'];
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
