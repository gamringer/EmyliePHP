<?php

include dirname(__FILE__).'/../SplClassLoader.php';

(new \SplClassLoader('Emylie', dirname(__FILE__).'/../src'))->register();