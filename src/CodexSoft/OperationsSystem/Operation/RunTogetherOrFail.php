<?php
/**
 * Created by PhpStorm.
 * User: Dmitriy V Kozubskiy (kozubsky@inbox.ru, @Kozubskiy)
 * Date: 06.07.18
 * Time: 20:12
 */

namespace CodexSoft\OperationsSystem\Operation;

use CodexSoft\Code\Helpers\Strings;
use CodexSoft\OperationsSystem\OperationsProcessor;
use Doctrine\ORM\EntityManager;

/**
 * Class RunTogetherOrFail
 *
 * Назначение: когда надо ЗА РАЗ выполнить несколько операций, и если хоть одна из них не будет
 * успешно выполнена, откатить все изменения. Передается замыение, которое при необходимости может
 * возвращать результат (а результат можно использовать в вызывающем коде).
 *
 * Пример использования — в /transport-request/create-and-publish ЛК Заказчика. За раз выполняются
 * операции создания отложенной Заявки и её публикация. Если что-то пошло не так, в БД отложенная
 * заявка создана не будет.
 *
 * todo: решить что делать с неудачно завершенными операциями
 */
class RunTogetherOrFail
{

    /** @var \Closure */
    private $closure;

    /** @var EntityManager */
    private $em;

    /** @var OperationsProcessor */
    private $operationsProcessor;

    /**
     * RunTogether constructor.
     *
     * @param \Closure $closure
     *
     * @throws \Throwable
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @throws \Throwable
     */
    public function execute()
    {
        $operationsProcessor = $this->operationsProcessor;
        $em = $this->em;

        $em->beginTransaction();
        try {
            $closure = $this->closure;
            $result = $closure();
            $em->commit();
            return $result;
        } catch (\Throwable $e) {
            $em->rollback();

            /**
             * Поскольку операции в замыкании выполняются внутри транзакции, которую мы открыли
             * здесь сами, то, если что-то пошло не так, мы откатываем всю эту транзакцию.
             *
             * И чтобы невыполненные операции все-таки попали в журнал registered_operations, мы
             * достаем их из процессора операций и сохраняем в БД уже здесь.
             */
            $failedOperations = $operationsProcessor->getRegisteredFailedOperations();
            foreach ($failedOperations as $failedOperation) {

                /*
                 * Sometimes exception message or trace can have non-UTF characters, that causes
                 * failure while executing SQL query.
                 */
                if (Strings::isUtf8($failedOperation->getExceptionInstance()->getMessage())) {
                    $failedOperation->setExceptionMessage($failedOperation->getExceptionInstance()->getMessage());
                }

                $exceptionTrace = (string) $failedOperation->getExceptionInstance();
                if (Strings::isUtf8($exceptionTrace)) {
                    $failedOperation->setExceptionMessage($exceptionTrace);
                }

                $operationsProcessor->getInfrastructureEntityManager()->persist($failedOperation);
                $operationsProcessor->getInfrastructureEntityManager()->flush();
            }
            $operationsProcessor->setRegisteredFailedOperations([]);
            throw $e;
        }
    }

}