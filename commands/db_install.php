<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

$cmd = $console->register('db:install');

$cmd->addOption('fixtures', 'f', InputOption::VALUE_NONE, 'Install some fixtures additionally');

$cmd->setDescription('Installs/reloads database schema, can load some fixtures additionally');

$cmd->setCode(function(InputInterface $in, OutputInterface $out) {

    $env = $in->getOption('env');

    $out->writeLn('<info>Dumping assets in environment:</info> <comment>'.$env.'</comment>');
    $out->writeln(sprintf('Compression is <comment>%s</comment>.', $env === 'production' ? 'on' : 'off'));
    $out->writeLn('');

    $remembered = array(); // for watching mtime
    $watch = $in->getOption('watch');
    if ($env === 'production' && $watch !== false) {
        throw new Exception("Assets cannot be watched in production mode");
    }

    // create symlinks for images or fonts
    foreach (array('img') as $target) {
        if (!file_exists($link = APP_DIR . '/public/' . $target)) {
            symlink(APP_DIR . '/assets/' . $target, $link);
        }
    }
    if ($watch !== false) {
        $out->writeLn('');
        $out->writeLn('<info>Watching for asset changes...</info>');
        $out->writeLn('');
        while ($watch !== false) {
            $scan(function($filename, array $assets) use (&$remembered, &$packagist, &$out) {
                $regenerate = null;
                foreach ($assets as $file) {
                    $time = filemtime($file);
                    // check to regenerate
                    if (!isset($remembered[$filename][$file]) || $time !== $remembered[$filename][$file]) {
                        $regenerate = $file;
                        $remembered[$filename][$file] = $time;
                    }
                }
                if ($regenerate) {
                    $packagist($assets, $filename, $out, $regenerate);
                }
            });
            sleep(intval($watch));
        }
    } else {
        $scan(function($filename, array $assets) use (&$packagist, &$out) {
            $packagist($assets, $filename, $out);
        });
    }

});

