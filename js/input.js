jQuery( document ).ready(function($)
{
	$(document).on( 'acf/setup_fields', function()
	{
		$( '.post-selector' ).each(function()
		{
			$(this).select2();
		});
	} );

});