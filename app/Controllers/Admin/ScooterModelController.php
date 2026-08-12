<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\ScooterModel;
use App\Models\Brand;
use App\Helpers\Paginator;

class ScooterModelController extends Controller {
    protected ScooterModel $scooterModel;

    public function __construct() {
        parent::__construct();
        $this->scooterModel = new ScooterModel();
    }

    public function index(): string {
        if (!Auth::hasPermission('scooter_models.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $brandModel = new Brand();
        $brands = $brandModel->all('name ASC');

        $models = $this->scooterModel->getAllWithBrand();

        return $this->render('admin/scooter_models/index', [
            'models' => $models,
            'brands' => $brands
        ]);
    }

    public function store(): void {
        if (!Auth::hasPermission('scooter_models.add')) {
            $this->redirect(url('admin/scooter-models'));
        }

        $name = $this->request->input('name');
        $brandId = (int)$this->request->input('brand_id');

        $id = $this->scooterModel->insert([
            'brand_id' => $brandId,
            'name' => $name,
            'slug' => slugify($name),
            'year_generation' => $this->request->input('year_generation'),
            'sort_order' => (int)$this->request->input('sort_order', 0),
            'status' => $this->request->input('status', 'active')
        ]);

        activity_log('Create Scooter Model', 'Scooter Models', $id, "Added model: {$name}");
        $this->setFlash('success', 'Scooter model added successfully.');
        $this->redirect(url('admin/scooter-models'));
    }

    public function update(int $id): void {
        if (!Auth::hasPermission('scooter_models.edit')) {
            $this->redirect(url('admin/scooter-models'));
        }

        $name = $this->request->input('name');
        $this->scooterModel->update($id, [
            'brand_id' => (int)$this->request->input('brand_id'),
            'name' => $name,
            'slug' => slugify($name),
            'year_generation' => $this->request->input('year_generation'),
            'sort_order' => (int)$this->request->input('sort_order', 0),
            'status' => $this->request->input('status', 'active')
        ]);

        activity_log('Update Scooter Model', 'Scooter Models', $id, "Updated model: {$name}");
        $this->setFlash('success', 'Scooter model updated.');
        $this->redirect(url('admin/scooter-models'));
    }

    public function delete(int $id): void {
        if (!Auth::hasPermission('scooter_models.delete')) {
            $this->redirect(url('admin/scooter-models'));
        }

        $this->scooterModel->delete($id);
        activity_log('Delete Scooter Model', 'Scooter Models', $id, "Deleted model ID: {$id}");
        $this->setFlash('success', 'Scooter model deleted.');
        $this->redirect(url('admin/scooter-models'));
    }
}
