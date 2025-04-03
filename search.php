<?php
// 配置区
define('CSE_API_KEY', 'your-api-key');
define('CSE_ID', 'your-cse-id');
define('RESULTS_PER_PAGE', 10);
define('MAX_PAGES', 10);

// 安全获取请求参数
$keyword = isset($_GET['q']) ? htmlspecialchars(trim($_GET['q'])) : '';
$page = max(1, min(intval($_GET['page'] ?? 1), MAX_PAGES));
$start_index = ($page - 1) * RESULTS_PER_PAGE + 1;
$results = [];
$totalResults = 0;

// API请求处理
if (!empty($keyword)) {
    $api_url = "https://www.googleapis.com/customsearch/v1?" . http_build_query([
        'key' => CSE_API_KEY,
        'cx' => CSE_ID,
        'q' => $keyword,
        'start' => $start_index,
        'num' => RESULTS_PER_PAGE
    ]);

    $response = @file_get_contents($api_url);
    if ($response !== false) {
        $data = json_decode($response, true);
        $results = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
        $rawTotal = intval($data['searchInformation']['totalResults'] ?? 0);
        $totalResults = min($rawTotal, RESULTS_PER_PAGE * MAX_PAGES);
        $searchTime = number_format($data['searchInformation']['searchTime'] ?? 0, 2);
    }
}

// 分页计算
$totalPages = ceil($totalResults / RESULTS_PER_PAGE);
$totalPages = min($totalPages, MAX_PAGES);
$currentStart = $totalResults > 0 ? $start_index : 0;
$currentEnd = $currentStart + count($results) - 1;

