<?php

namespace Example\Services\Examples\Rooms;

use DocuSign\Rooms\Model\FieldDataForCreate;
use DocuSign\Rooms\Model\Room;
use DocuSign\Rooms\Model\RoomForCreate;

class CreateRoomsWithDataService
{
    /**
     * 1. Get available roles
     * 2. Create room object
     * 3. Submit newly created room using SDK.
     *
     * @param  $args array
     * @param $clientService
     * @return Room
     */
    public static function createRoomsWithData(array $args, $clientService): Room
    {
        # Step 1. Get avaiable roles
        $roles = $clientService->getRoles($args);

        $admin_roles = array_values(array_filter($roles, function ($r) {
            return $r['is_default_for_admin'] === true;
        }));
        $role_id = $admin_roles[0]['role_id'];
        # Step 2. Create RoomForCreate object
        #ds-snippet-start:Rooms1Step3
        $room = new RoomForCreate(
            [
                'name'                => $args["room_name"],
                'role_id'             => $role_id,
                'transaction_side_id' => "listbuy",
                'field_data'          => new FieldDataForCreate(
                    ['data' => [
                        'address1'          => '111',
                        'address2'          => 'unit 10',
                        'city'              => 'Galaxian',
                        'state'             => 'US-HI',
                        'postalCode'        => '88888',
                        'companyRoomStatus' => '5',
                        'comments'          => 'Lorem ipsum dolor sit amet, consectetur adipiscin',
                    ]
                    ]
                )
            ]
        );
        #ds-snippet-end:Rooms1Step3

        # Step 3. Post new room using SDK
        #ds-snippet-start:Rooms1Step4
        return $clientService->createRoom($args, $room);
        #ds-snippet-end:Rooms1Step4
    }
}
