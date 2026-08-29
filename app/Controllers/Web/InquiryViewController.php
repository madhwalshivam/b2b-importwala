<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Repositories\Eloquent\CategoryRepository;

class InquiryViewController extends BaseController
{
    private CategoryRepository $categoryRepo;

    public function __construct()
    {
        $this->categoryRepo = new CategoryRepository();
    }

    public function index(): void
    {
        $categories = $this->categoryRepo->getTree();
        $this->renderView('web/inquiry', [
            'categories' => $categories,
        ]);
    }
}
