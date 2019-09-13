<?php

namespace CodexSoft\OperationsSystem;

use CodexSoft\Code\AbstractModuleSchema;
use CodexSoft\Code\Helpers\Strings;

class OperationsSystemSchema extends AbstractModuleSchema
{

    /** @var string */
    private $namespaceOperations;

    /** @var string */
    private $pathToOperations;

    /**
     * @return string
     */
    public function getNamespaceOperations(): string
    {
        return $this->namespaceOperations ?: $this->getNamespaceBase().'\\Operations';
    }

    /**
     * @param string $namespaceOperations
     *
     * @return OperationsSystemSchema
     */
    public function setNamespaceOperations(string $namespaceOperations): OperationsSystemSchema
    {
        $this->namespaceOperations = $namespaceOperations;
        return $this;
    }

    /**
     * @return string
     */
    public function getPathToOperations(): string
    {
        return $this->pathToOperations ?: $this->pathToPsrRoot.'/'.Strings::bs2s($this->getNamespaceOperations());
    }

    /**
     * @param string $pathToOperations
     *
     * @return OperationsSystemSchema
     */
    public function setPathToOperations(string $pathToOperations): OperationsSystemSchema
    {
        $this->pathToOperations = $pathToOperations;
        return $this;
    }

}