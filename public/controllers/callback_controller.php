<?php
class CallbackController extends Controller {
    function __construct() {
        $this->render	= false;
        $this->layout	= false;

        parent::__construct();
    }

    private function good_request() {
        header("HTTP/1.1 200 OK");
    }

    private function bad_request() {
        header("HTTP/1.1 400 Bad Request");
    }

    function paypal() {
        $method = $_SERVER['REQUEST_METHOD'];
        if ('POST' == $method) {
            $p = new PayPal();
            if ($p->verifyIPN()) {
                $paymentData = $p->ipn_data;
                /*$paymentData = [
                    'custom'            => 1485,
                    'payment_status'    => 'Completed',
                    'txn_id'            => '2WF07612DY1362636'
                ];*/

                $star_purchase	= StarPurchase::find_first("id=" . $paymentData['custom']);
                if ($star_purchase) {
                    $is_dbl     = StarDouble::find_first("'{$star_purchase->created_at}' BETWEEN data_init AND data_end");
                    $star_plan  = StarPlan::find_first("id = " . $star_purchase->star_plan_id);
                    $user       = User::find($star_purchase->user_id);
                    $credits    = !$is_dbl ? $star_plan->coin : ($star_plan->coin * 2);

                    switch ($paymentData['payment_status']) {
                        case 'Completed':
                            if ($star_purchase->status != 'aprovado') {
                                $user->earn($credits);

                                $star_purchase->status  = 'aprovado';
                            }
                            break;
                        case 'Reversed':
                            $star_purchase->status      = 'estornado';
                            $user->spend($credits);
                            break;
                    }
                    $star_purchase->transid             = $paymentData['txn_id'];
                    $star_purchase->completed_at        = now(TRUE);
                    $star_purchase->save();

                } else {
                    // echo 'Achei n cara!';
                    $this->bad_request();
                    return;
                }
            } else {
                $this->good_request();
                return;
            }
        } else {
            $this->bad_request();
            return;
        }
    }

    function pagseguro() {
        \PagSeguro\Library::initialize();
        \PagSeguro\Library::cmsVersion()->setName(GAME_NAME)->setRelease(GAME_VERSION);
        \PagSeguro\Library::moduleVersion()->setName(GAME_NAME)->setRelease(GAME_VERSION);
        try {
            if (\PagSeguro\Helpers\Xhr::hasPost()) {
                $response = \PagSeguro\Services\Transactions\Notification::check(
                    \PagSeguro\Configuration\Configure::getAccountCredentials()
                );
            } else {
                throw new \InvalidArgumentException($_POST);
            }
        
            echo "<pre>";
            print_r($response);
            echo "</pre>";
        } catch (Exception $e) {
            $this->bad_request();
        }
    }
}