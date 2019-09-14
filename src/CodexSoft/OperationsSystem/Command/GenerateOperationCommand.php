<?php

namespace CodexSoft\OperationsSystem\Command;

use CodexSoft\Code\Helpers\Classes;
use CodexSoft\Code\Shortcuts;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use CodexSoft\OperationsSystem\Operation;
use CodexSoft\OperationsSystem\Exception\OperationException;
use function CodexSoft\Code\str;
use const CodexSoft\Code\TAB;

class GenerateOperationCommand extends Command
{

    use \CodexSoft\OperationsSystem\Traits\OperationsSystemSchemaAwareTrait;

    protected function configure()
    {

        $this
            ->setDescription('Generate a stub operation class.')
            ->addArgument('name', InputArgument::REQUIRED, 'New operation name, like "test.second.someOperation", that will produce "Test\Second\SomeOperation.php"');
        ;
        parent::configure();

    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        Shortcuts::register();
        $operationName = $input->getArgument('name');
        $operationsPath = $this->operationsSystemSchema->getPathToOperations();
        $output->writeln('Operations path: '.$operationsPath);
        $operationName = (string) str($operationName)->replace('/','\\')->replace('.','\\');
        $operationNameParts = explode('\\',$operationName);
        \array_walk($operationNameParts, function (&$part) {
            $part = \ucfirst($part);
        });
        $operationName = implode('\\',$operationNameParts);
        $operationShortName = Classes::short($operationName);

        $operationNamespaceParts = $operationNameParts;
        \array_pop($operationNamespaceParts);
        $operationNamespace = implode('\\',$operationNamespaceParts);

        $operationsNamespace = $this->operationsSystemSchema->getNamespaceOperations();
        if ($operationNamespace) {
            $operationsNamespace .= '\\'; // tood: why?
        }


        $operationClass = $operationsNamespace.$operationName;
        $output->writeln('Operation class: '.$operationClass);
        if (\class_exists($operationClass)) {
            throw new \RuntimeException("Operation class $operationClass already exists!");
        }

        $operationFile = $operationsPath.'/'.str($operationName)->replace('\\','/').'.php';
        if (\file_exists($operationFile)) {
            throw new \RuntimeException("Operation file $operationFile already exists!");
        }
        $output->writeln("Will be written to $operationFile");

        try {
            $uuid = Uuid::uuid4()->toString();
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate UUID for new operation!');
        }

        $operationUuidVar = Operation::_ID_VAR_NAME;

        $code = [
            '<?php',
            "namespace $operationsNamespace$operationNamespace;",
            '',
            'use '.OperationException::class.';',
            'use '.Operation::class.';',
            '',
            '/**',
            " * Class $operationShortName",
            ' * todo: Write description — what this operation for',
            ' * @method void execute() todo: change void to handle() method return type if other',
            ' */',
            "class $operationShortName extends ".Classes::short(Operation::class),
            '{',
            TAB."public const $operationUuidVar = '$uuid';",
            TAB."protected const ERROR_PREFIX = '$operationShortName cannot be completed: '; // todo describe",
            TAB,
            TAB.'/**',
            TAB.' * @return void',
            TAB.' * @throws '.Classes::short(OperationException::class),
            TAB.' */',
            TAB.'protected function validateInputData(): void',
            TAB.'{',
            TAB.TAB.'// todo: implement input data validation (if needed)',
            TAB.'}',
            TAB,
            TAB.'/**',
            TAB.' * @return void',
            TAB.' * @throws '.Classes::short(OperationException::class),
            TAB.' */',
            TAB.'protected function handle(): void',
            TAB.'{',
            TAB.TAB.'// todo: implement',
            TAB.'}',
            TAB,
            '}',
        ];

        foreach ($code as $line) {
            $output->writeln($line);
        }

        $fs = new Filesystem();
        $fs->dumpFile($operationFile,\implode("\n",$code));

    }

}