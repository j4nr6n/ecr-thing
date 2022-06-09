import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

import '@styles/modal-form.scss';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['modal', 'modalBody'];
    static values = {
        formUrl: String,
    }

    modal = null;

    async openModal(event) {
        event.preventDefault();

        this.modalBodyTarget.innerHTML = 'Loading...';

        this.modal = new Modal(this.modalTarget);
        this.modal.show();

        this.modalBodyTarget.innerHTML = await fetch(this.formUrlValue)
            .then((response) => response.text());
    }

    async submitForm(event) {
        event.preventDefault();

        const form = this.modalBodyTarget.getElementsByTagName('form')[0];

        let url = this.formUrlValue;
        let init = {method: form.method};

        if (['get', 'head'].includes(init.method)) {
            url += '?' + new URLSearchParams(new FormData(form)).toString();
        } else {
            init.body = new URLSearchParams(new FormData(form));
        }

        const response = await fetch(url, init);

        if (!response.ok) {
            this.modalBodyTarget.innerHTML = await response.text();

            return;
        }

        this.element.dispatchEvent(new CustomEvent('modal-form:success', {
            detail: Object.assign({}, this),
            bubbles: true,
            cancelable: true
        }));

        this.modal.hide();
    }
}
