(function($) {
	$('#post_author_override').select2({
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
					page_limit: 10,
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
				return { results: data.data.users };
			}
		}
	});
})(jQuery);