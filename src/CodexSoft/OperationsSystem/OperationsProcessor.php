<?php


namespace CodexSoft\OperationsSystem;


use CodexSoft\Code\Helpers\Classes;
use CodexSoft\Runtime\ClockService\NormalClockService;
use CodexSoft\OperationsSystem\Events\OperationExecutionProgressEvent;
use CodexSoft\OperationsSystem\Exception\OperationException;
use CodexSoft\OperationsSystem\Traits\ClockServiceAwareTrait;
use CodexSoft\OperationsSystem\Traits\EventDispatcherAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;

class OperationsProcessor
{

    use EventDispatcherAwareTrait;
    use ClockServiceAwareTrait;

    /** @var \SplStack|Operation[] */
    private $operationsStack;

    /**
     * @var OperationExecutionProgressEvent[]
     */
    private $registeredFailedOperations = [];

    public function __construct()
    {
        $this->operationsStack = new \SplStack;
        $this->eventDispatcher = new EventDispatcher;
        $this->clockService = new NormalClockService;
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

        $this->operationsStack->push($operation);

        $eventDispatcher = $this->eventDispatcher;

        $operationExecutionProgressEvent = (new OperationExecutionProgressEvent)
            ->setOperationInstance($operation)
            ->setOperationId($operation->_getId())
            ->setExecutionState(OperationExecutionProgressEvent::EXECUTION_PROCESSING)
            ->setParameters([])
            ->setCreatedAt($this->clockService->now())
            ->setRegisteredAt($this->clockService->now());

        $eventDispatcher->dispatch($operationExecutionProgressEvent);

        try {

            $result = $runner->call($operation);

            $operationExecutionProgressEvent
                ->setExecutionState(OperationExecutionProgressEvent::EXECUTION_SUCCESS)
                ->setProcessedAt($this->clockService->now())
                ->setResultContent([]) // todo: jsonable?.. try to get arrayed?
            ;

            $eventDispatcher->dispatch($operationExecutionProgressEvent);

            $this->operationsStack->pop();

        } catch (\Throwable $e) {

            if ($e instanceof OperationException) {
                /** @var OperationException $wrappedException */
                $wrappedException = $e;
            } else {
                $wrappedException = new OperationException('Failed to execute operation '.Classes::short($operation).': '.$e->getMessage(), Operation::ERROR_CODE_UNHANDLED_EXCEPTION, $e);
            }

            // Сохраняем в БД информацию о неудачной попытке выполнения операции. Чтобы точно
            // сохранилось, все открытые транзакции откатываем.

            $operationExecutionProgressEvent
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