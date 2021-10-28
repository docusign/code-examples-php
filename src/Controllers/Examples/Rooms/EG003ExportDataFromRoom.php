<?php

namespace Example\Controllers\Examples\Rooms;

use Example\Controllers\RoomsApiBaseController;
use Example\Services\Examples\Rooms\ExportDataFromRoomService;

class EG003ExportDataFromRoom extends RoomsApiBaseController
{

    const EG = 'reg003'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $rooms = ExportDataFromRoomService::getRooms(
            $this->routerService,
            $this->clientService,
            $this->args,
            $this::EG
        );
        parent::controller(null, $rooms);
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return RoomFormFieldData for selected room
     *
     * @return void
     */
    function createController(): void
    {
        $this->checkDsToken();
        $results = ExportDataFromRoomService::exportDataFromRoom($this->args, $this->clientService);

        if ($results) {
            $results = json_decode((string)$results, true);
            $this->clientService->showDoneTemplate(
                "Field data associated with a room",
                "Field data associated with a room",
                "Results from the Rooms::GetRoomFieldData method",
                json_encode(json_encode($results))
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
            'room_id' => $this->checkInputValues($_POST['room_id'])
        ];
    }
}
