<?php

namespace CodexSoft\OperationsSystem;

use CodexSoft\Code\Classes\Classes;
use CodexSoft\OperationsSystem\Exception\OperationException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class Operation implements LoggerAwareInterface
{

    protected const ERROR_PREFIX = '';

    protected const ID = null;

    /** @var string наименование константы с уникальным ID операции */
    public const _ID_VAR_NAME = 'ID';

    /**
     * Стандартный код ошибки при проверке входных данных операции, если данные некорректны
     */
    public const ERROR_CODE_INVALID_INPUT_DATA = -1;

    /**
     * Стандартный код ошибки, если метод handle() выбросил исключение, отличное от OperationException
     */
    public const ERROR_CODE_UNHANDLED_EXCEPTION = -2;

    public const ERROR_MESSAGE_INCOMPLETE_INPUT = 'Неполный набор данных для выполнения операции';

    /** extra data can be helpful to get additional information after executing */
    private array $extraData = [];

    private ?Operation $parentOperation;

    /**
     * todo: should be made static, maybe with entityManager
     */
    protected static ?OperationsProcessor $operationsProcessor;

    private LoggerInterface $logger;

    /**
     * Operation constructor.
     *
     * in order to use Actors functionality and get more flexibility to use in tests, use setters!
     * to setup initial state, override init()
     */
    final public function __construct()
    {
        $this->logger = new NullLogger; // todo...
        $this->init();
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return static
     */
    public function setLogger($logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger ?: $this->logger = new NullLogger;
    }

    /**
     * hook method to setup initial state for operation
     */
    protected function init(): void
    {
        // do nothing by default
    }

    /**
     * @param array $extraData
     *
     * @return static
     */
    protected function setExtraData(array $extraData): self
    {
        $this->extraData = $extraData;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtraData(): array
    {
        return $this->extraData;
    }

    /**
     * @param OperationsProcessor $operationsProcessor
     *
     * @return void
     */
    public static function setOperationsProcessor(?OperationsProcessor $operationsProcessor): void
    {
        static::$operationsProcessor = $operationsProcessor;
    }

    /**
     * todo: should it be protected? what about traits?
     * @param $statement
     * @param string $messageOnFail
     * @param int|null $errorCode
     *
     * @throws OperationException
     */
    protected function assert($statement, ?string $messageOnFail = null, ?int $errorCode = null): void
    {
        if (!$statement) {
            if ($errorCode) {
                throw $this->exception($errorCode, $messageOnFail ?? static::ERROR_MESSAGE_INCOMPLETE_INPUT);
            }
            throw $this->genericException($messageOnFail ?? static::ERROR_MESSAGE_INCOMPLETE_INPUT);
        }
    }

    /**
     * todo: should it be protected? what about traits?
     * @param array $statements
     * @param string $messageOnFail
     *
     * @throws OperationException
     */
    protected function assertAll(array $statements, ?string $messageOnFail = null): void
    {
        foreach ($statements as $statement) {
            if (!$statement) {
                throw $this->genericException($messageOnFail ?? static::ERROR_MESSAGE_INCOMPLETE_INPUT);
            }
        }
    }

    /**
     * Сгенерировать общее исключение OperationException с кодом этой операции
     *
     * @param string $message
     * @param \Throwable|null $previous
     *
     * @param array $extraData
     *
     * @return OperationException
     */
    protected function genericException(string $message = '', \Throwable $previous = null, array $extraData = []): OperationException
    {
        return $this->exception(0, $message, $previous, $extraData);
    }

    /**
     * Сгенерировать специфическое исключение с кодом этой операции и кодом ошибки
     *
     * @param int $errorCode
     * @param string $errorMessage
     * @param \Throwable|null $previous
     *
     * @param array $extraData
     *
     * @return OperationException
     */
    protected function exception(int $errorCode, string $errorMessage = '', \Throwable $previous = null, array $extraData = []): OperationException
    {
        if (!static::_id()) {
            throw new \RuntimeException('Operation '.static::class.' has not assigned ID!');
        }

        $errorMessage = static::ERROR_PREFIX.' '.$errorMessage;

        $this->logger->debug($errorMessage);

        return (new OperationException($this, $errorMessage, $errorCode, $previous, $extraData));
    }

    /**
     * @return mixed
     */
    abstract protected function handle();

    /**
     * @return mixed
     * @throws OperationException бросается из самой операции (или из вложенной операции, если не было перехвачено)
     */
    public function execute()
    {
        $this->logger->info('Попытка выполнения операции '.Classes::short($this).' ('.\get_class($this).')');

        $operationsProcessor = static::getOperationsProcessor();
        if ($operationsProcessor instanceof OperationsProcessor) {
            return $operationsProcessor->executeOperation($this, function() {
                $this->_doValidateInputData();
                return $this->handle();
            });
        }

        $this->_doValidateInputData();
        return $this->handle();
    }

    protected static function getOperationsProcessor(): ?OperationsProcessor
    {
        return static::$operationsProcessor;
    }

    /**
     * Выполнить операцию и вернуть массив с дополнительными данными
     * @return array
     * @throws OperationException
     */
    final public function executeReturningExtraData(): array
    {
        $this->execute();
        return $this->getExtraData();
    }

    protected function validateInputData(): void
    {
        // override and implement checks in concrete operation
    }

    /**
     * @throws OperationException
     */
    protected function _doValidateInputData(): void
    {
        try {
            $this->validateInputData();
        } catch (\Throwable $e) {
            throw $this->exception(self::ERROR_CODE_INVALID_INPUT_DATA,'Operation input parameters are not valid: '.$e->getMessage(),$e);
        }
    }

    /**
     * @return array
     * maybe it will be useful for journaling... maybe not.
     */
    public function exportProperties()
    {
        $exportArray = [];
        $vars = get_object_vars($this);
        $parent_vars = get_class_vars( $this );

        foreach( $vars as $var => $value ) {
            if ( !array_key_exists( $var, $parent_vars ) ) {
                $exportArray[$var] = $value;
            }
        }

        return $exportArray;
    }

    /**
     * UUID-код операции
     * @return string
     */
    final public static function _id(): string
    {
        if (!static::ID) {
            throw new \LogicException('Operation '.static::class.' has not UUID code!');
        }
        return static::ID;
    }

    /**
     * UUID-код операции
     * @return string
     */
    final public function _getId(): string
    {
        return self::_id();
    }

    /**
     * @param Operation|null $parentOperation
     *
     * @return static
     */
    public function setParentOperation(?Operation $parentOperation): self
    {
        $this->parentOperation = $parentOperation;
        return $this;
    }

    /**
     * @return Operation|null
     */
    public function getParentOperation(): ?Operation
    {
        return $this->parentOperation;
    }

    /**
     * example usage
     * public const ERROR_CODE_INVALID_INPUT_DATA = -1;
     * public const ERROR_CODE_UNHANDLED_EXCEPTION = -2;
     * Operation::getErrorCodeConstName(-1) => 'ERROR_CODE_INVALID_INPUT_DATA'
     *
     * @param int $value
     * @return string|null
     */
    public static function getErrorCodeConstName(int $value): ?string
    {
        return Classes::getConstantNameByValue($value, static::class, 'ERROR_CODE_');
    }

    /**
     * @return Operation[]
     */
    public function getOperationsStack(): array
    {
        if (!$this->parentOperation) {
            return [];
        }

        $ancestorOperations = [$this->parentOperation];
        $operationsStack = $this->parentOperation->getOperationsStack();
        if ($operationsStack) {
            \array_push($ancestorOperations, ...$this->parentOperation->getOperationsStack());
        }
        return $ancestorOperations;
    }

}
