<?php
/**
 * Index file for the Order of Mass app
 *
 * PHP version 7.4
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 * @link    https://github.com/tommander/catholic-mass
 */

namespace TMD\OrderOfMass;

define('OOM_BASE', 'orderofmass');

require __DIR__.'/vendor/autoload.php';

$baseurl = 'http://localhost/mass/';

// Include MassMain class and create an instance.
$mass = new MassMain($baseurl);
$mass->run();
