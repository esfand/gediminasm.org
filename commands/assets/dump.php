<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

$cmd = $console->register('assets:dump');

$cmd->addOption('watch', 'w', InputOption::VALUE_NONE, 'Check for changes every n seconds set in option, debug mode only');

$cmd->setDescription('Packs and compresses all assets, which are set in <info>assets/</info> directory');

$cmd->setCode(function(InputInterface $in, OutputInterface $out) {

    $env = $in->getOption('env');

    $scan = function(Closure $processor) {
        foreach (glob(APP_DIR . '/assets/*.php') as $file) {
            $list = include $file;
            foreach ($list as $outputFile => $group) {
                $processed = array();
                foreach ($group as $pattern) {
                    if (false !== strpos($pattern, '*')) {
                        list($before, $after) = explode('*', $pattern, 2);
                        $files = glob(APP_DIR . '/assets/' . $before . '*' . $after);
                        // filter ones which are mapped
                        $files = array_filter($files, function($file) use(&$processed) {
                            return !in_array($file, $processed);
                        });
                        $processed = array_merge($processed, $files);
                    } else {
                        $processed[] = APP_DIR . '/assets/' . $pattern;
                    }
                }
                $processor($outputFile, $processed);
            }
        }
    };

    $packagist = function(array $assets, $filename, OutputInterface $out, $due = null) use (&$env) {
        $yuiFilterCmd = 'java -jar ' . APP_DIR . '/vendor/nervo/yuicompressor/yuicompressor.jar';
        $cacheDir = APP_DIR . '/tmp/generated-assets';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }
        $assetDir = APP_DIR . '/public';

        // go through filters first, etc coffee, less
        $idx = 0;
        $compressed = array();
        $content = '';
        foreach ($assets as $resource) {
            $parts = explode('.', basename($resource));
            switch ($ext = array_pop($parts)) {
                case 'scss':
                    static $scssCompiler;
                    $scssCompiler = $scssCompiler ?: new scssc;
                    $data = file_get_contents($resource);
                    $data = $scssCompiler->compile($data);
                    $ext = 'css'; // compiled to css
                    break;
                case 'coffee':
                    // @TODO: compile coffee script
                    break;
                default:
                    $data = file_get_contents($resource);
                    break;
            }
            $name = str_pad($idx++, 8, '0', STR_PAD_LEFT) . '_' . implode($parts) . '.' . $ext;
            if ($env === 'production') {
                file_put_contents($tmp = $cacheDir.'/'.$name, $data, LOCK_EX);
                exec($yuiFilterCmd . " --type {$ext} -o {$tmp} {$tmp}", $result, $code);
                if (0 !== $code) {
                    // theres an error
                    $out->writeLn('<error>The compressing was interupted by the following error:</error>');
                    $out->writeLn(print_r($result, true));
                }
                $compressed[] = $tmp;
            } else {
                $content .= $data;
            }
        }
        // compress
        if ($compressed) {
            $content = '';
            foreach ($compressed as $file) {
                $content .= "\n" . file_get_contents($file);
                unlink($file);
            }
        }
        $msg = '<info>[file+]</info> '.($target = $assetDir . '/' . $filename);
        if ($due) {
            $msg .= " <comment>due to change in file:</comment> {$due}";
        }
        $out->writeLn($msg);
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0775, true);
        }
        file_put_contents($target, $content, LOCK_EX);
    };

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

