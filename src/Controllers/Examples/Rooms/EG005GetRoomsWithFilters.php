<?php

namespace Example\Controllers\Examples\Rooms;

use Example\Controllers\RoomsApiBaseController;
use Example\Services\Examples\Rooms\GetRoomsWithFiltersService;

class EG005GetRoomsWithFilters extends RoomsApiBaseController
{

    const EG = 'reg005'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $rooms = GetRoomsWithFiltersService::getRooms(
            $this->routerService,
            $this->args,
            $this->clientService,
            $this::EG
        );
        parent::controller(null, $rooms);
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return RoomSummaryList for specified time period
     *
     * @return void
     */
    function createController(): void
    {
        $this->checkDsToken();
        $results = GetRoomsWithFiltersService::getRoomsWithFilter($this->args, $this->clientService);

        if ($results) {
            $start_date = $this->args['start_date'];
            $end_date = $this->args['end_date'];
            $results = json_decode((string)$results, true);
            $this->clientService->showDoneTemplate(
                "Rooms filtered by date",
                "Rooms that have had their field data, updated within the time period between $start_date and $end_date",
                "Results from the Rooms::GetRooms methods",
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
            'start_date' => $this->checkInputValues($_POST['start_date']),
            'end_date' => $this->checkInputValues($_POST['end_date'])
        ];
    }
}
