<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

class AnnouncementController extends Controller {

    public function index(): string {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM announcements ORDER BY id DESC LIMIT 1");
        $announcement = $stmt->fetch() ?: [
            'id' => null,
            'message' => 'ImportWale Special: Flat 15% OFF on Crash Guards & Accessories! Use Code: IMPORTWALE15',
            'cta_text' => 'Shop Deals',
            'cta_link' => '/shop',
            'is_active' => 1
        ];

        return $this->render('admin/announcement/index', [
            'announcement' => $announcement
        ]);
    }

    public function update(): void {
        $id = $this->request->input('id');
        $message = trim($this->request->input('message', ''));
        $ctaText = trim($this->request->input('cta_text', ''));
        $ctaLink = trim($this->request->input('cta_link', ''));
        $isActive = $this->request->input('is_active') ? 1 : 0;

        $db = Database::getInstance();

        if (!empty($id)) {
            $stmt = $db->prepare("UPDATE announcements SET message = ?, cta_text = ?, cta_link = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$message, $ctaText, $ctaLink, $isActive, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO announcements (message, cta_text, cta_link, is_active) VALUES (?, ?, ?, ?)");
            $stmt->execute([$message, $ctaText, $ctaLink, $isActive]);
        }

        $this->setFlash('success', 'Announcement Bar updated successfully.');
        $this->response->redirect(url('admin/announcement'));
    }
}
