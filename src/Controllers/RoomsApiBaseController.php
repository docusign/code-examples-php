<?php

namespace Example\Controllers;

use Example\Services\RouterService;
abstract class RoomsApiBaseController extends BaseController
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
     * @param null $offices
     * @param null $formGroups
     * @return void
     */
    public function controller(
        string $eg,
        RouterService $routerService,
        $basename = null,
        $templates = null,
        $rooms = null,
        $forms = null,
        $offices = null,
        $formGroups = null
    ): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController($eg, $routerService, $basename, $templates, $rooms, $forms, $offices, $formGroups);
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
     * @param $rooms array|null
     * @param $forms array|null
     * @param null $offices
     * @param null $formGroups
     * @return void
     */
    private function getController(
        string $eg,
        RouterService $routerService,
        ?string $basename,
        ?array $templates,
        $rooms = null,
        $forms = null,
        $offices = null,
        $formGroups = null
    ): void
    {
        if ($this->isHomePage($eg)){
            $GLOBALS['twig']->display($eg . '.html', [
                'title' => $this->homePageTitle($eg),
                'show_doc' => false
            ]);
       
         } else {

            if ($routerService->ds_token_ok()) {
                $GLOBALS['twig']->display($routerService->getTemplate($eg), [
                    'title' => $routerService->getTitle($eg),
                    'templates' => $templates,
                    'rooms' => $rooms,
                    'forms' => $forms,
                    'offices' => $offices,
                    'form_groups' => $formGroups,
                    'source_file' => $basename,
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $eg,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                ]);
            } 
            else {

            
            # Save the current operation so it will be resumed after authentication
            $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $eg;
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
            }
        }
    }

    /**
     * Declaration for the base controller creator. Each creator should be described in specific Controller
     */
    abstract function createController();
}
