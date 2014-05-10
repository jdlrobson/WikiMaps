( function( $ ) {
	L.Icon.Default.imagePath = mw.config.get( 'extWikiMapsImagePath' );

	function makeEditable( map ) {
		mw.loader.using( 'wikimaps.editor', function() {
			// Initialise the FeatureGroup to store editable layers
			var drawnItems, drawnControl;
			drawnItems = new L.FeatureGroup();

			// Initialise the draw control and pass it the FeatureGroup of editable layers
			drawControl = new L.Control.Draw( {
				edit: {
					featureGroup: drawnItems
				}
			} );

			map.addLayer( drawnItems);
			map.addControl( drawControl );
			mw.wikimaps.bindSaveEvents( map );
		} );
	}

	function addMap( el, geoJsonData, isEditable ) {
		var lat = mw.util.getParamValue( 'lat' ),
			lon = mw.util.getParamValue( 'lon' ),
			zoom = mw.util.getParamValue( 'zoom' ),
			map = L.map( el ).setView( [ 0, 0 ], 1 ),
			geoJson;

		if ( geoJsonData ) {
			geoJson = L.geoJson( geoJsonData, {
				onEachFeature: function ( feature, layer ) {
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

			map.fitBounds( L.featureGroup( [ geoJson ] ).getBounds() );
			if ( map.getZoom() > 19 ) {
				map.setZoom( 15 );
			}
			if ( lat && lon ) {
				map.setView( L.latLng( lat, lon ) );
			}

			if ( zoom ) {
				map.setZoom( zoom );
			}

			geoJson.addTo( map );
		}
		L.tileLayer( mw.config.get( 'extWikiMapsTileServer' ), {
			attribution: mw.config.get( 'extWikiMapsAttribution' ),
			maxZoom: 18
		} ).addTo( map );

		if ( isEditable ) {
			makeEditable( map );
		}
	}

	$( '.mw-wiki-map' ).each( function() {
		addMap( this, $( this ).data( 'map' ), mw.util.getParamValue( 'mapedit' ) );
	} );

	mw.wikimaps = {};
} ( jQuery ) );
