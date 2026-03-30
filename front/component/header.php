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
            <a href="/front/frontoffice/">GigaArticle</a>
        </div>
        <div class="header-user">
            <a href="/front/backoffice/auth/login.php" class="user-icon" title="Accéder au Back Office">
                <svg viewBox="0 0 24 24">
                    <path d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10,10-4.48,10-10S17.52,2,12,2Zm0,3c1.66,0,3,1.34,3,3s-1.34,3-3,3-3-1.34-3-3,1.34-3,3-3Zm0,14.2c-2.5,0-4.71-1.28-6-3.22.03-1.99,4-3.08,6-3.08,1.99,0,5.97,1.09,6,3.08-1.29,1.94-3.5,3.22-6,3.22Z"/>
                </svg>
            </a>
        </div>
    </div>
</header>
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
        </script>
    </div>
</div>
