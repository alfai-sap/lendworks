use App\Traits\LogsAdminActivity;

class PaymentController extends Controller
{
    use LogsAdminActivity;

    public function verify(PaymentRequest $payment)
    {
        // ...existing verification code...

        $this->logActivity(
            "Payment verified: #{$payment->reference_number}",
            'info',
            'admin',
            [
                'payment_id' => $payment->id,
                'amount' => $payment->amount
            ]
        );

        // ...rest of existing code...
    }

    public function reject(PaymentRequest $payment)
    {
        // ...existing rejection code...

        $this->logActivity(
            "Payment rejected: #{$payment->reference_number}",
            'warning',
            'admin',
            [
                'payment_id' => $payment->id,
                'reason' => $request->feedback
            ]
        );

        // ...rest of existing code...
    }
}
