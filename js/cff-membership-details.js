(function () {
	'use strict';

	function getQuantityInput(container) {
		var form = container.closest('form.cart');
		return form ? form.querySelector('input.qty[name="quantity"], input.qty') : null;
	}

	function updateFieldNames(row, index) {
		var fields = {
			name: row.querySelector('[data-cff-membership-name]'),
			occupation: row.querySelector('[data-cff-membership-occupation]'),
			dateOfBirth: row.querySelector('[data-cff-membership-date-of-birth]'),
			placeOfBirth: row.querySelector('[data-cff-membership-place-of-birth]')
		};
		var heading = row.querySelector('[data-cff-membership-heading]');

		if (heading) {
			heading.textContent = 'Membership ' + (index + 1);
		}

		if (fields.name) {
			fields.name.name = 'cff_memberships[' + index + '][name]';
		}

		if (fields.occupation) {
			fields.occupation.name = 'cff_memberships[' + index + '][occupation]';
		}

		if (fields.dateOfBirth) {
			fields.dateOfBirth.name = 'cff_memberships[' + index + '][date_of_birth]';
		}

		if (fields.placeOfBirth) {
			fields.placeOfBirth.name = 'cff_memberships[' + index + '][place_of_birth]';
		}
	}

	function buildRows(container) {
		var list = container.querySelector('[data-cff-membership-list]');
		var template = container.querySelector('[data-cff-membership-template]');
		var quantityInput = getQuantityInput(container);
		var maxMemberships = parseInt(container.getAttribute('data-max-memberships'), 10) || 20;
		var quantity = quantityInput ? parseInt(quantityInput.value, 10) : 1;

		quantity = Math.max(1, Math.min(maxMemberships, quantity || 1));

		while (list.children.length < quantity) {
			list.appendChild(template.content.firstElementChild.cloneNode(true));
		}

		while (list.children.length > quantity) {
			list.removeChild(list.lastElementChild);
		}

		Array.prototype.forEach.call(list.children, function (row, index) {
			updateFieldNames(row, index);
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-cff-membership-fields]').forEach(function (container) {
			var quantityInput = getQuantityInput(container);

			buildRows(container);

			if (quantityInput) {
				quantityInput.addEventListener('input', function () {
					buildRows(container);
				});

				quantityInput.addEventListener('change', function () {
					buildRows(container);
				});
			}
		});
	});
})();
