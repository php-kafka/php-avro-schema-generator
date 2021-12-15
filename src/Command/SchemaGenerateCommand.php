<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Command;

use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGenerator;
use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGeneratorInterface;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistry;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaGenerateCommand extends Command
{
    private SchemaGeneratorInterface $generator;
    private ClassRegistryInterface $classRegistry;

    public function __construct(
        ClassRegistryInterface $classRegistry,
        SchemaGeneratorInterface $generator,
        string $name = null
    ) {
        $this->classRegistry = $classRegistry;
        $this->generator = $generator;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('avro:schema:generate')
            ->setDescription('Generate schema from classes')
            ->setHelp('Experimental: Generate schema files from class files')
            ->addArgument('classDirectory', InputArgument::REQUIRED, 'Class directory')
            ->addArgument('outputDirectory', InputArgument::REQUIRED, 'Output directory')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Generating schema files');

        /** @var string $classDirectoryArg */
        $classDirectoryArg = $input->getArgument('classDirectory');
        /** @var string $outputDirectoryArg */
        $outputDirectoryArg = $input->getArgument('outputDirectory');

        $classDirectory = $this->getPath($classDirectoryArg);
        $outputDirectory = $this->getPath($outputDirectoryArg);

        $registry = $this->classRegistry->addClassDirectory($classDirectory)->load();
        $this->generator->setOutputDirectory($outputDirectory);
        $this->generator->setClassRegistry($registry);

        $schemas = $this->generator->generate();
        $result = $this->generator->exportSchemas($schemas);

        // retrieve the argument value using getArgument()
        $output->writeln(sprintf('Generated %d schema files', $result));

        return 0;
    }

    /**
     * @param string $path
     * @return string
     */
    private function getPath(string $path): string
    {
        $result = realpath($path);

        if (false === $result || false === is_dir($result)) {
            throw new \RuntimeException(sprintf('Directory not found %s', $path));
        }

        return $result;
    }
}
