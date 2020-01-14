<?php

namespace CodexSoft\OperationsSystem\Command;

use CodexSoft\OperationsSystem\Operations;
use CodexSoft\OperationsSystem\Traits\OperationsSystemSchemaAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;

use const CodexSoft\Shortcut\TAB;

class GenerateCommandsForOperationsCommand extends Command
{
    use OperationsSystemSchemaAwareTrait;

    protected function configure()
    {
        $this->setDescription('Generate console commands for operations');
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $operationClassesReflections = Operations::collectOperationsFromPath(
            $this->operationsSystemSchema->getPathToOperations(),
            $this->operationsSystemSchema->getNamespaceOperations(),
            $output
        );

        foreach ($operationClassesReflections as $operationClassReflection) {

            $commandClassName = $operationClassReflection->getShortName().'Command';
            $output->writeln("Generating command class {$commandClassName} for operation {$operationClassReflection->getShortName()}");

            $operationProperties = $operationClassReflection->getProperties();
            $optionConfigurationLines = [];

            // todo: exclude some base properties like exceptionInstance

            foreach ($operationProperties as $operationProperty) {
                $operationPropertyType = $operationProperty->getType();
                if ($operationPropertyType instanceof \ReflectionType) {

                    if ($operationPropertyType->allowsNull()) {
                        $optionValueType = 'VALUE_OPTIONAL';
                    } else {
                        $optionValueType = 'VALUE_REQUIRED';
                    }

                    if ($operationPropertyType->isBuiltin()) {
                        $optionName = $operationProperty->getName();
                    } else {
                        $optionName = $operationProperty->getName().'Id';
                    }

                    $optionConfigurationLines[] = TAB.TAB."\$this->addOption({$optionName}, null, InputOption::{$optionValueType}, 'property description')";
                }
            }





            //$operationClassReflection->getName();
            $code = [
                '<?php',
                '',
                'namespace '.$this->operationsSystemSchema->getNamespaceCommands().';',
                '',
                'use '.$operationClassReflection->getName().';',
                'use '.\Symfony\Component\Console\Command\Command::class.';',
                'use '.\Symfony\Component\Console\Input\InputInterface::class.';',
                'use '.\Symfony\Component\Console\Input\InputOption::class.';',
                'use '.\Symfony\Component\Console\Output\OutputInterface::class.';',
                '',
                'class CreateBrandCommand extends Command',
                '{',
                TAB.'protected function configure()',
                TAB.'{',
                TAB.TAB.'$this->setDescription()',
                ...$optionConfigurationLines,
                //TAB.TAB."\$this->addOption('organization', null, InputOption::VALUE_REQUIRED, 'organization ID')",
                TAB.TAB.'parent::configure();',
                TAB.'}',
                TAB.'',
                TAB.'public function execute(InputInterface $input, OutputInterface $output)',
                TAB.'{',
                TAB.TAB."\$operation = new \\{$operationClassReflection->getName()}();",
                //$operation->setOrganizationOrId($input->getOption('organization'));
                //$operation->setName($input->getOption('name'));
                TAB.TAB.'$operation->execute();',
                TAB.'}',

                '}',
            ];

            $fs = new Filesystem();
            $fs->dumpFile($this->operationsSystemSchema->getPathToCommands().'/'.$commandClassName.'.php', implode("\n", $code));
        }
    }

}
