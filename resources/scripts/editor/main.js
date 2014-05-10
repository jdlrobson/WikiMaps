( function( $ ) {
	var module = {
		bindSaveEvents: function( map ) {
			map.on('draw:created', function (e) {
				var type = e.layerType,
					layer = e.layer;

				map.addLayer( layer );
				// TODO: Save geojson
			} );
		}
	};

	$.extend( mw.wikimaps, module );
} ( jQuery ) );
