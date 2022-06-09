import { Modal } from 'bootstrap';

const TurboHelper = class {
    constructor() {
        document.addEventListener('turbo:before-cache', () => {
            this.closeModal();
        });
    }

    closeModal() {
        if (document.body.classList.contains('modal-open')) {
            const modalEl = document.querySelector('.modal.show');
            const modal = Modal.getInstance(modalEl);

            modalEl.classList.remove('fade');

            modal._backdrop._config.isAnimated = false;
            modal.hide();
            modal.dispose();
        }
    }
}

export default new TurboHelper();
