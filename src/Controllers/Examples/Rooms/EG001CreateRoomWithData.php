<?php


namespace Example\Controllers\Examples\Rooms;


use Example\Services\RoomsApiClientService;
use Example\Services\RouterService;
use DocuSign\Rooms\Model\RoomForCreate;
use DocuSign\Rooms\Model\FieldDataForCreate;
use DocuSign\Rooms\Client\ApiException;

class EG001CreateRoomWithData extends \Example\Controllers\RoomsApiBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "reg001";  # reference (and url) for this example
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
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
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
                    "Creating a room with data",
                    "Creating a room with data",
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
     * 3. Submit newly created room using SDK.
     *
     * @param  $args array
     * @return \DocuSign\Rooms\Model\Room
     */
    public function worker(array $args)
    {
        # Step 1. Get avaiable roles
        $roles = $this->clientService->getRoles($this->args);

        $admin_roles = array_values(array_filter($roles, function ($r) { return $r['is_default_for_admin'] === true; }));
        $role_id = $admin_roles[0]['role_id'];
        # Step 2. Create RoomForCreate object
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

        # Step 3. Post new room using SDK
        $response = $this->clientService->createRoom($this->args, $room);
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
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'room_name' => $room_name
        ];
    }
}
