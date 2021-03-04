<?php


namespace Example\Services;
use DocuSign\Rooms\Api\ExternalFormFillSessionsApi;
use DocuSign\Rooms\Api\FormGroupsApi;
use DocuSign\Rooms\Api\OfficesApi;
use DocuSign\Rooms\Client\ApiException;
use DocuSign\Rooms\Api\RolesApi;
use DocuSign\Rooms\Api\RoomsApi;
use DocuSign\Rooms\Client\ApiClient;
use DocuSign\Rooms\Api\RoomTemplatesApi;
use DocuSign\Rooms\Api\FormLibrariesApi;
use DocuSign\Rooms\Configuration;
use DocuSign\Rooms\Model\FormGroup;
use DocuSign\Rooms\Model\FormGroupForCreate;
use \DocuSign\Rooms\Model\Room;

class RoomsApiClientService
{
    /**
     * DocuSign API Client
     */
    public $apiClient;

    /**
     * Router Service
     */
    public $routerService;

    /**
     * Create a new controller instance.
     *
     * @param $args
     * @return void
     */
    public function __construct($args)
    {
        # Step 2 start
        # Exceptions will be caught by the calling function
        $config = new Configuration();
        $config->setHost('https://demo.rooms.docusign.com/restapi');
        $config->addDefaultHeader('Authorization', 'Bearer ' . $args['ds_access_token']);
        $this->apiClient = new ApiClient($config);
        # Step 2 end        
        $this->routerService = new RouterService();
    }
    /**
     * Getter for the RolesApi
     */
    public function getRolesApi(): RolesApi
    {
        return new RolesApi($this->apiClient);
    }

    /**
     * Getter for the FormGroupsApi
     */
    public function getFromGroupsApi(): FormGroupsApi
    {
        return new FormGroupsApi($this->apiClient);
    }

    /**
     * Getter for the OfficesApi
     */
    public function getOfficesApi(): OfficesApi
    {
        return new OfficesApi($this->apiClient);
    }

    /**
     * Getter for the RoomsApi
     */
    public function getRoomsApi(): RoomsApi
    {
        return new RoomsApi($this->apiClient);
    }

    /**
     * Getter for the RoomTemplatesApi
     */
    public function getRoomTemplatesApi(): RoomTemplatesApi
    {
        return new RoomTemplatesApi($this->apiClient);
    }

    /**
     * Getter for the FormLibrariesApi
     */
    public function getFormLibrariesApi(): FormLibrariesApi
    {
        return new FormLibrariesApi($this->apiClient);
    }

    public function getExternalFormFillSessionsApi(): ExternalFormFillSessionsApi
    {
        return new ExternalFormFillSessionsApi($this->apiClient);
    }

    /**
     * Redirect user to the auth page
     *
     * @param $eg
     * @return void
     */
    public function needToReAuth($eg): void
    {
        $this->routerService->flash('Sorry, you need to re-authenticate.');
        # We could store the parameters of the requested operation
        # so it could be restarted automatically.
        # But since it should be rare to have a token issue here,
        # we'll make the user re-enter the form data after
        # authentication.
        $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $eg;
        header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
        exit;
    }
    /**
     * Redirect user to the error page
     *
     * @param  ApiException $e
     * @return void
     */
    public function showErrorTemplate(ApiException $e): void
    {
        $body = $e->getResponseBody();
        echo json_encode($body);
        $GLOBALS['twig']->display('error.html', [
                'error_code' => $body->errorCode ?? unserialize($body)->errorCode,
                'error_message' => $body->message ?? unserialize($body)->message]
        );
    }

    /**
     * Redirect user to the error page
     *
     * @param $title string
     * @param $headline string
     * @param $message string
     * @param $results
     * @return void
     */
    public function showDoneTemplate($title, $headline, $message, $results = null): void
    {
        $GLOBALS['twig']->display('example_done.html', [
            'title' => $title,
            'h1' => $headline,
            'message' => $message,
            'json' => $results
        ]);
        exit;
    }
    /**
     * @param $args
     * @return array
     */
    public function getRoles($args): array
    {
        $roles_api = $this->getRolesApi();
        try{
            $roles = $roles_api->getRoles($args["account_id"]);
        }  catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }
        return $roles['roles'];
    }

    /**
     * @param $args
     * @param $room
     * @return Room
     */
    public function createRoom($args, $room): Room
    {
        $rooms_api = $this->getRoomsApi();
        try {
            $response = $rooms_api->createRoom($args['account_id'], $room);
        }  catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }
        return $response;
    }
    /**
     * Get list of available rooms
     *
     * @param $args
     * @return array $rooms
     */
    public function getRooms($args): array
    {
        $rooms_api = $this->getRoomsApi();
        try {
            $rooms = $rooms_api->getRooms($args['account_id']);
        } catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }
        return $rooms['rooms'];
    }
    /**
     * Get details for specific room
     *
     * @param $room_id
     * @param $account_id
     * @return Room
     */
    public function getRoom($room_id, $account_id): Room {
        $rooms_api = $this->getRoomsApi();
        try {
            $room = $rooms_api->getRoom($room_id, $account_id);
        } catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }
        return $room;
    }
    /**
     * Get available Form Libraries
     *
     * @param $args
     * @return array $forms_library_summaries
     */
    public function getFormLibraries($args): array {
        $form_libraries_api = $this->getFormLibrariesApi();
        try {
            $form_libraries = $form_libraries_api->getFormLibraries($args['account_id']);
        } catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }
        return $form_libraries['forms_library_summaries'];
    }

    /**
     * Get available forms in specific form library
     *
     * @param $forms_library_id
     * @param $account_id
     * @return array $forms
     */
    public function getFormLibraryForms($forms_library_id, $account_id): array {
        $form_libraries_api = $this->getFormLibrariesApi();
        try {
            $forms = $form_libraries_api->getFormLibraryForms($forms_library_id, $account_id);
        } catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }
        return $forms['forms'];
    }
    /**
     * Get available document for specific room
     *
     * @param $room_id
     * @param $account_id
     * @return array $documents
     */
    public function getDocuments($room_id, $account_id): array {
        $rooms_api = $this->getRoomsApi();
        try {
            $documents = $rooms_api->getDocuments($room_id, $account_id);
        } catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }
        return $documents['documents'];
    }

    /**
     * Create from groups for specific account
     *
     * @param $account_id
     * @param FormGroupForCreate $formGroup
     * @return FormGroup
     */
    public function createFormGroup(string $account_id, FormGroupForCreate $formGroup): FormGroup
    {
        $form_groups_api = $this->getFromGroupsApi();
        try {
            $response = $form_groups_api->createFormGroup($account_id, $formGroup);
        } catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }
        return $response;
    }

    /**
     * Get from groups for specific account
     *
     * @param $account_id
     * @return array $form_groups
     */
    public function getFormGroups($account_id): array {
        $form_groups_api = $this->getFromGroupsApi();
        try {
            $form_groups = $form_groups_api->getFormGroups($account_id);
        } catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }
        return $form_groups['form_groups'];
    }

    /**
     * Get offices for specific account
     *
     * @param $account_id
     * @return array $offices
     */
    public function getOffices($account_id): array {
        $offices_api = $this->getOfficesApi();
        try {
            $offices = $offices_api->getOffices($account_id);
        } catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }
        return $offices['office_summaries'];
    }
}
