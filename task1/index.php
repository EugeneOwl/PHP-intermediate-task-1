<?php

declare(strict_types=1);

require_once 'functions.php';

if (isset($argv[1])) {
    $_SESSION['searchName'] = $argv[1];
}
$dir = getcwd() . '/';
determineExecutorAndRun($dir);
