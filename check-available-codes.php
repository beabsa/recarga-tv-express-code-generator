<?php

use CViniciusSDias\RecargaTvExpress\Repository\CodeRepository;
use CViniciusSDias\RecargaTvExpress\Service\CodesCountWarningSender;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/bootstrap.php';

/** @var ContainerInterface $container */
$container = require_once __DIR__ . '/config/dependencies.php';
/** @var CodeRepository $codeRepository */
$codeRepository = $container->get(CodeRepository::class);
$codes = $codeRepository->findNumberOfAvailableCodes();

foreach ($codes as $codesForProduct) {
    if ($codesForProduct < 10) {
        $container->get(CodesCountWarningSender::class)->sendWarning($codes);
    }
}
