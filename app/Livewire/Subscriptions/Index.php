<?php

namespace App\Livewire\Subscriptions;

use App\Models\Category;
use App\Models\Subscription;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Response;

class Index extends Component
{
    use WithFileUploads; // Removido WithPagination para evitar que o Livewire force a URL

    public int $page = 1;

    // protected string $paginationTheme = 'bootstrap'; // Não necessário com paginação manual

    public $csvFile;
    public $importStatus = '';
    public bool $ignoreDuplicates = true;
    public bool $showImportModal = false;
    public array $importSummary = [
        'total' => 0,
        'duplicates' => 0,
        'new' => 0,
    ];
    // public array $tempImportData = []; // Removido para evitar erro de snapshot grande
    public array $selectedIds = [];
    public bool $selectAll = false;

    public function updatedCsvFile()
    {
        if ($this->csvFile) {
            $this->prepareImport();
        }
    }

    public function prepareImport()
    {
        $this->validate([
            'csvFile' => 'required|mimes:csv,txt|max:1024',
        ]);

        $token = auth()->user()->privacyToken?->token;
        if (!$token) return;

        $path = $this->csvFile->getRealPath();
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) return;

        $delimiter = str_contains($lines[0], ';') ? ';' : ',';
        $data = [];
        foreach ($lines as $line) {
            if (empty($data)) {
                $line = preg_replace('/^[\xef\xbb\xbf]+/', '', $line);
            }
            $data[] = str_getcsv($line, $delimiter);
        }

        array_shift($data); // Remove header
        session()->put('temp_import_data_' . auth()->id(), $data);

        $total = count($data);
        $namesInCsv = array_unique(array_filter(array_map(fn($row) => trim($row[0] ?? ''), $data)));
        
        $existingNames = Subscription::where('privacy_token', $token)
            ->whereIn('name', $namesInCsv)
            ->pluck('name')
            ->map(fn($n) => strtolower($n))
            ->toArray();

        $duplicates = 0;
        foreach ($data as $row) {
            if (count($row) >= 1 && in_array(strtolower(trim($row[0])), $existingNames)) {
                $duplicates++;
            }
        }

        $this->importSummary = [
            'total' => $total,
            'duplicates' => $duplicates,
            'new' => $total - $duplicates,
        ];

