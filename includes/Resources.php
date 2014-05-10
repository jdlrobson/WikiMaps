<?php
$modules = array(
	'wikimaps.scripts' => $wgWikiMapsBoilerplate + array(
		'scripts' => array(
			'resources/scripts/skin.js',
		),
	),
	'wikimaps.leaflet' => $wgWikiMapsBoilerplate + array(
		'scripts' => array(
			'resources/scripts/leaflet.js',
		),
	),
	'wikimaps.view.scripts' => $wgWikiMapsBoilerplate + array(
		'dependencies' => array(
			'wikimaps.leaflet',
		),
		'scripts' => array(
			'resources/scripts/WikiMap.js',
			'resources/scripts/main.js',
		),
	),
	'wikimaps.styles' => $wgWikiMapsBoilerplate + array(
		'styles' => array(
			'resources/styles/leaflet.css',
			'resources/styles/common.less',
		),
	),
	'wikimaps.editor' => $wgWikiMapsBoilerplate + array(
		'dependencies' => array(
			'wikimaps.leaflet',
			'mediawiki.api',
		),
		'scripts' => array(
			'resources/scripts/editor/leaflet.draw.js',
		),
		'styles' => array(
			'resources/styles/editor/leaflet.draw.css',
		),
	),
);
$wgResourceModules = array_merge( $wgResourceModules, $modules );
