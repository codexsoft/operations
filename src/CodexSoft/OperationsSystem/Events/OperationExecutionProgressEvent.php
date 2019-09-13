<?php


namespace CodexSoft\OperationsSystem\Events;


use CodexSoft\OperationsSystem\Operation;

class OperationExecutionProgressEvent
{

    /**
     * @var int операция выполняется
     */
    public const EXECUTION_PROCESSING = 1;

    /**
     * @var int выполнение операции успешно завершено
     */
    public const EXECUTION_SUCCESS = 2;

    /**
     * @var int выполнение операции завершилось неудачей
     */
    public const EXECUTION_FAILED = 3;

    /**
     * @var \DateTime|null
     * Момент внесения записи
     */
    private $createdAt;

    /**
     * @var integer|null
     * Время исполнения операции (в микросекундах)
     */
    private $executionTimeInMsec;

    /**
     * @var array|null
     * Значения атрибутов операции
     */
    private $fields;

    /**
     * @var array|null
     * Параметры операции
     */
    private $parameters;

    /**
     * @var \DateTime|null
     * Когда обработано
     */
    private $processedAt;

    /**
     * @var \DateTime|null
     * Когда
     */
    private $registeredAt;

    /**
     * @var array|null
     * Содержимое результата
     */
    private $resultContent;

    /**
     * @var string|null
     * UUID операции
     */
    private $operationId;

    /**
     * @var \Exception|null
     * Operation exception instance
     */
    private $exceptionInstance;

    /**
     * @var int
     * Статус выполнения операции
     */
    private $executionState = self::EXECUTION_PROCESSING;

    /** @var Operation */
    private $operationInstance;

    /**
     * @param \DateTime|null $createdAt
     *
     * @return static
     */
    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @param int|null $executionTimeInMsec
     *
     * @return static
     */
    public function setExecutionTimeInMsec(?int $executionTimeInMsec): self
    {
        $this->executionTimeInMsec = $executionTimeInMsec;
        return $this;
    }

    /**
     * @param array|null $fields
     *
     * @return static
     */
    public function setFields(?array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param array|null $parameters
     *
     * @return static
     */
    public function setParameters(?array $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @param \DateTime|null $processedAt
     *
     * @return static
     */
    public function setProcessedAt(?\DateTime $processedAt): self
    {
        $this->processedAt = $processedAt;
        return $this;
    }

    /**
     * @param \DateTime|null $registeredAt
     *
     * @return static
     */
    public function setRegisteredAt(?\DateTime $registeredAt): self
    {
        $this->registeredAt = $registeredAt;
        return $this;
    }

    /**
     * @param array|null $resultContent
     *
     * @return static
     */
    public function setResultContent(?array $resultContent): self
    {
        $this->resultContent = $resultContent;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return int|null
     */
    public function getExecutionTimeInMsec(): ?int
    {
        return $this->executionTimeInMsec;
    }

    /**
     * @return array|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * @return array|null
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @return \DateTime|null
     */
    public function getProcessedAt(): ?\DateTime
    {
        return $this->processedAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getRegisteredAt(): ?\DateTime
    {
        return $this->registeredAt;
    }

    /**
     * @return array|null
     */
    public function getResultContent(): ?array
    {
        return $this->resultContent;
    }

    /**
     * @return string|null
     */
    public function getOperationId(): ?string
    {
        return $this->operationId;
    }

    /**
     * @param string|null $operationId
     *
     * @return static
     */
    public function setOperationId(?string $operationId): self
    {
        $this->operationId = $operationId;
        return $this;
    }

    /**
     * @return \Exception|null
     */
    public function getExceptionInstance(): ?\Exception
    {
        return $this->exceptionInstance;
    }

    /**
     * @param \Exception|null $exceptionInstance
     *
     * @return static
     */
    public function setExceptionInstance(?\Exception $exceptionInstance): self
    {
        $this->exceptionInstance = $exceptionInstance;
        return $this;
    }

    /**
     * @return int
     */
    public function getExecutionState(): int
    {
        return $this->executionState;
    }

    /**
     * @param int $executionState
     *
     * @return static
     */
    public function setExecutionState(int $executionState): self
    {
        $this->executionState = $executionState;
        return $this;
    }

    /**
     * @param Operation $operationInstance
     *
     * @return static
     */
    public function setOperationInstance(Operation $operationInstance): self
    {
        $this->operationInstance = $operationInstance;
        return $this;
    }

    /**
     * @return Operation
     */
    public function getOperationInstance(): Operation
    {
        return $this->operationInstance;
    }

}