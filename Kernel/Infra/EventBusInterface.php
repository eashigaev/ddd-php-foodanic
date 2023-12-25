<?php

namespace Foodanic\Kernel\Infra;

interface EventBusInterface
{
    /** Emit immediately, rollback changes on error */
    public function emit(array $events);

    /** Add to queue, emit asynchronously with retries */
    public function emitAsync(array $events);
}