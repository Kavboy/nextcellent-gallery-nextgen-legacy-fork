/**
 * NextCellent implementation of jQuery UI Autocomplete.
 *
 * @param {Object} args
 * @see http://jqueryui.com/demos/autocomplete/
 * @see /xml/json.php for the API.
 * @version 2.0
 */
jQuery.fn.nggAutocomplete = function (args) {
	console.log('autocomplete', args);
	const defaults = {
		type: 'image',
		domain: '',
		limit: 50,
	};

	// overwrite the default values and add others
	const settings = {
		...defaults,
		...args,
	};

	const obj = this[0];
	const id = jQuery(this).attr('id');
	const cache = {};
	let lastXhr;

	/**
	 * The element.
	 */
	const objSelector = jQuery(obj);

	/**
	 * The current value of the dropdown field.
	 */
	let dropdownText = jQuery('#' + id + ' option:selected').text();

	/**
	 * Hide the drop down field and add the search field.
	 */
	objSelector
		.hide()
		.after(
			'<input name="' + id + '_ac" type="search" id="' + id + '_ac"/>'
		);

	/**
	 * The search field.
	 */
	const searchField = jQuery('#' + id + '_ac');

	/**
	 * Add the current value and set the style.
	 */
	searchField
		.val(dropdownText)
		.css('width', '60%')
		.addClass('ui-autocomplete-start');

	/**
	 * Initiate the autocomplete
	 * 20150305: only add term to request if term is not empty
	 */
	searchField.autocomplete({
		source(request, response) {
			console.log('request', request);
			console.log('response', response);
			const term = request.term;
			if (term in cache) {
				console.log('term cache', term);
				response(cache[term]);
				return;
			}
			// adding more $_GET parameter
			lastXhr = jQuery.getJSON(
				settings.domain,
				{
					type: settings.type,
					limit: settings.limit,
					method: 'autocomplete',
					format: 'json',
					callback: 'json',
				},
				function (data, status, xhr) {
					console.log('status', status);
					console.log('data', data);
					// add term to cache
					cache[term] = data;
					if (xhr === lastXhr) response(data);
				}
			);
		},
		minLength: 0,
		select(event, ui) {
			/**
			 * We we will add this to the selector.
			 *
			 * @type {Option} The option to be added.
			 */
			const option = new Option(ui.item.label, ui.item.id);

			/**
			 * Add the select attribute to the option and remove it from the others.
			 */
			jQuery(option).attr('selected', true);
			jQuery('#' + id + ' option:selected').attr('selected', false);

			/**
			 * Add the option.
			 */
			objSelector.append(option);

			/**
			 * Remove autocomplete class.
			 */
			searchField.removeClass('ui-autocomplete-start');

			/**
			 * Update the text selector
			 */
			dropdownText = ui.item.label;

			/**
			 * Trigger a custom event.
			 *
			 * @since 1.1
			 */
			objSelector.trigger('nggAutocompleteDone');
		},
	});

	/**
	 * If the search field is empty and the focus is lost set default text
	 */
	searchField.on('focusout', () => {
		if (searchField.val() === '') {
			searchField[0].value = dropdownText;
		}
	});

	searchField.on('click', function () {
		let search = searchField.val();

		/**
		 * If the selected value is already present, we need to show all images.
		 */
		if (search === dropdownText) {
			searchField[0].value = '';
			search = '';
		}
		searchField.autocomplete('search', search);
	});
};
