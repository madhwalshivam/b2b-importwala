<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\Testimonial;

class ReviewsController extends BaseController
{
    private Testimonial $testimonialModel;

    public function __construct()
    {
        $this->testimonialModel = new Testimonial();
    }

    public function index(): void
    {
        $testimonials = $this->testimonialModel->getAllActive(100);

        // Calculate metrics
        $totalReviews = count($testimonials);
        $avgRating = 5.0;
        if ($totalReviews > 0) {
            $sum = array_sum(array_column($testimonials, 'rating'));
            $avgRating = round($sum / $totalReviews, 1);
        }

        $this->renderView('web/reviews', [
            'pageTitle'    => 'Verified Customer Reviews & Buyer Testimonials | ImportWale Wholesale',
            'testimonials' => $testimonials,
            'totalReviews' => $totalReviews,
            'avgRating'    => $avgRating
        ]);
    }
}
