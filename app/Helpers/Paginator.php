<?php
namespace App\Helpers;

class Paginator
{
    protected int $total;
    protected int $perPage;
    protected int $currentPage;
    protected int $lastPage;
    protected string $path;
    protected array $queryParams;

    public function __construct(int $total, int $perPage = 12, int $currentPage = 1, string $path = '', array $queryParams = [])
    {
        $this->total = max(0, $total);
        $this->perPage = max(1, $perPage);
        $this->currentPage = max(1, $currentPage);
        $this->lastPage = (int) ceil($this->total / $this->perPage);
        if ($this->lastPage > 0 && $this->currentPage > $this->lastPage) {
            $this->currentPage = $this->lastPage;
        }
        $this->path = $path;
        $this->queryParams = $queryParams;
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function getLimit(): int
    {
        return $this->perPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLastPage(): int
    {
        return max(1, $this->lastPage);
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function hasPages(): bool
    {
        return $this->lastPage > 1;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getUrl(int $page): string
    {
        $params = array_merge($this->queryParams, ['page' => $page]);
        $queryString = http_build_query($params);
        return $this->path . ($queryString ? '?' . $queryString : '');
    }

    public function render(): string
    {
        if ($this->lastPage <= 1) {
            return '';
        }

        $html = '<nav class="flex items-center justify-between border-t border-gray-900 bg-white px-4 py-3 sm:px-6 my-6 rounded-lg shadow-sm" aria-label="Pagination">';
        $html .= '<div class="hidden sm:block">';
        $html .= '<p class="text-sm text-gray-700">Showing <span class="font-semibold">' . (($this->currentPage - 1) * $this->perPage + 1) . '</span> to <span class="font-semibold">' . min($this->currentPage * $this->perPage, $this->total) . '</span> of <span class="font-semibold">' . $this->total . '</span> results</p>';
        $html .= '</div>';

        $html .= '<div class="flex flex-1 justify-between sm:justify-end gap-2">';

        // Previous Button
        if ($this->currentPage > 1) {
            $html .= '<a href="' . $this->getUrl($this->currentPage - 1) . '" class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Previous</a>';
        } else {
            $html .= '<span class="relative inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-400 cursor-not-allowed">Previous</span>';
        }

        // Page Numbers
        $startPage = max(1, $this->currentPage - 2);
        $endPage = min($this->lastPage, $this->currentPage + 2);

        if ($startPage > 1) {
            $html .= '<a href="' . $this->getUrl(1) . '" class="relative inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 rounded-md">1</a>';
            if ($startPage > 2) {
                $html .= '<span class="relative inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-700">...</span>';
            }
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i === $this->currentPage) {
                $html .= '<span class="relative z-10 inline-flex items-center bg-red-600 px-3 py-2 text-sm font-semibold text-white focus:z-20 rounded-md">' . $i . '</span>';
            } else {
                $html .= '<a href="' . $this->getUrl($i) . '" class="relative inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 rounded-md">' . $i . '</a>';
            }
        }

        if ($endPage < $this->lastPage) {
            if ($endPage < $this->lastPage - 1) {
                $html .= '<span class="relative inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-700">...</span>';
            }
            $html .= '<a href="' . $this->getUrl($this->lastPage) . '" class="relative inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 rounded-md">' . $this->lastPage . '</a>';
        }

        // Next Button
        if ($this->currentPage < $this->lastPage) {
            $html .= '<a href="' . $this->getUrl($this->currentPage + 1) . '" class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Next</a>';
        } else {
            $html .= '<span class="relative inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-400 cursor-not-allowed">Next</span>';
        }

        $html .= '</div></nav>';
        return $html;
    }
}
