<?php
// 读取sy.json文件
$jsonFile = 'sy.json';
$data = [];
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
}

// 背景图片处理
$bgImages = [];
foreach ($data as $key => $value) {
    if (strpos($key, 'bj') === 0 && $value !== '') {
        $bgImages[] = $value;
    }
}
$currentBgIndex = 0;
$bgCount = count($bgImages);

// 栏目状态处理函数
function getItemStatus($key, $data) {
    if (!isset($data[$key])) return '';
    
    $status = $data[$key] === 'y' ? 'enabled' : 'disabled';
    $symbol = $status === 'enabled' ? '●' : '○';
    $color = $status === 'enabled' ? '#2ecc71' : '#e74c3c';
    
    return "<span class='item-status' title='{$status}' style='color:{$color}'>$symbol</span>";
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['ztt1'] ?? '多栏目展示系统'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Microsoft YaHei', sans-serif;
        }
        
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: rgba(231, 76, 60, 0.25);
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: rgba(231, 76, 60, 0.25);
            --info-color: #3498db;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }
        
        body {
            overflow: hidden;
            background-color: #0f1721;
            color: white;
            height: 100vh;
            position: relative;
            background: linear-gradient(135deg, #0d1117, #161b22);
        }
        
        #orientation-warning {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: white;
            z-index: 1000;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            backdrop-filter: blur(5px);
        }
        
        #orientation-warning h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: rgba(231, 76, 60, 0.25);
        }
        
        #orientation-warning p {
            font-size: 1rem;
            max-width: 500px;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        #orientation-warning .icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            animation: rotate 2s infinite linear;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(90deg); }
        }
        
        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            transition: background-image 0.5s ease;
            z-index: -1;
            opacity: 0.7;
        }
        
        .container {
            display: grid;
            grid-template-areas:
                "top top top"
                "left center right"
                "bottom bottom bottom";
            grid-template-rows: 60px 1fr 60px;
            grid-template-columns: 60px 1fr 60px;
            height: 100vh;
            padding: 8px;
            gap: 8px;
        }
        
        .top-bar {
            grid-area: top;
            background: rgba(30, 41, 59, 0.05);
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            height: 35px;
            align-items: center;
            padding: 0 10px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.08);
        }
        
        .bottom-bar {
            grid-area: bottom;
            background: rgba(30, 41, 59, 0.05);
            border-radius: 12px;
            display: flex;
            justify-content: space-around;
            height: 35px;
            align-items: center;
            padding: 0 10px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.08);
        }
        
        .left-bar {
            grid-area: left;
            background: rgba(30, 41, 59, 0.05);
            border-radius: 2px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            width: 35px;
            align-items: center;
            padding: 1px 0;
            box-shadow: var(--shadow);
            backdrop-filter: blur(1px);
            border: 1px solid rgba(255,255,255,0.08);
        }
        
        .right-bar {
            grid-area: right;
            background: rgba(30, 41, 59, 0.05);
            border-radius: 2px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            width: 35px;
            align-items: center;
            padding: 1px 0;
            box-shadow: var(--shadow);
            backdrop-filter: blur(1px);
            border: 1px solid rgba(255,255,255,0.08);
        }
        
        .center-area {
            grid-area: center;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            background: rgba(15, 23, 42, 0);
            border: 1px solid rgba(255,255,255,0);
        }
        
        .nav-item {
            width: 30px;
            height: 30px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(51, 65, 85, 0.25), rgba(30, 41, 59, 0.25));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 4px;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: var(--transition);
        }
        
        .nav-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .nav-item:hover::before {
            left: 100%;
        }
        
        .nav-item.menu {
            background: linear-gradient(135deg, var(--accent-color), rgba(231, 76, 60, 0.25));
        }
        
        .nav-item.dialog {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.25), rgba(231, 76, 60, 0.25));
        }
        
        .nav-item i {
            font-size: 1rem;
        }
        
        .nav-item .item-label {
            font-size: 0.5rem;
            margin-top: 2px;
            text-align: center;
        }
        
        .bg-control {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(51, 65, 85, 0.25);
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            font-size: 1.2rem;
            color: white;
            transition: var(--transition);
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .bg-control:hover {
            transform: translateY(-50%) scale(1.1);
            background: rgba(30, 41, 59, 0.95);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }
        
        .bg-control.prev {
            left: 10px;
        }
        
        .bg-control.next {
            right: 10px;
        }
        
        .dialog-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 100;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(8px);
        }
        
        .dialog-content {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            width: 85%;
            max-width: 700px;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
            position: relative;
            animation: scaleIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .dialog-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--primary-color);
        }
        
        .dialog-title {
            font-size: 1.3rem;
            color: var(--light-color);
        }
        
        .close-dialog {
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--accent-color);
            transition: transform 0.3s ease;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .close-dialog:hover {
            transform: rotate(90deg);
            background: rgba(231, 76, 60, 0.1);
        }
        
        .dialog-body {
            font-size: 0.9rem;
            line-height: 1.5;
            color: #bdc3c7;
            max-height: 70vh;
            overflow-y: auto;
            padding: 5px;
        }
        
        .info-panel {
            position: absolute;
            bottom: 75px;
            left: 10px;
            background: rgba(30, 41, 59, 0.9);
            border-radius: 10px;
            padding: 10px;
            max-width: 300px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.1);
            z-index: 5;
        }
        
        .info-panel h3 {
            font-size: 0.9rem;
            margin-bottom: 8px;
            color: var(--primary-color);
        }
        
        .info-panel p {
            font-size: 0.7rem;
            line-height: 1.4;
            margin-bottom: 5px;
        }
        
        .status-indicators {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .status-indicator.enabled {
            background-color: var(--success-color);
        }
        
        .status-indicator.disabled {
            background-color: var(--danger-color);
        }
        
        .item-status {
            font-size: 0.5rem;
            position: absolute;
            bottom: 2px;
            right: 2px;
        }
        
        .iframe-container {
            width: 100%;
            height: 65vh;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .iframe-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* 响应式设计 */
        @media (orientation: portrait) {
            #orientation-warning {
                display: flex;
            }
            .container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- 横屏提示 -->
    <div id="orientation-warning">
        <div class="icon"><i class="fas fa-sync-alt fa-spin"></i></div>
        <h2>请将设备转为横屏模式</h2>
        <p>为了获得最佳体验，请将您的设备旋转至横向模式。</p>
        <p>本系统需要更宽的屏幕空间来展示所有栏目内容。</p>
    </div>
    
    <!-- 背景图片 -->
    <div class="background" id="bgImage"></div>

    
    <!-- 主布局容器 -->
    <div class="container">
        <!-- 顶部栏 -->
        <div class="top-bar">
            <?php if (($data['tc'] ?? 'y') === 'y'): ?>
                <!-- 第一个栏目：打开左侧菜单 -->
                <div class="nav-item menu" onclick="openLeftMenu()">
                    <i class="fas fa-bars"></i>
                    <div class="item-label"><?php echo $data['tc1'] ?? '菜单'; ?></div>
                    <?php echo getItemStatus('tc1', $data); ?>
                </div>
                
                <!-- 中间栏目 -->
                <?php for ($i = 2; $i <= 12; $i++): ?>
                    <?php if (isset($data["tc$i"]) && $data["tc$i"] !== 'n' && $data["tc$i"] !== ''): ?>
                        <div class="nav-item" 
                             onclick="navigateTo('<?php echo $data["tcu$i"] ?? '#'; ?>')">
                            <i class="fas fa-icon"></i>
                            <div class="item-label"><?php echo $data["tc$i"]; ?></div>
                            <?php echo getItemStatus("tc$i", $data); ?>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <!-- 最后一个栏目：打开设置弹窗 -->
                <div class="nav-item dialog" onclick="openDialog()">
                    <i class="fas fa-cog"></i>
                    <div class="item-label"><?php echo $data['tc13'] ?? '设置'; ?></div>
                    <?php echo getItemStatus('tc13', $data); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 左侧栏 -->
        <div class="left-bar">
            <?php if (($data['lc'] ?? 'y') === 'y'): ?>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <?php if (isset($data["lc$i"]) && $data["lc$i"] !== 'n' && $data["lc$i"] !== ''): ?>
                        <div class="nav-item" 
                             onclick="navigateTo('<?php echo $data["lcu$i"] ?? '#'; ?>')">
                            <i class="fas fa-icon"></i>
                            <div class="item-label"><?php echo $data["lc$i"]; ?></div>
                            <?php echo getItemStatus("lc$i", $data); ?>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
        
        <!-- 右侧栏 -->
        <div class="right-bar">
            <?php if (($data['rc'] ?? 'y') === 'y'): ?>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <?php if (isset($data["rc$i"]) && $data["rc$i"] !== 'n' && $data["rc$i"] !== ''): ?>
                        <div class="nav-item" 
                             onclick="navigateTo('<?php echo $data["rcu$i"] ?? '#'; ?>')">
                            <i class="fas fa-icon"></i>
                            <div class="item-label"><?php echo $data["rc$i"]; ?></div>
                            <?php echo getItemStatus("rc$i", $data); ?>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
        
        <!-- 中间区域 - 留空 -->
        <div class="center-area">
            <div class="bg-control prev" onclick="changeBackground(-1)"><i class="fas fa-chevron-left"></i></div>
            <div class="bg-control next" onclick="changeBackground(1)"><i class="fas fa-chevron-right"></i></div>
        </div>
        
        <!-- 底部栏 -->
        <div class="bottom-bar">
            <?php if (($data['bc'] ?? 'y') === 'y'): ?>
                <?php for ($i = 1; $i <= 15; $i++): ?>
                    <?php if (isset($data["bc$i"]) && $data["bc$i"] !== 'n' && $data["bc$i"] !== ''): ?>
                        <div class="nav-item" 
                             onclick="navigateTo('<?php echo $data["bcu$i"] ?? '#'; ?>')">
                            <i class="fas fa-icon"></i>
                            <div class="item-label"><?php echo $data["bc$i"]; ?></div>
                            <?php echo getItemStatus("bc$i", $data); ?>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 弹窗 -->
    <div class="dialog-overlay" id="dialogOverlay">
        <div class="dialog-content">
            <div class="dialog-header">
                <h2 class="dialog-title">系统设置</h2>
                <div class="close-dialog" onclick="closeDialog()"><i class="fas fa-times"></i></div>
            </div>
            <div class="dialog-body">
                <div class="iframe-container">
                    <iframe src="rc.html" id="settingsFrame"></iframe>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 左侧菜单弹窗 -->
    <div class="dialog-overlay" id="leftMenuOverlay">
        <div class="dialog-content" style="width: 80%; max-width: 1000px;">
            <div class="dialog-header">
                <h2 class="dialog-title">左侧菜单</h2>
                <div class="close-dialog" onclick="closeLeftMenu()"><i class="fas fa-times"></i></div>
            </div>
            <div class="dialog-body">
                <div class="iframe-container">
                    <iframe src="lc.html" id="menuFrame"></iframe>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 背景图片数组
        const bgImages = <?php echo json_encode($bgImages); ?>;
        let currentBgIndex = 0;
        
        // 初始化背景
        function initBackground() {
            if (bgImages.length > 0) {
                document.getElementById('bgImage').style.backgroundImage = `url('${bgImages[currentBgIndex]}')`;
            }
        }
        
        // 切换背景
        function changeBackground(direction) {
            if (bgImages.length === 0) return;
            
            currentBgIndex = (currentBgIndex + direction + bgImages.length) % bgImages.length;
            document.getElementById('bgImage').style.backgroundImage = `url('${bgImages[currentBgIndex]}')`;
            
            // 更新左下角信息
            document.querySelector('.info-panel .highlight').textContent = 
                `${currentBgIndex + 1}/${bgImages.length}`;
        }
        
        // 导航到指定URL
        function navigateTo(url) {
            if (url && url !== '#') {
                window.location.href = url;
            }
        }
        
        // 打开设置弹窗
        function openDialog() {
            document.getElementById('dialogOverlay').style.display = 'flex';
        }
        
        // 关闭设置弹窗
        function closeDialog() {
            document.getElementById('dialogOverlay').style.display = 'none';
        }
        
        // 打开左侧菜单
        function openLeftMenu() {
            document.getElementById('leftMenuOverlay').style.display = 'flex';
        }
        
        // 关闭左侧菜单
        function closeLeftMenu() {
            document.getElementById('leftMenuOverlay').style.display = 'none';
        }
        
        // 横屏检测
        function checkOrientation() {
            if (window.innerHeight > window.innerWidth) {
                document.getElementById('orientation-warning').style.display = 'flex';
            } else {
                document.getElementById('orientation-warning').style.display = 'none';
            }
        }
        
        // 初始化
        window.onload = function() {
            initBackground();
            checkOrientation();
            window.addEventListener('resize', checkOrientation);
            window.addEventListener('orientationchange', checkOrientation);
            
            // 添加一些动画效果
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach((item, index) => {
                item.style.animation = `fadeIn 0.5s ease-out ${index * 0.05}s forwards`;
                item.style.opacity = '0';
            });
            
            // 添加样式
            const style = document.createElement('style');
            style.innerHTML = `
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            `;
            document.head.appendChild(style);
        };
    </script>
</body>
</html>