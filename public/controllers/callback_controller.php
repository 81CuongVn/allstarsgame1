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

                $star_purchase	= StarPurchase::find_first("id=" . $paymentData['custom']);
                if ($star_purchase) {
                    $is_dbl     = StarDouble::find_first("'{$star_purchase->created_at}' BETWEEN data_init AND data_end");
                    $star_plan  = StarPlan::find_first("id = " . $star_purchase->star_plan_id);
                    $user       = User::find($star_purchase->user_id);
                    $credits    = !$is_dbl ? $star_plan->coin : ($star_plan->coin * 2);

                    if ($paymentData['payment_status'] == 'Completed') {
                        if ($star_purchase->status != 'aprovado') {
                            $user->earn($credits);

                            $star_purchase->status  = 'aprovado';
                        }
                    } elseif ($paymentData['payment_status'] == 'Completed') {
                        $user->spend($credits);

                        $star_purchase->status      = 'estornado';
                    }
                    $star_purchase->transid         = $paymentData['txn_id'];
                    $star_purchase->completed_at    = now(TRUE);
                    $star_purchase->save();
                } else {
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
                $transaction = \PagSeguro\Services\Transactions\Notification::check(
                    \PagSeguro\Configuration\Configure::getAccountCredentials()
                );

                $star_purchase	= StarPurchase::find_first("id=" . $transaction->getReference());
                if ($star_purchase) {
                    $is_dbl     = StarDouble::find_first("'{$star_purchase->created_at}' BETWEEN data_init AND data_end");
                    $star_plan  = StarPlan::find_first("id = " . $star_purchase->star_plan_id);
                    $user       = User::find($star_purchase->user_id);
                    $credits    = !$is_dbl ? $star_plan->coin : ($star_plan->coin * 2);

                    $statusCode = $transaction->getStatus();
                    if (in_array($statusCode, [3, 4])) {
                        if ($star_purchase->status != 'aprovado') {
                            $user->earn($credits);

                            $star_purchase->status  = 'aprovado';
                            echo "[{$star_purchase->star_plan_id}] Estrelas creditadas!";
                        }
                    } elseif (in_array($statusCode, [5, 6, 8, 9])) {
                        if ($star_purchase->status == 'aprovado') {
                            $user->spend($credits);

                            $star_purchase->status      = 'estornado';
                            echo "[{$star_purchase->star_plan_id}] Estrelas debitadas!";
                        }

                        if (/*$star_purchase->status != 'disputa' && */$statusCode == 5) {
                            $star_purchase->status  = 'disputa';
                            $user->banned       = TRUE;
                            $user->session_key  = NULL;
                            $user->save();

                            echo "<br />[{$star_purchase->star_plan_id}] Conta banida!";
                        }
                    } elseif (in_array($statusCode, [7])) {
                        $star_purchase->status      = 'cancelado';

                        echo "[{$star_purchase->star_plan_id}] Pagamento cancelado!";
                    }

                    $star_purchase->transid             = $transaction->getCode();
                    $star_purchase->completed_at        = now(TRUE);
                    $star_purchase->save();
                }

                echo '<br />';
                echo 'getReference(): ' . $transaction->getReference() . '<br //>';
                echo 'getStatusType(): ' . $transaction->getStatus() . '<br //>';
                echo 'getStatusCode(): ' . ps_paymentStatus($transaction->getStatus()) . '<br //>';
                echo 'getPaymentMethodType(): ' . ps_paymentMethodType($transaction->getPaymentMethod()->getType()) . '<br //>';
                echo 'getPaymentMethodCode(): ' . ps_paymentMethodCode($transaction->getPaymentMethod()->getCode()) . '<br //>';
                echo 'getCode(): ' . $transaction->getCode() . '<br //>';
            } else {
                throw new \InvalidArgumentException($_POST);
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}