import './bootstrap';
import 'datatables.net-dt/css/dataTables.dataTables.css';

import Alpine from 'alpinejs';
import $ from 'jquery';
import DataTable from 'datatables.net-dt';

window.$ = window.jQuery = $;
DataTable(window, $);

window.Alpine = Alpine;

Alpine.start();
