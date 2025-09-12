<?php

namespace DocuSign\Controllers\Examples\WebForms;

use DocuSign\Controllers\WebFormsApiBaseController;
use DocuSign\Services\Examples\WebForms\CreateRemoteInstanceService;
use DocuSign\WebForms\Client\ApiException;
use DocuSign\Services\ManifestService;

class EG002CreateRemoteInstance extends WebFormsApiBaseController
{
    const EG = 'web002'; # reference (and URL) for this example
    const FILE = __FILE__;
    const EMBED = 'webforms/embed';
    const WEB_FORM_EXAMPLE_TEMPLATE = 'Web Form Example Template';

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
            'account_id' => $_SESSION['ds_account_id'] ?? null,
            'base_path' => $_SESSION['ds_base_path'] ?? null,
            'ds_access_token' => $_SESSION['ds_access_token'] ?? null
        ];
    }

    /**
     * Main controller logic
     *
     * @return void
     * @throws ApiException
     * @throws \DocuSign\eSign\Client\ApiException
     */
    protected function createController(): void
    {
        $this->checkDsToken();
        $accountId = $this->args['account_id'] ?? null;

        if (empty($_SESSION['is_initial_entry'])) {
            $templatesApi = $this->eSignatureClientService->getTemplatesApi();

            $templatesByName = CreateRemoteInstanceService::getTemplatesByName(
                $templatesApi,
                self::WEB_FORM_EXAMPLE_TEMPLATE,
                $accountId
            );
            if (empty($templatesByName) || count($templatesByName) == 0) {
                $createTemplate = CreateRemoteInstanceService::createTemplate(
                    $this->args,
                    self::WEB_FORM_EXAMPLE_TEMPLATE,
                    $this::DEMO_DOCS_PATH,
                    $this->eSignatureClientService
                );
                $_SESSION['web_forms_template_id'] = $createTemplate['template_id'];
            } else {
                $_SESSION['web_forms_template_id'] = $templatesByName[0]['template_id'];
            }

            CreateRemoteInstanceService::addTemplateIdToForm(
                $this::DEMO_DOCS_PATH . 'web-form-config.json',
                $_SESSION['web_forms_template_id']
            );

            $this->askToCreateWebForm();
            return;
        }

        $_SESSION['is_initial_entry'] = null;

        $formList = CreateRemoteInstanceService::getFormsByName(
            $this->clientService->formManagementApi(),
            $accountId,
            self::WEB_FORM_EXAMPLE_TEMPLATE
        );

        if (empty($formList->getItems()) || count($formList->getItems()) == 0) {
            $GLOBALS['twig']->display(
                'error.html',
                [
                    'error_code' => '400',
                    'error_message' => $this->codeExampleText['CustomErrorTexts'][0]['ErrorMessage'],
                    'common_texts' => ManifestService::getCommonTexts()
                ]
            );
            return;
        }

        $formId = $formList->getItems()[0]->getId();

        $webFormInstance = CreateRemoteInstanceService::createRemoteInstance(
            $this->clientService->formInstanceManagementApi(),
            $accountId,
            $formId,
            $GLOBALS['DS_CONFIG']['signer_name'],
            $GLOBALS['DS_CONFIG']['signer_email']
        );

        if ($webFormInstance) {
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                null,
                ManifestService::replacePlaceholders(
                    '{0}',
                    $webFormInstance['envelopes'][0]['id'],
                    ManifestService::replacePlaceholders('{1}', $webFormInstance['id'], $this->codeExampleText['ResultsPageText'])
                )
            );
        }
    }

    /**
     * @return void
     */
    public function askToCreateWebForm(): void
    {
        $_SESSION['is_initial_entry'] = true;

        $GLOBALS['twig']->display(
            self::EMBED . '.html',
            [
                'common_texts' => $this->getCommonText(),
                'code_example_text' => $this->codeExampleText,
                'description' => ManifestService::replacePlaceholders(
                    '{0}',
                    'public/demo_documents',
                    $this->codeExampleText['AdditionalPage'][0]['ResultsPageText']
                ),
            ]
        );
    }
}
