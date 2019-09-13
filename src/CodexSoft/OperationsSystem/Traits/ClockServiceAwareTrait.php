<?php


namespace CodexSoft\OperationsSystem\Traits;


use CodexSoft\Runtime\ClockService\ClockServiceInterface;

trait ClockServiceAwareTrait
{
    /** @var ClockServiceInterface */
    protected $clockService;

    /**
     * @param ClockServiceInterface $clockService
     *
     * @return static
     */
    public function setClockService(ClockServiceInterface $clockService): self
    {
        $this->clockService = $clockService;
        return $this;
}
}