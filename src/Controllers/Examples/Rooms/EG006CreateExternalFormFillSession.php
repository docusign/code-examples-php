<?php


namespace Example\Controllers\Examples\Rooms;


use DocuSign\Rooms\Client\ApiException;
use DocuSign\Rooms\Model\ExternalFormFillSessionForCreate;
use Example\Services\RoomsApiClientService;
use Example\Services\RouterService;

class EG006CreateExternalFormFillSession extends \Example\Controllers\RoomsApiBaseController
{

    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "reg006";  # reference (and url) for this example
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
     * 2. Render new form if room were selected
     * 3.
     * 4. Return RoomSummaryList for specified time period
     *
     * @return void
     */
    function createController()
    {
        $minimum_buffer_min = 3;
        $room_id = $this->args['room_id'];
        $form_id = $this->args['form_id'];
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            if ($room_id && !$form_id) {
                $room = $this->getRoom($room_id);
                $room_documents = $this->getDocuments($room_id);
                $room_name = $room['name'];
                $room_forms = array_values(
                    array_filter($room_documents, function($f) { return $f['docu_sign_form_id']; })
                );

                $GLOBALS['twig']->display($this->routerService->getTemplate($this->eg), [
                    'title' => $this->routerService->getTitle($this->eg),
                    'forms' => $room_forms,
                    'room_id' => $room_id,
                    'room_name' => $room_name,
                    'source_file' => basename(__FILE__),
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . basename(__FILE__),
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $this->eg,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                ]);
            }
            else {
                $results = $this->worker($this->args);

                if ($results) {
                    $results = json_decode((string)$results, true);
                    $this->clientService->showDoneTemplate(
                        "Create an external form fill session",
                        "Create an external form fill session",
                        "Results of Rooms::createExternalFormFillSession",
                        json_encode(json_encode($results))
                    );
                }
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    public function worker($args) {
        $form_session_api = $this->clientService->getExternalFormFillSessionsApi();
        try{
            $form_for_add = new ExternalFormFillSessionForCreate($args);
            $response = $form_session_api->createExternalFormFillSession($args["account_id"], $form_for_add);
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
        $room_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['room_name']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'room_id' => $room_id,
            'form_id' => $form_id,
            'room_name' => $room_name,
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
    /**
     * Get Room details
     *
     * @return \DocuSign\Rooms\Model\Room
     */
    private function getRoom($room_id)
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $room = $this->clientService->getRoom($room_id, $this->args['account_id']);
            return $room;
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
    /**
     * Get form documents
     *
     * @return array
     */
    private function getDocuments($room_id):array
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $documents = $this->clientService->getDocuments($room_id, $this->args['account_id']);
            return $documents;
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
}
