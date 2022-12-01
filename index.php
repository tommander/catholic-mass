<?php
/**
 * Index file for the Order of Mass app
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 * @link    https://github.com/tommander/catholic-mass
 */

namespace TMD\OrderOfMass;

// Without this the source files won't be loaded.
define('OOM_BASE', 'orderofmass');

require __DIR__.'/vendor/autoload.php';

// Include MassMain class and create an instance.
$mass = new MassMain();
$mass->run();
