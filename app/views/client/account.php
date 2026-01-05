<?php Auth::requireRole(['client']); ?>
<?php require __DIR__ . '/../partials/head.php'; ?>

<div class="container">
    <h2>My Account</h2>
    <p><a href="<?= BASE_URL ?>client/dashboard">⬅ Back</a></p>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <p style="color:#b91c1c; background:#fee2e2; padding:10px; border-radius:10px;">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </p>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <p style="color:#166534; background:#dcfce7; padding:10px; border-radius:10px;">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </p>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <style>
        .tabs {
            display: flex;
            gap: 10px;
            margin: 12px 0;
        }

        .tab-btn {
            padding: 10px 14px;
            border: 1px solid #ddd;
            background: #f7f7f7;
            cursor: pointer;
            border-radius: 12px;
            font-weight: 600;
        }

        .tab-btn.active {
            background: #fff;
            border-bottom: 2px solid #111;
        }

        .tab-panel {
            display: none;
            border: 1px solid #e5e7eb;
            padding: 16px;
            border-radius: 16px;
            background: #fff;
        }

        .tab-panel.active {
            display: block;
        }

        .card {
            display: flex;
            gap: 18px;
            align-items: center;
            padding: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: #fafafa;
            margin: 10px 0 18px;
        }

        .avatar {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #d1d5db;
            background: #fff;
        }

        .avatar-fallback {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e5e7eb;
            color: #374151;
            font-size: 34px;
            font-weight: 700;
            border: 1px solid #d1d5db;
        }

        .muted {
            color: #6b7280;
        }

        .row {
            margin: 10px 0;
        }

        .label {
            font-size: 13px;
            color: #374151;
            font-weight: 600;
        }

        .input {
            width: 340px;
            max-width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            outline: none;
        }

        .input:focus {
            border-color: #111;
        }

        .btn {
            padding: 10px 14px;
            border: 1px solid #111;
            background: #111;
            color: #fff;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
        }

        .btn.secondary {
            background: #fff;
            color: #111;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            max-width: 720px;
        }

        @media (max-width: 720px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="tabs">
        <button class="tab-btn" data-tab="profile">Profile</button>
        <button class="tab-btn" data-tab="password">Change password</button>
    </div>

    <div id="profile" class="tab-panel">
        <h3 style="margin-top:0;">Profile</h3>

        <?php
        $avatar = $client['avatar'] ?? '';
        $name = $client['name'] ?? '';
        $email = $client['email'] ?? '';
        $initial = strtoupper(substr(trim($name), 0, 1)) ?: '?';
        ?>

        <!-- <div class="card">
        <div>
            <?php if (!empty($client['avatar'])): ?>
                <img
                    class="avatar"
                    src="<?= BASE_URL ?>avatar&user_id=<?= (int)$client['id'] ?>"
                    alt="avatar">
            <?php else: ?>
                <div class="avatar-fallback"><?= htmlspecialchars($initial) ?></div>
            <?php endif; ?>

        </div>

        <div style="flex:1;">
            <div style="display:flex; align-items:baseline; justify-content:space-between; gap:10px;">
                <div>
                    <div style="font-size:20px; font-weight:800; line-height:1.2;">
                        <?= htmlspecialchars($name) ?>
                    </div>
                    <div class="muted" style="margin-top:4px;">
                        <?= htmlspecialchars($email) ?>
                    </div>
                </div>
            </div>

            <div style="margin-top:10px; display:flex; flex-wrap:wrap; gap:10px;">
                <?php if (!empty($client['company'])): ?>
                    <span style="background:#fff; border:1px solid #e5e7eb; padding:6px 10px; border-radius:999px;">
                        🏢 <?= htmlspecialchars($client['company']) ?>
                    </span>
                <?php endif; ?>

                <?php if (!empty($client['phone'])): ?>
                    <span style="background:#fff; border:1px solid #e5e7eb; padding:6px 10px; border-radius:999px;">
                        📞 <?= htmlspecialchars($client['phone']) ?>
                    </span>
                <?php endif; ?>

                <?php if (!empty($client['address'])): ?>
                    <span style="background:#fff; border:1px solid #e5e7eb; padding:6px 10px; border-radius:999px;">
                        📍 <?= htmlspecialchars($client['address']) ?>
                    </span>
                <?php endif; ?>

                <?php if (empty($client['company']) && empty($client['phone']) && empty($client['address'])): ?>
                    <span class="muted">Completează datele de mai jos ca să apară aici.</span>
                <?php endif; ?>
            </div>
        </div>
    </div> -->

        <h4 style="margin:0 0 10px;">Edit profile</h4>

        <form method="POST" action="<?= BASE_URL ?>client/profile" enctype="multipart/form-data">
            <div class="grid">
                <div class="row">
                    <div class="label">Company</div>
                    <input class="input" name="company" value="<?= htmlspecialchars($client['company'] ?? '') ?>">
                </div>

                <div class="row">
                    <div class="label">Phone</div>
                    <input class="input" name="phone" value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
                </div>

                <div class="row" style="grid-column: 1 / -1;">
                    <div class="label">Address</div>
                    <input class="input" name="address" value="<?= htmlspecialchars($client['address'] ?? '') ?>">
                </div>

                <div class="row" style="grid-column: 1 / -1;">
                    <div class="label">Avatar (jpg/png/webp, max 3MB)</div>
                    <input class="input" style="padding:8px 12px;" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>

            <div style="margin-top:14px;">
                <button class="btn" type="submit">Save changes</button>
            </div>
        </form>
    </div>

    <div id="password" class="tab-panel">
        <h3 style="margin-top:0;">Change password</h3>

        <form method="POST" action="<?= BASE_URL ?>client/change-password">
            <div class="row">
                <div class="label">Old password</div>
                <input class="input" type="password" name="current_password" required>
            </div>

            <div class="row">
                <div class="label">New password</div>
                <input class="input" type="password" name="new_password" required>
            </div>

            <div class="row">
                <div class="label">Confirm new password</div>
                <input class="input" type="password" name="new_password_confirm" required>
            </div>

            <div style="margin-top:14px;">
                <button class="btn" type="submit">Update password</button>
            </div>
        </form>
    </div>

    <script>
        const buttons = document.querySelectorAll('.tab-btn');
        const panels = document.querySelectorAll('.tab-panel');

        function activate(tab) {
            buttons.forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
            panels.forEach(p => p.classList.toggle('active', p.id === tab));
            if (location.hash !== '#' + tab) {
                history.replaceState(null, '', '#' + tab);
            }
        }

        // default: profile la refresh
        const hash = (location.hash || '#profile').replace('#', '');
        activate(hash === 'password' ? 'password' : 'profile');

        buttons.forEach(btn => btn.addEventListener('click', () => activate(btn.dataset.tab)));
    </script>

</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>