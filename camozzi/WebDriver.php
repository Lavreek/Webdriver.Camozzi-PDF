<?php

namespace Camozzi\Control;

define('ROOT', dirname(__DIR__));

include ROOT . "/vendor/autoload.php";

$driver = new Driver();

register_shutdown_function([$driver, 'closeDriver']);

$resource_origin = ROOT . "/resources";

if (!is_dir($resource_origin)) {
    mkdir($resource_origin, 777, true);
}

$driver->moveTo("https://catalog.camozzi.ru/");
$driver->wait(time: 5);


$camozziFullTree = $driver->getElements(false,  'id', 'treebase');
$camozziTreeHeads = $driver->getElements(true, 'className', 'treenodehead', $camozziFullTree);

$treeLevelStart = 0;
$treeLevels = [];
$currentLevels = [];

$counts = 0;
foreach ($camozziTreeHeads as $treeHead) {
    $treeHead->click();

    for ($level = 0; $level < 5; $level++) {
        $headLevel = $driver->getElements(false, 'className', 'treelevel'.$level, $treeHead);

        if (!is_null($headLevel)) {
            break;
        }
    }

    $pdfLink = "";
    $headTitle = trim($headLevel->getText(), '\.');
    $headTitle = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '', $headTitle);

    $treePDF = $driver->getElements(false, 'css', 'pdf.invisible', $treeHead);

    if (!is_null($treePDF)) {
        $pdfLink = $treePDF->getDomProperty('href');
    }

    if (empty($currentLevels[$level])) {
        $currentLevels[$level] = $headTitle;
    } else {
        foreach ($currentLevels as $cLevel => $cTitle) {
            if ($cLevel == $level) {
                $currentLevels[$level] = $headTitle;
            } elseif ($cLevel > $level) {
                unset($currentLevels[$cLevel]);
            }
        }
        $currentLevels = array_values($currentLevels);
    }

    $resourcePath = "";

    if (!empty($headTitle)) {
        $path = "";

        for ($i = 0; $i < $level; $i++) {
            $path .= $currentLevels[$i] . "/";
        }

        $resourcePath = $resource_origin . "/". $path . $headTitle . "/";

        if (!is_dir($resourcePath)) {
            var_dump("\n Create path: \n\t $resourcePath \n");
            mkdir($resourcePath, 777, true);
        }
    }

    if (!empty($pdfLink)) {
        $f = fopen($pdfLink, 'r');

        $pdfContent = false;

        while (!$pdfContent) {
            $pdfContent = stream_get_contents($f);
        }

        $filepath = $resourcePath . $headTitle . ".pdf";

        $writed = false;

        while (!$writed) {
            $writed = file_put_contents($filepath, $pdfContent);
        }
    }
}
