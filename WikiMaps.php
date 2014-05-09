<?php
/*
 * This file is part of the MediaWiki extension Geo
 *
 * VectorBeta is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * VectorBeta is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Geo.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup extensions
 */

define( 'NS_MAP', 42 );
define( 'NS_MAP_TALK', 43 );
$wgExtraNamespaces[NS_MAP] = "Map";
$wgExtraNamespaces[NS_MAP_TALK] = "Map_talk";

// autoload extension classes
$autoloadClasses = array (
	'GeoHooks' => 'includes/WikiMaps.hooks.php',
);

foreach ( $autoloadClasses as $className => $classFilename ) {
	$wgAutoloadClasses[$className] = __DIR__ . "/$classFilename";
}

$wgHooks['CustomEditor'][] = 'GeoHooks::onCustomEditor';
$wgHooks['BeforePageDisplay'][]  = 'GeoHooks::onBeforePageDisplay';

// Global variables
$extWikiMapsTitleServer = 'http://{s}.tiles.mapbox.com/v3/jdlrobson.i6l7dh8b/{z}/{x}/{y}.png';
$extWikiMapsAttribution = 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>';

// ResourceLoader modules
/**
 * A boilerplate for resource loader modules
 */
$extWikiMapsBoilerplate = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'WikiMaps',
);
require_once __DIR__ . "/includes/Resources.php";
