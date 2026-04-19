<?php

namespace App\Observers;

use App\Models\MenuItem;
use App\Services\CacheService;

class MenuItemObserver
{
    public function created(MenuItem $menuItem): void
    {
        CacheService::forget(CacheService::categoryWithMenu($menuItem->category_id));
        CacheService::forget(CacheService::featuredItems());
    }

    public function updated(MenuItem $menuItem): void
    {
        if ($menuItem->wasChanged(['category_id', 'is_available', 'featured_image'])) {
            CacheService::forget(CacheService::categoryWithMenu($menuItem->category_id));
            CacheService::forget(CacheService::categoryWithMenu($menuItem->getOriginal('category_id')));
            CacheService::forget(CacheService::featuredItems());
        }
    }

    public function deleted(MenuItem $menuItem): void
    {
        CacheService::forget(CacheService::categoryWithMenu($menuItem->category_id));
        CacheService::forget(CacheService::featuredItems());
    }
}