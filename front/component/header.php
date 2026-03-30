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
</style>
<header class="site-header">
    <div class="header-container">
        <div class="header-date">
            <?= (new DateTime())->format('l, F j, Y') ?><br>
            <small><?= (new DateTime())->format('g:i a') ?></small>
        </div>
        <div class="header-logo">
            GigaArticle
        </div>
        <div class="header-spacer"></div>
    </div>
</header>
