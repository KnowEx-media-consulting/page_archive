import Persistent from '@typo3/backend/storage/persistent.js';

/**
 * Hängt den Filter "Archiv ausblenden" ins Menü des Seitenbaums.
 * Die Einstellung liegt beim Benutzer; ausgewertet wird sie über die Bedingung in der User-TSconfig.
 */
const SETTING = 'pageArchiveHideArchived';
const MENU_SELECTOR = 'typo3-backend-navigation-component-pagetree-toolbar ul.dropdown-menu';

const isArchiveHidden = () => String(Persistent.get(SETTING) ?? '1') !== '0';

const updateToggles = () => {
  const visibility = isArchiveHidden() ? 'visible' : 'hidden';
  document.querySelectorAll('.page-archive-filter typo3-backend-icon').forEach((icon) => {
    icon.style.visibility = visibility;
  });
};

const addToggle = (menu) => {
  if (menu.querySelector('.page-archive-filter')) {
    return;
  }
  const item = document.createElement('li');
  item.innerHTML = '<button type="button" class="dropdown-item page-archive-filter">'
    + '<span class="dropdown-item-columns">'
    + '<span class="dropdown-item-column dropdown-item-column-icon" aria-hidden="true">'
    + '<typo3-backend-icon identifier="actions-check" size="small"></typo3-backend-icon></span>'
    + '<span class="dropdown-item-column dropdown-item-column-title"></span>'
    + '</span></button>';
  item.querySelector('.dropdown-item-column-title').textContent = TYPO3.lang['pageArchive.hideArchived'];
  item.querySelector('button').addEventListener('click', () => {
    Persistent.set(SETTING, isArchiveHidden() ? '0' : '1').then(() => {
      updateToggles();
      document.dispatchEvent(new CustomEvent('typo3:pagetree:refresh'));
    });
  });
  menu.append(item);
  updateToggles();
};

const addToggles = () => document.querySelectorAll(MENU_SELECTOR).forEach(addToggle);

// Der Seitenbaum wird erst beim Öffnen eines Moduls und asynchron aufgebaut.
new MutationObserver(addToggles).observe(document.body, { childList: true, subtree: true });
addToggles();
