<?php

namespace Example\Services\Examples\Rooms;

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
        $rooms_api = $clientService->getRoomsApi();

        try {
            $options = new GetRoomsOptions();
            $options->setFieldDataChangedStartDate($args['start_date']);
            $options->setFieldDataChangedEndDate($args['end_date']);
            $rooms = $rooms_api->getRooms($args['account_id'], $options);
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
        $minimumBufferMin = 3;
        if ($routerService->ds_token_ok($minimumBufferMin)) {
            return $clientService->getRooms($args);
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
