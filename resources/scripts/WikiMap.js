( function( $ ) {
	var activeMap;
	window.onbeforeunload = function() {
		if ( activeMap && activeMap.status.isDirty() ) {
			return 'You have unsaved changes to your map. Please wait for your save to complete before leaving the page.';
		}
	};

	function WikiMapEditStatus( el ) {
		var isDirty = false,
			$icon = $( '<div class="mw-wikimap-status"></div>' ).
				text( 'saving map...' ).appendTo( el );

		this.setDirty = function( status ) {
			isDirty = status;
			if ( status ) {
				$icon.addClass( 'dirty' );
			} else {
				$icon.removeClass( 'dirty' )
			}
		}
		this.isDirty = function() {
			return isDirty;
		}
		this.reportError = function() {
			$icon.addClass( 'error' ).text( 'Failed to save changes.' );
		}
	}

	function WikiMap( el, geoJsonData ) {
		var lat = mw.util.getParamValue( 'lat' ),
			lon = mw.util.getParamValue( 'lon' ),
			zoom = mw.util.getParamValue( 'zoom' );

		this.api = new mw.Api();
		this.status = new WikiMapEditStatus( el );
		this.el = el;
		this.map = L.map( el, { keyboard: false } ).setView( [ 0, 0 ], 1 );
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

		L.tileLayer( mw.config.get( 'wgWikiMapsTileServer' ), {
			attribution: mw.config.get( 'wgWikiMapsAttribution' ),
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
			} ),
			fg = this.featureGroup;

			$.each( geoJson.getLayers(), function() {
				fg.addLayer( this );
			} );
			this.map.fitBounds( fg.getBounds() );
			if ( this.map.getZoom() > 19 ) {
				this.map.setZoom( 15 );
			}
			fg.addTo( this.map );
		},
		makeEditable: function() {
			if ( !activeMap ) {
				this._makeEditable();
				activeMap = this;
			} else {
				throw "Only one map is editable at any given time.";
			}
		},
		_makeEditable: function() {
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
				wikimap._bindSaveEvents();
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
		},
		_bindSaveEvents: function() {
			var self = this;
			this.map.on( 'draw:created', function ( e ) {
				var type = e.layerType,
					layer = e.layer;

				self.addLayer( layer );
				self.save();
			} );
			this.map.on( 'draw:deletestop', function ( e ) {
				self.save();
			} );
			this.map.on( 'draw:edited', function ( e ) {
				self.save();
			} );
		},
		save: function() {
			var self = this,
				apiOptions = {
					action: 'edit',
					title: mw.config.get( 'wgPageName' ),
					summary: 'Updated map via edit interface',
					contentformat: 'application/json',
					text: $.toJSON( this.toGeoJSON() ),
					contentmodel: 'GeoJSON'
				};
			if ( this.status.isDirty() ) {
				this.api.abort();
			}
			this.status.setDirty( true );
			this.api.postWithToken( 'edit', apiOptions ).done( function( data ) {
				self.status.setDirty( false );
			} ).fail( function( msg ) {
				self.status.reportError();
			} );
		}
	};

	mw.wikiMaps = {
		WikiMap: WikiMap
	};
} ( jQuery ) );
