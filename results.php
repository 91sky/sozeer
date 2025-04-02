<?php
// 配置信息（需要替换为你的实际信息）
define('CSE_ID', 'your_GOOGLE_CSE ID');      // Google CSE ID
define('API_KEY', 'Your GOOGLE_CSE_KEY');    // Google Cloud API密钥
define('ADSENSE_ID', 'ca-pub-你的广告ID');   // AdSense发布商ID

// 获取搜索查询
$query = isset($_GET['q']) ? htmlspecialchars(trim($_GET['q'])) : '';
$page = isset($_GET['start']) ? (int)$_GET['start'] : 1;
$resultsPerPage = 10;

// 通过CSE API获取搜索结果
function getCseResults($query, $start = 1) {
    $url = "https://www.googleapis.com/customsearch/v1?" . http_build_query([
        'q' => $query,
        'cx' => CSE_ID,
        'key' => API_KEY,
        'start' => $start,
        'num' => $resultsPerPage,
        'hl' => 'zh-CN'
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// 获取搜索结果
$searchResults = [];
$totalResults = 0;
if (!empty($query)) {
    $resultData = getCseResults($query, $page);
    $searchResults = $resultData['items'] ?? [];
    $totalResults = $resultData['searchInformation']['totalResults'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $query ?> - 搜索结果-SOZEER.COM 搜这儿，啥都有</title>
    <style>
        /* 固定顶部导航栏 */
        .search-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            z-index: 1000;
            margin:0 auto;
        }

        .logo {
            height: 40px;
            width: auto;
        }

        /* 搜索框样式 */
        .search-box {
            flex: 1;
            max-width: 700px;
            display: flex;
            gap: 8px;
        }

        .search-input {
            flex: 1;
            padding: 12px 24px;
            border: 1px solid #dfe1e5;
            border-radius: 24px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s;
        }

        .search-input:focus {
            box-shadow: 0 1px 6px rgba(32,33,36,0.28);
            border-color: rgba(223,225,229,0);
        }

        .search-button {
            padding: 12px 32px;
            background: #4285f4;
            border: none;
            border-radius: 24px;
            color: white;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-button:hover {
            background: #2b6cd4;
        }

        /* 搜索结果区域 */
        .results-container {
            max-width: 760px;
            margin: 100px auto 40px;
            padding: 0 20px;
            text-align: left;
        }

        /* 单个搜索结果样式 */
        .search-result {
            margin: 30px 0;
            padding-left: 15px;
            border-left: 3px solid #4285f4;
        }

        .search-result h3 {
            margin: 0 0 8px;
            font-size: 20px;
        }

        .search-result a {
            color: #1a0dab;
            text-decoration: none;
        }

        .search-result .url {
            color: #006621;
            font-size: 14px;
            display: block;
            margin: 5px 0;
        }

        .search-result .snippet {
            color: #545454;
            font-size: 14px;
            line-height: 1.5;
        }

        /* 广告单元样式 */
        .ad-unit {
            margin: 40px 0;
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #dfe1e5;
            border-radius: 8px;
            text-align: center;
        }

        /* 分页样式 */
        .pagination {
            margin: 40px 0;
            text-align: center;
        }

        .pagination a {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 5px;
            color: #1a0dab;
            border: 1px solid #dadce0;
            border-radius: 4px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .pagination a:hover {
            background: #f8f9fa;
        }

        /* 移动端适配 */
        @media (max-width: 768px) {
            .search-header {
                padding: 10px 15px;
                gap: 15px;
            }

            .logo {
                height: 35px;
            }

            .search-input {
                padding: 10px 20px;
                font-size: 15px;
            }

            .search-button {
                padding: 10px 25px;
            }

            .results-container {
                margin-top: 90px;
                padding: 0 15px;
            }

            .search-result {
                margin: 25px 0;
                padding-left: 10px;
            }
        }

        @media (max-width: 480px) {
            .search-header {
                flex-wrap: wrap;
            }

            .logo {
                order: 1;
                height: 30px;
            }

            .search-box {
                order: 2;
                width: 100%;
            }

            .search-button {
                display: none;
            }

            .search-button::after {
                content: "🔍";
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- 固定顶部导航栏 -->
    <header class="search-header">
        <img src="logo.png" alt="网站Logo" class="logo">
        <form action="results.php" method="GET" class="search-box">
            <input type="text" 
                   name="q" 
                   class="search-input" 
                   placeholder="请输入搜索关键词"
                   value="<?php echo $query ?>"
                   autofocus
                   required>
            <button type="submit" class="search-button">搜索</button>
        </form>
    </header>

    <div class="results-container">
        <?php if (!empty($query)): ?>

            <!-- 显示搜索结果 -->
            <?php if (!empty($searchResults)): ?>
                <?php foreach ($searchResults as $index => $item): ?>
                    <div class="search-result">
                        <h3><a href="<?php echo $item['link'] ?>"><?php echo $item['title'] ?></a></h3>
                        <div class="url"><?php echo parse_url($item['link'], PHP_URL_HOST) ?></div>
                        <p class="snippet"><?php echo $item['snippet'] ?? '' ?></p>
                    </div>

                    <!-- 每3条结果插入广告 -->
                    <?php if (($index + 1) % 3 === 0): ?>
                        <div class="ad-unit">
                            <ins class="adsbygoogle"
                                style="display:block"
                                data-ad-client="<?php echo ADSENSE_ID ?>"
                                data-ad-slot="0987654321"
                                data-ad-format="rectangle"
                                data-full-width-responsive="true"></ins>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- 分页导航 -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?q=<?php echo urlencode($query) ?>&start=<?php echo max(1, $page - 1) ?>">上一页</a>
                    <?php endif; ?>
                    
                    <?php if ($totalResults > $page * $resultsPerPage): ?>
                        <a href="?q=<?php echo urlencode($query) ?>&start=<?php echo $page + 1 ?>">下一页</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <h3>未找到相关结果</h3>
                    <p>建议：请检查输入是否正确或尝试其他关键词</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- AdSense脚本 -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script>
        // 初始化广告
        (adsbygoogle = window.adsbygoogle || []).push({});
        
        // 广告自动刷新控制
        let adRefreshInterval;
        function refreshAds() {
            if (document.visibilityState === 'visible') {
                adsbygoogle = window.adsbygoogle || []).push({});
            }
        }

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                adRefreshInterval = setInterval(refreshAds, 30000);
            } else {
                clearInterval(adRefreshInterval);
            }
        });

        // 分页后重新加载广告
        if (history.pushState) {
            const originalPushState = history.pushState;
            history.pushState = function(state) {
                originalPushState.apply(this, arguments);
                setTimeout(() => {
                    document.querySelectorAll('.adsbygoogle').forEach(ad => {
                        if (!ad.dataset.adsbygoogleStatus) {
                            (adsbygoogle = window.adsbygoogle || []).push({});
                        }
                    });
                }, 500);
            };
        }
    </script>
</body>
</html>
