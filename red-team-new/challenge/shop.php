<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";

// Secret item definition - only accessible after gaining enough credits
$secret_item = [
    'id' => 99,
    'name' => 'Executive Access Pass',
    'description' => 'Grants access to restricted executive areas. For authorized personnel only.',
    'price' => 50000
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];  // VULNERABLE: No validation on quantity!

    $query = "SELECT * FROM shop_items WHERE id = $item_id";
    $result = mysqli_query($conn, $query);
    $item = mysqli_fetch_assoc($result);

    if ($item_id == 99) {
        // Secret item purchase - requires having gained credits through exploit
        $item = $secret_item;
    }

    if ($item) {
        // LOGIC FLAW: Negative quantity = negative cost = GAIN credits!
        // Try: quantity=-100 on any item to gain credits
        $total_cost = $item['price'] * $quantity;
        $current_credits = getUserCredits($user_id);

        // Bug: Only checks if credits >= cost, but negative cost always passes!
        if ($current_credits >= $total_cost) {
            $new_credits = $current_credits - $total_cost;  // Subtracting negative = adding!
            mysqli_query($conn, "UPDATE users SET credits = $new_credits WHERE id = $user_id");

            if ($item_id == 99 && $quantity > 0) {
                // Executive Access Pass purchased - this is the flag!
                $message = "Executive Access Pass acquired! <br><span class='text-success'>Access Code: <code>CCEE{l0g1c_fl4w_bus1n3ss_rul3s_br0k3n}</code></span>";
                logActivity('logic_flaw_flag', "User exploited logic flaw to purchase Executive Pass");
            } else if ($item['name'] == 'CTF Flag' && $quantity > 0) {
                $message = "You bought the Premium Flag! But there's something more valuable hidden in the system...";
            } else if ($quantity < 0) {
                $message = "Interesting... you 'returned' " . abs($quantity) . " x " . $item['name'] . " and gained $" . number_format(abs($total_cost)) . " credits!";
            } else {
                $message = "Purchase successful! You bought $quantity x " . $item['name'];
            }
        } else {
            $error = "Insufficient credits! You need $" . number_format($total_cost) . " but only have $" . number_format($current_credits);
        }
    }
}

include 'includes/header.php';
?>

<div class="section-padding">
    <div class="container-custom">
        <div class="mb-5 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-text mb-2">Service Shop</h1>
                <p class="text-secondary" style="font-size: 20px;">Purchase premium support and tools.</p>
            </div>
            <div class="bento-card p-3 d-inline-flex align-items-center gap-3 h-auto">
                <span class="text-secondary small text-uppercase">Balance</span>
                <span class="text-white fw-bold"
                    style="font-size: 24px;">$<?php echo number_format(getUserCredits($user_id)); ?></span>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success bg-opacity-10 border-success text-success mb-4 text-center">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger bg-opacity-10 border-danger text-danger mb-4 text-center"><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="bento-grid">
            <?php
            $items = mysqli_query($conn, "SELECT * FROM shop_items");
            while ($item = mysqli_fetch_assoc($items)):
                ?>
                <div class="bento-card p-4 text-center align-items-center">
                    <div class="mb-4">
                        <?php if (strpos($item['name'], 'Flag') !== false): ?>
                            <i class="fas fa-flag bento-icon text-danger" style="font-size: 48px;"></i>
                        <?php elseif (strpos($item['name'], 'Premium') !== false): ?>
                            <i class="fas fa-headset bento-icon text-warning" style="font-size: 48px;"></i>
                        <?php else: ?>
                            <i class="fas fa-envelope bento-icon text-accent" style="font-size: 48px;"></i>
                        <?php endif; ?>
                    </div>

                    <h3 class="mb-2 text-white"><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p class="text-secondary small mb-4"><?php echo htmlspecialchars($item['description']); ?></p>
                    <h2 class="text-white mb-4">$<?php echo number_format($item['price']); ?></h2>

                    <form method="POST" class="d-flex gap-2 w-100">
                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                        <input type="number" name="quantity" class="form-control text-center" value="1"
                            style="width: 80px;">
                        <button type="submit" class="btn btn-primary flex-grow-1">Buy Now</button>
                    </form>
                </div>
            <?php endwhile; ?>

            <?php
            // Show secret item only when user has exploited the logic flaw
            $current_credits = getUserCredits($user_id);
            if ($current_credits >= 10000):
                ?>
                <!-- Secret Executive Access Pass - only visible after gaining credits -->
                <div class="bento-card p-4 text-center align-items-center"
                    style="border: 2px solid #ffd60a; background: linear-gradient(135deg, rgba(255,214,10,0.1), transparent);">
                    <div class="mb-4">
                        <i class="fas fa-key bento-icon text-warning" style="font-size: 48px;"></i>
                    </div>
                    <span class="badge bg-warning text-dark mb-2">RESTRICTED</span>
                    <h3 class="mb-2 text-warning">Executive Access Pass</h3>
                    <p class="text-secondary small mb-4">Grants access to restricted executive areas. For authorized
                        personnel only.</p>
                    <h2 class="text-warning mb-4">$50,000</h2>
                    <form method="POST" class="d-flex gap-2 w-100">
                        <input type="hidden" name="item_id" value="99">
                        <input type="number" name="quantity" class="form-control text-center" value="1"
                            style="width: 80px;">
                        <button type="submit" class="btn btn-warning flex-grow-1">Purchase</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>