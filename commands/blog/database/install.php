<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

$cmd = $console->register('blog:database:install');

$cmd->addOption('fixtures', 'f', InputOption::VALUE_NONE, 'Install some fixtures additionally');

$cmd->setDescription('Installs/reloads database schema, can load some fixtures additionally');

$cmd->setCode(function(InputInterface $in, OutputInterface $out) {

    $env = $in->getOption('env');

    $out->writeLn('<info>Reloading database schema for environment: </info><comment>'.$env.'</comment>');
    $out->writeLn('');

    $conf = include APP_DIR . '/config.php';
    $name = $conf['db']['dbname'];
    unset($conf['db']['dbname']);
    $link = '';
    foreach ($conf['db'] as $key => $val) {
        $link .= $key . '=' . $val . ' ';
    }
    // use different link since we cannot select database with query
    if (!$link = pg_connect($link . "options='--client_encoding=UTF8 --timezone=UTC'")) {
        throw new Exception(pg_last_error($link));
    }
    $out->writeLn('<info>Dropping database: </info><comment>'.$name.'</comment>');
    if (!pg_query(sprintf('DROP DATABASE IF EXISTS %s', $name))) {
        throw new Exception(pg_last_error($link));
    }
    $out->writeLn('<info>Creating database: </info><comment>'.$name.'</comment>');
    if (!pg_query(sprintf('CREATE DATABASE %s', $name))) {
        throw new Exception(pg_last_error($link));
    }
    pg_close($link);

    // initialize link with correct database
    $db = service('db');
    $db->query('BEGIN');
    try {
        $out->writeLn('<info>Loading schema..</info>');
        $db->query(file_get_contents(APP_DIR . '/resources/schema/structure.sql'));

        if ($in->getOption('fixtures') !== false) {
            $out->writeLn('<info>Theres no fixtures so far..</info>');
        }
        $db->query('COMMIT');
    } catch (Exception $e) {
        $db->query('ROLLBACK');
        throw $e;
    }
    $out->writeLn('<info>Done.</info>');
});

