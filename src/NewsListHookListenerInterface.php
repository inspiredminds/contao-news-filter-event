<?php

declare(strict_types=1);

namespace InspiredMinds\ContaoNewsFilterEvent;

use Contao\Module;

interface NewsListHookListenerInterface
{
    /**
     * @return false|int
     */
    public function onNewsListCountItems(array $archives, bool|null $featured, Module $module);

    /**
     * @return false|Collection<array-key, NewsModel>|NewsModel|null
     */
    public function onNewsListFetchItems(array $archives, bool|null $featured, int $limit, int $offset, Module $module);
}
