<?php

namespace CodexSoft\OperationsSystem\Command;

use CodexSoft\Code\Helpers\Classes;
use const CodexSoft\Code\TAB;
use CodexSoft\Code\Helpers\Files;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use CodexSoft\OperationsSystem\Operation;
use function CodexSoft\Code\str;

class SelfCheckCommand extends Command
{

    use \CodexSoft\OperationsSystem\Traits\OperationsSystemSchemaAwareTrait;

    private $uuidVar = Operation::_ID_VAR_NAME;

    private $autofixIds = false;

    protected function configure()
    {

        $this
            ->setDescription('Check core for logical errors.')
            ->addOption('autofix', null, InputOption::VALUE_NONE, 'auto-fix ID costants')
        ;
        parent::configure();

    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->autofixIds = (bool) $input->getOption('autofix');
        $operationsPath = $this->operationsSystemSchema->getPathToOperations();
        $operationsNamespace = $this->operationsSystemSchema->getNamespaceOperations();
        $this->checkOperations($operationsPath, $operationsNamespace, $output);
    }

    private function checkOperations(string $operationsPath, string $operationsNamespace, OutputInterface $output)
    {
        $filesList = Files::listFilesWithPath($operationsPath);

        // getting operation class reflections
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

        $withoutUuid = [];
        foreach ($operationClassesReflections as $operationClassReflection) {
            if (!$operationClassReflection->getConstant($this->uuidVar)) {

                $withoutUuid[] = $operationClassReflection->getName();

                if ($this->autofixIds) {
                    $output->writeln($operationClassReflection->getName().' has not '.$this->uuidVar, OutputInterface::VERBOSITY_VERBOSE);
                    $output->writeln('trying to add...', OutputInterface::VERBOSITY_VERBOSE);
                    $operationCode = file($operationClassReflection->getFileName());
                    $operationCode = $this->addUuidToOperationClassCode($operationCode);
                    file_put_contents($operationClassReflection->getFileName(), implode('',$operationCode));
                }
            }

        }

        $uuids = [];
        foreach ($operationClassesReflections as $operationClassReflection) {
            $uuid = $operationClassReflection->getConstant($this->uuidVar);
            if (\in_array($uuid,$uuids,true)) {
                $output->writeln('UUID repeats in '.$operationClassReflection->getName());
            }
        }

        $output->writeln('Total found '.\count($operationClassesReflections).' operations', OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('Total found '.\count($withoutUuid).' operations without ID', OutputInterface::VERBOSITY_NORMAL);
        foreach ($withoutUuid as $withoutUuidItem) {
            $output->writeln('Missed UUID in class '.$withoutUuidItem);
        }
    }

    /**
     * @param string[] $code
     *
     * @return string[]
     * @throws \Exception
     */
    private function addUuidToOperationClassCode(array $code): array
    {
        $output = [];
        $classLineFound = 0;
        foreach ($code as $line) {
            $output[] = $line;
            if ($classLineFound === 1) {
                $uuid = Uuid::uuid4()->toString();
                //$uuid->toString()
                $output[] = TAB."protected const \$this->uuidVar = '$uuid';\n";
                $classLineFound = 2;
            }
            if (($classLineFound === 0) && (str($line)->startsWith('abstract class ') || str($line)->startsWith('final class ') || str($line)->startsWith('class '))) {
                $classLineFound = 1;
            }
        }
        return $output;
    }

}