<?php

declare(strict_types=1);

/*
 * This file is part of the Contao News Filter Event extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoNewsFilterEvent\EventListener;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Date;
use Contao\Model\Collection;
use Contao\Module;
use Contao\NewsModel;
use InspiredMinds\ContaoNewsFilterEvent\Event\NewsFilterEvent;
use InspiredMinds\ContaoNewsFilterEvent\NewsListHookListenerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NewsListHookListener implements NewsListHookListenerInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TokenChecker $tokenChecker,
    ) {
    }

    /**
     * @return false|int
     */
    public function onNewsListCountItems(array $archives, bool|null $featured, Module $module)
    {
        return $this->execute($archives, $featured, null, null, $module, true);
    }

    /**
     * @return false|Collection<array-key, NewsModel>|NewsModel|null
     */
    public function onNewsListFetchItems(array $archives, bool|null $featured, int $limit, int $offset, Module $module)
    {
        return $this->execute($archives, $featured, $limit, $offset, $module, false);
    }

    /**
     * @return false|int|Collection<array-key, NewsModel>|NewsModel|null
     */
    private function execute(array $archives, bool|null $featured, int|null $limit, int|null $offset, Module $module, bool $countOnly)
    {
        $event = new NewsFilterEvent($archives, $featured, $limit, $offset, $module, $countOnly);
        $this->eventDispatcher->dispatch($event);

        if ($event->getForceEmptyResult()) {
            return null;
        }

        // If no additional queries or options where added to the event,
        // discard this hook execution, so that Contao's default behavior applies.
        if (!$event->hasData()) {
            return false;
        }

        if ($event->getAddDefaults()) {
            $this->addDefaults($event, $archives, $featured, $limit, $offset, $module);
        }

        if ($countOnly) {
            return NewsModel::countBy($event->getColumns(), $event->getValues(), $event->getOptions());
        }

        return NewsModel::findBy($event->getColumns(), $event->getValues(), $event->getOptions());
    }

    private function addDefaults(NewsFilterEvent $event, array $archives, bool|null $featured, int|null $limit, int|null $offset, Module $module): void
    {
        $t = NewsModel::getTable();
        $event->addColumn("$t.pid IN(".implode(',', array_map(\intval(...), $archives)).')');

        if (true === $featured) {
            $event->addColumn("$t.featured='1'");
        } elseif (false === $featured) {
            $event->addColumn("$t.featured=''");
        }

        if (!$this->isPreviewMode($event)) {
            $time = Date::floorToMinute();
            $event->addColumn("$t.published='1' AND ($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'$time')");
        }

        if (null !== $limit) {
            $event->addOption('limit', $limit);
        }

        if (null !== $offset) {
            $event->addOption('offset', $offset);
        }

        // Determine sorting
        if (empty($event->getOption('order'))) {
            $order = '';

            if ('featured_first' === $module->news_featured) {
                $order .= "$t.featured DESC, ";
            }

            match ($module->news_order) {
                'order_headline_asc' => $order .= "$t.headline",
                'order_headline_desc' => $order .= "$t.headline DESC",
                'order_random' => $order .= 'RAND()',
                'order_date_asc' => $order .= "$t.date",
                default => $order .= "$t.date DESC",
            };

            $event->addOption('order', $order);
        }
    }

    private function isPreviewMode(NewsFilterEvent $event): bool
    {
        if (isset($event->getOptions()['ignoreFePreview'])) {
            return false;
        }

        return $this->tokenChecker->isPreviewMode();
    }
}
