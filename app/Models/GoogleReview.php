<?php
namespace App\Models;

use App\Core\Model;

class GoogleReview extends Model {
    protected string $table = 'google_reviews';

    public function getActiveReviews(): array {
        return $this->all('display_order ASC, id DESC');
    }
}
