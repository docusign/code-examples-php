<?php


namespace Example\Controllers\Examples\Rooms;


use Example\Services\RoomsApiClientService;
use Example\Services\RouterService;
use DocuSign\Rooms\Model\RoomForCreate;
use DocuSign\Rooms\Model\FieldDataForCreate;
use DocuSign\Rooms\Client\ApiException;

class EG002CreateRoomWithTemplate extends \Example\Controllers\RoomsApiBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "reg002";  # reference (and url) for this example
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
        $templates = $this->getRoomTemplates();
        parent::controller($this->eg, $this->routerService, basename(__FILE__), $templates);
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
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = $this->worker($this->args);

            if ($results) {
                $room_name =  $results['name'];
                $room_id = $results['room_id'];
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Creating a room with a template",
                    "Creating a room with a template",
                    "Room $room_name has been created!<BR>Room ID: $room_id",
                    json_encode(json_encode($results))
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }

    }

    /**
     * 1. Get available roles
     * 2. Create room object
     * 3. Submit newly created room based on selected template.
     *
     * @param  $args array
     * @return \DocuSign\Rooms\Model\Room
     */
    public function worker($args)
    {
        #Step 1. Create an API client with headers
        $rooms_api = $this->clientService->getRoomsApi();

        # Step 2. Get Default Admin role id
        $roles_api = $this->clientService->getRolesApi();

        try{
            $roles = $roles_api->getRoles($args["account_id"]);
        }  catch (ApiException $e) {
            error_log($e);
            $this->clientService->showErrorTemplate($e);
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
        }  catch (ApiException $e) {
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
        $room_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['room_name']);
        $template_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['template_id']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'room_name' => $room_name,
            'template_id' => $template_id,
        ];
    }

    /**
     * Get existing template
     *
     * @return array
     */
    private function getRoomTemplates():array
    {
        $templates_api = $this->clientService->getRoomTemplatesApi();
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            try{
                $templates = $templates_api->getRoomTemplates($this->args['account_id']);
            }  catch (ApiException $e) {
                return [];
            }
            return $templates['room_templates'];
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
}
