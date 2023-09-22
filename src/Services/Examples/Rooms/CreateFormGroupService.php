<?php

namespace DocuSign\Services\Examples\Rooms;

use DocuSign\Rooms\Model\FormGroup;
use DocuSign\Rooms\Model\FormGroupForCreate;

class CreateFormGroupService
{
    /**
     * 1. Create FormGroupForCreate object
     * 2. Submit newly created form group using SDK.
     *
     * @param  $args array
     * @param $clientService
     * @return FormGroup
     */
    public static function createFormGroup(array $args, $clientService): FormGroup
    {
        #ds-snippet-start:Rooms7Step3
        $form_group = new FormGroupForCreate(['name' => $args['form_group_name']]);
        #ds-snippet-end:Rooms7Step3

        #ds-snippet-start:Rooms7Step4
        return $clientService->createFormGroup($args["account_id"], $form_group);
        #ds-snippet-end:Rooms7Step4
    }
}
