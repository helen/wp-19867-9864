(function($) {
	// Expose a jQuery widget for an AJAX-powered authors dropdown.
	$.widget( 'wp.wpUsersDropdown', {
		_create: function() {
			var self = this;

			this.name = this.element.attr('name'),
			this.queryArgs = wp.select2[this.name].queryArgs,
			this.show = wp.select2[this.name].show;
			// Need to know how many users to query per page for infinite scroll.
			this.queryArgs.number = this.queryArgs.number || 10;
			this.element.select2({
				// @todo: override default CSS of .select2-container instead.
				width: '100%',
				initSelection: function(element, callback) {
					var data = { id: self.element.val(), text: self.element.data('selected-show') };
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
							query_args: self.queryArgs,
							show: self.show,
							page_limit: self.queryArgs.number,
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
						var more = (page * self.queryArgs.number) < data.data.total;

						return { results: data.data.users, more: more };
					}
				}
			});
		}
	});
	//Turn any element with the `.wp-users-dropdown` class into a Select2 dropdown.
	$('.wp-users-dropdown').wpUsersDropdown();
})(jQuery);