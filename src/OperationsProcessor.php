<?php

namespace CodexSoft\OperationsSystem;

use CodexSoft\Code\Classes\Classes;
use CodexSoft\OperationsSystem\Events\OperationExecutionProgressEvent;
use CodexSoft\OperationsSystem\Exception\OperationException;
use CodexSoft\OperationsSystem\Traits\TimeServiceAwareTrait;
use CodexSoft\OperationsSystem\Traits\EventDispatcherAwareTrait;
use CodexSoft\TimeService\NormalTimeService;
use CodexSoft\TimeService\TimeServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class OperationsProcessor
{

    use EventDispatcherAwareTrait;
    use TimeServiceAwareTrait;

    /** @var \SplStack|Operation[] */
    private $operationsStack;

    /**
     * @var OperationExecutionProgressEvent[]
     */
    private $registeredFailedOperations = [];

    public function __construct(?TimeServiceInterface $timeService = null)
    {
        $this->operationsStack = new \SplStack;
        $this->eventDispatcher = new EventDispatcher;
        $this->timeService = $timeService ?: new NormalTimeService();
    }

    /**
     * @param Operation $operation
     * @param \Closure $runner
     *
     * @return mixed
     * @throws OperationException
     */
    public function executeOperation(Operation $operation, \Closure $runner)
    {

        if ($this->getOperationsStack()->count()) {
            $operation->setParentOperation($this->getOperationsStack()->top());
        }

        $this->operationsStack->push($operation);

        $eventDispatcher = $this->eventDispatcher;

        $operationExecutionProgressEvent = (new OperationExecutionProgressEvent)
            ->setOperationInstance($operation)
            ->setOperationId($operation->_getId())
            ->setExecutionState(OperationExecutionProgressEvent::EXECUTION_PROCESSING)
            ->setParameters([])
            ->setCreatedAt($this->timeService->now())
            ->setRegisteredAt($this->timeService->now());

        $eventDispatcher->dispatch($operationExecutionProgressEvent);

        try {

            $result = $runner->call($operation);

            $operationExecutionProgressEvent
                ->setExecutionState(OperationExecutionProgressEvent::EXECUTION_SUCCESS)
                ->setProcessedAt($this->timeService->now())
                ->setResultContent([]) // todo: jsonable?.. try to get arrayed?
            ;

            $eventDispatcher->dispatch($operationExecutionProgressEvent);

            $this->operationsStack->pop();

        } catch (\Throwable $e) {

            if ($e instanceof OperationException) {
                /** @var OperationException $wrappedException */
                $wrappedException = $e;
            } else {
                $wrappedException = new OperationException($operation, 'Failed to execute operation '.Classes::short($operation).': '.$e->getMessage().' in file '.$e->getFile().':'.$e->getLine(), Operation::ERROR_CODE_UNHANDLED_EXCEPTION, $e);
            }

            // Сохраняем в БД информацию о неудачной попытке выполнения операции. Чтобы точно
            // сохранилось, все открытые транзакции откатываем.

            $operationExecutionProgressEvent
                ->setProcessedAt($this->timeService->now())
                ->setExecutionState(OperationExecutionProgressEvent::EXECUTION_FAILED)
                ->setExceptionInstance($wrappedException);

            $eventDispatcher->dispatch($operationExecutionProgressEvent);

            $this->operationsStack->pop();

            throw $wrappedException;
        }

        return $result;
    }

    /**
     * @return \SplStack
     */
    public function getOperationsStack(): \SplStack
    {
        return $this->operationsStack;
    }

}
