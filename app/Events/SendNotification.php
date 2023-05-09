<?php

namespace App\Events;

use App\Models\Transactions\Transaction;

class SendNotification extends Event
{
    public Transaction $transaction;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
