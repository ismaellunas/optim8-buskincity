<?php

namespace App\Contracts;

interface MenuInterface {
    public function getTranslation(): object;
    public function getTitle(): string;
    public function getUrl(): string;
}