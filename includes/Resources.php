<?php
$modules = array(
	'wikimaps.scripts' => $extWikiMapsBoilerplate + array(
		'scripts' => array(
			'resources/scripts/leaflet.js',
			'resources/scripts/main.js',
		),
	),
	'wikimaps.styles' => $extWikiMapsBoilerplate + array(
		'styles' => array(
			'resources/styles/leaflet.css',
			'resources/styles/common.less',
		),
	),
);
$wgResourceModules = array_merge( $wgResourceModules, $modules );
