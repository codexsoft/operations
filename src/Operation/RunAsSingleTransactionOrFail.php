<?php

namespace CodexSoft\OperationsSystem\Operation;

use CodexSoft\OperationsSystem\OperationsProcessor;
use Doctrine\ORM\EntityManager;

/**
 * Назначение: когда надо ЗА РАЗ выполнить несколько операций, и если хоть одна из них не будет
 * успешно выполнена, откатить все изменения. Передается замыение, которое при необходимости может
 * возвращать результат (а результат можно использовать в вызывающем коде).
 *
 * Пример использования — в /order/create-and-publish. За раз выполняются операции создания заказа и
 * его публикация. Если что-то пошло не так, в БД отложенная заявка создана не будет.
 *
 * todo: решить что делать с неудачно завершенными операциями
 */
class RunAsSingleTransactionOrFail
{

    /** @var \Closure */
    private $closure;

    /** @var EntityManager */
    private $em;

    /** @var OperationsProcessor */
    private $operationsProcessor;

    /**
     * @param EntityManager $em
     * @param \Closure $closure
     *
     */
    public function __construct(EntityManager $em, \Closure $closure)
    {
        $this->em = $em;
        $this->closure = $closure;
    }

    /**
     * @throws \Throwable
     */
    public function execute()
    {
        $em = $this->em;

        $em->beginTransaction();
        try {
            $closure = $this->closure;
            $result = $closure();
            $em->commit();
            return $result;
        } catch (\Throwable $e) {
            $em->rollback();

            //$operationsProcessor = $this->operationsProcessor;
            //
            ///**
            // * Поскольку операции в замыкании выполняются внутри транзакции, которую мы открыли
            // * здесь сами, то, если что-то пошло не так, мы откатываем всю эту транзакцию.
            // *
            // * И чтобы невыполненные операции все-таки попали в журнал registered_operations, мы
            // * достаем их из процессора операций и сохраняем в БД уже здесь.
            // */
            //$failedOperations = $operationsProcessor->getRegisteredFailedOperations();
            //foreach ($failedOperations as $failedOperation) {
            //
            //    /*
            //     * Sometimes exception message or trace can have non-UTF characters, that causes
            //     * failure while executing SQL query.
            //     */
            //    if (Strings::isUtf8($failedOperation->getExceptionInstance()->getMessage())) {
            //        $failedOperation->setExceptionMessage($failedOperation->getExceptionInstance()->getMessage());
            //    }
            //
            //    $exceptionTrace = (string) $failedOperation->getExceptionInstance();
            //    if (Strings::isUtf8($exceptionTrace)) {
            //        $failedOperation->setExceptionMessage($exceptionTrace);
            //    }
            //
            //    $operationsProcessor->getInfrastructureEntityManager()->persist($failedOperation);
            //    $operationsProcessor->getInfrastructureEntityManager()->flush();
            //}
            //$operationsProcessor->setRegisteredFailedOperations([]);

            throw $e;
        }
    }

}