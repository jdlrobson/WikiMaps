( function( $ ) {
	var inEditMode = mw.util.getParamValue( 'mapedit' ),
		WikiMap = mw.wikiMaps.WikiMap;

	L.Icon.Default.imagePath = mw.config.get( 'extWikiMapsImagePath' );

	$( '.mw-wiki-map' ).each( function() {
		var map = new WikiMap( this, $( this ).data( 'map' ) );
		if ( inEditMode ) {
			map.makeEditable( map );
		}
	} );
} ( jQuery ) );
