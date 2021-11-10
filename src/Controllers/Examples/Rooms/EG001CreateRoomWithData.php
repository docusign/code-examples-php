<?php

namespace Example\Controllers\Examples\Rooms;

use Example\Controllers\RoomsApiBaseController;
use Example\Services\Examples\Rooms\CreateRoomsWithDataService;

class EG001CreateRoomWithData extends RoomsApiBaseController
{
    const EG = 'reg001'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        parent::controller();
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return create room data
     *
     * @return void
     */
    function createController(): void
    {
        $this->checkDsToken();
        $room = CreateRoomsWithDataService::createRoomsWithData($this->args, $this->clientService);

        if ($room) {
            $room_name = $room['name'];
            $room_id = $room['room_id'];
            $room = json_decode((string)$room, true);
            $this->clientService->showDoneTemplate(
                "Creating a room with data",
                "Creating a room with data",
                "Room $room_name has been created!<BR>Room ID: $room_id",
                json_encode(json_encode($room))
            );
        }
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'room_name' => $this->checkInputValues($_POST['room_name'])
        ];
    }
}
