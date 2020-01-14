<?php

namespace CodexSoft\OperationsSystem\Operation;

use CodexSoft\OperationsSystem\Operation;

/**
 * Назначение: когда надо ЗА РАЗ выполнить несколько операций, и если хоть одна из них не будет
 * успешно выполнена, откатить все изменения. Передается замыение, которое при необходимости может
 * возвращать результат (а результат можно использовать в вызывающем коде).
 */
class ClosureOperation extends Operation
{
    private ?\Closure $closure = null;

    /**
     * @return mixed
     */
    protected function handle()
    {
        $closure = $this->closure;
        return $closure();
    }

    /**
     * @param \Closure $closure
     *
     * @return ClosureOperation
     */
    public function setClosure(\Closure $closure): ClosureOperation
    {
        $this->closure = $closure;
        return $this;
    }
}
