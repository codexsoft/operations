<?php
/**
 * Created by PhpStorm.
 * User: dx
 * Date: 19.08.17
 * Time: 4:20
 */

namespace CodexSoft\OperationsSystem;

use CodexSoft\Code\Helpers\Classes;
use CodexSoft\Code\Traits\Loggable;
use CodexSoft\OperationsSystem\Exception\OperationException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;

abstract class Operation implements LoggerAwareInterface
{

    use Loggable;

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

    /** @var array extra data can be helpful to get additional information after executing */
    private $extraData = [];

    /** @var Operation|null */
    private $parentOperation;

    /** @var OperationsProcessor */
    protected $operationsProcessor;

    /**
     * Operation constructor.
     *
     * in order to use Actors functionality and get more flexibility to use in tests, use setters!
     * to setup initial state, override init()
     */
    final public function __construct()
    {
        $this->init();
        $this->logger = new NullLogger; // todo...
    }

    ///**
    // * @return static
    // */
    //public static function construct(): self
    //{
    //    $operation = new static;
    //    $dice = new \Dice\Dice; // how to get it here?
    //    $dice->create(static::class);
    //    return $operation;
    //}

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
     * @return Operation
     */
    protected function setExtraData(array $extraData): Operation
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
     * @return static
     */
    public function setOperationsProcessor(OperationsProcessor $operationsProcessor): self
    {
        $this->operationsProcessor = $operationsProcessor;
        return $this;
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
                throw $this->exception($errorCode, $messageOnFail ?? 'Неполный набор данных для выполнения операции');
            }
            throw $this->genericException($messageOnFail ?? 'Неполный набор данных для выполнения операции');
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
                throw $this->genericException($messageOnFail ?? 'Неполный набор данных для выполнения операции');
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

        $exception = (new OperationException($errorMessage, $errorCode, $previous, $extraData))
            ->setOperationId(static::_id())
            ->setOperationInstance($this)
            ->setOperationClass(static::class);

        return $exception;
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

        $operationsProcessor = $this->_getOperationsProcessor();
        if ($operationsProcessor->getOperationsStack()->count()) {
            $this->setParentOperation($operationsProcessor->getOperationsStack()->top());
        }

        return $operationsProcessor->executeOperation($this, function() {
            $this->_doValidateInputData();
            return $this->handle();
        });
    }

    private function _getOperationsProcessor(): OperationsProcessor
    {
        return $this->operationsProcessor;
        //return Context::get(OperationsProcessor::class);
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
     * @return Operation
     */
    private function setParentOperation(?Operation $parentOperation): Operation
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

}
