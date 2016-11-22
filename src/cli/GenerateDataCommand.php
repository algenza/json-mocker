<?php

namespace Algenza\Json\Mocker\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Algenza\Fjg\Generator;

class GenerateDataCommand extends Command {

    protected function configure()
    {   
        $this
            // the name of the command (the part after "bin/console")
            ->setName('generate')
            // the short description shown while running "php bin/console list"
            ->setDescription('Generate Fake Data in Json Format.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("This command allows you to generate fake data in json format...");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Starting to generate ...',
            '',
        ]);
        
        $schemaPath = __DIR__.'/../../api/schema.json';
        $targetJson = __DIR__.'/../../api/db.json';

        $generator = new Generator($schemaPath,$targetJson);
        $generator->run();
        // outputs a message followed by a "\n"
        $output->writeln('Json Data Generated!');

    }
}