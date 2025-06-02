$(function() {

	var selectControl = $('select#pagemodule');
	var dataControl = $('#il_prop_cont_data');

	var clearDataControl = function() {
		dataControl.siblings('.b3-subform').remove();
	};

	var updateDataControl = function() {
		const data = {};
		$('.b3-control').each(function() {
			data[$(this).attr('name').substr(8)] = $(this).val();
		});
		console.log(data);
		const json = JSON.stringify(data);
		dataControl.find('input').val(json);
	};

	var transformDataControl = function(schema) {
		let data = {};
		try {
			data = JSON.parse(dataControl.find('input').val());
		} catch(e) {
			data = {};
		}
		for (const field in schema.properties) {
			var property = schema.properties[field];
			var row = $('<div class="form-group row b3-subform"></div>');
			var required = schema.required.includes(field) ? '<span class="asterisk">*</span>' : '';
			$('<label for="subform-' + field + '" class="col-lg-2 col-md-3 col-sm-4 control-label">' + property.description + ' ' + required + '</label>').appendTo(row);
			var col = $('<div class="col-lg-10 col-md-9 col-sm-8"></div>');
			var value = data[field] === undefined ? '' : data[field];
			var maxlength = property.maxLength === undefined ? '' : 'maklength="' + property.maxLength + '"';
			$('<input type="text" name="subform-' + field + '" value="' + value + '" class="form-control b3-control" ' + maxlength + ' />')
				.on('change', function() { updateDataControl(); })
				.appendTo(col);
			col.appendTo(row);
			dataControl.after(row);
		}
	};

	var loadSchema = function(pagemodule) {
		clearDataControl();
		var req = {
			name: 'moduledpageservice',
			out: 'json',
			// rest: 1,
			method: 'schema',
			pagemodule: pagemodule
		};
		$.getJSON("base3.php", req, function(schema) {
			if (!schema || schema.properties === undefined) {
				console.log('No schema available');
				return;
			}
			console.log(schema);
			transformDataControl(schema);
		});
	};

	selectControl.on('change', function() {
		var pagemodule = $(this).val();
		console.log('pagemodule selected: ' + pagemodule);
		loadSchema(pagemodule);
	});

	dataControl.hide();
	loadSchema(selectControl.val());

});
