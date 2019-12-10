<?php


namespace CodexSoft\OperationsSystem\Traits;

use CodexSoft\OperationsSystem\OperationsSystemSchema;

trait OperationsSystemSchemaAwareTrait
{

    /** @var OperationsSystemSchema */
    private $operationsSystemSchema;

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