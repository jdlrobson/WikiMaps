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

	function featureGroupToGeoJSON( featureGroup ) {
		var newFeatures = [];
		featureGroup.eachLayer( function( l ) {
			newFeatures.push( l.toGeoJSON() );
		});

		return {
			type: 'FeatureCollection',
			features: newFeatures
		};
	}

	function addMap( el, geoJsonData, isEditable ) {
		var lat = mw.util.getParamValue( 'lat' ),
			lon = mw.util.getParamValue( 'lon' ),
			zoom = mw.util.getParamValue( 'zoom' ),
			map = L.map( el ).setView( [ 0, 0 ], 1 ),
			geoJson,
			geoJsonLayer;

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

			geoJsonLayer = L.featureGroup( [ geoJson ] );
			map.fitBounds( geoJsonLayer.getBounds() );

			if ( lat && lon ) {
				map.setView( L.latLng( lat, lon ) );
			}

			if ( zoom ) {
				map.setZoom( zoom );
			}

			geoJsonLayer.addTo( map );
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
