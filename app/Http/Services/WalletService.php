<?php
namespace App\Http\Services;
use App\Models\User;

class WalletService {
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function deposit(float $amount)
    {
        // Ensure the amount is positive
        if ($amount > 0) {
            // Increase the wallet balance
            $this->user->wallet += $amount;
            $this->user->save();
            return $this->getBalance();
            // You can also log the deposit or perform other actions here
        } else {
            // Handle invalid deposit amount (negative or zero)
            throw new \InvalidArgumentException('Invalid deposit amount');
        }
    }

    public function withdraw(float $amount)
    {
        // Ensure the amount is positive
        if ($amount > 0) {
            // Ensure the user has sufficient balance
            if ($this->user->wallet >= $amount) {
                // Decrease the wallet balance
                $this->user->wallet -= $amount;
                $this->user->save();
                return $this->getBalance();
                // You can also log the withdrawal or perform other actions here
            } else {
                // Handle insufficient balance
                throw new \RuntimeException('Insufficient balance');
            }
        } else {
            // Handle invalid withdrawal amount (negative or zero)
            throw new \InvalidArgumentException('Invalid withdrawal amount');
        }
    }

    public function getBalance(): float
    {
        return $this->user->wallet;
    }
}
