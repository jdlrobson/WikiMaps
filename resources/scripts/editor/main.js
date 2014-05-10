( function( $ ) {
	var api = new mw.Api(), module;

	module = {
		bindSaveEvents: function( wikiMap ) {
			var self = this;
			wikiMap.map.on('draw:created', function ( e ) {
				var type = e.layerType,
					layer = e.layer;

				wikiMap.addLayer( layer );
				self.save( wikiMap.toGeoJSON() );
			} );
		},
		save: function( geoJson ) {
			var apiOptions = {
				action: 'edit',
				title: mw.config.get( 'wgPageName' ),
				summary: 'Updated map via edit interface',
				contentformat: 'application/json',
				text: $.toJSON( geoJson ),
				contentmodel: 'GeoJSON'
			};
			api.postWithToken( 'edit', apiOptions );
		}
	};

	$.extend( mw.wikimaps, module );
} ( jQuery ) );
