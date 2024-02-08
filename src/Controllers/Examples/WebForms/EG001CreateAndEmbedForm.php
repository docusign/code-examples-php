<?php

namespace DocuSign\Controllers\Examples\WebForms;

use DocuSign\Controllers\WebFormsApiBaseController;
use DocuSign\Services\Examples\WebForms\CreateAndEmbedFormService;
use DocuSign\WebForms\Client\ApiException;

class EG001CreateAndEmbedForm extends WebFormsApiBaseController
{
    const EG = 'web001'; # reference (and URL) for this example
    const FILE = __FILE__;
    const EMBED = 'webforms/embed';
    const WEB_FORM = 'webforms/webForm';
    const WEB_FORM_EXAMPLE_TEMPLATE = "Web Form Example Template";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        parent::controller();
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
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token']
        ];
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return create room data
     *
     * @return void
     * @throws ApiException
     * @throws \DocuSign\eSign\Client\ApiException
     */
    protected function createController(): void
    {
        $this->checkDsToken();
        $accountId = $this->args["account_id"];

        if ($_SESSION['can_embed_form'] == null) {
            $templatesApi = $this->eSignatureClientService->getTemplatesApi();

            $templatesByName = CreateAndEmbedFormService::getTemplatesByName(
                $templatesApi,
                self::WEB_FORM_EXAMPLE_TEMPLATE,
                $accountId
            );
            if ($templatesByName == null || count($templatesByName) == 0) {
                $createTemplate = CreateAndEmbedFormService::createTemplate(
                    $this->args,
                    self::WEB_FORM_EXAMPLE_TEMPLATE,
                    $this::DEMO_DOCS_PATH,
                    $this->eSignatureClientService
                );
                $_SESSION['web_forms_template_id'] = $createTemplate["template_id"];
            } else {
                $_SESSION['web_forms_template_id'] = $templatesByName[0]["template_id"];
            }

            CreateAndEmbedFormService::addTemplateIdToForm(
                $this::DEMO_DOCS_PATH . "web-form-config.json",
                $_SESSION['web_forms_template_id']
            );

            $this->askToCreateWebForm();
        }

        $_SESSION['can_embed_form'] = null;

        $formList = CreateAndEmbedFormService::getForms(
            $this->clientService->FormManagementApi(),
            $accountId
        );

        if ($formList->getItems() == null || count($formList->getItems()) == 0) {
            $this->askToCreateWebForm();
        }

        $formId = $formList->getItems()[0]->getId();

        $webFormInstance = CreateAndEmbedFormService::createInstance(
            $this->clientService->FormInstanceManagementApi(),
            $accountId,
            $formId
        );

        $GLOBALS['twig']->display(
            self::WEB_FORM . '.html',
            [
                'instance_token ' => $webFormInstance["instance_token"],
                'url' => $webFormInstance["form_url"],
                'integration_key' => $GLOBALS['DS_CONFIG']['ds_client_id'],
                'common_texts' => $this->getCommonText(),
            ]
        );
        exit();
    }

    /**
     * @return void
     */
    public function askToCreateWebForm(): void
    {
        $_SESSION['can_embed_form'] = true;

        $GLOBALS['twig']->display(
            self::EMBED . '.html',
            [
                'common_texts' => $this->getCommonText(),
                'code_example_text' => $this->codeExampleText,
                'description' => $this->codeExampleText["AdditionalPage"][1]["ResultsPageText"],
            ]
        );

        exit();
    }
}
