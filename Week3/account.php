<?php
abstract class Account
{
    protected string $type;
    protected float $balance;

    public function __construct(float $initialBalance = 0.0)
    {
        $this->balance = $initialBalance;
        $this->type = static::class;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function deposit(float $amount): void
    {
        if ($amount > 0) {
            $this->balance += $amount;
        }
    }

    abstract public function withdraw(float $amount): bool;

    public function getNameOfClass()
   {
      return static::class;
   }
}


class DebitAccount extends Account
{
    public function withdraw(float $amount): bool
    {
        if ($amount > 0 && $amount <= $this->balance) {
            $this->balance -= $amount;
            return true;
        }
        return false;
    }
}

class CreditAccount extends Account
{
    public function withdraw(float $amount): bool
    {
        if ($amount > 0) {
            $this->balance -= $amount; // can go below 0
            return true;
        }
        return false;
    }
}

class SavingsAccount extends Account
{
    public function withdraw(float $amount): bool
    {
        // Restrict withdrawals to minimum $100 balance
        if ($amount > 0 && ($this->balance - $amount) >= 100) {
            $this->balance -= $amount;
            return true;
        }
        return false;
    }
}
