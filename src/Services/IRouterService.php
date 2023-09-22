<?php

namespace DocuSign\Services;

interface IRouterService
{
    public function router(): void;

    public function dsTokenOk(int $buffer_min = 10): bool;

    public function getController($eg): string;

    public function dsLogoutInternal(): void;

    public function dsLogin(): void;

    public function dsCallback(): void;

    public function flash(string $msg): void;

    public function checkCsrf(): void;

    public function getTemplate($eg): string;

    public function getTitle($eg): string;
}
