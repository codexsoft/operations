<?php

namespace CodexSoft\OperationsSystem\Command;

use CodexSoft\OperationsSystem\Operations;
use CodexSoft\OperationsSystem\Traits\OperationsSystemSchemaAwareTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use CodexSoft\OperationsSystem\Operation;
use function Stringy\create as str;

use const CodexSoft\Shortcut\TAB;

class SelfCheckCommand extends Command
{
    use OperationsSystemSchemaAwareTrait;

    private string $uuidVar = Operation::_ID_VAR_NAME;
    private bool $autofixIds = false;

    protected function configure()
    {
        $this
            ->setDescription('Check core for logical errors.')
            ->addOption('autofix', null, InputOption::VALUE_NONE, 'auto-fix ID costants')
        ;
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->autofixIds = (bool) $input->getOption('autofix');
        $operationClassesReflections = Operations::collectOperationsFromPath(
            $this->operationsSystemSchema->getPathToOperations(),
            $this->operationsSystemSchema->getNamespaceOperations(),
            $output
        );

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
