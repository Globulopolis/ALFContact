(function () {
	window.ALF = window.ALF || {};
	let formControl;

	ALF.displayWordCounter = function () {
		const max = parseInt(this.dataset.max, 10);

		if (!isNaN(max)) {
			let textValue = this.value,
				textLength = textValue.length;

			if (textLength > max) {
				this.value = textValue.substring(0, max);

				return false;
			}

			document.querySelector('#textcount').innerText = '(' + String(max - textLength) + '/' + max + ')';
		}

		return false;
	}

	ALF.val = function (el) {
		if (el.options && el.multiple) {
			return el.options
				.filter((option) => option.selected)
				.map((option) => option.value);
		} else {
			return el.value;
		}
	}

	ALF.updateForm = function (e) {
		let form, contactData;

		if (e.target && e.target.nodeName.toLowerCase() === 'select') {
			form = e.target.closest('form');
			contactData = decodeURIComponent(atob(ALF.val(e.target)));
		} else if (e && e.nodeName.toLowerCase() === 'form') {
			form = e;
			contactData = decodeURIComponent(atob(ALF.val(e.querySelector('#' + formControl + '_emailid'))));
		} else {
			return;
		}

		const dataArray = contactData.split(','),
			optFields = dataArray[1].split("\n"),
			optFieldsDiv = document.querySelector('.optfields');
		let optFieldsHtml = '';

		form.querySelector('#' + formControl + '_subject').value = dataArray[2];
		form.querySelector('#' + formControl + '_emailto_id').value = dataArray[0];

		if (optFieldsDiv) {
			optFieldsDiv.remove();
		}

		// Optional field can be empty
		if (optFields[0] !== '') {
			let optDiv = document.createElement('div');

			optDiv.classList.add('optfields');
			optFields.forEach((field, i) => {
				optFieldsHtml += '<div class="control-group">' +
					'<div class="control-label">' +
						'<label id="' + formControl + '_extra_' + i + '-lbl" for="' + formControl + '_extra_' + i + '">' + field + '</label>' +
					'</div>' +
					'<div class="controls">' +
						'<input class="extra_field form-control" type="text" id="' + formControl + '_extra_' + i + '" name="' + formControl + '[extra][' + i + ']" value="" />' +
					'</div>' +
				'</div>';

				optDiv.innerHTML = optFieldsHtml;
			});

			document.querySelector('.startfields').after(optDiv);
		}
	}

	ALF.resetForm = function (form) {
		const fldPrefix = '#' + formControl + '_';

		if (form) {
			const fldName = form.querySelector(fldPrefix + 'name'),
				fldEmail = form.querySelector(fldPrefix + 'email'),
				fldMsg = form.querySelector(fldPrefix + 'message'),
				fldSelect = form.querySelector(fldPrefix + 'emailid'),
				fldCopy = form.querySelector(fldPrefix + 'copy');

			if (!fldName.hasAttribute('readonly')) {
				fldName.value = '';
			}

			if (!fldEmail.hasAttribute('readonly')) {
				fldEmail.value = '';
			}

			fldMsg.value = '';
			fldCopy.checked = false;

			fldSelect.querySelectorAll('option').forEach(function (el) {
				if (el.hasAttribute('selected')) {
					fldSelect.selectedIndex = el.index;
					fldSelect.dispatchEvent(new Event('change'));

					return;
				}
			});

			ALF.updateForm(form);
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		const form = document.querySelector('#contact-form');

		formControl = form.dataset.control;

		const messageField = document.querySelector('#' + formControl + '_message'),
			buttonDataSelector = 'data-submit-task',
			buttons = [].slice.call(document.querySelectorAll(`[${buttonDataSelector}]`));

		/**
		 * Submit the task
		 * @param task
		 * @param el
		 */
		const submitTask = (task, el) => {
			if (task === 'reset' || document.formvalidator.isValid(form)) {
				if (task === 'reset') {
					ALF.resetForm(form);
				} else {
					let extraValues = 'init';

					document.querySelectorAll('.extra_field').forEach((el) => {
						extraValues = extraValues + '#' + el.value;
					});

					document.querySelector('#' + formControl + '_extravalues').value = extraValues;

					form.submit();
				}
			}
		};

		if (messageField) {
			const counterSpan = document.createElement('span'),
				selectInput = document.querySelector('#' + formControl + '_emailid'),
				maxChars = parseInt(messageField.dataset.max, 10);

			if (!isNaN(maxChars)) {
				counterSpan.id = 'textcount';
				counterSpan.innerText = '(' + messageField.dataset.max + '/' + messageField.dataset.max + ')';
				messageField.parentNode.append(counterSpan);
				messageField.addEventListener('keyup', ALF.displayWordCounter);
			}

			if (selectInput) {
				selectInput.addEventListener('change', ALF.updateForm);
			}

			ALF.updateForm(form);
		}

		buttons.forEach(button => {
			button.addEventListener('click', e => {
				e.preventDefault();
				submitTask(e.target.getAttribute(buttonDataSelector), e.target);
			});
		});
	});
})();
