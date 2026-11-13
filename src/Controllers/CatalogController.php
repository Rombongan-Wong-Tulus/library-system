<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Repositories\MediaRepository;
use App\Services\AuthService;

class CatalogController extends BaseController
{
    public function __construct(View $view, AuthService $auth, private MediaRepository $media)
    {
        parent::__construct($view, $auth);
    }

    public function index(): void
    {
        $this->render('catalog/index', ['items' => $this->media->all()]);
    }
}
