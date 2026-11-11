/**
 * Shop Page JavaScript — View toggle
 * Suprova's Gallery
 */

// Grid / List view toggle
const gridBtn      = document.getElementById('gridViewBtn');
const listBtn      = document.getElementById('listViewBtn');
const productsGrid = document.getElementById('productsGrid');

gridBtn?.addEventListener('click', () => {
    productsGrid?.classList.remove('list-view');
    gridBtn.classList.add('active');
    listBtn?.classList.remove('active');
    localStorage.setItem('shopView', 'grid');
});

listBtn?.addEventListener('click', () => {
    productsGrid?.classList.add('list-view');
    listBtn.classList.add('active');
    gridBtn?.classList.remove('active');
    localStorage.setItem('shopView', 'list');
});

// Restore preference
if (localStorage.getItem('shopView') === 'list') {
    productsGrid?.classList.add('list-view');
    listBtn?.classList.add('active');
    gridBtn?.classList.remove('active');
}
