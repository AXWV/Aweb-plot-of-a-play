<?php
// pt.php - 优化版横屏互动故事系统
$p = isset($_GET['p']) ? preg_replace('/[^a-z0-9]/i', '', $_GET['p']) : '1';
$t = isset($_GET['t']) ? preg_replace('/[^a-z0-9]/i', '', $_GET['t']) : '1';
$s = isset($_GET['s']) ? intval($_GET['s']) : 0;
$page = isset($_GET['page']) ? $_GET['page'] : 'main';
$sss = isset($_GET['sss']) ? $_GET['sss'] : '';

// 安全加载JSON数据
function load_json($file) {
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }
    return [];
}

// 加载三方故事源
function load_sss_data($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($data, true) ?: [];
}

$p_data = load_json("P{$p}.json");
$t_data = [];

if ($sss) {
    $t_data = load_sss_data($sss);
} else {
    $t_data = load_json("T{$t}.json");
}

// 设置标题
$title = $p_data['ztt1'] ?? '故事互动系统';

// 计算对话总数
$total_dialogues = isset($t_data['tt']) ? count($t_data['tt']) : 0;
$maxS = $total_dialogues * 2;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Microsoft YaHei', sans-serif;
            touch-action: manipulation;
        }
        
        body {
            background-color: #1a1a2e;
            color: #fff;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
            position: fixed;
        }
        
        .orientation-warning {
            display: flex;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #16213e 0%, #0f3460 100%);
            color: white;
            z-index: 1000;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            padding: 20px;
        }
        
        .orientation-warning h1 {
            font-size: min(6vw, 2.5rem);
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .orientation-warning p {
            font-size: min(4vw, 1.2rem);
            max-width: 80%;
            line-height: 1.6;
        }
        
        .container {
            display: none;
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        /* 主页面样式 */
        .main-screen {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            background: linear-gradient(to bottom, #1a1a2e 0%, #16213e 100%);
            padding: 20px;
            text-align: center;
        }
        
        .main-image {
            width: min(80vw, 300px);
            height: min(80vw, 300px);
            border-radius: 20px;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 5px solid #e94560;
            margin-bottom: 30px;
            animation: float 6s ease-in-out infinite;
        }
        
        .main-text {
            font-size: min(5vw, 2.2rem);
            color: #fff;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 30px;
            max-width: 90%;
            background: rgba(233, 69, 96, 0.2);
            padding: 20px;
            border-radius: 15px;
            line-height: 1.5;
        }
        
        .start-btn {
            background: #e94560;
            color: white;
            border: none;
            padding: 15px 50px;
            font-size: min(4.5vw, 1.5rem);
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(233, 69, 96, 0.5);
            transition: all 0.3s ease;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .start-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(233, 69, 96, 0.7);
            background: #ff5470;
        }
        
        /* 故事页面样式 - 重构布局 */
        .story-screen {
            display: none;
            position: relative;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
        }
        
        /* 角色区域 - 占屏幕高度的2/3 */
        .characters-container {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            height: 100vh; /* 屏幕高度的2/3 */
            gap: 2px;
            padding: 25px;
 