import './bootstrap';
import 'datatables.net-dt/css/dataTables.dataTables.css';

import Alpine from 'alpinejs';
import $ from 'jquery';
import DataTable from 'datatables.net-dt';

window.$ = window.jQuery = $;
DataTable(window, $);
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
    if (localStorage.getItem('theme')) {
        return;
    }

    const nextTheme = event.matches ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', nextTheme === 'dark');
    document.documentElement.dataset.theme = nextTheme;
});

window.Alpine = Alpine;

Alpine.start();
