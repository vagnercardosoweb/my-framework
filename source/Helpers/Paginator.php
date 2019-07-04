<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core\Helpers;

/**
 * Class Paginator.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Paginator
{
    /**
     * @var int
     */
    protected $total;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var int
     */
    protected $pages;

    /**
     * @var int
     */
    protected $range;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var string
     */
    protected $currentPage;

    /**
     * @param int    $total
     * @param int    $limit
     * @param int    $range
     * @param string $link
     * @param string $pageString
     */
    public function __construct($total, $link, $limit = 10, $range = 4, $pageString = 'page')
    {
        // Attributes
        $this->total = (int)$total;
        $this->link = (string)$link;
        $this->limit = (int)($limit ? $limit : 10);
        $this->range = (int)($range ? $range : 4);

        // Calculates total pages
        $this->pages = max((int)ceil($this->total / $this->limit), 1);

        // Filter page
        $currentPage = filter_input(INPUT_GET, $pageString, FILTER_DEFAULT);
        $currentPage = ($currentPage > PHP_INT_MAX) ? $this->pages : $currentPage;
        $this->currentPage = (int)(isset($currentPage) ? $currentPage : 1);

        // Calculate offset
        $this->offset = ($this->currentPage * $this->limit) - $this->limit;

        // Mount o link
        if (false !== strpos($this->link, '?')) {
            $this->link = "{$this->link}&{$pageString}=";
        } else {
            $this->link = "{$this->link}?{$pageString}=";
        }

        // Check the page total passed
        if (($this->total > 0 && $this->offset > 0) && ($this->offset >= $this->total)) {
            header("Location: {$this->link}{$this->pages}", true, 301);
            exit;
        }
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode(
            $this->toArray()
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'total' => $this->getTotal(),
            'limit' => $this->getLimit(),
            'offset' => $this->getOffset(),
            'pages' => $this->getPages(),
            'range' => $this->getRange(),
            'prevPage' => $this->getPrevPage(),
            'nextPage' => $this->getNextPage(),
            'currentPage' => $this->getCurrentPage(),
            'currentPageFirstItem' => $this->getCurrentPageFirstItem(),
            'currentPageLastItem' => $this->getCurrentPageLastItem(),
            'items' => $this->getItems(),
        ];
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @return int
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @return string|null
     */
    public function getPrevPage(): ?string
    {
        if ($this->currentPage > 1) {
            $prevPage = (int)($this->currentPage - 1);

            return "{$this->link}{$prevPage}";
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getNextPage(): ?string
    {
        if ($this->pages > $this->currentPage) {
            $nextPage = (int)($this->currentPage + 1);

            return "{$this->link}{$nextPage}";
        }

        return null;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return (int)$this->currentPage;
    }

    /**
     * @return int|null
     */
    public function getCurrentPageFirstItem(): ?int
    {
        $first = ($this->currentPage - 1) * $this->limit + 1;

        return $first <= $this->total
            ? (int)$first
            : null;
    }

    /**
     * @return int|null
     */
    public function getCurrentPageLastItem(): ?int
    {
        if (!$first = $this->getCurrentPageFirstItem()) {
            return null;
        }

        $last = $first + $this->limit - 1;

        return $last > $this->total
            ? (int)$this->total
            : (int)$last;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        $items = [];

        if ($this->getPages() <= 1) {
            return $items;
        }

        if ($this->getPages() <= $this->getRange()) {
            for ($i = 1; $i <= $this->getPages(); $i++) {
                $items[] = $this->createItem($i, $this->getCurrentPage() == $i);
            }
        } else {
            $startPage = ($this->getCurrentPage() - $this->getRange()) > 0
                ? $this->getCurrentPage() - $this->getRange()
                : 1;

            $endPage = ($this->getCurrentPage() + $this->getRange()) < $this->getPages()
                ? $this->getCurrentPage() + $this->getRange()
                : $this->getPages();

            if ($startPage > 1) {
                $items[] = $this->createItem(1, 1 == $this->getCurrentPage());
                $items[] = $this->createItem();
            }

            for ($i = $startPage; $i <= $endPage; $i++) {
                $items[] = $this->createItem($i, $this->getCurrentPage() == $i);
            }

            if ($endPage < $this->getPages()) {
                $items[] = $this->createItem();
                $items[] = $this->createItem($this->getPages(), $this->getCurrentPage() == $this->getPages());
            }
        }

        return $items;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $class
     *
     * @return string|null
     */
    public function toHtml(string $class = 'pagination'): ?string
    {
        if ($this->getPages() <= 1) {
            return null;
        }

        $html = "<ul class='{$class}'>";

        foreach ($this->getItems() as $item) {
            if (!empty($item['pattern'])) {
                $html .= sprintf(
                    "<li class='%s-item %s'><a href='%s'>%s</a></li>",
                    htmlspecialchars($class),
                    htmlspecialchars($item['current'] ? 'active' : ''),
                    htmlspecialchars($item['pattern']),
                    htmlspecialchars($item['number'])
                );
            } else {
                $html .= sprintf(
                    "<li class='%s-item ellipsis'><span>%s</span></li>",
                    htmlspecialchars($class),
                    htmlspecialchars($item['number'])
                );
            }
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * @param int  $number
     * @param bool $current
     *
     * @return array
     */
    protected function createItem(int $number = 0, bool $current = false): array
    {
        return [
            'number' => ($number > 0 ? $number : '...'),
            'pattern' => ($number > 0 ? "{$this->getLink()}{$number}" : false),
            'current' => $current,
        ];
    }
}
