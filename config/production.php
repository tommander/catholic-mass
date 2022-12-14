<?php
/**
 * Production environment configuration
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace Tmd\OrderOfMass;

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

const BASE_URL = 'https://mass.tommander.cz/';
const JSON_KEY = '1234567890123456';
const CSRF_KEY = '2345678901234567';
