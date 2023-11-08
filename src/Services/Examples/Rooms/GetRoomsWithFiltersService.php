<?php

namespace DocuSign\Services\Examples\Rooms;

use DocuSign\Rooms\Api\RoomsApi\GetRoomsOptions;
use DocuSign\Rooms\Client\ApiException;
use DocuSign\Rooms\Model\RoomSummaryList;

class GetRoomsWithFiltersService
{
    /**
     * 1. Create RoomsOptions object
     * 2. Set Start date and End date for RoomsOptions
     * 3. Get Room using specific options
     *
     * @param  $args array
     * @param $clientService
     * @return RoomSummaryList
     */
    public static function getRoomsWithFilter(array $args, $clientService): RoomSummaryList
    {
        #ds-snippet-start:Rooms5Step4
        $rooms_api = $clientService->getRoomsApi();
        #ds-snippet-end:Rooms5Step4

        try {
            #ds-snippet-start:Rooms5Step3
            $options = new GetRoomsOptions();
            $options->setFieldDataChangedStartDate($args['start_date']);
            $options->setFieldDataChangedEndDate($args['end_date']);
            #ds-snippet-end:Rooms5Step3
            #ds-snippet-start:Rooms5Step4
            $rooms = $rooms_api->getRooms($args['account_id'], $options);
            #ds-snippet-end:Rooms5Step4
        } catch (ApiException $e) {
            error_log($e);
            $clientService->showErrorTemplate($e);
            exit;
        }
        return $rooms;
    }

    /**
     * Get available Rooms
     *
     * @param $routerService
     * @param $args
     * @param $clientService
     * @param $eg
     * @return array
     */
    public static function getRooms($routerService, $args, $clientService, $eg): array
    {
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            return $clientService->getRooms($args);
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
