<?php

namespace App\Http\Livewire;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class CashPaymentTable extends LivewireTableComponent
{
    protected $listeners = ['refresh' => '$refresh', 'resetPage'];

    public string $tableName = 'cash-payment';
    public string $pageName = 'cash-payment';

    public $orderBy = 'desc';  // default

    protected $queryString = []; //url

    public function query()
    {

        return Subscription::with(['user', 'plan.currency'])->whereNotNull('payment_type');
    }


    public function rowView(): string
    {
        return 'livewire-tables.rows.cash_payment_table';
    }

    public function render()
    {
        return view('livewire-tables::'.config('livewire-tables.theme').'.datatable')
            ->with([
                'columns'       => $this->columns(),
                'rowView'       => $this->rowView(),
                'filtersView'   => $this->filtersView(),
                'customFilters' => $this->filters(),
                'rows'          => $this->rows,
                'modalsView'    => $this->modalsView(),
                'bulkActions'   => $this->bulkActions,
                //                'componentName' => 'album.add-button',
            ]);
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.user.full_name'), 'user.first_name')
                ->searchable(function (Builder $query, $value) {
                    return $query->whereHas('user', function ($q) use ($value) {
                        $q->where('first_name', 'like', '%'.$value.'%');
                    });
                })->sortable(function (Builder $query, $direction) {
                    return $query->orderBy(User::select('first_name')
                        ->whereColumn('subscriptions.user_id', 'users.id'),
                        $direction);
                }),
            Column::make(__('messages.subscription.plan_name'), 'plan.name')
                ->searchable()
                ->sortable(function (Builder $query, $direction) {
                    return $query->orderBy(Plan::select('name')
                        ->whereColumn('subscriptions.plan_id', 'plans.id'),
                        $direction);
                }),
            Column::make(__('messages.subscription.plan_price'), 'plan_amount')
                ->sortable()->addClass('plan-amount'),
            Column::make(__('messages.subscription.payable_amount'), 'payable_amount')
                ->sortable()->addClass('plan-amount px-10'),
            Column::make(__('messages.subscription.start_date'), 'starts_at')
                ->sortable()->addClass('date-align'),
            Column::make(__('messages.subscription.end_date'), 'ends_at')
                ->sortable()->addClass('date-align'),
            Column::make(__('messages.attachment'), 'id'),
            Column::make(__('messages.notes'), 'notes'),
            Column::make(__('messages.status'))->addClass('text-center'),

        ];
    }
}
