<?php

namespace Algenza\Json\Mocker\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Algenza\Fjg\Generator;

class GenerateDataCommand extends Command 
{

    protected function configure()
    {   
        $this
            ->setName('generate')
            ->setDescription('Generate Fake Data in Json Format.')
            ->setHelp("This command allows you to generate fake data in json format...");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Starting to generate ...',
            '',
        ]);
        
        $schemaPath = __DIR__.'/../../data/schema.json';
        $targetJson = __DIR__.'/../../data/db.json';

        $generator = new Generator($schemaPath,$targetJson);
        $generator->run();
        $output->writeln('Json Data Generated!');

    }
}
