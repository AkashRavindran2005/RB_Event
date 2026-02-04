<h5 class="text-white mb-4"><i class="fas fa-envelope me-2"></i>Contact Messages</h5>

<?php
$result = mysqli_query($conn, "SELECT * FROM messages ORDER BY created_at DESC");
?>

<div class="table-responsive">
    <table class="table table-dark table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Private</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>
                        <?php echo $row['id']; ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['name']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['email']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars(substr($row['message'], 0, 50)) . (strlen($row['message']) > 50 ? '...' : ''); ?>
                    </td>
                    <td>
                        <?php if ($row['is_private']): ?>
                            <span class="badge bg-warning">Private</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Public</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<p class="text-secondary small mt-3">
    <i class="fas fa-info-circle me-1"></i>
    Viewing all contact form submissions including private messages.
</p>