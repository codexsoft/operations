<?php


namespace CodexSoft\OperationsSystem\Traits;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait EventDispatcherAwareTrait
{
    protected ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @return static
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): self
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

}
