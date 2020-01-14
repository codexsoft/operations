<?php

use CodexSoft\Cli\Cli;
use CodexSoft\OperationsSystem\OperationsSystemSchema;
use Symfony\Component\Console\Command\Command;

require_once __DIR__.'/findautoloader.php';
$operationsSystemConfigFile = Cli::getFirstArgumentOrDie();
$operationsSystemSchema = OperationsSystemSchema::getFromConfigFile($operationsSystemConfigFile);
$console = new \Symfony\Component\Console\Application('CodexSoft operations tools CLI');

$commandList = [

    'add-operation' => (new \CodexSoft\OperationsSystem\Command\GenerateOperationCommand)
        ->setOperationsSystemSchema($operationsSystemSchema),

    'commands' => (new \CodexSoft\OperationsSystem\Command\GenerateCommandsForOperationsCommand)
        ->setOperationsSystemSchema($operationsSystemSchema),

    'selfcheck' => (new \CodexSoft\OperationsSystem\Command\SelfCheckCommand)
        ->setOperationsSystemSchema($operationsSystemSchema),

];

foreach ($commandList as $command => $commandClass) {
    try {

        if ($commandClass instanceof Command) {
            $commandInstance = $commandClass;
        } else {
            $commandInstance = new $commandClass($command);
        }
        $console->add($commandInstance->setName($command));

    } catch ( \Throwable $e ) {
        echo "\nSomething went wrong: ".$e->getMessage();
    };

}

/** @noinspection PhpUnhandledExceptionInspection */
$console->run();
