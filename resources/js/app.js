import Alpine from 'alpinejs';

import '@fontsource/figtree/400.css';
import '@fontsource/figtree/500.css';
import '@fontsource/figtree/600.css';
import '@fontsource/source-serif-4/400.css';
import '@fontsource/source-serif-4/600.css';

import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
Chart.register(ChartDataLabels);
window.Chart = Chart;

window.Alpine = Alpine;

Alpine.start();
