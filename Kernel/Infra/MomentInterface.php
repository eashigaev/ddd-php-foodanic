<?php

namespace Foodanic\Kernel\Infra;

use DateTime;

interface MomentInterface
{
    public function now(): DateTime;
}