<?php

namespace Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Nette\PhpGenerator\PhpFile;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;

class MakeDtosCommand extends Command
{
    protected static $defaultName = 'app:make-dtos';

    protected function configure(): void
    {
        $this
            ->setName('app:make-dtos')
            ->setDescription('Creates or updates DTO classes from Model classes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting DTO generation...');

        $modelsPath = __DIR__ . '/../Models';
        $dtosPath = __DIR__ . '/../DTOs';

        if (!is_dir($dtosPath)) {
            mkdir($dtosPath, 0777, true);
        }

        $finder = new Finder();
        $finder->files()->in($modelsPath)->name('*.php')->notName('Model.php');

        $reflectionExtractor = new ReflectionExtractor();
        $phpDocExtractor = new PhpDocExtractor();
        $propertyInfo = new PropertyInfoExtractor(
            [$reflectionExtractor], // List extractors
            [$phpDocExtractor, $reflectionExtractor], // Type extractors
            [$phpDocExtractor], // Description extractors
            [$reflectionExtractor], // Access extractors
            [$reflectionExtractor]  // Initializable extractors
        );

        foreach ($finder as $modelFile) {
            $modelClassName = "Models\\" . $modelFile->getBasename('.php');
            $dtoClassName = "DTOs\\" . $modelFile->getBasename('.php') . 'DTO';
            $dtoFilePath = $dtosPath . '/' . $modelFile->getBasename('.php') . 'DTO.php';

            $output->writeln("Processing model: {$modelClassName}");

            try {
                $properties = $propertyInfo->getProperties($modelClassName);
            } catch (\ReflectionException $e) {
                $output->writeln("<error>Could not reflect class {$modelClassName}: {$e->getMessage()}</error>");
                continue;
            }


            if (empty($properties)) {
                $output->writeln("No properties found for {$modelClassName}. Skipping.");
                continue;
            }

            $phpFile = new PhpFile;
            $phpFile->setStrictTypes();

            $namespace = $phpFile->addNamespace('DTOs');
            $class = $namespace->addClass($modelFile->getBasename('.php') . 'DTO');
            $class->setReadOnly();

            $constructor = $class->addMethod('__construct');

            foreach ($properties as $property) {
                $types = $propertyInfo->getTypes($modelClassName, $property);
                if ($types) {
                    $type = $types[0];
                    $typeName = $type->getBuiltinType();
                    $isNullable = $type->isNullable();

                    if ($typeName === 'object' && $type->getClassName()) {
                        $typeName = "\\" . $type->getClassName();
                    }

                    $constructor->addPromotedParameter($property)
                        ->setPublic()
                        ->setType($typeName)
                        ->setNullable($isNullable);
                }
            }

            file_put_contents($dtoFilePath, (string) $phpFile);

            $output->writeln("Generated DTO: {$dtoClassName}");
        }

        $output->writeln('DTO generation finished.');
        return Command::SUCCESS;
    }
}