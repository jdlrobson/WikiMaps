( function() {
	var map = L.map( 'mw-wiki-map-main' ).setView( [ 51.505, -0.09 ], 13 );
	L.tileLayer( mw.config.get( 'extWikiMapsTitleServer' ), {
		attribution: mw.config.get( 'extWikiMapsAttribution' ),
		maxZoom: 18
	} ).addTo(map);
} () );
