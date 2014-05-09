( function( $ ) {
	L.Icon.Default.imagePath = mw.config.get( 'extWikiMapsImagePath' );

	var map = L.map( 'mw-wiki-map-main' ).setView( [ 0, 0 ], 1 ),
		geoJsonData = mw.config.get( 'extWikiMapsCurrentMap' );

	if ( geoJsonData ) {
		L.geoJson( geoJsonData, {
		    onEachFeature: function (feature, layer) {
					var $popup = $( '<div>' ),
						props = feature.properties || {},
						name = props.name,
						desc = props.description;
					if ( name || desc ) {
						if ( name ) {
							$( '<h2>' ).text( name ).appendTo( $popup );
						}
						if ( desc ) {
							$popup.append( desc );
						}
						layer.bindPopup( $popup[0] );
					}
				}
		} ).addTo( map );
	}
	L.tileLayer( mw.config.get( 'extWikiMapsTitleServer' ), {
		attribution: mw.config.get( 'extWikiMapsAttribution' ),
		maxZoom: 18
	} ).addTo(map);
} ( jQuery ) );
