<div>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Painel Pessoal</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="refreshData">
                <i class="bi bi-arrow-clockwise me-1"></i> Atualizar
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Gasto Mensal -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px;">
                <div class="card-body p-4 text-white">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-wallet2 fs-4 opacity-75"></i>
                        <h6 class="mb-0 fw-semibold text-uppercase small opacity-75"><span>Gasto Mensal Estimado</span></h6>
                    </div>
                    @forelse($monthlyTotal as $curr => $val)
                        <div class="{{ !$loop->first ? 'mt-3 pt-2 border-top border-white border-opacity-10' : '' }}">
                            <div class="d-flex justify-content-between align-items-end">
                                <span class="small opacity-75 fw-semibold">{{ $curr }}</span>
                                <h2 class="fw-bold mb-0 {{ !$loop->first ? 'fs-3' : '' }}">
                                    {{ $curr === 'USD' ? 'US$' : ($curr === 'EUR' ? '€' : 'R$') }} {{ number_format($val, 2, ',', '.') }}
                                </h2>
                            </div>
                        </div>
                    @empty
                        <h2 class="fw-bold mb-0">R$ 0,00</h2>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Projeção Anual -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #475569 0%, #334155 100%); border-radius: 16px;">
                <div class="card-body p-4 text-white">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-graph-up-arrow fs-4 opacity-75"></i>
                        <h6 class="mb-0 fw-semibold text-uppercase small opacity-75"><span>Projeção Anual Total</span></h6>
                    </div>
                    @forelse($annualProjection as $curr => $val)
                        <div class="{{ !$loop->first ? 'mt-3 pt-2 border-top border-white border-opacity-10' : '' }}">
                            <div class="d-flex justify-content-between align-items-end">
                                <span class="small opacity-75 fw-semibold">{{ $curr }}</span>
                                <h2 class="fw-bold mb-0 {{ !$loop->first ? 'fs-3' : '' }}">
                                    {{ $curr === 'USD' ? 'US$' : ($curr === 'EUR' ? '€' : 'R$') }} {{ number_format($val, 2, ',', '.') }}
                                </h2>
                            </div>
                        </div>
                    @empty
                        <h2 class="fw-bold mb-0">R$ 0,00</h2>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Próximos Vencimentos -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); border-radius: 16px;">
                <div class="card-body p-4 text-white">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-calendar-event fs-4 opacity-75"></i>
                        <h6 class="mb-0 fw-semibold text-uppercase small opacity-75"><span>Próximos 30 Dias</span></h6>
                    </div>
                    <h2 class="fw-bold mb-0">{{ count($expiringSoonData) }} <small class="fw-normal fs-6 opacity-75">contas vencendo</small></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Histórico de Gastos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up text-primary"></i><span>Histórico de Gastos</span></h6>
                    
                    {{-- Filtros do Gráfico --}}
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="d-flex align-items-center gap-1">
                            <label class="small text-secondary fw-semibold">DE:</label>
                            <input type="date" wire:model.live="startDate" class="form-control form-control-sm bg-dark text-white border-secondary" style="width: 140px; border-radius: 8px;">
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <label class="small text-secondary fw-semibold">ATÉ:</label>
                            <input type="date" wire:model.live="endDate" class="form-control form-control-sm bg-dark text-white border-secondary" style="width: 140px; border-radius: 8px;">
                        </div>
                        <select wire:model.live="aggregation" class="form-select form-select-sm bg-dark text-white border-secondary" style="width: 120px; border-radius: 8px;">
                            <option value="monthly">Mensal</option>
                            <option value="quarterly">Trimestral</option>
                            <option value="semiannual">Semestral</option>
                            <option value="yearly">Anual</option>
                        </select>
                    </div>
                </div>
                <div class="card-body" 
                     wire:ignore 
                     x-data="{
                        instance: null,
                        init() {
                            this.draw($wire.spendingHistory, $wire.todayIndex);
                            $wire.on('update-spending-chart', (data) => {
                                const chartData = Array.isArray(data) ? data[0] : data;
                                this.draw(chartData, $wire.todayIndex);
                            });
                        },
                        draw(data, tIdx) {
                            const canvas = this.$refs.canvas;
                            if (!canvas) return;
                            const existingChart = Chart.getChart(canvas);
                            if (existingChart) existingChart.destroy();
                            if (!data || !data.labels || data.labels.length === 0) return;

                            const currencyColors = { 'BRL': '#10b981', 'USD': '#3b82f6', 'EUR': '#f59e0b' };
                            const datasets = data.datasets.map((ds, index) => {
                                const color = currencyColors[ds.currency] || `hsl(${index * 137.5}, 70%, 50%)`;
                                return {
                                    label: ds.currency,
                                    data: ds.values,
                                    borderColor: color,
                                    backgroundColor: color + '1a',
                                    borderWidth: 3,
                                    fill: data.labels.length > 1,
                                    tension: 0.4,
                                    pointRadius: 5,
                                    pointBackgroundColor: color,
                                    segment: {
                                        borderDash: ctx => (tIdx !== -1 && ctx.p0DataIndex >= tIdx) ? [5, 5] : undefined
                                    }
                                };
                            });

                            this.instance = new Chart(canvas, {
                                type: 'line',
                                data: { labels: data.labels, datasets: datasets },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    interaction: { intersect: false, mode: 'index' },
                                    plugins: {
                                        legend: { display: true, position: 'top', labels: { color: '#94a3b8' } },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    let label = context.dataset.label + ': ';
                                                    label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: context.dataset.label || 'BRL' }).format(context.parsed.y);
                                                    if (tIdx !== -1 && context.dataIndex > tIdx) label += ' (Estimado)';
                                                    return label;
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        y: { beginAtZero: true, grid: { color: 'rgba(148, 163, 184, 0.1)' }, ticks: { color: '#94a3b8' } },
                                        x: { grid: { display: false }, ticks: { color: '#94a3b8', maxRotation: 45, minRotation: 45 } }
                                    }
                                }
                            });
                        }
                     }">
                    <div style="height: 300px; position: relative;">
                        <canvas x-ref="canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Gráfico Categoria -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Despesas por Categoria</h6>
                </div>
                <div class="card-body" wire:ignore
                     x-data="{
                        chartInstance: null,
                        init() {
                            this.$nextTick(() => { this.renderChart($wire.categoryData); });
                            this.$watch('$wire.categoryData', (value) => {
                                this.$nextTick(() => { this.renderChart(value); });
                            });
                        },
                        renderChart(data) {
                            if (typeof Chart === 'undefined') return;
                            if (this.chartInstance) this.chartInstance.destroy();
                            if (!data || data.length === 0) {
                                data = [{ name: 'Sem dados', amount: 1, color: '#444' }];
                            }
                            const labels = data.map(d => d.name);
                            const values = data.map(d => d.amount);
                            const colors = data.map(d => d.color);

                            this.chartInstance = new Chart(this.$refs.canvas, {
                                type: 'doughnut',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        data: values,
                                        backgroundColor: colors,
                                        borderWidth: 0
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { 
                                            position: 'right',
                                            labels: { color: '#fff' } 
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    if (context.label === 'Sem dados') return ' Cadastre uma assinatura!';
                                                    return ' R$ ' + parseFloat(context.raw).toFixed(2).replace('.', ',');
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }
                     }">
                    <div style="height: 250px; position: relative;">
                        <canvas x-ref="canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Próximas a Vencer -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 16px;">
                <div class="card-header bg-transparent py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-hourglass-split me-2 text-warning"></i>Próximos Vencimentos</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse(array_slice($expiringSoonData, 0, 5) as $expiring)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="d-block fw-bold">{{ $expiring['name'] }}</span>
                                    <small class="text-secondary">{{ $expiring['next_billing_date'] }}</small>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill">R$ {{ number_format($expiring['amount'], 2, ',', '.') }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-secondary text-center py-4">
                                Tudo tranquilo pelos próximos 30 dias.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
