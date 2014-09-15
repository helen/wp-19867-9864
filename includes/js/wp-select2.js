(function($) {
	/**
	 * Turn any element with the `.wp-users-dropdown` class into a Select2 dropdown.
	 */
	$('.wp-users-dropdown').each( function( index, element ) {
		var $el = $(element),
			name = $el.attr('name'),
			queryArgs = wp.select2[name].queryArgs,
			show = wp.select2[name].show;
		// Need to know how many users to query per page for infinite scroll.
		queryArgs.number = queryArgs.number || 10;
		$el.select2({
			// @todo: override default CSS of .select2-container instead.
			width: '100%',
			initSelection: function(element, callback) {
				var data = { id: element.val(), text: element.data('selected-show') };
				callback(data);
			},
			/**
			 * Use a custom WordPress endpoint for querying users.
			 */
			ajax: {
				url: wp.ajax.settings.url,
				dataType: 'json',
				/**
				 * Create the settings hash for the $.ajax call.
				 *
				 * @param string search Search input by user.
				 * @param int    page   Pagination of request.
				 * @return object
				 */
				data: function (search, page) {
					return {
						action: 'get_users',
						search: search,
						query_args: queryArgs,
						show: show,
						page_limit: queryArgs.number,
						page: page
					};
				},
				/**
				 * Parse results from AJAX request into format expected by Select2.
				 *
				 * @param  object data Response.
				 * @param  int    page
				 * @return objet
				 */
				results: function (data, page) {
					var more = (page * queryArgs.number) < data.data.total;

					return { results: data.data.users, more: more };
				}
			}
		});
	});
})(jQuery);