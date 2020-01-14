<?php


namespace CodexSoft\OperationsSystem\Traits;

use CodexSoft\OperationsSystem\OperationsSystemSchema;

trait OperationsSystemSchemaAwareTrait
{
    private ?OperationsSystemSchema $operationsSystemSchema = null;

    /**
     * @param OperationsSystemSchema $operationsSystemSchema
     *
     * @return static
     */
    public function setOperationsSystemSchema(OperationsSystemSchema $operationsSystemSchema): self
    {
        $this->operationsSystemSchema = $operationsSystemSchema;
        return $this;
    }

}
