[![](https://img.shields.io/packagist/v/inspiredminds/contao-news-filter-event.svg)](https://packagist.org/packages/inspiredminds/contao-news-filter-event)
[![](https://img.shields.io/packagist/dt/inspiredminds/contao-news-filter-event.svg)](https://packagist.org/packages/inspiredminds/contao-news-filter-event)

Contao News Filter Event
========================

Contao provides a way to output custom news items in the news list module via the `newsListFetchItems` hook. However,
if two or more extensions want to filter the news items according to some parameters, only one can win. This Contao
extension instead provides a `NewsFilterEvent` where you can basically customize the parameters of the 
`NewsModel::findBy()` call that will fetch the news items from the database. Multiple extensions can add their 
conditions and thus the news list can be filtered down by multiple parameters provided by these different extensions.

For example, the following event listener would filter the news list via an author query parameter:

```php
// src/EventListener/AuthorNewsFilterListener.php
namespace App\EventListener;

use InspiredMinds\ContaoNewsFilterEvent\Event\NewsFilterEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsEventListener]
class AuthorNewsFilterListener
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function __invoke(NewsFilterEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $authorId = (int) $request->query->get('author');

        if ($authorId <= 0) {
            return;
        }

        $event
            ->addColumn('tl_news.author = ?')
            ->addValue($authorId)
        ;
    }
}
```

Or the following would force sorting all news by their subheadline:

```php
// src/EventListener/AuthorNewsFilterListener.php
namespace App\EventListener;

use InspiredMinds\ContaoNewsFilterEvent\Event\NewsFilterEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class AuthorNewsFilterListener
{
    public function __invoke(NewsFilterEvent $event): void
    {
        $event->addOption('order', 'tl_news.subheadline ASC', true);
    }
}
```

See [here](https://packagist.org/packages/inspiredminds/contao-news-filter-event/dependents?order_by=name) for further examples.
