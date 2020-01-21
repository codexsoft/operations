<?php

namespace CodexSoft\OperationsSystem\Command;

use CodexSoft\Code\Traits\Traits;
use CodexSoft\OperationsSystem\Operations;
use CodexSoft\OperationsSystem\Traits\OperationsSystemSchemaAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;

use function Stringy\create as str;
use const CodexSoft\Shortcut\TAB;

class GenerateCommandsForOperationsCommand extends Command
{
    use OperationsSystemSchemaAwareTrait;

    private bool $overwriteExisting = false;
    private string $prefix = '';

    protected function configure()
    {
        $this->setDescription('Generate console commands for operations');
        $this->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'should command files be overriden if exists');
        $this->addOption('prefix', 'p', InputOption::VALUE_OPTIONAL, 'prefix to add to command name (for example, "app:")');
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->overwriteExisting = (bool) $input->getOption('overwrite');
        $this->prefix = (string) $input->getOption('overwrite');

        $operationClassesReflections = Operations::collectOperationsFromPath(
            $this->operationsSystemSchema->getPathToOperations(),
            $this->operationsSystemSchema->getNamespaceOperations(),
            $output
        );

        $fs = new Filesystem();

        foreach ($operationClassesReflections as $operationClassReflection) {

            $commandClassName = $operationClassReflection->getShortName().'Command';
            $commandFile = $this->operationsSystemSchema->getPathToCommands().'/'.$commandClassName.'.php';

            if (!$this->overwriteExisting && $fs->exists($commandFile)) {
                $output->writeln("Command file $commandFile already exists, generation skipped.");
                continue;
            }

            $operationProperties = $operationClassReflection->getProperties();
            $optionConfigurationLines = [];
            $optionSetLines = [];

            // todo: exclude some base properties like exceptionInstance

            $propertiesToSkip = [
                'executorUser',
                'em',
                'wem',
                'operationLogger',
                'transactionControl',
                'operationsProcessor',
            ];

            foreach ($operationProperties as $operationProperty) {

                $propertyName = $operationProperty->getName();

                if (\in_array($propertyName, $propertiesToSkip, true)) {
                    continue;
                }

                $output->writeln("Generating command class {$commandClassName} for operation {$operationClassReflection->getShortName()}");

                $operationPropertyType = $operationProperty->getType();
                if ($operationPropertyType instanceof \ReflectionType) {

                    if ($operationPropertyType->allowsNull()) {
                        $optionValueType = 'VALUE_OPTIONAL';
                    } else {
                        $optionValueType = 'VALUE_REQUIRED';
                    }

                    $isScalar = $operationPropertyType->isBuiltin();
                    if ($isScalar) {
                        $optionName = $propertyName;
                    } else {
                        $optionName = $propertyName.'Id';
                    }

                    $optionConfigurationLines[] = TAB.TAB."\$this->addOption('{$optionName}', null, InputOption::{$optionValueType}, 'property description');";

                    if ($isScalar && $operationProperty->isPublic()) {
                        $optionSetLines[] = TAB.TAB."\$operation->{$propertyName} = \$input->getOption('{$optionName}')";
                    } else {
                        if ($isScalar) {
                            //$output->writeln("checking setter method for scalar property {$propertyName}");
                            $testMethods = [
                                'set'.ucfirst($propertyName)
                            ];
                        } else {
                            $testMethods = [
                                'set'.ucfirst($propertyName).'Id',
                                'set'.ucfirst($propertyName).'OrId',
                                'set'.ucfirst($propertyName).'OrIdOrNull',
                                'set'.ucfirst($propertyName).'IdOrNull',
                            ];
                        }

                        foreach ($testMethods as $methodName) {

                            $output->writeln("- checking setter method {$methodName} for property {$propertyName}");

                            if ($operationClassReflection->hasMethod($methodName)) {
                                $optionSetLines[] = TAB.TAB."\$operation->{$methodName}(\$input->getOption('{$optionName}'));";
                                break;
                            }

                            $operationTraits = Traits::usedByClass($operationClassReflection->getName());
                            foreach ($operationTraits as $operationTrait) {
                                $operationTraitReflection = new \ReflectionClass($operationTrait);
                                if ($operationTraitReflection->hasMethod($methodName)) {
                                    $optionSetLines[] = TAB.TAB."\$operation->{$methodName}(\$input->getOption('{$optionName}'));";
                                    break;
                                }
                            }
                        }
                    }

                } // todo: and if not typed?

            }






            //$cmdName = (string) str($operationClassReflection->getShortName())->removeRight('Operation')->slugify();
            $cmdName = (string) str($operationClassReflection->getShortName())->removeRight('Operation')->dasherize();
            $cmdName = $this->prefix.$cmdName;

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
                "class {$commandClassName} extends Command",
                '{',
                TAB.'protected function configure()',
                TAB.'{',
                TAB.TAB."\$this->setDescription('');",
                TAB.TAB."\$this->setName('{$cmdName}');",
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
                ...$optionSetLines,
                TAB.TAB.'$operation->execute();',
                TAB.'}',

                '}',
            ];


            $fs->dumpFile($commandFile, implode("\n", $code));

        }
    }

}
