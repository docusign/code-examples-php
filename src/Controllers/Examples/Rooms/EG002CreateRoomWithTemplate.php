<?php

namespace DocuSign\Controllers\Examples\Rooms;

use DocuSign\Controllers\RoomsApiBaseController;
use DocuSign\Services\Examples\Rooms\CreateRoomWithTemplatesService;
use DocuSign\Services\ManifestService;

class EG002CreateRoomWithTemplate extends RoomsApiBaseController
{
    const EG = 'reg002'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $templates = CreateRoomWithTemplatesService::getRoomTemplates(
            $this->clientService,
            $this->args,
            $this->routerService,
            $this::EG
        );
        parent::controller($templates);
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return create room data
     *
     * @return void
     */
    protected function createController(): void
    {
        $this->checkDsToken();
        $room = CreateRoomWithTemplatesService::createRoomWithTemplates($this->args, $this->clientService);

        if ($room) {
            $room_name = $room['name'];
            $room_id = $room['room_id'];
            $room = json_decode((string)$room, true);
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                json_encode(json_encode($room)),
                ManifestService::replacePlaceholders(
                    "{1}",
                    $room_id,
                    ManifestService::replacePlaceholders("{0}", $room_name, $this->codeExampleText["ResultsPageText"])
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
            'room_name' => $this->checkInputValues($_POST['room_name']),
            'template_id' => $this->checkInputValues($_POST['template_id']),
        ];
    }
}
