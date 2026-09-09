<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;

class PaymentService
{
    protected FinancialService $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Record financial payment receipt against booking & milestones.
     */
    public function recordPayment(array $data, User|int|string $user): Payment
    {
        return $this->financialService->recordPayment($data, $user);
    }
}
