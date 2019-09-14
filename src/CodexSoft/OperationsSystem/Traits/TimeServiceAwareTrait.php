<?php

namespace CodexSoft\OperationsSystem\Traits;

use CodexSoft\Code\TimeService\TimeServiceInterface;

trait TimeServiceAwareTrait
{
    /** @var TimeServiceInterface */
    protected $timeService;

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