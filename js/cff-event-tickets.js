(function () {
	'use strict';

	function getQuantityInput(container) {
		var form = container.closest('form.cart');
		return form ? form.querySelector('input.qty[name="quantity"], input.qty') : null;
	}

	function updateFieldNames(row, index) {
		var name = row.querySelector('[data-cff-ticket-name]');
		var other = row.querySelector('[data-cff-ticket-other]');
		var dietary = row.querySelectorAll('[data-cff-ticket-dietary]');
		var heading = row.querySelector('[data-cff-ticket-heading]');

		if (heading) {
			heading.textContent = 'Ticket ' + (index + 1);
		}

		if (name) {
			name.name = 'cff_event_tickets[' + index + '][name]';
		}

		if (other) {
			other.name = 'cff_event_tickets[' + index + '][other]';
		}

		dietary.forEach(function (field) {
			field.name = 'cff_event_tickets[' + index + '][dietary][]';
		});
	}

	function syncOtherField(row) {
		var otherCheckbox = row.querySelector('[data-cff-ticket-dietary][value="other"]');
		var otherWrap = row.querySelector('[data-cff-ticket-other-wrap]');

		if (!otherCheckbox || !otherWrap) {
			return;
		}

		otherWrap.hidden = !otherCheckbox.checked;
	}

	function buildRows(container) {
		var list = container.querySelector('[data-cff-event-ticket-list]');
		var template = container.querySelector('[data-cff-event-ticket-template]');
		var quantityInput = getQuantityInput(container);
		var maxTickets = parseInt(container.getAttribute('data-max-tickets'), 10) || 20;
		var quantity = quantityInput ? parseInt(quantityInput.value, 10) : 1;

		quantity = Math.max(1, Math.min(maxTickets, quantity || 1));

		while (list.children.length < quantity) {
			var row = template.content.firstElementChild.cloneNode(true);
			list.appendChild(row);
		}

		while (list.children.length > quantity) {
			list.removeChild(list.lastElementChild);
		}

		Array.prototype.forEach.call(list.children, function (row, index) {
			updateFieldNames(row, index);
			syncOtherField(row);
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-cff-event-ticket-fields]').forEach(function (container) {
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

			container.addEventListener('change', function (event) {
				if (event.target.matches('[data-cff-ticket-dietary]')) {
					syncOtherField(event.target.closest('[data-cff-event-ticket]'));
				}
			});
		});
	});
})();
