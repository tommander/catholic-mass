<?php
/**
 * Index file for the Order of Mass app
 *
 * PHP version 7.4
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 * @link    https://github.com/tommander/catholic-mass
 */

namespace TMD\OrderOfMass;

define('OOM_BASE', 'orderofmass');

require __DIR__.'/vendor/autoload.php';

// Include MassMain class and create an instance.
$mass = new MassMain();
$mass->run();
