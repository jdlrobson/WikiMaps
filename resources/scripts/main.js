( function( $ ) {
	var WikiMap = mw.wikiMaps.WikiMap,
		inEditMode = mw.util.getParamValue( 'mapaction' );
	L.Icon.Default.imagePath =  mw.config.get( 'wgWikiMapsImagePath' );

	$( '.mw-wiki-map' ).each( function() {
		var map = new WikiMap( this, $( this ).data( 'map' ) );
		if ( inEditMode ) {
			map.makeEditable( map );
		}
	} );

} ( jQuery ) );
