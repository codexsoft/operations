<?php

namespace CodexSoft\OperationsSystem;

use CodexSoft\Code\Strings\Strings;

class OperationsSystemSchema
{

    protected $namespaceBase = 'App\\Domain';

    /** @var string */
    protected $pathToPsrRoot = '/src';

    /** @var string */
    private $namespaceOperations;

    /** @var string */
    private $pathToOperations;

    /** @var string */
    private $baseOperationClass = Operation::class;

    /**
     * @param string $domainConfigFile
     *
     * @return static
     * @throws \Exception
     */
    public static function getFromConfigFile(string $domainConfigFile): self
    {
        ob_start();
        $domainSchema = include $domainConfigFile;
        ob_end_clean();

        if (!$domainSchema instanceof static) {
            throw new \Exception("File $domainConfigFile does not return valid ".static::class."!\n");
        }

        return $domainSchema;
    }

    /**
     * @return string
     */
    public function getPathToPsrRoot(): string
    {
        return $this->pathToPsrRoot;
    }

    /**
     * @param string $pathToPsrRoot
     *
     * @return static
     */
    public function setPathToPsrRoot(string $pathToPsrRoot): self
    {
        $this->pathToPsrRoot = $pathToPsrRoot;
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespaceBase(): string
    {
        return $this->namespaceBase;
    }

    /**
     * @param string $namespaceBase
     *
     * @return static
     */
    public function setNamespaceBase(string $namespaceBase): self
    {
        $this->namespaceBase = $namespaceBase;
        return $this;
    }

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

    /**
     * @return string
     */
    public function getBaseOperationClass(): string
    {
        return $this->baseOperationClass;
    }

    /**
     * @param string $baseOperationClass
     *
     * @return OperationsSystemSchema
     */
    public function setBaseOperationClass(string $baseOperationClass): OperationsSystemSchema
    {
        $this->baseOperationClass = $baseOperationClass;
        return $this;
    }

}
