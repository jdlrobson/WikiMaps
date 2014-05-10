( function( $ ) {
	L.Icon.Default.imagePath = mw.config.get( 'extWikiMapsImagePath' );

	function WikiMap( el, geoJsonData ) {
		var lat = mw.util.getParamValue( 'lat' ),
			lon = mw.util.getParamValue( 'lon' ),
			zoom = mw.util.getParamValue( 'zoom' );

		this.map = L.map( el ).setView( [ 0, 0 ], 1 );
		this.featureGroup = new L.FeatureGroup();

		if ( geoJsonData ) {
			this.loadGeoJson( geoJsonData );

			if ( lat && lon ) {
				this.map.setView( L.latLng( lat, lon ) );
			}

			if ( zoom ) {
				this.map.setZoom( zoom );
			}
		}

		L.tileLayer( mw.config.get( 'extWikiMapsTileServer' ), {
			attribution: mw.config.get( 'extWikiMapsAttribution' ),
			maxZoom: 18
		} ).addTo( this.map );
	}

	WikiMap.prototype = {
		loadGeoJson: function( geoJsonData ) {
			var geoJson = L.geoJson( geoJsonData, {
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

			this.featureGroup.addLayer( geoJson );
			this.map.fitBounds( this.featureGroup.getBounds() );
			if ( this.map.getZoom() > 19 ) {
				this.map.setZoom( 15 );
			}
			this.featureGroup.addTo( this.map );
		},
		makeEditable: function() {
			var wikimap = this,
				drawnItems = wikimap.featureGroup,
				map = this.map;
			mw.loader.using( 'wikimaps.editor', function() {
				// Initialise the FeatureGroup to store editable layers
				var drawnControl;

				// Initialise the draw control and pass it the FeatureGroup of editable layers
				drawControl = new L.Control.Draw( {
					edit: {
						featureGroup: drawnItems
					},
					draw: {
						circle: false
					}
				} );

				map.addLayer( drawnItems);
				map.addControl( drawControl );
				mw.wikimaps.bindSaveEvents( wikimap );
			} );
		},
		addLayer: function( layer ) {
			this.featureGroup.addLayer( layer );
			this.map.addLayer( layer );
		},
		toGeoJSON: function() {
			var featureGroup = this.featureGroup,
				newFeatures = [];
			featureGroup.eachLayer( function( l ) {
				newFeatures.push( l.toGeoJSON() );
			} );

			return {
				type: 'FeatureCollection',
				features: newFeatures
			};
		}
	};

	function addMap( el, geoJsonData, isEditable ) {
		var map = new WikiMap( el, geoJsonData );
		if ( isEditable ) {
			map.makeEditable( map );
		}
	}

	$( '.mw-wiki-map' ).each( function() {
		addMap( this, $( this ).data( 'map' ), mw.util.getParamValue( 'mapedit' ) );
	} );

	mw.wikimaps = {};
} ( jQuery ) );
