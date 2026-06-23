<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $userCount = User::query()->count();
        $productCount = Product::query()->count();
        $pendingCount = Order::query()
            ->where('status_code', OrderStatus::Pending)
            ->count();

        $revenueToday = Order::query()
            ->whereDate('created_at', today())
            ->where('payment_status_code', PaymentStatus::Paid->value)
            ->sum('total_price');

        // Trend data 7 hari untuk sparkline
        $userTrend = $this->getDailyTrend(User::class, 7);
        $orderTrend = $this->getDailyOrderTrend(7);

        return [
            Stat::make(__('admin.stats.users'), (string) $userCount)
                ->description(__('admin.stats.users_description'))
                ->descriptionIcon('heroicon-m-users')
                ->chart($userTrend)
                ->color('info'),

            Stat::make(__('admin.stats.products'), (string) $productCount)
                ->description(__('admin.stats.products_description'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make(__('admin.stats.pending_orders'), (string) $pendingCount)
                ->description(__('admin.stats.pending_orders_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->chart($orderTrend)
                ->color($pendingCount > 5 ? 'danger' : 'warning'),

            Stat::make(__('admin.stats.revenue_today'), 'Rp '.number_format((float) $revenueToday, 0, ',', '.'))
                ->description(__('admin.stats.revenue_today_description'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }

    /**
     * @return array<int>
     */
    private function getDailyTrend(string $modelClass, int $days): array
    {
        $startDate = CarbonImmutable::today()->subDays($days - 1);
        $counts = $modelClass::query()
            ->whereDate('created_at', '>=', $startDate->toDateString())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $trend = [];
        foreach (range(0, $days - 1) as $offset) {
            $date = $startDate->addDays($offset)->toDateString();
            $trend[] = (int) ($counts[$date] ?? 0);
        }

        return $trend;
    }

    /**
     * @return array<int>
     */
    private function getDailyOrderTrend(int $days): array
    {
        $startDate = CarbonImmutable::today()->subDays($days - 1);
        $counts = Order::query()
            ->whereDate('created_at', '>=', $startDate->toDateString())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $trend = [];
        foreach (range(0, $days - 1) as $offset) {
            $date = $startDate->addDays($offset)->toDateString();
            $trend[] = (int) ($counts[$date] ?? 0);
        }

        return $trend;
    }
}
