<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable(),
                TextColumn::make('user.full_name')
                    ->label(__('admin.orders.fields.customer'))
                    ->searchable(),
                TextColumn::make('total_price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('fulfillment_type_code')
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status_code')
                    ->badge()
                    ->sortable(),
                TextColumn::make('payment_status_code')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.orders.fields.order_date'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status_code')
                    ->options(OrderStatus::options()),
                SelectFilter::make('payment_status_code')
                    ->options(PaymentStatus::options()),
                SelectFilter::make('fulfillment_type_code')
                    ->options(FulfillmentType::options()),
                Filter::make('created_at')
                    ->label(__('admin.orders.filters.order_date'))
                    ->form([
                        DatePicker::make('from')
                            ->label(__('admin.orders.filters.order_date_from')),
                        DatePicker::make('until')
                            ->label(__('admin.orders.filters.order_date_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (($data['from'] ?? null) !== null) {
                            $query->whereDate('created_at', '>=', $data['from']);
                        }

                        if (($data['until'] ?? null) !== null) {
                            $query->whereDate('created_at', '<=', $data['until']);
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    self::makeStatusAction('set_status_confirmed', OrderStatus::Confirmed),
                    self::makeStatusAction('set_status_packed', OrderStatus::Packed),
                    self::makeStatusAction('set_status_on_delivery', OrderStatus::OnDelivery),
                    self::makeStatusAction('set_status_completed', OrderStatus::Completed),
                    Action::make('cancel_order')
                        ->label(__('admin.dashboard.quick_actions.cancel'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Order $record): bool => self::canCancel($record))
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
                            self::cancelOrder($record, (string) ($data['cancel_reason'] ?? ''));
                        }),
                ])
                    ->label(__('admin.orders.actions.group'))
                    ->icon('heroicon-o-arrow-path')
                    ->button()
                    ->color('success')
                    ->outlined()
                    ->visible(fn (Order $record): bool => self::hasAvailableStatusTransition($record) || self::canCancel($record)),
                Action::make('view_detail')
                    ->label(__('filament-actions::view.single.label'))
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->outlined()
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.view', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(false),
                ]),
            ]);
    }

    private static function makeStatusAction(string $name, OrderStatus $targetStatus): Action
    {
        return Action::make($name)
            ->label($targetStatus->getLabel())
            ->icon(self::statusIcon($targetStatus))
            ->visible(fn (Order $record): bool => self::canSetStatus($record, $targetStatus))
            ->requiresConfirmation()
            ->modalHeading(__('admin.orders.messages.update_status_heading'))
            ->modalDescription(fn (): string => __('admin.orders.messages.update_status_description', [
                'status' => $targetStatus->getLabel(),
            ]))
            ->action(fn (Order $record): mixed => self::moveStatus($record, $targetStatus));
    }

    private static function hasAvailableStatusTransition(Order $record): bool
    {
        return self::canSetStatus($record, OrderStatus::Confirmed)
            || self::canSetStatus($record, OrderStatus::Packed)
            || self::canSetStatus($record, OrderStatus::OnDelivery)
            || self::canSetStatus($record, OrderStatus::Completed);
    }

    private static function canSetStatus(Order $record, OrderStatus $targetStatus): bool
    {
        $currentStatus = (int) ($record->status_code?->value ?? $record->status_code);

        if ($currentStatus === $targetStatus->value) {
            return false;
        }

        if (! OrderStatus::canTransition($currentStatus, $targetStatus->value)) {
            return false;
        }

        if (
            in_array($targetStatus, [OrderStatus::Packed, OrderStatus::OnDelivery, OrderStatus::Completed], true)
            && ! self::isPaid($record)
        ) {
            return false;
        }

        return true;
    }

    private static function statusIcon(OrderStatus $targetStatus): string
    {
        return match ($targetStatus) {
            OrderStatus::Confirmed => 'heroicon-o-check-circle',
            OrderStatus::Packed => 'heroicon-o-archive-box',
            OrderStatus::OnDelivery => 'heroicon-o-truck',
            OrderStatus::Completed => 'heroicon-o-check-badge',
            default => 'heroicon-o-arrow-path',
        };
    }

    private static function canCancel(Order $record): bool
    {
        $current = (int) ($record->status_code?->value ?? $record->status_code);

        return OrderStatus::canTransition($current, OrderStatus::Cancelled->value);
    }

    private static function cancelOrder(Order $record, ?string $cancelReason = null): void
    {
        if (! self::canCancel($record)) {
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

            $record->status_code = OrderStatus::Cancelled;
            $record->cancel_reason = $reason;
            $record->save();

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

    private static function isPaid(Order $record): bool
    {
        $payment = (int) ($record->payment_status_code?->value ?? $record->payment_status_code);

        return $payment === PaymentStatus::Paid->value;
    }

    private static function moveStatus(Order $record, OrderStatus $next): void
    {
        try {
            if (in_array($next, [OrderStatus::Packed, OrderStatus::OnDelivery, OrderStatus::Completed], true) && ! self::isPaid($record)) {
                FilamentNotification::make()
                    ->title(__('admin.dashboard.quick_actions.waiting_payment'))
                    ->body(__('admin.orders.messages.payment_required'))
                    ->warning()
                    ->send();

                return;
            }

            $record->status_code = $next;
            $record->save();

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
}
