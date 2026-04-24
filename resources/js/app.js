import './bootstrap';
import 'datatables.net-dt/css/dataTables.dataTables.css';

import Alpine from 'alpinejs';
import $ from 'jquery';
import 'datatables.net-dt';

window.$ = window.jQuery = $;
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
    if (localStorage.getItem('theme')) {
        return;
    }

    const nextTheme = event.matches ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', nextTheme === 'dark');
    document.documentElement.dataset.theme = nextTheme;
});

window.Alpine = Alpine;

Alpine.data('appLayout', () => ({
    isDesktop: window.innerWidth >= 1024,
    sidebarOpen: window.innerWidth >= 1024,
    init() {
        this.handleResize = () => {
            this.isDesktop = window.innerWidth >= 1024;
        };

        window.addEventListener('resize', this.handleResize);
    },
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
    },
    closeSidebar() {
        this.sidebarOpen = false;
    },
    sidebarTransform() {
        return this.sidebarOpen ? 'transform: translateX(0);' : 'transform: translateX(-100%);';
    },
    contentOffset() {
        return this.isDesktop && this.sidebarOpen ? 'padding-left: 18rem;' : 'padding-left: 0;';
    },
}));

Alpine.start();
