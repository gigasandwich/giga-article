<style>
    .site-header {
        background-color: #1b1b1b;
        color: #fff;
        padding: 10px 20px;
        border-bottom: 1px solid #333;
        font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
    }

    .header-container {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
    }

    .header-logo {
        font-family: "Comic Sans MS", "Comic Sans", cursive;
        font-size: 1.8rem;
        font-weight: bold;
        color: #fff;
        text-align: center;
    }

    .header-logo a {
        color: inherit;
        text-decoration: none;
        cursor: pointer;
    }

    .back-office-link {
        display: inline-block;
        margin-left: 15px;
        padding: 4px 12px;
        background: #333;
        color: #fff;
        text-decoration: none;
        font-size: 0.8rem;
        border-radius: 4px;
        font-family: inherit;
        border: 1px solid #444;
        transition: all 0.2s;
    }

    .back-office-link:hover {
        background: #444;
        border-color: #666;
    }

    .header-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }

    .header-date {
        font-size: 0.85rem;
        font-weight: 500;
        line-height: 1.2;
        color: #ccc;
    }

    .header-date small {
        color: #999;
        font-size: 0.75rem;
    }

    .header-user {
        display: flex;
        justify-content: flex-end;
        position: relative;
    }

    .user-menu {
        position: relative;
        display: flex;
        align-items: center;
        padding: 5px 0; /* Add vertical padding to bridging the gap */
    }

    .user-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: #2a2a2a;
        border: 1px solid #444;
        border-radius: 4px;
        min-width: 150px;
        margin-top: 0; /* Changed from 10px to 0 to remove the physical gap */
        display: none;
        flex-direction: column;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    }

    .user-dropdown::after {
        content: '';
        position: absolute;
        top: -10px; /* Bridge the hover area to the parent */
        left: 0;
        right: 0;
        height: 10px;
        background: transparent;
    }

    .user-menu:hover .user-dropdown,
    .user-menu.active .user-dropdown {
        display: flex;
    }

    .user-dropdown::before {
        content: '';
        position: absolute;
        top: -6px;
        right: 12px;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-bottom: 6px solid #444;
    }

    .user-dropdown-item {
        padding: 10px 15px;
        color: #fff;
        text-decoration: none;
        font-size: 0.85rem;
        transition: background 0.2s;
    }

    .user-dropdown-item:hover {
        background: #3a3a3a;
    }

    .user-dropdown-item.logout {
        border-top: 1px solid #444;
        color: #ff4d4d;
    }

    .user-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background-color: #333;
        color: #fff;
        border-radius: 50%;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .user-icon:hover {
        background-color: #444;
    }

    .user-icon svg {
        width: 20px;
        height: 20px;
        fill: currentColor;
    }

    .filter-bar {
        background-color: #1b1b1b;
        border-top: 1px solid #333;
        padding: 8px 20px;
        color: #fff;
        font-family: Inter, system-ui, -apple-system, sans-serif;
    }

    .filter-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .filter-label {
        font-size: 0.8rem;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .filter-form {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .filter-select {
        background: #2a2a2a;
        border: 1px solid #444;
        color: #fff;
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
        outline: none;
        cursor: pointer;
    }

    .filter-select:hover {
        border-color: #666;
    }

    .manual-inputs {
        display: none;
        align-items: center;
        gap: 10px;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .filter-input {
        background: #2a2a2a;
        border: 1px solid #444;
        color: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        outline: none;
    }

    .filter-input:focus {
        border-color: #666;
    }

    .filter-submit {
        background: #444;
        border: none;
        color: #fff;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .filter-submit:hover {
        background: #555;
    }

    .filter-status {
        font-size: 0.85rem;
        color: #ccc;
        margin-left: auto;
    }
</style>
<header class="site-header">
    <div class="header-container">
        <div class="header-date">
            <?= (new DateTime())->format('l, F j, Y') ?><br>
            <small><?= (new DateTime())->format('g:i a') ?></small>
        </div>
        <div class="header-logo">
            <a href="/">GigaArticle</a>
            <?php 
            if (isset($auth) && $auth->isLoggedIn()): ?>
                <a href="/backoffice" class="back-office-link">Back-office</a>
            <?php endif; ?>
        </div>
        <div class="header-user">
            <div class="user-menu">
                <a href="#" class="user-icon" title="Menu Utilisateur" onclick="return false;">
                    <svg viewBox="0 0 24 24">
                        <path d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10,10-4.48,10-10S17.52,2,12,2Zm0,3c1.66,0,3,1.34,3,3s-1.34,3-3,3-3-1.34-3-3,1.34-3,3-3Zm0,14.2c-2.5,0-4.71-1.28-6-3.22.03-1.99,4-3.08,6-3.08,1.99,0,5.97,1.09,6,3.08-1.29,1.94-3.5,3.22-6,3.22Z"/>
                    </svg>
                </a>
                <div class="user-dropdown">
                    <?php if (isset($auth) && $auth->isLoggedIn()): ?>
                        <div class="user-dropdown-item" style="color: #999; font-size: 0.75rem; border-bottom: 1px solid #444;">
                            Connecté en tant que<br>
                            <strong style="color: #fff; font-size: 0.8rem;"><?= htmlspecialchars($auth->getUsername()) ?></strong>
                        </div>
                        <a href="/backoffice" class="user-dropdown-item">BACK-OFFICE</a>
                        <a href="/logout" class="user-dropdown-item logout">Se déconnecter</a>
                    <?php else: ?>
                        <a href="/login" class="user-dropdown-item">Se connecter</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>

<?php 
// Check if the current page is an index.php (Front office or Back office)
$current_page = basename($_SERVER['PHP_SELF']);
$is_list_page = ($current_page === 'index.php');
?>

<?php if ($is_list_page): ?>
<div class="filter-bar">
    <div class="filter-container">
        <span class="filter-label">Voir les informations datant:</span>
        <form class="filter-form" action="" method="GET" id="filterForm">
            <select name="range" class="filter-select" id="rangeSelect">
                <option value="all" <?= ($_GET['range'] ?? '') === 'all' ? 'selected' : '' ?>>Jusqu'à aujourd'hui</option>
                <option value="today" <?= ($_GET['range'] ?? '') === 'today' ? 'selected' : '' ?>>Aujourd'hui seulement</option>
                <option value="manual" <?= ($_GET['range'] ?? '') === 'manual' ? 'selected' : '' ?>>Filtrer manuellement</option>
            </select>

            <div class="manual-inputs" id="manualInputs" style="<?= ($_GET['range'] ?? '') === 'manual' ? 'display: flex;' : '' ?>">
                <input type="datetime-local" name="date_start" class="filter-input" placeholder="Start Date" value="<?= htmlspecialchars($_GET['date_start'] ?? '') ?>">
                <span style="color: #666">a</span>
                <input type="datetime-local" name="date_end" class="filter-input" placeholder="End Date" value="<?= htmlspecialchars($_GET['date_end'] ?? '') ?>">
                <button type="submit" class="filter-submit">Filtrer</button>
            </div>
        </form>

        <script>
            document.getElementById('rangeSelect').addEventListener('change', function() {
                const manualInputs = document.getElementById('manualInputs');
                if (this.value === 'manual') {
                    manualInputs.style.display = 'flex';
                } else {
                    manualInputs.style.display = 'none';
                    document.getElementById('filterForm').submit();
                }
            });

            // Toggle user menu on click
            const userIcon = document.querySelector('.user-icon');
            const userMenu = document.querySelector('.user-menu');
            const userDropdown = document.querySelector('.user-dropdown');

            if (userIcon) {
                userIcon.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    userMenu.classList.toggle('active');
                });
            }

            // Close menu when clicking outside
            document.addEventListener('click', function() {
                if (userMenu) userMenu.classList.remove('active');
            });

            // Prevent closing when clicking inside the dropdown
            if (userDropdown) {
                userDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        </script>
    </div>
</div>
<?php endif; ?>

<?php if (!$is_list_page): ?>
<script>
    // Toggle user menu on click for non-list pages
    const userIconAlt = document.querySelector('.user-icon');
    const userMenuAlt = document.querySelector('.user-menu');
    const userDropdownAlt = document.querySelector('.user-dropdown');

    if (userIconAlt) {
        userIconAlt.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            userMenuAlt.classList.toggle('active');
        });
    }

    document.addEventListener('click', function() {
        if (userMenuAlt) userMenuAlt.classList.remove('active');
    });

    if (userDropdownAlt) {
        userDropdownAlt.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
</script>
<?php endif; ?>
