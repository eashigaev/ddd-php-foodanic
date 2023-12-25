<?php

namespace Foodanic\Kernel\Infra;

trait OptimisticLockingTrait
{
    public int $version = 1;

    public function withVersion(int $version): void
    {
        $this->version = $version;
    }
}