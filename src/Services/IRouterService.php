<?php

namespace Example\Services;

interface IRouterService
{
    public function router(): void;

    function ds_token_ok(int $buffer_min = 10): bool;

    public function getController($eg): string;

    function ds_logout_internal(): void;

    function ds_login(): void;

    function ds_callback(): void;

    public function flash(string $msg): void;

    function check_csrf(): void;

    public function getTemplate($eg): string;

    public function getTitle($eg): string;
}