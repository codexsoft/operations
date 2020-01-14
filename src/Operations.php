<?php

namespace CodexSoft\OperationsSystem;

use CodexSoft\Code\Classes\Classes;
use CodexSoft\Code\Files\Files;
use Symfony\Component\Console\Output\OutputInterface;

use function Stringy\create as str;

class Operations
{

    /**
     * getting operation class reflections
     *
     * @param string $operationsPath
     * @param string $operationsNamespace
     *
     * @param OutputInterface $output
     *
     * @return \ReflectionClass[]
     */
    public static function collectOperationsFromPath(string $operationsPath, string $operationsNamespace, OutputInterface $output)
    {
        $filesList = Files::listFilesWithPath($operationsPath);

        /** @var \ReflectionClass[] $operationClassesReflections */
        $operationClassesReflections = [];
        foreach ($filesList as $item => $content) {
            $content = (string) str($content)->replace('//','/');
            if (!str($content)->endsWith('.php')) {
                continue;
            }
            $operationClass = $operationsNamespace.'\\'.str($content)->removeRight('.php')->replace('/','\\');

            if (\trait_exists($operationClass)) {
                continue;
            }

            if (!\class_exists($operationClass)) {
                $output->writeln($operationClass." does not exists (but file $content does)!");
                continue;
            }

            $output->writeln($content.' - '.$operationClass, OutputInterface::VERBOSITY_VERY_VERBOSE);

            try {
                $operationClassReflection = new \ReflectionClass($operationClass);
                $operationClassReflection->getFileName();
            } catch (\ReflectionException $e) {
                $output->writeln($operationClass.' failed to get reflection!');
                continue;
            }

            if ($operationClassReflection->isAbstract()) {
                continue;
            }

            if (!Classes::isSameOrExtends($operationClass, Operation::class)) {
                $output->writeln($operationClass.' is not extending Operation, skipped!', OutputInterface::VERBOSITY_VERY_VERBOSE);
                continue;
            }

            $operationClassesReflections[$operationClass] = $operationClassReflection;
        }

        return $operationClassesReflections;
    }
}
