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
use Exception;
use InspiredMinds\ContaoNewsFilterEvent\Event\NewsFilterEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NewsListHookListener
{
    private EventDispatcherInterface $eventDispatcher;
    private TokenChecker $tokenChecker;

    public function __construct(EventDispatcherInterface $eventDispatcher, TokenChecker $tokenChecker)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenChecker = $tokenChecker;
    }

    /**
     * @return false|int
     */
    public function onNewsListCountItems(array $archives, ?bool $featured, Module $module)
    {
        return $this->execute($archives, $featured, null, null, $module, true);
    }

    /**
     * @return false|Collection<array-key, NewsModel>|NewsModel|null
     */
    public function onNewsListFetchItems(array $archives, ?bool $featured, int $limit, int $offset, Module $module)
    {
        return $this->execute($archives, $featured, $limit, $offset, $module, false);
    }

    /**
     * @return false|int|Collection<array-key, NewsModel>|NewsModel|null
     */
    private function execute(array $archives, ?bool $featured, ?int $limit, ?int $offset, Module $module, bool $countOnly)
    {
        $event = new NewsFilterEvent($archives, $featured, $limit, $offset, $module, true);
        $this->eventDispatcher->dispatch($event);

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

    private function addDefaults(NewsFilterEvent $event, array $archives, ?bool $featured, ?int $limit, ?int $offset, Module $module): void
    {
        $t = NewsModel::getTable();
        $event->addColumn("$t.pid IN(".implode(',', array_map('\intval', $archives)).')');

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

            switch ($module->news_order) {
                case 'order_headline_asc':
                    $order .= "$t.headline";
                    break;

                case 'order_headline_desc':
                    $order .= "$t.headline DESC";
                    break;

                case 'order_random':
                    $order .= 'RAND()';
                    break;

                case 'order_date_asc':
                    $order .= "$t.date";
                    break;

                default:
                    $order .= "$t.date DESC";
            }

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
