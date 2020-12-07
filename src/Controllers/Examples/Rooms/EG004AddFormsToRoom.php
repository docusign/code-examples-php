<?php


namespace Example\Controllers\Examples\Rooms;


use DocuSign\Rooms\Client\ApiException;
use DocuSign\Rooms\Model\FormForAdd;
use Example\Services\RoomsApiClientService;
use Example\Services\RouterService;

class EG004AddFormsToRoom extends \Example\Controllers\RoomsApiBaseController
{

    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "reg004";  # reference (and url) for this example
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
        $libraries = $this->getFormLibraries();
        $forms = null;
        if (count($libraries)) {
            $forms = $this->getForms($libraries[0]['forms_library_id']);
        }
        parent::controller($this->eg, $this->routerService, basename(__FILE__), null, $rooms, $forms);
    }
    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return RoomFormFieldData for selected room
     *
     * @return void
     */
    function createController()
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = $this->worker($this->args);

            if ($results) {
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Add a form to a room",
                    "The DocuSign Form was successfully added to the room",
                    "Results from the Rooms::AddFormToRoom method",
                    json_encode(json_encode($results))
                );
            }

        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
    /**
     * 1. Create FormForAdd for selected form
     * 2. Add created FormForAdd to selected room
     * 3. Return RoomDocument
     *
     * @param  $args array
     * @return \DocuSign\Rooms\Model\RoomDocument
     */
    public function worker($args) {
        $rooms_api = $this->clientService->getRoomsApi();

        try{
            $form_for_add = new FormForAdd($args);
            $response = $rooms_api->addFormToRoom($args['room_id'], $args["account_id"], $form_for_add);
        }  catch (ApiException $e) {
            error_log($e);
            $this->clientService->showErrorTemplate($e);
            exit;
        }
        return $response;
    }
    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $room_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['room_id']);
        $form_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['form_id']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'room_id' => $room_id,
            'form_id' => $form_id
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
            try{
                $rooms = $this->clientService->getRooms($this->args);
            }  catch (ApiException $e) {
                return [];
            }
            return $rooms;
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
    /**
     * Get Form Libraries
     *
     * @return array
     */
    private function getFormLibraries():array
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $rooms = $this->clientService->getFormLibraries($this->args);
            return $rooms;
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
    /**
     * Get available Forms
     *
     * @return array
     */
    private function getForms($libraryID):array
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $forms = $this->clientService->getFormLibraryForms($libraryID, $this->args['account_id']);
            return $forms;
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
}