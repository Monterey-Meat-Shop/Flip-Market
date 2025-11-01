<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

class CategoryStock extends ChartWidget
{
    public static ?int $sort = 2; // para same column cla

    //dropdown
    protected function getFilters(): ?array
    {
        return [
            'Category' => 'Category',
            'Brand' => 'Brand',
        ];
    }

    public function getHeading(): ?string
    {
        return 'Stocks per ' . ($this->filter ?? 'Category');
    }

    protected function getData(): array
    {
        if ($this->filter === 'Brand') {
            $groups = Product::with('brand')
                ->get()
                ->groupBy(fn($prod) => $prod->brand->name ?? 'No Brand') //if ever na wlang 
                ->map->count();
            $label = 'Products per Brand';
        } else {
            $groups = Product::with('category')
                ->get()
                ->groupBy(fn($prod) => $prod->category->name ?? 'Uncategorized')
                ->map->count();
            $label = 'Products per Category';
        }

        return [
            'datasets' => [
                [
                    'label' => $label,
                    'data' => $groups->values(),
                    'backgroundColor' => [
                        '#3B82F6',
                        '#F59E0B',
                        '#10B981',
                        '#EF4444',
                        '#8B5CF6',
                        '#EC4899',
                    ],
                ],
            ],
            'labels' => $groups->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
