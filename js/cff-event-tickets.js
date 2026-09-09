(function () {
	'use strict';

	function getQuantityInput(container) {
		var form = container.closest('form.cart');
		return form ? form.querySelector('input.qty[name="quantity"], input.qty') : null;
	}

	function getForm(container) {
		return container.closest('form.cart');
	}

	function getDetailsToggle(container) {
		return container.querySelector('[data-cff-event-ticket-toggle]');
	}

	function getTicketList(container) {
		return container.querySelector('[data-cff-event-ticket-list]');
	}

	function isDetailsEnabled(container) {
		var toggle = getDetailsToggle(container);
		return !!(toggle && toggle.checked);
	}

	function syncRowDisabledState(row, enabled) {
		row.querySelectorAll('input, select, textarea').forEach(function (field) {
			field.disabled = !enabled;
		});
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
		var otherField = row.querySelector('[data-cff-ticket-other]');
		var nameField = row.querySelector('[data-cff-ticket-name]');

		if (!otherCheckbox || !otherWrap) {
			return;
		}

		otherWrap.hidden = !otherCheckbox.checked;

		if (otherField) {
			otherField.disabled = (nameField && nameField.disabled) || !otherCheckbox.checked;
		}
	}

	function buildRows(container) {
		var list = getTicketList(container);
		var template = container.querySelector('[data-cff-event-ticket-template]');
		var quantityInput = getQuantityInput(container);
		var maxTickets = parseInt(container.getAttribute('data-max-tickets'), 10) || 20;
		var quantity = quantityInput ? parseInt(quantityInput.value, 10) : 1;
		var enabled = isDetailsEnabled(container);

		quantity = Math.max(1, Math.min(maxTickets, quantity || 1));
		list.hidden = !enabled;

		while (list.children.length < quantity) {
			var row = template.content.firstElementChild.cloneNode(true);
			list.appendChild(row);
		}

		while (list.children.length > quantity) {
			list.removeChild(list.lastElementChild);
		}

		Array.prototype.forEach.call(list.children, function (row, index) {
			updateFieldNames(row, index);
			syncRowDisabledState(row, enabled);
			syncOtherField(row);
		});
	}

	function setupTableOption(container) {
		var form = getForm(container);
		var quantityInput = getQuantityInput(container);
		var tableToggle = form ? form.querySelector('[data-cff-buy-table]') : null;
		var quantityWrap = quantityInput ? quantityInput.closest('.quantity') : null;

		if (!quantityInput || !tableToggle) {
			return;
		}

		function syncTableState() {
			quantityInput.readOnly = tableToggle.checked;
			quantityInput.setAttribute('aria-disabled', tableToggle.checked ? 'true' : 'false');

			if (quantityWrap) {
				quantityWrap.classList.toggle('is-locked', tableToggle.checked);
			}
		}

		tableToggle.addEventListener('change', function () {
			if (tableToggle.checked) {
				quantityInput.value = 10;
			}

			syncTableState();
			quantityInput.dispatchEvent(new Event('change', { bubbles: true }));
		});

		quantityInput.addEventListener('input', function () {
			if (!quantityInput.readOnly && parseInt(quantityInput.value, 10) !== 10) {
				tableToggle.checked = false;
				syncTableState();
			}
		});

		quantityInput.addEventListener('change', function () {
			if (!quantityInput.readOnly && parseInt(quantityInput.value, 10) !== 10) {
				tableToggle.checked = false;
				syncTableState();
			}
		});

		syncTableState();
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-cff-event-ticket-fields]').forEach(function (container) {
			var quantityInput = getQuantityInput(container);
			var detailsToggle = getDetailsToggle(container);

			buildRows(container);
			setupTableOption(container);

			if (quantityInput) {
				quantityInput.addEventListener('input', function () {
					buildRows(container);
				});

				quantityInput.addEventListener('change', function () {
					buildRows(container);
				});
			}

			if (detailsToggle) {
				detailsToggle.addEventListener('change', function () {
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
