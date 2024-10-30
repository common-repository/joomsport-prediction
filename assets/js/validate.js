( function ( $ ) {
	$( document ).ready( function () {

		//Require post title when adding/editing Project Summaries
		$( 'body' ).on( 'submit.edit-post', '#post', function () {

			// If the title isn't set
			if ( $( "#title" ).val().replace( / /g, '' ).length === 0 ) {

				// Show the alert
				window.alert( 'A title is required.' );

				// Hide the spinner
				$( '#major-publishing-actions .spinner' ).hide();

				// The buttons get "disabled" added to them on submit. Remove that class.
				$( '#major-publishing-actions' ).find( ':button, :submit, a.submitdelete, #post-preview' ).removeClass( 'disabled' );

				// Focus on the title field.
				$( "#title" ).focus();

				return false;
			}
                        if($('#post_type').val() == 'jswprediction_league'){
                            console.log($('#js_seasons_list option:selected').length);
                            if($('#js_seasons_list option:selected').length == 0){
                                    window.alert( 'Seasons is required.' );

                                    // Hide the spinner
                                    $( '#major-publishing-actions .spinner' ).hide();

                                    // The buttons get "disabled" added to them on submit. Remove that class.
                                    $( '#major-publishing-actions' ).find( ':button, :submit, a.submitdelete, #post-preview' ).removeClass( 'disabled' );

                                    // Focus on the title field.
                                    $( "#joomsport_tournament_inseas_id" ).focus();
                                    return false;
                            }
                        }
                        
		});
                
                
	});
}( jQuery ) );