<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

$cmd = $console->register('assets:dump');

$cmd->setDefinition(array(
    //new InputArgument('dir', InputArgument::REQUIRED, 'Directory name'),
));

$cmd->setDescription('Displays the files in the given directory');

$cmd->setCode(function(InputInterface $input, OutputInterface $output) {
    //$dir = $input->getArgument('dir');

    $output->writeln(sprintf('Assets dump <info>TODO</info>'));
});
