<?php

namespace Example\Controllers;

use Example\Services\RouterService;
abstract class RoomsApiBaseController
{
    /**
     * Base controller
     *
     * @param $eg string
     * @param $routerService RouterService
     * @param $basename string|null
     * @param $templates array|null
     * @param $rooms array|null
     * @param $forms array|null
     * @return void
     */
    public function controller(
        string $eg,
        $routerService,
        $basename = null,
        $templates = null,
        $rooms = null,
        $forms = null,
        $room_id = null,
        $room_name = null
    ): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController($eg, $routerService, $basename, $templates, $rooms, $forms, $room_id, $room_name);
        };
        if ($method == 'POST') {
            $routerService->check_csrf();
            $this->createController();
        };
    }

    /**
     * Show the example's form page
     *
     * @param $eg string
     * @param $routerService RouterService
     * @param $basename string|null
     * @param $templates array|null
     * @param $groups array|null
     * @param $rooms array|null
     * @param $forms array|null
     * @return void
     */
    private function getController(
        string $eg,
        $routerService,
        $basename,
        $templates,
        $rooms = null,
        $forms = null,
        $room_id = null,
        $room_name = null
    ): void
    {
        if ($routerService->ds_token_ok()) {
            $GLOBALS['twig']->display($routerService->getTemplate($eg), [
                'title' => $routerService->getTitle($eg),
                'templates' => $templates,
                'rooms' => $rooms,
                'forms' => $forms,
                'room_id' => $room_id,
                'room_name' => $room_name,
                'source_file' => $basename,
                'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
                'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $eg,
                'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
            ]);
        } elseif ($eg == "home"){
            $GLOBALS['twig']->display($eg . '.html', [
                'title' => 'Home--PHP Code Examples',
                'show_doc' => false
            ]);
        } elseif ($eg == "home_rooms"){
            $GLOBALS['twig']->display($eg . '.html', [
                'title' => 'Home--PHP Rooms Code Examples',
                'show_doc' => false
            ]);
         } else {
            # Save the current operation so it will be resumed after authentication
            $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $eg;
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
        }
    }

    /**
     * Declaration for the base controller creator. Each creator should be described in specific Controller
     */
    abstract function createController();
}
