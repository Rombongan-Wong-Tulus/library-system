<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Media;
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
        $allItems = $this->media->all();

        // Parameter pencarian dan penyaringan sisi server
        $q = trim((string) ($_GET['q'] ?? ''));
        $filterType = trim((string) ($_GET['type'] ?? ''));
        $onlyAvail = isset($_GET['available']) && ($_GET['available'] === '1' || $_GET['available'] === 'on');
        $sort = (string) ($_GET['sort'] ?? 'title_asc');

        $filtered = array_filter($allItems, static function (Media $m) use ($q, $filterType, $onlyAvail): bool {
            if ($q !== '') {
                $searchSpace = mb_strtolower($m->getTitle() . ' ' . $m->getAuthor() . ' ' . $m->getDescription());
                if (!str_contains($searchSpace, mb_strtolower($q))) {
                    return false;
                }
            }
            if ($filterType !== '' && $m->getType()->value !== $filterType) {
                return false;
            }
            if ($onlyAvail && !$m->isAvailable()) {
                return false;
            }
            return true;
        });

        // Pengurutan sisi server
        usort($filtered, static function (Media $a, Media $b) use ($sort): int {
            return match ($sort) {
                'title_desc' => strcasecmp($b->getTitle(), $a->getTitle()),
                'price_asc' => $a->getBasePrice() <=> $b->getBasePrice(),
                'price_desc' => $b->getBasePrice() <=> $a->getBasePrice(),
                'newest' => strcmp($b->getCreatedAt() ?? '', $a->getCreatedAt() ?? ''),
                default => strcasecmp($a->getTitle(), $b->getTitle()),
            };
        });

        // Paginasi sisi server (6 item per halaman)
        $perPage = 6;
        $totalItems = count($filtered);
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $page = max(1, min($totalPages, (int) ($_GET['page'] ?? 1)));
        $offset = ($page - 1) * $perPage;
        $items = array_slice($filtered, $offset, $perPage);

        $this->render('catalog/index', [
            'items' => $items,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'q' => $q,
            'filterType' => $filterType,
            'onlyAvail' => $onlyAvail,
            'sort' => $sort,
        ]);
    }

    /** Halaman detail media (?r=media&id=...) */
    public function show(): void
    {
        $id = (string) ($_GET['id'] ?? '');
        $media = $this->media->find($id);
        if ($media === null) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('catalog/show', ['media' => $media]);
    }
}
