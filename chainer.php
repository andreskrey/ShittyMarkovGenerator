<?php

require_once(__DIR__ . '/src/ShittyMarkovGenerator/markovBot.php');

// Get cli options
$opts = getopt('hr:t:n:l:');
if (isset($opts['h']) || !isset($opts['r'])) {
    echo "Usage: {$_SERVER['PHP_SELF']} <options>\n\n" .
        "Options:\n" .
        "    -h         print this help\n" .
        "    -r <path>  set text file to read\n" .
        "    -t <theme> set theme (default: random)\n" .
        "    -n <len>   set chain length (default: 2)\n" .
        "    -l <num>   set number of chains to generate (default: 10)\n";
    exit(0);
}

if (!is_readable($opts['r'])) {
    fwrite(STDERR, "Path not readable: {$opts['r']}\n");
    exit(1);
} else if (($text = file_get_contents($opts['r'])) === false) {
    fwrite(STDERR, "Failed to read path: {$opts['r']}\n");
    exit(1);
}

$chainer = new ShittyMarkovGenerator\markovBot($text, (isset($opts['n'])) ? $opts['n'] : 2);

echo $chainer->makeChain(isset($opts['l']) ? $opts['l'] : 10, isset($opts['t']) ? $opts['t'] : null);
