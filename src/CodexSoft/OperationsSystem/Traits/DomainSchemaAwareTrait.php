<?php


namespace CodexSoft\OperationsSystem\Traits;

use CodexSoft\OperationsSystem\OperationsSystemSchema;

trait DomainSchemaAwareTrait
{

    /** @var OperationsSystemSchema */
    private $domainSchema;

    /**
     * @param OperationsSystemSchema $domainSchema
     *
     * @return static
     */
    public function setDomainSchema(OperationsSystemSchema $domainSchema): self
    {
        $this->domainSchema = $domainSchema;
        return $this;
    }

}