<?php
use Symfony\Component\ErrorHandler\Debug;

if (WP_DEBUG && WP_DEBUG_DISPLAY && class_exists('Symfony\Component\ErrorHandler\Debug')) {
    Debug::enable();
}
