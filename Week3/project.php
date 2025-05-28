<?php
session_start();

$errors = [];

// Initialize accounts on first visit
if (!isset($_SESSION['accounts'])) {
    $_SESSION['accounts'] = [];
    $_SESSION['account_counter'] = 1;
    $_SESSION['accounts'][1] = new DebitAccount(5000.00);
    $_SESSION['current_account_id'] = 1;
}

$currentId = $_SESSION['current_account_id'];
$currentAccount = $_SESSION['accounts'][$currentId];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Deposit Money
    if (isset($_POST['add_amount'])) {
        $amount = floatval($_POST['add_amount']);
        $currentAccount->deposit($amount);
    }

    // Withdraw Money
    if (isset($_POST['send_amount'])) {
        $amount = floatval($_POST['send_amount']);
        if (!$currentAccount->withdraw($amount) && !($currentAccount instanceof DebitAccount)) {
            $errors[] = "Insufficient balance";
        }
    }

    // Switch account
    if (isset($_POST['switch_account'])) {
        $switchId = intval($_POST['switch_account']);
        if (isset($_SESSION['accounts'][$switchId])) {
            $_SESSION['current_account_id'] = $switchId;
            $currentAccount = $_SESSION['accounts'][$switchId];
        } else {
            $errors[] = "Account not found.";
        }
    }

        // Open account
    if (isset($_POST['open_account'])) {
        $type = $_POST['open_account'];
        $newAccount = match ($type) {
            'debit' => new DebitAccount(0.0),
            'credit' => new CreditAccount(0.0),
            'savings' => new SavingsAccount(0.0),
            default => null
        };

        if ($newAccount) {
            $id = ++$_SESSION['account_counter'];
            $_SESSION['accounts'][$id] = $newAccount;
            $_SESSION['current_account_id'] = $id;
            $currentAccount = $newAccount;
        } else {
            $errors[] = "Invalid account type.";
        }
    }

    // Close account
    if (isset($_POST['close_account'])) {
        if ($currentAccount->getBalance() == 0.0) {
            unset($_SESSION['accounts'][$currentId]);
            if (!empty($_SESSION['accounts'])) {
                $_SESSION['current_account_id'] = array_key_first($_SESSION['accounts']);
            } else {
                // If no accounts left, reset
                $_SESSION['account_counter'] = 1;
                $_SESSION['accounts'][1] = new DebitAccount(0.0);
                $_SESSION['current_account_id'] = 1;
            }
            $currentId = $_SESSION['current_account_id'];
            $currentAccount = $_SESSION['accounts'][$currentId];
        } else {
            $errors[] = "Account must have $0 balance to close.";
        }
    }
}

$balance = number_format($currentAccount->getBalance(), 2);
$accountType = $currentAccount-> getNameOfClass();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <title>My Bank Account</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' href='main.css'>
</head>

<body>
    <main>
        <section class="main-card">
            <h1 class="text">BANK ACCOUNT</h1>
            <p class="text">Type: <strong><?php echo $accountType; ?></strong></p>
            <p class="text">Account Balance</p>
            <h2 class="text">$<?php echo $balance; ?></h2>

            <?php if (!empty($errors)): ?>
                <ul class="text" style="color:red;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <section id="buttons-list">
                <!-- Add Money -->
                <form method="POST" class="form-group">
                    <input type="number" step="0.01" name="add_amount" placeholder="Amount to add" class="input">
                    <button type="submit" class="btn">Add Money</button>
                </form>

                <!-- Withdraw Money -->
                <form method="POST" class="form-group">
                    <input type="number" step="0.01" name="send_amount" placeholder="Amount to send" class="input">
                    <button type="submit" class="btn">Send Money</button>
                </form>

                <!-- Switch Account -->
                <form method="POST" class="form-group">
                    <select name="switch_account" class="select" required>
                        <option value="">-- Switch Account --</option>
                        <?php foreach ($_SESSION['accounts'] as $id => $acc): ?>
                            <option value="<?= $id ?>" <?= $id == $currentId ? 'selected' : '' ?>>
                                <?= "$id - " . $acc->getNameOfClass() ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn">Switch Account</button>
                </form>

                <!-- Create New Account -->
                <form method="POST" class="form-group">
                    <select name="open_account" class="select" required>
                        <option value="">-- Select New Account Type --</option>
                        <option value="debit">Debit Account</option>
                        <option value="credit">Credit Account</option>
                        <option value="savings">Savings Account</option>
                    </select>
                    <button type="submit" class="btn">Open Account</button>
                </form>

                <!-- Delete Account -->
                <form method="POST" class="form-group">
                    <input type="hidden" name="close_account" value="1">
                    <button type="submit" class="btn" style="background-color: darkred; color: wheat;">Close Account</button>
                </form>
            </section>
        </section>
    </main>
</body>

</html>


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
