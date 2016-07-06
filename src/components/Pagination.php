<?php
namespace Avenue\Components;

use Avenue\App;

class Pagination
{
    /**
     * App class instance.
     *
     * @var mixed
     */
    protected $app;

    /**
     * Default pagination configuration.
     *
     * @var array
     */
    protected $config = [
        'previous' => '&lt;&lt;',
        'next' => '&gt;&gt;',
        'link' => './',
        'limit' => 10,
        'total' => 0
    ];

    /**
     * Page limit.
     *
     * @var mixed
     */
    protected $limit;

    /**
     * Total records.
     *
     * @var mixed
     */
    protected $total;

    /**
     * Current page.
     *
     * @var mixed
     */
    protected $page;

    /**
     * Page link.
     *
     * @var mixed
     */
    protected $link;

    /**
     * Previous text.
     *
     * @var mixed
     */
    protected $previous;

    /**
     * Next text.
     *
     * @var mixed
     */
    protected $next;

    /**
     * Pagination class constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Init pagination by setting based on the configuration.
     *
     * @param array $config
     * @return \Avenue\Pagination
     */
    public function set(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->page = (int) $this->app->escape($this->app->arrGet('page', $_GET, 1));
        $this->link = $this->config['link'];
        $this->limit = (int) $this->config['limit'];
        $this->total = (int) $this->config['total'];
        $this->previous = $this->config['previous'];
        $this->next = $this->config['next'];

        return $this;
    }

    /**
     * Get the limit, number of rows per page.
     *
     * @return mixed
     */
    public function getPageLimit()
    {
        return ($this->limit > 0) ? $this->limit : 1;
    }

    /**
     * Get the calculated offset.
     *
     * @return number
     */
    public function getPageOffset()
    {
        if ($this->page < 1) {
            $this->page = 1;
        }

        return ($this->page - 1) * $this->getPageLimit();
    }

    /**
     * Get the total page number.
     */
    public function getPageTotal()
    {
        return ceil($this->total / $this->getPageLimit());
    }

    /**
     * Get the current page.
     *
     * @return mixed
     */
    public function getCurrentPage()
    {
        return $this->page;
    }

    /**
     * Get the previous label.
     *
     * @return mixed
     */
    public function getPreviousLabel()
    {
        return $this->previous;
    }

    /**
     * Get the next label.
     *
     * @return mixed
     */
    public function getNextLabel()
    {
        return $this->next;
    }

    /**
     * Get the page link.
     *
     * @return mixed
     */
    public function getPageLink()
    {
        return $this->link;
    }

    /**
     * Create the pagination links and return.
     */
    public function render()
    {
        $total = $this->getPageTotal();
        $previous = $this->getPreviousLabel();
        $next = $this->getNextLabel();
        $link = $this->getPageLink();
        $limit = $this->getPageLimit();
        $page = $this->getCurrentPage();

        // reset to 1 if out of valid range
        if ($page < 1 || $page > $total) {
            $page = 1;
        }

        $range = 10;

        if ($total < $range) {
            $pageStart = 1;
            $pageEnd = $total;
        } else {
            $pageStart = $page < ($range / 2) ? 1 : $page - ($range / 2) + 1;
            $pageEnd = ($pageStart + $range - 1) < $total ? $pageStart + $range - 1 : $total;
        }

        if ($total > $range && $pageEnd === $total) {
            $pageStart = $pageEnd - $range + 1;
        }

        $html = '<ul class="pagination">';

        // previous page link
        if ($page > 1) {
            $html .= '<li class="previous">';
            $html .= '<a href="' . $link . '?page=' . ($page - 1) . '">' . $previous . '</a>';
            $html .= '</li>';
        }

        // shown page numbers
        for ($p = $pageStart; $p <= $pageEnd; $p++) {
            ($p == $page) ? $active = 'active' : $active = '';
            $html .= '<li class="page ' . $active . '">';
            $html .= '<a href="' . $link . '?page=' . $p . '">' . $p . '</a>';
            $html .= '</li>';
        }

        // next page link
        if ($page < $total) {
            $html .= '<li class="next">';
            $html .= '<a href="' . $link . '?page=' . ($page + 1) . '">' . $next . '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        return $html;
    }
}