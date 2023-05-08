<?php

namespace App\Observers;

use App\Models\Retailer;
use Ramsey\Uuid\Uuid;

class RetailerObserver
{
    public function created(Retailer $retailer)
    {
        try {
            $retailer->wallet()->create([
                'id' => Uuid::uuid4()->toString(),
                'balance' => 0
            ]);
        } catch (\Exception $exception){
            dd($exception->getMessage());
        }

    }
}
