<?php

declare(strict_types=1);

namespace PhpKafka\PhpAvroSchemaGenerator\Command;

use PhpKafka\PhpAvroSchemaGenerator\Generator\SchemaGenerator;
use PhpKafka\PhpAvroSchemaGenerator\Registry\ClassRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaGenerateCommand extends Command
{
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

        $classDirectory = $this->getPath($input->getArgument('classDirectory'));
        $outputDirectory = $this->getPath($input->getArgument('outputDirectory'));

        $registry = (new ClassRegistry())
            ->addClassDirectory($classDirectory)
            ->load();

        $generator = new SchemaGenerator($registry, $outputDirectory);

        $schemas = $generator->generate();

        $result = $generator->exportSchemas($schemas);

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
