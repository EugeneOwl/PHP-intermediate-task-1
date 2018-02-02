<?php

declare(strict_types=1);

define('LVL_SYMBOL_SERVER', '<ul>');
define('LVL_SYMBOL_TERMINAL', '->');
define('LVL_SYMBOL_CLOSE', '</ul>');
define('NEW_LINE_SERVER', '<br>');
define('NEW_LINE_TERMINAL', "\n");
define('GREEN_TERMINAL_OUTPUT', array(32));
define('RED_TERMINAL_OUTPUT', array(31));
define('NOT_VALID_FILE_NAMES', array('..', '.'));
define('NO_ACCESS_WARNING', '(No access rights)');
define('NO_ACCESS_WARNING_RED', "\033[".implode(';', RED_TERMINAL_OUTPUT).'m'.NO_ACCESS_WARNING."\033[0m");
define('URL_ADDITION', '?searchName=');
define('PROTOCOL', 'http://');

function isInRegion(int $cur, int $position, int $lenght): bool
{
    if ($cur >= $position && $cur < $position + $lenght) {
        return true;
    } else {
        return false;
    }
}

function boldOutput(string $name, int $position, int $lenght, bool $isServer)
{
    for ($i = 0; $i < strlen($name); $i++) {
        if (isInRegion($i, $position, $lenght)) {
            if ($isServer) {
                echo '<b>' . $name[$i] . '</b>';
            } else {
                echo "\033[".implode(';', GREEN_TERMINAL_OUTPUT).'m'.$name[$i]."\033[0m";
            }
        } else {
            echo $name[$i];
        }
    }
}

function fileNameOutputServer(string $name)
{
    echo "<li>";
    if (($position = stripos($name, FIND)) !== false) {
        boldOutput($name, $position, strlen(FIND), true);
    } else {
        echo $name;
    }
    echo '</li>';
}

function showAccessRightsWarning()
{
    if (isServerRunning()) {
        echo "<span style='color: orangered'>(No access rights)</span><br>";
    } else {
        echo NO_ACCESS_WARNING_RED . NEW_LINE_TERMINAL;
    }
}

function lvlOutput(int $lvl, string $symbol)
{
    for ($i = 0; $i < $lvl; $i++) {
        echo $symbol;
    }
}

function lvlOutputClose(int $lvl)
{
    for ($i = 0; $i < $lvl; $i++) {
        echo LVL_SYMBOL_CLOSE;
    }
}

function fileNameAndPositionOutputServer(string $file, int $lvl)
{
    lvlOutput($lvl, LVL_SYMBOL_SERVER);
    fileNameOutputServer($file);
    lvlOutputClose($lvl);
}

function fileNameOutputTerminal(string $name)
{
    if (($position = stripos($name, FIND)) !== false) {
        boldOutput($name, $position, strlen(FIND), false);
    } else {
        echo $name;
    }
    echo NEW_LINE_TERMINAL;
}

function fileNameAndPositionOutputTerminal(string $file, int $lvl)
{
    lvlOutput($lvl, LVL_SYMBOL_TERMINAL);
    fileNameOutputTerminal($file);
}

function isSymlink(string $dir, string $file): bool
{
    if (filetype($dir . $file) == 'link') {
        return true;
    } else {
        return false;
    }
}

function fileFullName(string $parents, string $fileName): string
{
    return $parents . $fileName . '/';
}

function fileNameAndPositionOutput(string $file, int $lvl)
{
    if (isServerRunning()) {
        fileNameAndPositionOutputServer($file, $lvl);
    } else {
        fileNameAndPositionOutputTerminal($file, $lvl);
    }
}

function isFileFormatValid(string $name): bool
{
    if (!in_array($name, NOT_VALID_FILE_NAMES) && $name[0] != '.') {
        return true;
    } else {
        return false;
    }
}

function setSearchNameTerminal()
{
    if (isset($_SESSION['searchName'])) {
        define('FIND', $_SESSION['searchName']);
    } else {
        define('FIND', '');
    }
}

function walkingDown(string $dir, int $lvl)
{
    if (is_dir($dir)) {
        if (@$dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (isFileFormatValid($file)) {
                    fileNameAndPositionOutput($file, $lvl);
                    if (is_dir(fileFullName($dir, $file)) && !isSymlink($dir, $file)) {
                        walkingDown(fileFullName($dir, $file), ++$lvl);
                        $lvl--;
                    }
                }
            }
            closedir($dh);
        } else {
            showAccessRightsWarning();
        }
    }
}

function setSearchNameServer()
{
    if (isset($_GET['searchName'])) {
        define('FIND', $_GET['searchName']);
    } else {
        define('FIND', '');
    }
}

function addSearchToURL(string $addition)
{
    $url = PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    if (stripos($url, $addition) === false) {
        $url .= $addition;
        header('Location: ' . $url);
        exit;
    }
}

function showTerminalHierarchy(string $dir)
{
    setSearchNameTerminal();
    walkingDown($dir, 0);
}

function showServerHierarchy(string $dir)
{
    addSearchToURL(URL_ADDITION);
    setSearchNameServer();
    echo LVL_SYMBOL_SERVER;
    walkingDown($dir, 0);
}

function isServerRunning(): bool
{
    if (PHP_SAPI == 'cli') {
        return false;
    } else {
        return true;
    }
}

function determineExecutorAndRun($dir)
{
    if (isServerRunning()) {
        showServerHierarchy($dir);
    } else {
        showTerminalHierarchy($dir);
    }
}
