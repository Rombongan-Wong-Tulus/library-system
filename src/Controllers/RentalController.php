<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Exceptions\RentalException;
use App\Repositories\MediaRepository;
use App\Repositories\TransactionRepository;
use App\Services\AuthService;
use App\Services\RentalService;

class RentalController extends BaseController
{
    public function __construct(
        View $view,
        AuthService $auth,
        private RentalService $rentals,
        private TransactionRepository $transactions,
        private MediaRepository $media
    ) {
        parent::__construct($view, $auth);
    }

    public function myRentals(): void
    {
        $user = $this->requireLogin();
        $rows = [];
        foreach (array_reverse($this->transactions->byUser($user->getId())) as $trx) {
            $rows[] = ['trx' => $trx, 'media' => $this->media->find($trx->getMediaId())];
        }
        $this->render('rental/index', ['rows' => $rows]);
    }

    public function rent(): void
    {
        $user = $this->requireLogin();
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            $mediaId = (string) ($_POST['media_id'] ?? '');
            $trx = $this->rentals->rent($user, $mediaId, (int) ($_POST['days'] ?? 0));
            $msg = 'Berhasil menyewa. Total ' . $this->view->rupiah($trx->getTotalPrice()) . '.';

            if ($isAjax) {
                header('Content-Type: application/json');
                $media = $this->media->find($mediaId);
                echo json_encode([
                    'success' => true,
                    'message' => $msg,
                    'mediaId' => $mediaId,
                    'remainingStock' => $media ? $media->getStock() : 0,
                    'totalPrice' => $trx->getTotalPrice(),
                ]);
                exit;
            }

            Session::flash('success', $msg);
            $this->redirect('rentals');
        } catch (RentalException $e) {
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);
                exit;
            }

            Session::flash('error', $e->getMessage());
            $this->redirect('catalog');
        }
    }

    public function giveBack(): void
    {
        $user = $this->requireLogin();
        try {
            $trx = $this->rentals->returnMedia($user, (string) ($_POST['trx_id'] ?? ''));
            $msg = 'Media dikembalikan.';
            if ($trx->getLateFee() > 0) {
                $msg .= ' Denda keterlambatan ' . $this->view->rupiah($trx->getLateFee()) . '.';
            }
            Session::flash('success', $msg);
        } catch (RentalException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('rentals');
    }
}
