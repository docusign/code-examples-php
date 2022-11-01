<?php

namespace Example\Controllers\Examples\Rooms;

use Example\Controllers\RoomsApiBaseController;
use Example\Services\Examples\Rooms\GetRoomsWithFiltersService;
use Example\Services\ManifestService;

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
        $roomSummaryList = GetRoomsWithFiltersService::getRoomsWithFilter($this->args, $this->clientService);

        if ($roomSummaryList) {
            $start_date = $this->args['start_date'];
            $end_date = $this->args['end_date'];
            $roomSummaryList = json_decode((string)$roomSummaryList, true);
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                json_encode(json_encode($roomSummaryList)),
                ManifestService::replacePlaceholders(
                    "{1}", 
                    $end_date, 
                    ManifestService::replacePlaceholders("{0}", "$start_date", $this->codeExampleText["ExampleName"])
                )
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
