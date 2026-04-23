// ============================================================
// Gerenciador de Assinaturas — JavaScript Principal
// Bootstrap 5.3 + Alpine.js
// ============================================================

// Bootstrap JS (dropdowns, modals, tooltips, etc.)
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Em Livewire 4, Alpine vem do bundle ESM do próprio Livewire.
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
window.Alpine = Alpine;
Livewire.start();

// Chart.js será importado sob demanda nos componentes que precisam
// import { Chart } from 'chart.js';
