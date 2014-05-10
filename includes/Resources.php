<?php
$modules = array(
	'wikimaps.leaflet' => $extWikiMapsBoilerplate + array(
		'scripts' => array(
			'resources/scripts/leaflet.js',
		),
	),
	'wikimaps.scripts' => $extWikiMapsBoilerplate + array(
		'dependencies' => array(
			'wikimaps.leaflet',
		),
		'scripts' => array(
			'resources/scripts/WikiMap.js',
			'resources/scripts/main.js',
		),
	),
	'wikimaps.styles' => $extWikiMapsBoilerplate + array(
		'styles' => array(
			'resources/styles/leaflet.css',
			'resources/styles/common.less',
		),
	),
	'wikimaps.editor' => $extWikiMapsBoilerplate + array(
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
