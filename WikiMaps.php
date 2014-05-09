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
	'GeoJSONContent' => 'includes/GeoJSONContent.php',
	'GeoJSONContentHandler' => 'includes/GeoJSONContentHandler.php',
);

$wgHooks[ 'CodeEditorGetPageLanguage' ][] = 'GeoHooks::onCodeEditorGetPageLanguage';

/**
 * Takes a string of JSON data and formats it for readability.
 * @param string $json
 * @return string|null: Formatted JSON or null if input was invalid.
 */
function efMapBeautifyJson( $json ) {
	$decoded = FormatJson::decode( $json, true );
	if ( !is_array( $decoded ) ) {
		return;
	}
	return FormatJson::encode( $decoded, true );
}

foreach ( $autoloadClasses as $className => $classFilename ) {
	$wgAutoloadClasses[$className] = __DIR__ . "/$classFilename";
}

$wgHooks['BeforePageDisplay'][]  = 'GeoHooks::onBeforePageDisplay';

$wgContentHandlers[ 'GeoJSON' ] = 'GeoJSONContentHandler';
$wgNamespaceContentModels[ NS_MAP ] = 'GeoJSON';

// Parser hook
$wgHooks['ParserFirstCallInit'][] = 'GeoHooks::onWikiMapParserInit';


// Global variables
$extWikiMapsTileServer = 'http://{s}.tiles.mapbox.com/v3/jdlrobson.i6l7dh8b/{z}/{x}/{y}.png';
$extWikiMapsAttribution = 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>';
$extWikiMapsImagePath = '//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.2/images/';


// ResourceLoader modules
/**
 * A boilerplate for resource loader modules
 */
$extWikiMapsBoilerplate = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'WikiMaps',
	'targets' => array( 'mobile', 'desktop' ),
);
require_once __DIR__ . "/includes/Resources.php";
