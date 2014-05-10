( function( $ ) {
	// Hack in the edit link
	function addEditLink() {
		var $li = $( '<li>' ).insertBefore( '#ca-edit' ),
			$span = $( '<span>' ).appendTo( $li );
		$( '<a>' ).attr( 'href', mw.util.getUrl( mw.config.get( 'wgPageName' ), { mapaction: 'edit' } ) ).
			text( 'Edit' ).appendTo( $span );

		if ( mw.util.getParamValue( 'mapaction' ) ) {
			$li.parent().find( '.selected' ).removeClass( 'selected' );
			$li.addClass( 'selected' );
		}
	}
	$( addEditLink );

} ( jQuery ) );
