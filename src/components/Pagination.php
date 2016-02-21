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
        'previous' => 'Prev',
        'next' => 'Next',
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
     * Init pagination by passing users' config.
     *
     * @param array $config
     * @return \Avenue\Components\Pagination
     */
    public function init(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->page = $this->app->escape($this->app->arrGet('page', $_GET, 1));
        $this->link = $this->config['link'];
        $this->limit = $this->config['limit'];
        $this->total = $this->config['total'];
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
        return $this->limit;
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

        return ($this->page - 1) * $this->limit;
    }

    /**
     * Get the total page number.
     */
    public function getPageTotal()
    {
        return ceil((int)$this->total / (int)$this->limit);
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
        $page = $this->getCurrentPage();

        // reset to 1 if out of valid range
        if ($page < 1 || $page > $total) {
            $page = 1;
        }

        // range of shown page
        // decide the page start and end
        $stopper = 4;
        $pageStart = (($page - $stopper) > 0) ? $page - $stopper : 1;
        $pageEnd = (($page + $stopper) < $total ) ? $page + $stopper : $total;

        $html = '<ul class="pagination">';

        // previous page link
        if ($page < 2) {
            $html .= '<li class="previous disabled">';
            $html .= '<span>' . $previous . '</span>';
            $html .= '</li>';
        } else {
            $html .= '<li class="previous">';
            $html .= '<a href="' . $link . '?page=' . ($page - 1) . '">' . $previous . '</a>';
            $html .= '</li>';
        }

        // float and first page
        if ($pageStart > 1) {
            (1 == $page) ? $active = 'active' : $active = '';
            $html .= '<li class="page ' . $active . '">';
            $html .= '<a href="' . $link . '?page=1">1</a>';
            $html .= '</li>';

            $html .= '<li class="page float">';
            $html .= '<span>...</span>';
            $html .= '</li>';
        }

        // shown page numbers
        for ($p = $pageStart; $p <= $pageEnd; $p++) {
            ($p == $page) ? $active = 'active' : $active = '';
            $html .= '<li class="page ' . $active . '">';
            $html .= '<a href="' . $link . '?page=' . $p . '">' . $p . '</a>';
            $html .= '</li>';
        }

        // float and last page
        if ($pageEnd < $total) {
            $html .= '<li class="page float">';
            $html .= '<span>...</span>';
            $html .= '</li>';

            ($total == $page) ? $active = 'active' : $active = '';
            $html .= '<li class="page">';
            $html .= '<a href="' . $link . '?page=' . $total. '">' . $total . '</a>';
            $html .= '</li>';
        }

        // next page link
        if ($page < $total) {
            $html .= '<li class="next">';
            $html .= '<a href="' . $link . '?page=' . ($page + 1) . '">' . $next . '</a>';
            $html .= '</li>';
        } else {
            $html .= '<li class="next disabled">';
            $html .= '<span>' . $next . '</span>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        return $html;
    }
}