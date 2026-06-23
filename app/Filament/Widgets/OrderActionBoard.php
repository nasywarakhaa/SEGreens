<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class OrderActionBoard extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.dashboard.quick_actions.heading'))
            ->description(__('admin.dashboard.quick_actions.description'))
            ->query(fn (): Builder => Order::query()
                ->with('user')
                ->whereIn('status_code', [
                    OrderStatus::Pending->value,
                    OrderStatus::Confirmed->value,
                    OrderStatus::Packed->value,
                    OrderStatus::OnDelivery->value,
                ]))
            ->defaultSort('created_at', 'desc')
            ->poll('15s')
            ->defaultPaginationPageOption(8)
            ->paginationPageOptions([8, 10, 25])
            ->recordActionsColumnLabel(__('admin.dashboard.quick_actions.columns.actions'))
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('admin.dashboard.quick_actions.columns.order'))
                    ->searchable()
                    ->weight('semibold')
                    ->description(function (Order $record): ?string {
                        return $record->fulfillment_type_code?->getLabel();
                    }),
                TextColumn::make('user.full_name')
                    ->label(__('admin.dashboard.quick_actions.columns.customer'))
                    ->searchable()
                    ->default('-'),
                TextColumn::make('created_at')
                    ->label(__('admin.dashboard.quick_actions.columns.date'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label(__('admin.dashboard.quick_actions.columns.total'))
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status_code')
                    ->label(__('admin.dashboard.quick_actions.columns.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('payment_status_code')
                    ->label(__('admin.dashboard.quick_actions.columns.payment'))
                    ->badge()
                    ->sortable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('set_status_confirmed')
                        ->label(OrderStatus::Confirmed->getLabel())
                        ->icon($this->statusIcon(OrderStatus::Confirmed))
                        ->visible(fn (Order $record): bool => $this->canSetStatus($record, OrderStatus::Confirmed))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.orders.messages.update_status_heading'))
                        ->modalDescription(fn (): string => __('admin.orders.messages.update_status_description', [
                            'status' => OrderStatus::Confirmed->getLabel(),
                        ]))
                        ->action(fn (Order $record): mixed => $this->updateStatus((string) $record->id, OrderStatus::Confirmed->value)),
                    Action::make('set_status_packed')
                        ->label(OrderStatus::Packed->getLabel())
                        ->icon($this->statusIcon(OrderStatus::Packed))
                        ->visible(fn (Order $record): bool => $this->canSetStatus($record, OrderStatus::Packed))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.orders.messages.update_status_heading'))
                        ->modalDescription(fn (): string => __('admin.orders.messages.update_status_description', [
                            'status' => OrderStatus::Packed->getLabel(),
                        ]))
                        ->action(fn (Order $record): mixed => $this->updateStatus((string) $record->id, OrderStatus::Packed->value)),
                    Action::make('set_status_on_delivery')
                        ->label(OrderStatus::OnDelivery->getLabel())
                        ->icon($this->statusIcon(OrderStatus::OnDelivery))
                        ->visible(fn (Order $record): bool => $this->canSetStatus($record, OrderStatus::OnDelivery))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.orders.messages.update_status_heading'))
                        ->modalDescription(fn (): string => __('admin.orders.messages.update_status_description', [
                            'status' => OrderStatus::OnDelivery->getLabel(),
                        ]))
                        ->action(fn (Order $record): mixed => $this->updateStatus((string) $record->id, OrderStatus::OnDelivery->value)),
                    Action::make('set_status_completed')
                        ->label(OrderStatus::Completed->getLabel())
                        ->icon($this->statusIcon(OrderStatus::Completed))
                        ->visible(fn (Order $record): bool => $this->canSetStatus($record, OrderStatus::Completed))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.orders.messages.update_status_heading'))
                        ->modalDescription(fn (): string => __('admin.orders.messages.update_status_description', [
                            'status' => OrderStatus::Completed->getLabel(),
                        ]))
                        ->action(fn (Order $record): mixed => $this->updateStatus((string) $record->id, OrderStatus::Completed->value)),
                    Action::make('cancel_order')
                        ->label(__('admin.dashboard.quick_actions.cancel'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Order $record): bool => $this->canCancel($record))
                        ->form([
                            Textarea::make('cancel_reason')
                                ->label(__('admin.orders.fields.cancel_reason'))
                                ->placeholder(__('admin.dashboard.quick_actions.cancel_reason_placeholder'))
                                ->required()
                                ->rows(4)
                                ->maxLength(500),
                        ])
                        ->modalHeading(__('admin.dashboard.quick_actions.cancel_modal_heading'))
                        ->modalDescription(__('admin.dashboard.quick_actions.cancel_modal_description'))
                        ->modalSubmitActionLabel(__('admin.dashboard.quick_actions.cancel_modal_submit'))
                        ->action(function (Order $record, array $data): void {
                            $this->cancelOrder((string) $record->id, (string) ($data['cancel_reason'] ?? ''));
                        }),
                ])
                    ->label(__('admin.orders.actions.group'))
                    ->icon('heroicon-o-arrow-path')
                    ->button()
                    ->color('success')
                    ->outlined()
                    ->visible(fn (Order $record): bool => $this->hasAvailableStatusTransition($record) || $this->canCancel($record)),
                Action::make('view_detail')
                    ->label(__('filament-actions::view.single.label'))
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->outlined()
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.view', ['record' => $record])),
            ]);
    }

    public function hasAvailableStatusTransition(Order $order): bool
    {
        return $this->canSetStatus($order, OrderStatus::Confirmed)
            || $this->canSetStatus($order, OrderStatus::Packed)
            || $this->canSetStatus($order, OrderStatus::OnDelivery)
            || $this->canSetStatus($order, OrderStatus::Completed);
    }

    public function canSetStatus(Order $order, OrderStatus $targetStatus): bool
    {
        $current = (int) ($order->status_code?->value ?? $order->status_code);

        if ($current === $targetStatus->value) {
            return false;
        }

        if (! OrderStatus::canTransition($current, $targetStatus->value)) {
            return false;
        }

        if (
            in_array($targetStatus, [OrderStatus::Packed, OrderStatus::OnDelivery, OrderStatus::Completed], true)
            && ! $this->isPaid($order)
        ) {
            return false;
        }

        return true;
    }

    public function updateStatus(string $orderId, int $targetStatusCode): void
    {
        $order = Order::query()->find($orderId);
        if (! $order) {
            return;
        }

        try {
            $targetStatus = OrderStatus::from($targetStatusCode);
        } catch (\ValueError) {
            FilamentNotification::make()
                ->title(__('admin.dashboard.quick_actions.status_not_allowed'))
                ->warning()
                ->send();

            return;
        }

        if (! $this->canSetStatus($order, $targetStatus)) {
            FilamentNotification::make()
                ->title(__('admin.dashboard.quick_actions.status_not_allowed'))
                ->warning()
                ->send();

            return;
        }

        $this->moveStatus($order, $targetStatus);
    }

    public function cancelOrder(string $orderId, ?string $cancelReason = null): void
    {
        $order = Order::query()->find($orderId);
        if (! $order) {
            return;
        }

        $current = (int) ($order->status_code?->value ?? $order->status_code);
        if (! OrderStatus::canTransition($current, OrderStatus::Cancelled->value)) {
            FilamentNotification::make()
                ->title(__('admin.dashboard.quick_actions.cancel_blocked'))
                ->warning()
                ->send();

            return;
        }

        try {
            $reason = trim((string) $cancelReason);
            if ($reason === '') {
                $reason = __('admin.dashboard.quick_actions.cancelled_note');
            }

            $order->status_code = OrderStatus::Cancelled;
            $order->cancel_reason = $reason;
            $order->save();

            FilamentNotification::make()
                ->title(__('admin.dashboard.quick_actions.cancel_success'))
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            FilamentNotification::make()
                ->title(__('admin.dashboard.quick_actions.cancel_failed'))
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    private function statusIcon(OrderStatus $targetStatus): string
    {
        return match ($targetStatus) {
            OrderStatus::Confirmed => 'heroicon-o-check-circle',
            OrderStatus::Packed => 'heroicon-o-archive-box',
            OrderStatus::OnDelivery => 'heroicon-o-truck',
            OrderStatus::Completed => 'heroicon-o-check-badge',
            default => 'heroicon-o-arrow-path',
        };
    }

    private function moveStatus(Order $order, OrderStatus $next): void
    {
        try {
            if (in_array($next, [OrderStatus::Packed, OrderStatus::OnDelivery, OrderStatus::Completed], true) && ! $this->isPaid($order)) {
                FilamentNotification::make()
                    ->title(__('admin.dashboard.quick_actions.waiting_payment'))
                    ->body(__('admin.orders.messages.payment_required'))
                    ->warning()
                    ->send();

                return;
            }

            $order->status_code = $next;
            $order->save();

            FilamentNotification::make()
                ->title(__('admin.dashboard.quick_actions.status_updated'))
                ->body(__('admin.dashboard.quick_actions.status_updated_body', ['status' => $next->getLabel()]))
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            FilamentNotification::make()
                ->title(__('admin.dashboard.quick_actions.update_failed'))
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function canCancel(Order $order): bool
    {
        $current = (int) ($order->status_code?->value ?? $order->status_code);

        return OrderStatus::canTransition($current, OrderStatus::Cancelled->value);
    }

    private function isPaid(Order $order): bool
    {
        $payment = (int) ($order->payment_status_code?->value ?? $order->payment_status_code);

        return $payment === PaymentStatus::Paid->value;
    }
}
