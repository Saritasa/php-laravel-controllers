<?php

namespace Saritasa\LaravelControllers\Paging;

const PAGE = 'page';
const PAGE_SIZE = 'per_page';
const TOTAL_PAGES = 'total_pages';
const TOTAL_COUNT = 'total_count';

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Saritasa\Exceptions\PagingException;
use Saritasa\Traits\SimpleJsonSerialize;

/**
 * Paging Data - information about page size, current page, number of total pages and items count
 *
 * @property integer $page Selected page in data set
 * @property integer $pageSize Number of records in single page
 * @property-read integer $totalPages Number of pages in data set
 * @property integer $totalCount Total number of records in data set
 */
class PagingInfo implements Arrayable, Jsonable, \JsonSerializable
{
    use SimpleJsonSerialize;

    private $page = 1;
    private $pageSize = 0;
    private $totalPages = 0;
    private $totalCount = 0;

    const KEYS = [PAGE, PAGE_SIZE, TOTAL_PAGES, TOTAL_COUNT];
    const PROPERTIES = ['page', 'pageSize', 'totalPages', 'totalCount'];

    public function __construct(array $input = null)
    {
        $this->pageSize = config('api.defaultLimit');
        if ($input) {
            if (isset($input[PAGE])) {
                $this->setPage($input[PAGE]);
            }
            if (isset($input[PAGE_SIZE])) {
                $this->setPageSize($input[PAGE_SIZE]);
            }
            if (isset($input[TOTAL_COUNT])) {
                $this->setTotalCount($input[TOTAL_COUNT]);
            }
        }
    }

    public function __get(string $key): int
    {
        if (in_array($key, static::PROPERTIES)) {
            return $this->$key;
        } else {
            throw new PagingException("Unknown property $key requested");
        }
    }

    public function __set(string $key, $value)
    {
        if (in_array($key, static::PROPERTIES)) {
            $method = 'set'.ucfirst($key);
            $this->$method($value);
        } else {
            throw new PagingException("Trying to set unknown property $key");
        }
    }

    protected function setPage(int $value)
    {
        $val = $value;
        if ($val < 1) {
            throw new PagingException("Page number cannot be less, than 1");
        }
        $this->page = $val;
    }

    protected function setPageSize(int $value)
    {
        $val = $value;
        if ($val < 1) {
            throw new PagingException("Page size cannot be less, than 1");
        }
        // We do not handle config('api.maxLimit') here, because, unlike user input,
        // it's OK, if developer set it programmatically to greater value
        $this->pageSize = $val;
        $this->totalPages = (int)ceil($this->totalCount / $this->pageSize);
    }

    protected function setTotalPages(int $value)
    {
        $val = $value;
        if ($val < 0) {
            throw new PagingException("Total pages count cannot be less, than 0");
        }
        $this->totalPages = $val;
    }

    protected function setTotalCount(int $value)
    {
        $val = $value;
        if ($val < 0) {
            throw new PagingException("Total items count cannot be less, than 0");
        }
        $this->totalCount = $val;
        $this->totalPages = (int)ceil($this->totalCount / $this->pageSize);
    }

    public function getOffset()
    {
        $pageZeroIndexed = min($this->page - 1, 0);
        $offset = $this->pageSize * $pageZeroIndexed;
        if ($offset > $this->totalCount) {
            $actualPageCount = (int)ceil($this->totalCount / $this->pageSize);
            throw new PagingException("Non existing page requested: $this->pageSize of $actualPageCount");
        }
        return $offset;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            PAGE => $this->page,
            PAGE_SIZE => $this->pageSize,
            TOTAL_PAGES => $this->totalPages,
            TOTAL_COUNT => $this->totalCount
        ];
    }
}
