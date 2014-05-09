( function( $ ) {
	L.Icon.Default.imagePath = mw.config.get( 'extWikiMapsImagePath' );

	var lat = mw.util.getParamValue( 'lat' ),
		lon = mw.util.getParamValue( 'lon' ),
		zoom = mw.util.getParamValue( 'zoom' ),
		map = L.map( 'mw-wiki-map-main' );
		geoJsonData = mw.config.get( 'extWikiMapsCurrentMap' );

	if ( geoJsonData ) {
		var geoJson = L.geoJson( geoJsonData, {
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
		} );

		map.fitBounds( L.featureGroup([geoJson]).getBounds() );

		if ( lat && lon ) {
			map.setView[ lat, lon ];
		}

		if ( zoom ) {
			map.setZoom( zoom );
		}

		geoJson.addTo( map );
	}
	L.tileLayer( mw.config.get( 'extWikiMapsTitleServer' ), {
		attribution: mw.config.get( 'extWikiMapsAttribution' ),
		maxZoom: 18
	} ).addTo(map);

} ( jQuery ) );
