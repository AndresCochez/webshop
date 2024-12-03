<h2>Reviews</h2>
            <?php if ($reviews): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="mb-3">
                        <strong><?php echo htmlspecialchars($review['username']); ?></strong> 
                        <span>(<?php echo $review['rating']; ?>/5)</span>
                        <p><?php echo htmlspecialchars($review['comment']); ?></p>
                        <small class="text-muted">Op <?php echo date('d-m-Y', strtotime($review['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Er zijn nog geen reviews voor dit product.</p>
            <?php endif; ?>