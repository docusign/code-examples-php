<?php


namespace Example\Controllers\Examples\Rooms;


use DocuSign\Rooms\Api\RoomsApi\GetRoomsOptions;
use DocuSign\Rooms\Client\ApiException;
use Example\Services\RoomsApiClientService;
use Example\Services\RouterService;

class EG005GetRoomsWithFilters extends \Example\Controllers\RoomsApiBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "reg005";  # reference (and url) for this example
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new RoomsApiClientService($this->args);
        $this->routerService = new RouterService();
        $rooms = $this->getRooms();
        parent::controller($this->eg, $this->routerService, basename(__FILE__), null, $rooms);
    }
    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return RoomSummaryList for specified time period
     *
     * @return void
     */
    function createController()
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = $this->worker($this->args);

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

        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
    /**
     * 1. Create RoomsOptions object
     * 2. Set Start date and End date for RoomsOptions
     * 3. Get Room using specific options
     *
     * @param  $args array
     * @return \DocuSign\Rooms\Model\RoomSummaryList
     */
    public function worker($args) {
        $rooms_api = $this->clientService->getRoomsApi();

        try{
            $options = new GetRoomsOptions();
            $options->setFieldDataChangedStartDate($args['start_date']);
            $options->setFieldDataChangedEndDate($args['end_date']);
            $rooms = $rooms_api->getRooms($args['account_id'], $options);
        }  catch (ApiException $e) {
            error_log($e);
            $this->clientService->showErrorTemplate($e);
            exit;
        }
        return $rooms;
    }
    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $start_date = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['start_date']);
        $end_date = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['end_date']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
    }
    /**
     * Get available Rooms
     *
     * @return array
     */
    private function getRooms():array
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $rooms = $this->clientService->getRooms($this->args);
            return $rooms;
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
}