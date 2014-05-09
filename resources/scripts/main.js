( function() {
	var map = L.map( 'mw-wiki-map-main' ).setView( [ 0, 0 ], 1 );
	L.tileLayer( mw.config.get( 'extWikiMapsTitleServer' ), {
		attribution: mw.config.get( 'extWikiMapsAttribution' ),
		maxZoom: 18
	} ).addTo(map);
} () );
