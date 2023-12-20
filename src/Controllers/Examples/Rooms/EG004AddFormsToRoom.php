<?php

namespace DocuSign\Controllers\Examples\Rooms;

use DocuSign\Controllers\RoomsApiBaseController;
use DocuSign\Services\Examples\Rooms\AddFormsToRoomService;

class EG004AddFormsToRoom extends RoomsApiBaseController
{
    const EG = 'reg004'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $rooms = AddFormsToRoomService::getRooms($this->clientService, $this::EG, $this->args, $this->routerService);
        $libraries = AddFormsToRoomService::getFormLibraries(
            $this->args,
            $this->routerService,
            $this->clientService,
            $this::EG
        );
        $forms = null;
        if (count($libraries)) {
            $forms = AddFormsToRoomService::getForms(
                $libraries[10]['forms_library_id'],
                $this->routerService,
                $this->clientService,
                $this->args,
                $this::EG
            );
        }
        parent::controller(null, $rooms, $forms);
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return RoomFormFieldData for selected room
     *
     * @return void
     */
    protected function createController(): void
    {
        $this->checkDsToken();
        $roomsDocument = AddFormsToRoomService::addFormsToRoom($this->args, $this->clientService);

        if ($roomsDocument) {
            $roomsDocument = json_decode((string)$roomsDocument, true);
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                json_encode(json_encode($roomsDocument))
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
            'room_id' => $this->checkInputValues($_POST['room_id']),
            'form_id' => $this->checkInputValues($_POST['form_id'])
        ];
    }
}
