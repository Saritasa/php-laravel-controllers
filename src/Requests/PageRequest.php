<?php

namespace Saritasa\LaravelControllers\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Saritasa\DingoApi\Paging\PagingInfo;

/**
 * Contains information about page number and items per page count.
 *
 * @property-read int $page
 * @property-read int $per_page
 */
class PageRequest extends FormRequest
{
    public const PER_PAGE = 'per_page';
    public const PAGE = 'page';

    /**
     * Get the validation rules that apply to the request.
     *
     * @param string[]|array $rules The array of rules for request
     *
     * @return string[]
     */
    public function rules(array $rules = []): array
    {
        return array_merge([
            self::PAGE => 'integer|min:1',
            self::PER_PAGE => 'integer|min:1',
        ], $rules);
    }

    /**
     * Returns Paging Data from request.
     *
     * @return PagingInfo
     */
    public function getPageInfo(): PagingInfo
    {
        return new PagingInfo([
            self::PAGE => $this->page,
            self::PER_PAGE => $this->per_page,
        ]);
    }
}