// 富媒体类型配置
$media_config = [
    'image' => ['icon' => '📷', 'class' => 'media-image'],
    'video' => ['icon' => '🎬', 'class' => 'media-video'],
    'news'  => ['icon' => '📰', 'class' => 'media-news'],
    'default' => ['icon' => '🌐', 'class' => 'media-default']
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $keyword ? htmlspecialchars($keyword) . ' - ' : '' ?>SOZEER.COM 搜这儿啥都有</title>
    <style>
        :root {
            --primary-color: #1a73e8;
            --secondary-color: #f8f9fa;
            --border-color: #dfe1e5;
            --text-secondary: #70757a;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* 搜索头部 */
        .search-header {
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 25px;
            padding: 0 20px;
        }

        .nav-logo {
            height: 40px;
            transition: opacity 0.2s;
        }

        /* 搜索框 */
        .nav-search {
            flex: 1;
            display: flex;
            gap: 12px;
            max-width: 650px;
        }

        .search-input {
            flex: 1;
            padding: 12px 25px;
            border: 1px solid var(--border-color);
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .search-btn {
            padding: 12px 30px;
            background: var(--primary-color);
            border: none;
            border-radius: 25px;
            color: white;
            cursor: pointer;
        }

        /* 主要内容容器 */
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
            flex: 1;
        }

        /* 结果统计 */
        .result-stats {
            color: var(--text-secondary);
            margin: 20px 0;
            font-size: 0.95em;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .stats-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stats-icon {
            font-size: 1.1em;
        }

        /* 搜索结果项 */
        .result-item {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .media-badge {
            position: absolute;
            right: 20px;
            top: 20px;
            background: var(--secondary-color);
            padding: 6px 12px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9em;
        }

        .result-title {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 1.1em;
            display: block;
            margin-bottom: 5px;
        }

        .result-url {
            color: #0d904f;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        /* 分页导航 */
        .pagination {
            margin: 30px 0;
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .page-link {
            display: block;
            padding: 8px 16px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.2s;
        }

        .page-link:hover {
            background: var(--secondary-color);
        }

        .page-link.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .page-link.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* 页脚 */
        .site-footer {
            padding: 20px;
            margin-top: auto;
            text-align: center;
            background: #fff;
            border-top: 1px solid var(--border-color);
        }

        @media (max-width: 480px) {
            .page-link { padding: 6px 12px; }
            .pagination { gap: 4px; }
            .nav-container { gap: 15px; }
            .search-input { padding: 10px 20px; }
        }
    </style>
</head>
<body>
    <header class="search-header">
        <div class="nav-container">
            <a href="index.html">
                <img src="logo.png" alt="返回首页" class="nav-logo">
            </a>
            <form action="search.php" method="GET" class="nav-search">
                <input 
                    type="text" 
                    name="q" 
                    class="search-input"
                    value="<?= $keyword ?>"
                    placeholder="输入搜索关键词..."
                    required
                >
                <button type="submit" class="search-btn">搜索</button>
            </form>
        </div>
    </header>

    <div class="container">
        <?php if (!empty($results)): ?>
            <!-- 结果统计 -->
            <div class="result-stats">
                <div class="stats-item">
                    <span class="stats-icon">🔍</span>
                    <span>找到约 <?= number_format($totalResults) ?> 条结果</span>
                </div>
                <div class="stats-item">
                    <span class="stats-icon">📄</span>
                    <span>显示 <?= number_format($currentStart) ?> - <?= number_format($currentEnd) ?> 条</span>
                </div>
                <?php if(isset($searchTime)): ?>
                <div class="stats-item">
                    <span class="stats-icon">⏱️</span>
                    <span>耗时 <?= $searchTime ?> 秒</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- 搜索结果列表 -->
            <?php foreach ($results as $item): 
                $media_type = strtolower($item['pagemap']['metatags']['og:type'] ?? 'default');
                $media = $media_config[$media_type] ?? $media_config['default'];
            ?>
                <div class="result-item <?= $media['class'] ?>">
                    <div class="media-badge">
                        <span><?= $media['icon'] ?></span>
                        <span><?= ucfirst($media_type) ?></span>
                    </div>

                    <a href="<?= htmlspecialchars($item['link']) ?>" class="result-title">
                        <?= htmlspecialchars($item['title'] ?? '无标题信息') ?>
                    </a>
                    <div class="result-url">
                        <?= parse_url($item['link'], PHP_URL_HOST) ?>
                    </div>

                    <?php if ($media_type === 'image' && !empty($item['pagemap']['cse_image'])): ?>
                        <img src="<?= htmlspecialchars($item['pagemap']['cse_image']['src']) ?>" 
                             class="preview-image" 
                             alt="结果预览"
                             loading="lazy">
                    <?php elseif ($media_type === 'video' && !empty($item['pagemap']['videoobject'])): ?>
                        <div class="video-container">
                            <iframe class="video-iframe"
                                src="<?= htmlspecialchars($item['pagemap']['videoobject']['embedurl']) ?>"
                                allowfullscreen>
                            </iframe>
                        </div>
                    <?php else: ?>
                        <p class="result-snippet">
                            <?= htmlspecialchars($item['snippet'] ?? '暂无内容摘要') ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- 分页导航 -->
            <?php if ($totalPages > 1): ?>
                <nav class="pagination">
                    <div class="page-item">
                        <?php if ($page > 1): ?>
                            <a href="?q=<?= urlencode($keyword) ?>&page=<?= $page-1 ?>" 
                               class="page-link">上一页</a>
                        <?php else: ?>
                            <span class="page-link disabled">上一页</span>
                        <?php endif; ?>
                    </div>

                    <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    if ($startPage > 1) echo '<span class="page-link">···</span>';
                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <div class="page-item">
                            <a href="?q=<?= urlencode($keyword) ?>&page=<?= $i ?>" 
                               class="page-link <?= $i == $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        </div>
                    <?php endfor; 
                    if ($endPage < $totalPages) echo '<span class="page-link">···</span>'; ?>

                    <div class="page-item">
                        <?php if ($page < $totalPages): ?>
                            <a href="?q=<?= urlencode($keyword) ?>&page=<?= $page+1 ?>" 
                               class="page-link">下一页</a>
                        <?php else: ?>
                            <span class="page-link disabled">下一页</span>
                        <?php endif; ?>
                    </div>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-results">
                <h3>未找到相关结果</h3>
                <p>请尝试以下方法：</p>
                <ul>
                    <li>检查拼写是否正确</li>
                    <li>使用更通用的关键词</li>
                    <li>减少筛选条件</li>
                </ul>
                <?php if (!empty($keyword)): ?>
                    <div class="result-stats" style="margin-top:20px;">
                        <div class="stats-item">
                            <span class="stats-icon">⚠️</span>
                            <span>搜索关键词：<?= $keyword ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="site-footer">
        <div class="container">
            <p>© 2015-<?= date('Y') ?> 智能搜索 京ICP备XXXXX号</p>
            <p style="margin-top:10px;">严格执行隐私保护政策，绝不记录用户搜索行为</p>
        </div>
    </footer>
</body>
</html>
