<?php
// 读取并解析 JSON 文件
$jsonFile = 'index.json';
$data = [];
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    if ($data === null) {
        die("JSON 解析错误: " . json_last_error_msg());
    }
} else {
    die("index.json 文件不存在");
}

// 设置页面标题
$pageTitle = isset($data['ztt1']) ? $data['ztt1'] : '默认标题';

// 收集背景图片信息
$backgroundImage = '';
$backgroundStyle = '';

foreach ($data as $key => $value) {
    // 收集背景图片（bjj 或 bjp）
    if (strpos($key, 'bjj') === 0 || strpos($key, 'bjp') === 0) {
        $backgroundImage = $value;
    }
    // 收集背景样式（bjz）
    if (strpos($key, 'bjz') === 0 && in_array($value, ['n', 'f', 'l'])) {
        $backgroundStyle = $value;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: #f0f0f0;
            <?php if (!empty($backgroundImage)): ?>
                background-image: url('<?php echo htmlspecialchars($backgroundImage); ?>');
                background-size: cover;
                background-position: center;
            <?php endif; ?>
            
            <?php if ($backgroundStyle === 'n'): ?>
                background-attachment: fixed;
            <?php elseif ($backgroundStyle === 'f'): ?>
                background-repeat: repeat-y;
                background-attachment: fixed;
            <?php elseif ($backgroundStyle === 'l'): ?>
                background-attachment: scroll;
                animation: scrollBackground 60s linear infinite;
            <?php endif; ?>
            
            /* 添加内边距确保内容不顶头 */
            padding: 20px;
            box-sizing: border-box;
        }
        
        @keyframes scrollBackground {
            0% { background-position: center top; }
            100% { background-position: center bottom; }
        }
        
        .content-container {
            max-width: 800px;
            margin: 20px auto; /* 增加顶部外边距 */
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.85);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            
            /* 添加圆角效果 */
            border-radius: 15px;
            
            /* 平滑过渡效果 */
            transition: all 0.3s ease;
        }
        
        /* 添加悬停效果增强交互感 */
        .content-container:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 30px; /* 更圆的按钮 */
            margin: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        .button:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }
        .align-left {
            text-align: left;
        }
        .align-center {
            text-align: center;
        }
        .align-right {
            text-align: right;
        }
        .content-block {
            margin: 15px 0;
            padding: 10px;
            border-radius: 8px; /* 内容块圆角 */
            transition: all 0.2s ease;
        }
        
        /* 添加内容块悬停效果 */
        .content-block:hover {
            background-color: rgba(245, 245, 245, 0.7);
        }
        
        h1, h2, h3, h4, h5, h6 {
            margin-top: 0;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="content-container">
        <?php
        // 处理并显示内容
        foreach ($data as $key => $value) {
            // 跳过标题、a系列链接和背景相关键
            if ($key === 'ztt1' || strpos($key, 'a') === 0 || 
                strpos($key, 'bj') === 0) continue;
            
            // 检测对齐方式 (l, c, r)
            $alignment = 'left'; // 默认左对齐
            $cleanKey = $key;
            
            // 检查键名是否以对齐标识结尾
            if (strlen($key) > 1) {
                $lastChar = substr($key, -1);
                if (in_array($lastChar, ['l', 'c', 'r'])) {
                    $alignment = $lastChar;
                    $cleanKey = substr($key, 0, -1); // 移除对齐标识
                }
            }
            
            // 确定对齐类名
            $alignClass = "align-" . ($alignment === 'c' ? 'center' : ($alignment === 'r' ? 'right' : 'left'));
            
            // 输出内容块
            echo "<div class='content-block $alignClass'>";
            
            // 处理标题标签 h1-h6
            if (preg_match('/^h([1-6])$/', $cleanKey, $matches)) {
                $level = $matches[1];
                echo "<h$level>" . htmlspecialchars($value) . "</h$level>";
            }
            // 处理普通段落 p
     