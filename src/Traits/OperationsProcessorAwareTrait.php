<?php


namespace CodexSoft\OperationsSystem\Traits;


use CodexSoft\OperationsSystem\OperationsProcessor;

trait OperationsProcessorAwareTrait
{
    protected ?OperationsProcessor $operationsProcessor = null;

    /**
     * @param OperationsProcessor $operationsProcessor
     *
     * @return static
     */
    public function setOperationsProcessor(?OperationsProcessor $operationsProcessor): self
    {
        $this->operationsProcessor = $operationsProcessor;
        return $this;
    }

    /**
     * @return OperationsProcessor|null
     */
    public function getOperationsProcessor(): ?OperationsProcessor
    {
        return $this->operationsProcessor;
    }

}
