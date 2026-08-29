<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Inquiry;

class InquiryController extends BaseController
{
    private Inquiry $inquiryModel;

    public function __construct()
    {
        $this->inquiryModel = new Inquiry();
    }

    public function index(): void
    {
        $search = trim($_GET['search'] ?? $_GET['q'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $businessType = trim($_GET['business_type'] ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $results = $this->inquiryModel->getAllFiltered(
            $search,
            $status,
            $businessType,
            $dateFrom,
            $dateTo,
            $limit,
            $offset
        );

        $totalPages = ceil(($results['total'] ?? 0) / $limit);

        $this->renderView('admin/inquiries/index', [
            'inquiries' => $results['items'],
            'total' => $results['total'],
            'search' => $search,
            'status' => $status,
            'businessType' => $businessType,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'newCount' => $this->inquiryModel->getNewCount(),
        ]);
    }

    public function show($id): void
    {
        $inquiryId = (int)$id;
        $inquiry = $this->inquiryModel->getInquiryWithItems($inquiryId);

        if (!$inquiry) {
            header('Location: ' . url('admin/inquiries'));
            exit;
        }

        $this->renderView('admin/inquiries/show', [
            'inquiry' => $inquiry
        ]);
    }

    public function updateStatus($id): void
    {
        $inquiryId = (int)$id;
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $status = trim($input['status'] ?? '');

        $success = $this->inquiryModel->updateStatus($inquiryId, $status);

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'status' => $status]);
            return;
        }

        header('Location: ' . url('admin/inquiries/' . $inquiryId));
        exit;
    }

    public function updateNotes($id): void
    {
        $inquiryId = (int)$id;
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $notes = trim($input['admin_notes'] ?? '');

        $success = $this->inquiryModel->updateNotes($inquiryId, $notes);

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            return;
        }

        header('Location: ' . url('admin/inquiries/' . $inquiryId));
        exit;
    }

    public function delete($id): void
    {
        $inquiryId = (int)$id;
        $this->inquiryModel->deleteInquiry($inquiryId);

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            return;
        }

        header('Location: ' . url('admin/inquiries'));
        exit;
    }

    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
