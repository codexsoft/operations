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
     * Момент внесения записи
     */
    private ?\DateTime $createdAt = null;

    /**
     * Время исполнения операции (в микросекундах)
     */
    private ?int $executionTimeInMsec = null;

    /**
     * Значения атрибутов операции
     */
    private ?array $fields = null;

    /**
     * Параметры операции
     */
    private ?array $parameters = null;

    /**
     * Когда обработано
     */
    private ?\DateTime $processedAt = null;

    /**
     * Когда
     */
    private ?\DateTime $registeredAt = null;

    /**
     * Содержимое результата
     */
    private ?array $resultContent = null;

    /**
     * UUID операции
     */
    private ?string $operationId = null;

    /**
     * Operation exception instance
     */
    private ?\Exception $exceptionInstance = null;

    /**
     * Статус выполнения операции
     */
    private int $executionState = self::EXECUTION_PROCESSING;

    private ?Operation $operationInstance = null;

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
