<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Setting;
use App\Core\Database;
use App\Helpers\Paginator;

class SettingsController extends Controller {
    public function index(): string {
        if (!Auth::hasPermission('settings.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $settingModel = new Setting();
        $settings = $settingModel->getAllAsKeyValue();

        return $this->render('admin/settings/index', [
            'settings' => $settings
        ]);
    }

    public function update(): void {
        if (!Auth::hasPermission('settings.edit')) {
            $this->redirect(url('admin/settings'));
        }

        $settingModel = new Setting();
        $inputs = $_POST;

        // Determine which show_social_* keys were submitted from the hidden input trick.
        // The hidden input (value=0) + checkbox (value=1) pattern means PHP's $_POST
        // array will hold the LAST submitted value for duplicate keys.
        // Since the hidden input always comes before the checkbox in the form,
        // PHP $_POST correctly holds '1' when checked and '0' when unchecked.
        // We just need to make sure any show_social_* key NOT present at all defaults to '0'.
        $socialShowKeys = [
            'show_social_instagram', 'show_social_youtube', 'show_social_facebook',
            'show_social_twitter',   'show_social_whatsapp', 'show_social_linkedin',
        ];
        foreach ($socialShowKeys as $sk) {
            if (!isset($inputs[$sk])) {
                $inputs[$sk] = '0';
            }
        }

        foreach ($inputs as $key => $val) {
            if ($key !== '_csrf_token') {
                $settingModel->set($key, $val);
            }
        }

        activity_log('Update Settings', 'Settings', null, "Updated store & API settings");
        $this->setFlash('success', 'Settings saved successfully.');
        $this->redirect(url('admin/settings'));
    }
}