        $this->showImportModal = true;
    }

    public string $search = '';
    public string $statusFilter = 'all';
    public string $categoryFilter = 'all';
    public string $sortColumn = 'next_billing_date';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $token = auth()->user()->privacyToken?->token;
            $this->selectedIds = Subscription::byPrivacyToken($token)
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function deleteSelected(): void
    {
        if (empty($this->selectedIds)) return;

        $token = auth()->user()->privacyToken?->token;
        Subscription::byPrivacyToken($token)
            ->whereIn('id', $this->selectedIds)
            ->delete();

        app(\App\Services\CacheService::class)->invalidateUserCache($token);
        
        $count = count($this->selectedIds);
        $this->selectedIds = [];
        $this->selectAll = false;

        session()->flash('success', "{$count} assinaturas excluídas com sucesso!");
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public bool $showFormModal = false;
    public bool $showDeleteModal = false;
    
    public ?string $editingId = null;
    public ?string $deletingId = null;
    public string $deletingName = '';

    public function gotoPage($page)
    {
        $this->page = $page;
    }

    public function nextPage()
    {
        $this->page++;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function resetPage()
    {
        $this->page = 1;
    }

    // Form fields
    public string $name = '';
    public ?int $category_id = null;
    public bool $isCreatingCategory = false;
    public string $newCategoryName = '';
    public string $newCategoryColor = '#0F6CBD';
    public string $selectedCategoryColor = '#0F6CBD';
    public string $billing_cycle = 'monthly';
    public ?int $custom_cycle_interval = null;
    public string $custom_cycle_period = 'months';
    public string $amount = '';
    public string $currency = 'BRL';
    public string $start_date = '';
    public string $next_billing_date = '';
    public string $status = 'active';
    public ?string $cancelled_at = null;
    public bool $auto_renew = true;
    public bool $is_domain = false;
    public string $notes = '';
    public string $service_url = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedName($value): void
    {
        if ($this->editingId) {
            return; // Only autofill when creating new
        }

        $service = strtolower(trim($value));

        $templates = [
            'netflix' => ['amount' => '39.90', 'cycle' => 'monthly', 'color' => '#E50914'],
            'spotify' => ['amount' => '21.90', 'cycle' => 'monthly', 'color' => '#1DB954'],
            'amazon prime' => ['amount' => '19.90', 'cycle' => 'monthly', 'color' => '#00A8E1'],
            'amazon' => ['amount' => '19.90', 'cycle' => 'monthly', 'color' => '#00A8E1'],
            'youtube premium' => ['amount' => '24.90', 'cycle' => 'monthly', 'color' => '#FF0000'],
            'disney+' => ['amount' => '33.90', 'cycle' => 'monthly', 'color' => '#113CCF'],
            'disney plus' => ['amount' => '33.90', 'cycle' => 'monthly', 'color' => '#113CCF'],
            'hbo max' => ['amount' => '34.90', 'cycle' => 'monthly', 'color' => '#5C068C'],
            'max' => ['amount' => '34.90', 'cycle' => 'monthly', 'color' => '#002BE7'],
            'globoplay' => ['amount' => '24.90', 'cycle' => 'monthly', 'color' => '#FA0054'],
            'apple tv' => ['amount' => '21.90', 'cycle' => 'monthly', 'color' => '#FFFFFF'],
            'apple music' => ['amount' => '21.90', 'cycle' => 'monthly', 'color' => '#FA243C'],
            'chatgpt' => ['amount' => '110.00', 'cycle' => 'monthly', 'color' => '#10A37F'],
            'github copilot' => ['amount' => '55.00', 'cycle' => 'monthly', 'color' => '#FAFBFC'],
        ];

        foreach ($templates as $key => $template) {
            if (str_contains($service, $key)) {
                if (empty($this->amount)) {
                    $this->amount = $template['amount'];
                }
                $this->billing_cycle = $template['cycle'];
                break;
            }
        }
    }

    public function updatedCategoryId($value): void
    {
        if ($value) {
            $category = Category::find($value);
            if ($category) {
                $this->selectedCategoryColor = $category->color;
            }
        }
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'billing_cycle' => 'required|in:monthly,yearly,quarterly,semiannual,custom',
            'amount' => 'required|numeric|min:0|max:99999999',
            'currency' => 'required|string|size:3',
            'start_date' => 'required|date',
            'next_billing_date' => 'nullable|date',
        ];

        if ($this->billing_cycle === 'custom') {
            $rules['custom_cycle_interval'] = 'required|integer|min:1';
            $rules['custom_cycle_period'] = 'required|in:days,months,years';
        }

        $rules += [
            'status' => 'required|in:active,paused,cancelled',
            'cancelled_at' => 'nullable|date',
            'auto_renew' => 'boolean',
            'is_domain' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'service_url' => 'nullable|url|max:255',
        ];

        if ($this->isCreatingCategory) {
            $rules['newCategoryName'] = 'required|string|max:50';
            $rules['newCategoryColor'] = 'required|string|size:7';
        } else {
            $rules['category_id'] = 'nullable|exists:categories,id';
        }

        return $rules;
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(string $id): void
    {
        $this->resetErrorBag();
        
        $token = auth()->user()->privacyToken?->token;
        $subscription = Subscription::byPrivacyToken($token)->findOrFail($id);

        $this->editingId = $subscription->id;
        $this->name = $subscription->name;
        $this->category_id = $subscription->category_id;
        $this->billing_cycle = $subscription->billing_cycle;
        $this->custom_cycle_interval = $subscription->custom_cycle_interval;
        $this->custom_cycle_period = $subscription->custom_cycle_period ?? 'months';
        $this->amount = (string) $subscription->amount;
        $this->currency = $subscription->currency ?? 'BRL';
        $this->start_date = $subscription->start_date->format('Y-m-d');
        $this->next_billing_date = $subscription->next_billing_date ? $subscription->next_billing_date->format('Y-m-d') : '';
        $this->status = $subscription->status;
        $this->cancelled_at = $subscription->cancelled_at ? $subscription->cancelled_at->format('Y-m-d') : '';
        $this->auto_renew = (bool) $subscription->auto_renew;
        $this->is_domain = (bool) $subscription->is_domain;
        $this->notes = (string) $subscription->notes;
        $this->service_url = (string) $subscription->service_url;
        
        if ($this->category_id) {
            $category = Category::find($this->category_id);
            $this->selectedCategoryColor = $category->color ?? '#0F6CBD';
        }

        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    public function exportCsv()
    {
        $token = auth()->user()->privacyToken?->token;
        if (!$token) {
            session()->flash('error', 'Token de privacidade não encontrado.');
            return;
        }

        $subscriptions = Subscription::byPrivacyToken($token)->with('category')->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=Minhas_Assinaturas.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($subscriptions) {
            $file = fopen('php://output', 'w');
            
            // Add BOM to fix UTF-8 in Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Headers (Now 13 columns including all fields)
            fputcsv($file, ['Nome', 'URL', 'Valor', 'Moeda', 'Ciclo', 'Intervalo_Custom', 'Periodo_Custom', 'Categoria', 'Início', 'Vencimento', 'Status', 'Auto_Renew', 'Notas'], ';');

            foreach ($subscriptions as $sub) {
                fputcsv($file, [
                    $sub->name,
                    $sub->service_url,
                    number_format($sub->amount, 2, ',', ''),
                    $sub->currency ?? 'BRL',
                    $sub->billing_cycle,
                    $sub->custom_cycle_interval,
                    $sub->custom_cycle_period,
                    $sub->category->name ?? 'Sem categoria',
                    $sub->start_date ? $sub->start_date->format('d/m/Y') : '',
                    $sub->next_billing_date ? $sub->next_billing_date->format('d/m/Y') : '',
                    $sub->status,
                    $sub->auto_renew ? 'Sim' : 'Não',
                    $sub->notes
                ], ';');
            }

            fclose($file);
        };

        activity()->event('csv_export')->log('Usuário exportou suas assinaturas via CSV.');

        return response()->streamDownload($callback, 'Minhas_Assinaturas.csv', $headers);
    }

    public function confirmImport()
    {
        $token = auth()->user()->privacyToken?->token;
        if (!$token) return;

        $importedCount = 0;
        $skippedCount = 0;

        $tempData = session()->get('temp_import_data_' . auth()->id(), []);

        foreach ($tempData as $row) {
            // Check if row has at least Name and Value
            if (count($row) >= 2) {
                $name = trim($row[0]);
                
                if ($this->ignoreDuplicates) {
                    $exists = Subscription::where('privacy_token', $token)
                        ->where('name', $name)
                        ->exists();
                    
                    if ($exists) {
                        $skippedCount++;
                        continue;
                    }
                }

                // Detecção inteligente do formato das colunas
                $colsCount = count($row);
                
                // Se tiver 13 colunas, é o formato completo novo
                $isFullFormat = ($colsCount >= 13);
                $hasCurrencyColumn = ($colsCount >= 10);
                
                $urlIndex = $isFullFormat ? 1 : -1;
                $valIndex = $isFullFormat ? 2 : 1;
                $currencyIndex = $isFullFormat ? 3 : ($hasCurrencyColumn ? 2 : -1);
                $cycleIndex = $isFullFormat ? 4 : ($hasCurrencyColumn ? 3 : 2);
                $customIntervalIndex = $isFullFormat ? 5 : -1;
                $customPeriodIndex = $isFullFormat ? 6 : -1;
                $categoryIndex = $isFullFormat ? 7 : ($hasCurrencyColumn ? 4 : 3);
                $startDateIndex = $isFullFormat ? 8 : ($hasCurrencyColumn ? 5 : 4);
                $nextDateIndex = $isFullFormat ? 9 : ($hasCurrencyColumn ? 6 : 5);
                $statusIndex = $isFullFormat ? 10 : ($hasCurrencyColumn ? 7 : 6);
                $autoRenewIndex = $isFullFormat ? 11 : ($hasCurrencyColumn ? 8 : 7);
                $notesIndex = $isFullFormat ? 12 : ($hasCurrencyColumn ? 9 : 8);

                // Buscar Categoria pelo nome (ou criar se não existir)
                $categoryName = isset($row[$categoryIndex]) ? trim($row[$categoryIndex]) : '';
                $categoryId = null;
                if ($categoryName && !in_array($categoryName, ['Sem categoria', ''])) {
                    $category = \App\Models\Category::where(function($q) use ($token) {
                            $q->where('privacy_token', $token)->orWhere('is_system', true);
                        })
                        ->where('name', $categoryName)
                        ->first();
                    
                    if (!$category) {
                        // Criação automática para não perder a informação da categoria
                        $category = \App\Models\Category::create([
                            'privacy_token' => $token,
                            'name' => $categoryName,
                            'slug' => \Illuminate\Support\Str::slug($categoryName . '-' . uniqid()),
                            'color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
                            'icon' => 'bi-tag',
                            'is_system' => false,
                        ]);
                    }
                    $categoryId = $category?->id;
                }

                $currency = 'BRL';
                if ($currencyIndex !== -1 && !empty(trim($row[$currencyIndex] ?? ''))) {
                    $currency = strtoupper(trim($row[$currencyIndex]));
                }

                // Processar Datas
                $startDate = now();
                if (!empty($row[$startDateIndex] ?? '')) {
                    try {
                        $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($row[$startDateIndex]));
                    } catch (\Exception $e) { $startDate = now(); }
                }

                $nextDate = $startDate->copy()->addMonth();
                if (!empty($row[$nextDateIndex] ?? '')) {
                    try {
                        $nextDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($row[$nextDateIndex]));
                    } catch (\Exception $e) { $nextDate = $startDate->copy()->addMonth(); }
                }

                // Processar Auto Renew
                $autoRenew = true;
                $autoRenewStr = strtolower(trim($row[$autoRenewIndex] ?? 'sim'));
                if ($autoRenewStr === 'não' || $autoRenewStr === 'nao' || $autoRenewStr === 'no') {
                    $autoRenew = false;
                }

                // Processar Cancelamento
                $status = strtolower(trim($row[$statusIndex] ?? 'active'));
                $cancelledAt = null;
                if (in_array($status, ['cancelled', 'cancelada', 'inativa', 'inactive'])) {
                    $cancelledAt = $nextDate;
                }

                try {
                    Subscription::create([
                        'privacy_token' => $token,
                        'category_id' => $categoryId,
                        'name' => $name,
                        'service_url' => $urlIndex !== -1 ? trim($row[$urlIndex] ?? '') : null,
                        'amount' => (float) str_replace(',', '.', $row[$valIndex] ?? 0),
                        'currency' => $currency,
                        'billing_cycle' => $row[$cycleIndex] ?? 'monthly',
                        'custom_cycle_interval' => $customIntervalIndex !== -1 ? (int) ($row[$customIntervalIndex] ?? null) : null,
                        'custom_cycle_period' => $customPeriodIndex !== -1 ? trim($row[$customPeriodIndex] ?? '') : null,
                        'start_date' => $startDate,
                        'next_billing_date' => $nextDate,
                        'status' => $status,
                        'cancelled_at' => $cancelledAt,
                        'auto_renew' => $autoRenew,
                        'notes' => isset($row[$notesIndex]) ? trim($row[$notesIndex]) : null,
                    ]);
                    $importedCount++;
                } catch (\Exception $e) {
                    $skippedCount++;
                }
            }
        }

        $message = "{$importedCount} assinaturas importadas.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} falhas ou duplicadas ignoradas.";
        }

        $this->importStatus = $message;
        app(\App\Services\CacheService::class)->invalidateUserCache($token);
        
        session()->forget('temp_import_data_' . auth()->id());
        $this->reset(['csvFile', 'showImportModal', 'importSummary']);
        session()->flash('success', $this->importStatus);
    }

    public function cancelImport()
    {
        session()->forget('temp_import_data_' . auth()->id());
        $this->reset(['csvFile', 'showImportModal', 'importSummary']);
    }

    public function save(): void
    {
        try {
            $data = $this->validate();

            $token = auth()->user()->privacyToken?->token;
            
            if (! $token) {
                session()->flash('error', 'Token de privacidade inválido.');
                return;
            }

            // Limpeza de campos vazios para evitar erro de formato SQL
            $data['next_billing_date'] = !empty($data['next_billing_date']) ? $data['next_billing_date'] : null;
            $data['cancelled_at'] = !empty($data['cancelled_at']) ? $data['cancelled_at'] : null;
            $data['custom_cycle_interval'] = !empty($data['custom_cycle_interval']) ? $data['custom_cycle_interval'] : null;
            $data['custom_cycle_period'] = !empty($data['custom_cycle_period']) ? $data['custom_cycle_period'] : null;

            if ($this->isCreatingCategory) {
                $category = Category::create([
                    'privacy_token' => $token,
                    'name' => $data['newCategoryName'],
                    'slug' => \Illuminate\Support\Str::slug($data['newCategoryName'] . '-' . uniqid()),
                    'color' => $data['newCategoryColor'],
                    'icon' => 'bi-tag',
                    'is_system' => false,
                ]);
                $data['category_id'] = $category->id;
                unset($data['newCategoryName'], $data['newCategoryColor']);
            } else if ($this->category_id) {
                // Atualiza a cor da categoria existente se for uma categoria do próprio usuário
                $category = Category::where('id', $this->category_id)
                                    ->where('privacy_token', $token)
                                    ->first();
                
                if ($category && $category->color !== $this->selectedCategoryColor) {
                    $category->update(['color' => $this->selectedCategoryColor]);
                    app(\App\Services\CacheService::class)->invalidateUserCache($token);
                }
            }

            if ($this->status === 'cancelled' && empty($data['cancelled_at'])) {
                $data['cancelled_at'] = now();
            }

            if ($this->editingId) {
                $subscription = Subscription::byPrivacyToken($token)->findOrFail($this->editingId);
                $subscription->update($data);
                session()->flash('success', 'Assinatura atualizada com sucesso!');
            } else {
                $data['privacy_token'] = $token;
                Subscription::create($data);
                session()->flash('success', 'Assinatura criada com sucesso!');
            }

            app(\App\Services\CacheService::class)->invalidateUserCache($token);

            $this->showFormModal = false;
            $this->resetForm();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // Deixa o Livewire lidar com erros de validação
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro ao salvar assinatura: " . $e->getMessage());
            session()->flash('error', 'Ocorreu um erro inesperado ao salvar: ' . $e->getMessage());
        }
    }

    public function confirmDelete(string $id): void
    {
        $token = auth()->user()->privacyToken?->token;
        $subscription = Subscription::byPrivacyToken($token)->findOrFail($id);
        
        $this->deletingId = $subscription->id;
        $this->deletingName = $subscription->name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingName = '';
    }

    public function deleteSubscription(): void
    {
        if ($this->deletingId) {
            $token = auth()->user()->privacyToken?->token;
            $subscription = Subscription::byPrivacyToken($token)->findOrFail($this->deletingId);
            $subscription->delete();
            
            app(\App\Services\CacheService::class)->invalidateUserCache($token);

            session()->flash('success', 'Assinatura excluída com sucesso!');
        }

        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingName = '';
    }

    protected function resetForm(): void
    {
        $this->resetErrorBag();
        $this->editingId = null;
        $this->name = '';
        $this->category_id = null;
        $this->isCreatingCategory = false;
        $this->newCategoryName = '';
        $this->newCategoryColor = '#0F6CBD';
        $this->billing_cycle = 'monthly';
        $this->custom_cycle_interval = null;
        $this->custom_cycle_period = 'months';
        $this->amount = '';
        $this->currency = 'BRL';
        $this->start_date = now()->format('Y-m-d');
        $this->next_billing_date = now()->addMonth()->format('Y-m-d');
        $this->status = 'active';
        $this->cancelled_at = null;
        $this->auto_renew = true;
        $this->is_domain = false;
        $this->notes = '';
        $this->service_url = '';
    }

    public function render()
    {
        $token = auth()->user()->privacyToken?->token;
        if ($token) {
            app(\App\Services\ReportService::class)->syncSubscriptions($token);
        }
        
        $query = Subscription::query()
            ->byPrivacyToken($token)
            ->with('category')
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', '%' . trim($this->search) . '%');
            });

        if ($this->categoryFilter !== 'all' && $this->categoryFilter !== 'none') {
            $query->where('category_id', $this->categoryFilter);
        } elseif ($this->categoryFilter === 'none') {
            $query->whereNull('category_id');
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $total = $query->count();
        $subscriptions = $query->orderBy($this->sortColumn, $this->sortDirection)
            ->offset(($this->page - 1) * $this->perPage)
            ->limit($this->perPage)
            ->get();

        return view('livewire.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'categories' => Category::where(function($q) use ($token) {
                $q->where('privacy_token', $token)->orWhere('is_system', true);
            })->get(),
            'totalPages' => ceil($total / $this->perPage),
            'totalRecords' => $total
        ]);
    }
}
