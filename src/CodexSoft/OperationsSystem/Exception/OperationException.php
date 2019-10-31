<?php

namespace CodexSoft\OperationsSystem\Exception;

use CodexSoft\OperationsSystem\Operation;

final class OperationException extends \Exception
{

    /**
     * @var string|Operation
     * Класс операции, в которой произошло исключение
     */
    protected $operationClass;

    /**
     * @var string
     * UUID операции, в которой произошло исключение
     */
    protected $operationId;

    /**
     * @var Operation
     * Экземпляр операции, в которой произошло исключение
     */
    protected $operationInstance;

    /**
     * @var array
     * Дополнительные данные, которые могут быть полезны для обработки исключения
     */
    protected $extraData = [];

    /**
     * OperationException constructor.
     *
     * @param Operation $operationInstance
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param array $extraData
     */
    public function __construct(Operation $operationInstance, string $message = '', int $code = 0, \Throwable $previous = null, array $extraData = [])
    {
        $this->operationInstance = $operationInstance;
        $this->operationClass = \get_class($operationInstance);
        $this->operationId = $operationInstance->_getId();
        $this->extraData = $extraData;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Operation
     * todo: strict return Operation
     */
    public function getOperationInstance(): ?Operation
    {
        return $this->operationInstance;
    }

    /**
     * @param array $extraData
     *
     * @return static
     */
    public function setExtraData(array $extraData): self
    {
        $this->extraData = $extraData;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperationId(): string
    {
        return $this->operationId;
    }

    /**
     * Usage: $operationException->getErrorCodeConstName(-1) => 'ERROR_CODE_INVALID_INPUT_DATA'
     * @param int $constValue
     *
     * @return string|null
     */
    public function getErrorCodeConstName(?int $constValue = null): ?string
    {
        return $this->operationClass::getErrorCodeConstName($constValue ?: $this->code);
    }

}
