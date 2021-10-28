<?php

namespace Example\Services\Examples\Rooms;

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
        # Step 3 Start
        $form_group = new FormGroupForCreate(['name' => $args['form_group_name']]);
        # Step 3 End

        # Step 4 Start
        return $clientService->createFormGroup($args["account_id"], $form_group);
        # Step 4 End
    }
}
