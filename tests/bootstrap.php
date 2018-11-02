<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use SebastianBergmann\Comparator\Factory;
use tests\Libero\ContentApiBundle\ResourceComparator;

Factory::getInstance()->register(new ResourceComparator());
