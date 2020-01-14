<?php

namespace CodexSoft\OperationsSystem\Traits;

use CodexSoft\TimeService\TimeServiceInterface;

trait TimeServiceAwareTrait
{
    protected ?TimeServiceInterface $timeService = null;

    /**
     * @param TimeServiceInterface $timeService
     *
     * @return static
     */
    public function setTimeService(TimeServiceInterface $timeService): self
    {
        $this->timeService = $timeService;
        return $this;
    }
}
