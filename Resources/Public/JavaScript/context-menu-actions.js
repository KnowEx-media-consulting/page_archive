import { SeverityEnum } from '@typo3/backend/enum/severity.js';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';
import Viewport from '@typo3/backend/viewport.js';

class ContextMenuActions {
  archivePage(table, uid, dataset) {
    this.confirmAndSend(uid, dataset);
  }

  restorePage(table, uid, dataset) {
    this.confirmAndSend(uid, dataset);
  }

  confirmAndSend(uid, dataset) {
    const modal = Modal.confirm(dataset.title, dataset.message, SeverityEnum.warning, [
      { text: dataset.buttonCloseText, active: true, btnClass: 'btn-default', name: 'cancel' },
      { text: dataset.buttonOkText, btnClass: 'btn-warning', name: 'ok' },
    ]);
    modal.addEventListener('button.clicked', (event) => {
      if (event.target.getAttribute('name') === 'ok') {
        new AjaxRequest(dataset.actionUrl)
          .post({ uid: uid })
          .then(async (response) => {
            const result = await response.resolve();
            if (result.success) {
              Notification.success(result.title, result.message);
              top.document.dispatchEvent(new CustomEvent('typo3:pagetree:refresh'));
              Viewport.ContentContainer.refresh();
            } else {
              Notification.error(result.title, result.message);
            }
          })
          .catch(() => Notification.error(dataset.title, dataset.errorText));
      }
      modal.hideModal();
    });
  }
}

export default new ContextMenuActions();
