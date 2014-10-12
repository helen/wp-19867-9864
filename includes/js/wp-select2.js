(function($) {
	// Expose a jQuery widget for creating a Select2 instance.
	$.widget( 'wp.wpSelect', {
		_create: function() {
			var self = this;

			// Default options for the Select2 instance.
			this.select2Options = {
				// @todo: override default CSS of .select2-container instead.
				width: '100%'
			};

			this.prepareSelect2Options();

			this.element.select2(this.select2Options);
		},

		prepareSelect2Options: function() {
			if ( this.element.data( 'dropdown-type' ) === 'users' ) {
				this.prepareUserDropdownOptions();
			}
		},

		/**
		 * Add config to the options hash for a user dropdwon.
		 */
		prepareUserDropdownOptions: function() {
			var self = this,
				name = this.element.attr('name'),
				queryArgs = wp.select2[name].queryArgs,
				show = wp.select2[name].show;

			queryArgs.number = queryArgs.number || 10;

			this.select2Options.initSelection = function(element, callback) {
				var data = { id: self.element.val(), text: self.element.data('selected-show') };
				callback(data);
			};

			/**
			 * Use a custom WordPress endpoint for querying users.
			 */
			this.select2Options.ajax = {
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
			};
		}
	});
	//Turn any element with the `.wp-select-dropdown` class into a Select2 instance.
	$('.wp-select-dropdown').wpSelect();
})(jQuery);