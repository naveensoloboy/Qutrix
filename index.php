<?php
include __DIR__ . '/db.php';

$defaults = [
    'title' => 'QUTRIX 2K26',
    'subtitle' => 'An Intercollegiate Technical Symposium',
    'event_date' => 'Sep 18, 2026 09:00:00',
    'poster_image' => 'images/poster_2k26.jpeg',
    'support_network' => [
        'contact1_role' => 'Registration Committee',
        'contact1_name' => 'Naveen S',
        'contact1_phone' => '+919952655591',
        'contact2_role' => 'Registration Committee',
        'contact2_name' => 'Javakarbharathi K',
        'contact2_phone' => '+916379979364',
        'contact3_role' => 'GAIT Treasurer',
        'contact3_name' => 'Vignesh P',
        'contact3_phone' => '+917010520104',
        'email' => 'qutrix.official@gmail.com'
    ]
];

try {
    $doc = $db->settings->findOne(['_id' => 'event_config']);
    if ($doc) {
        $data = (array)$doc;
        $settings = array_merge($defaults, $data);
        if (isset($data['support_network'])) {
            $settings['support_network'] = array_merge($defaults['support_network'], (array)$data['support_network']);
        }
    } else {
        $settings = $defaults;
    }
} catch (Exception $e) {
    $settings = $defaults;
}

// Parse Title into main name & highlighted tag (e.g. QUTRIX 2K26 -> QUTRIX & 2K26)
$title_full = trim($settings['title'] ?? 'QUTRIX 2K26');
$title_parts = explode(' ', $title_full, 2);
$title_main = $title_parts[0] ?? 'QUTRIX';
$title_highlight = $title_parts[1] ?? '2K26';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title_full) ?> | Gobi Arts & Science College</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=Space+Grotesk:wght@500;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --primary: #020617; 
            --accent: #fbbf24;  
            --accent-glow: rgba(251, 191, 36, 0.3);
            --text-gray: #94a3b8;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --white: #ffffff;
            --skyblu: #71e4e4;
        }

        /* --- GLOBAL OVERFLOW FIX --- */
        html, body {
            overflow-x: hidden;
            width: 100%;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; scroll-behavior: smooth; }
        
        body {
            background-color: var(--primary);
            color: var(--white);
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            line-height: 1.6;
        }

        #systemCanvas {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            opacity: 0.5;
        }

        /* --- Header Branding --- */
        .header-branding {
            background: rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            margin: 20px auto;
            max-width: 1100px;
            width: 90%;
            border-radius: 100px;
            padding: 12px 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            position: relative;
            z-index: 100;
        }

        .clg-logo { height: clamp(35px, 5vw, 50px); filter: brightness(0) invert(1); }
        .v-line { width: 1px; height: 35px; background: rgba(255, 255, 255, 0.2); }

        .clg-text h2 {
            font-family: 'Space Grotesk', sans-serif;
            color: var(--skyblu);
            font-size: clamp(0.9rem, 2.5vw, 1.4rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            white-space: nowrap; 
        }
        .clg-text p { 
            font-size: 0.65rem; 
            font-weight: 700; 
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 2px;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .clg-text {
                flex-shrink: 0;
                text-align: left;
            }
            .header-branding {
                gap: 12px;
                padding: 10px 20px;
                justify-content: center;
            }
            .clg-text h2 {
                font-size: 0.85rem;
            }
            .clg-text p {
                font-size: 0.55rem;
                letter-spacing: 1px;
            }
        }

        /* --- Navigation --- */
        .navbar {
            padding: 0.8rem 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(2, 6, 23, 0.85);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-logo { font-size: 1.6rem; font-weight: 800; color: var(--skyblu); text-decoration: none; }
        .nav-logo span { color: var(--accent); }

        .nav-links { display: flex; list-style: none; gap: 30px; }
        .nav-links a { 
            text-decoration: none; 
            color: #94a3b8; 
            font-weight: 600; 
            font-size: 0.85rem; 
            transition: 0.3s;
            text-transform: uppercase;
        }
        .nav-links a:hover { color: var(--accent); }

        .menu-btn { display: none; color: var(--white); font-size: 1.5rem; cursor: pointer; }

        /* --- Banner --- */
        .organizer-banner { padding: 40px 5%; text-align: center; }
        .organizer-banner h2 { color: var(--accent); font-size: clamp(1.1rem, 5vw, 2.2rem); margin-bottom: 10px; font-family: 'Space Grotesk'; font-weight: 700; }
        .organizer-banner span { color: var(--text-gray); text-transform: uppercase; letter-spacing: 4px; font-size: 0.75rem; }
        .organizer-banner h2 span{ color: var(--skyblu); font-size: clamp(1.1rem, 5vw, 2.2rem);}
        .organizer-banner h4 { color: var(--skyblu); margin-top: 10px; font-size: clamp(0.9rem, 3.5vw, 1.4rem); opacity: 0.9; }

        /* --- Hero Section --- */
        .hero {
            padding: 40px 8% 60px;
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-content h1 {
            color: var(--skyblu);
            font-size: clamp(2.8rem, 7vw, 5rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 25px;
        }
        .hero-content h1 span { 
            background: linear-gradient(to right, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            background: var(--glass);
            border-left: 3px solid var(--accent);
            padding: 20px 25px;
            border-radius: 4px 16px 16px 4px;
            font-size: 1.05rem;
            color: seashell;
            margin-bottom: 35px;
        }

        .btn-register {
            background: var(--accent);
            color: #000;
            padding: 18px 45px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 800;
            display: inline-block;
            transition: 0.3s ease;
            text-transform: uppercase;
            box-shadow: 0 10px 20px var(--accent-glow);
        }
        .btn-register:hover { transform: translateY(-3px); filter: brightness(1.1); }

        .hero-btns {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .btn-how-to-register {
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
            padding: 18px 35px;
            border-radius: 12px;
            font-weight: 800;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9rem;
            text-transform: uppercase;
            border: 1px solid var(--glass-border);
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-how-to-register i {
            color: var(--accent);
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .btn-how-to-register:hover {
            background: rgba(251, 191, 36, 0.1);
            border-color: var(--accent);
            color: var(--accent);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(251, 191, 36, 0.15);
        }

        .btn-how-to-register:hover i {
            transform: scale(1.2);
        }

        /* --- RESPONSIVE VIDEO MODAL --- */
        .video-modal-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(2, 6, 23, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 3500;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        .video-modal-overlay.active {
            display: flex;
        }

        .video-modal-container {
            position: relative;
            width: 100%;
            max-width: 900px;
            background: #020617;
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 25px 20px 20px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.9), 0 0 40px rgba(251, 191, 36, 0.15);
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .video-modal-overlay.active .video-modal-container {
            transform: translateY(0);
        }

        .video-modal-close {
            position: absolute;
            top: -45px;
            right: 0;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            color: var(--white);
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 8px 18px;
            border-radius: 100px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .video-modal-close:hover {
            background: var(--accent);
            color: #000;
            border-color: var(--accent);
        }

        .video-wrapper {
            position: relative;
            width: 100%;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            border-radius: 16px;
            overflow: hidden;
            background: #000;
        }

        .video-wrapper video {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: contain;
            border-radius: 16px;
        }

        .poster-frame {
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.7);
            border: 1px solid var(--glass-border);
        }
        .poster-frame img { width: 100%; display: block; transition: 0.5s; }
        .poster-frame:hover img { transform: scale(1.03); }

        /* --- Card Grid --- */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            padding: 60px 8%;
        }

        .card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            padding: 40px 25px;
            border-radius: 24px;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: 0.3s;
        }
        .card:hover {
            border-color: var(--accent);
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.05);
        }
        .card i {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 20px;
            transition: 0.3s;
        }
        .card:hover i { transform: scale(1.1); }
        .card h4 { font-size: 1.3rem; margin-bottom: 12px; color: var(--skyblu); }

        /* --- Omnitrix System --- */
        .omnitrix-system {
            padding: 60px 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .terminal-header { text-align: center; margin-bottom: 40px; }
        .status-blink { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: green; letter-spacing: 3px; font-weight: 800; animation: blink 1.5s infinite; }
        .terminal-header h2 { color: var(--skyblu); font-family: 'Space Grotesk', sans-serif; font-size: clamp(1.5rem, 5vw, 2.2rem); margin: 10px 0; letter-spacing: 5px; text-transform: uppercase; }

        .omnitrix-main {
            position: relative;
            width: min(85vw, 85vh, 600px);
            height: min(85vw, 85vh, 600px);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .internal-display {
            position: absolute;
            width: 75%; height: 75%;
            background: rgba(2, 6, 23, 0.9);
            border: 2px solid var(--glass-border);
            border-radius: 50%;
            z-index: 5;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            text-align: center; padding: 10%;
            backdrop-filter: blur(15px);
            box-shadow: inset 0 0 50px rgba(0,0,0,0.8), 0 0 30px rgba(251, 191, 36, 0.1);
            overflow: hidden;
        }

        .content-wrap {
            position: relative;
            z-index: 10;
            animation: subtleGlitch 5s infinite;
        }

        .content-wrap h3 { font-family: 'Space Grotesk', sans-serif; color: var(--accent); font-size: clamp(1rem, 4vw, 1.8rem); margin-bottom: 10px; }
        .content-wrap p { font-size: clamp(0.7rem, 2vw, 0.9rem); color: seashell; margin-bottom: 15px; line-height: 1.4; min-height: 3em; }

        .btn-core { padding: 8px 20px; background: var(--accent); color: #000; font-weight: 800; font-size: 0.7rem; text-decoration: none; border-radius: 4px; letter-spacing: 1px; }

        .omnitrix-dial { position: absolute; width: 100%; height: 100%; border-radius: 50%; transition: transform 0.8s cubic-bezier(0.19, 1, 0.22, 1); }

        .event-node { position: absolute; top: 50%; left: 50%; width: clamp(50px, 12vw, 80px); height: clamp(50px, 12vw, 80px); margin-top: clamp(-25px, -6vw, -40px); margin-left: clamp(-25px, -6vw, -40px); cursor: pointer; }
        .node-inner { width: 100%; height: 100%; background: var(--glass); border: 1px solid var(--glass-border); border-radius: 50%; display: flex; justify-content: center; align-items: center; color: var(--text-gray); font-size: clamp(1.2rem, 3vw, 1.8rem); transition: 0.4s ease; }
        .event-node.active .node-inner { background: var(--accent); color: #000; box-shadow: 0 0 30px var(--accent); border-color: var(--accent); transform: scale(1.15); }

        .ring-decoration { position: absolute; width: 100%; height: 100%; border: 1px dashed rgba(251, 191, 36, 0.2); border-radius: 50%; animation: spin-rev 40s linear infinite; }
        
        /* --- SCANNING EFFECT --- */
        .scanner-bar { 
            position: absolute; 
            width: 100%; 
            height: 4px; 
            background: linear-gradient(to right, transparent, var(--accent), transparent); 
            box-shadow: 0 0 15px var(--accent);
            top: 0; 
            z-index: 6;
            opacity: 0.6;
            animation: scanningLine 3s ease-in-out infinite; 
        }

        .radar-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px solid var(--accent);
            border-radius: 50%;
            z-index: -5;
            opacity: 0;
            animation: radarPing 4s linear infinite;
        }

        /* --- Footer --- */
        .footer-main {
            background: rgba(0, 0, 0, 0.6);
            border-top: 1px solid var(--glass-border);
            padding: 80px 3% 2px;
            margin-top: 10px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1.5fr 1fr;
            gap: 40px;
            align-items: stretch;
        }

        .footer-section {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            padding: 15px;
            border-radius: 20px;
            transition: 0.3s ease;
        }

        .footer-section h4 {
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.8rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-section h4::after { content: ''; height: 1px; flex-grow: 1; background: linear-gradient(to right, var(--accent-glow), transparent); }
        .footer-section p { font-size: 0.85rem; color: var(--text-gray); line-height: 1.6; }
        .footer-section strong { color: var(--skyblu); font-size: 1rem; display: block; margin-bottom: 5px; }

        .support-card {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(251, 191, 36, 0.02));
            border: 1px solid rgba(251, 191, 36, 0.3);
            transform: translateY(-40px);
            box-shadow: 0 0 30px rgba(251, 191, 36, 0.1);
        }

        .contact-pill {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 18px;
            border-radius: 15px;
            margin-bottom: 12px;
            display: flex; align-items: center; gap: 15px;
            transition: 0.3s; text-decoration: none;
        }

        .contact-pill:hover { background: rgba(251, 191, 36, 0.1); border-color: var(--accent); transform: translateX(10px); }
        .pill-icon { width: 38px; height: 38px; background: var(--accent); color: #000; border-radius: 10px; display: flex; justify-content: center; align-items: center; font-size: 0.9rem; }
        .pill-text span { font-size: 0.65rem; color: var(--accent); text-transform: uppercase; font-weight: 700; display: block; }
        .pill-text p { font-size: 0.95rem !important; color: var(--skyblu) !important; margin: 0 !important; font-weight: 600; }

        .bottom-bar { text-align: center; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.05); margin-top: 5px; }
        .credit-text { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--text-gray); letter-spacing: 0; line-height: 1.8; }
        .credit-text strong { color: var(--accent); font-weight: 800; }

        .social-icons { display: flex; gap: 12px; margin-top: 15px; }
        .social-icons a { 
            width: 35px; height: 35px; background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 50%; display: flex; justify-content: center; align-items: center; color: var(--white);
            transition: 0.3s; 
        }
        .social-icons a:hover { background: var(--accent); color: #000; }

        /* --- Back to Top --- */
        #backToTop {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--accent);
            color: #000;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: 0.4s;
            box-shadow: 0 0 20px var(--accent-glow);
            border: none;
        }
        #backToTop.show { opacity: 1; visibility: visible; }
        #backToTop:hover { transform: translateY(-5px); }

        /* --- Keyframes --- */
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
        @keyframes spin-rev { from { transform: rotate(360deg); } to { transform: rotate(0deg); } }
        
        @keyframes scanningLine {
            0% { top: -10%; }
            50% { top: 110%; }
            100% { top: -10%; }
        }

        @keyframes radarPing {
            0% { transform: scale(0.6); opacity: 0.8; }
            50% { opacity: 0.4; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        @keyframes subtleGlitch {
            0%, 90%, 100% { transform: translate(0); }
            92% { transform: translate(1px, -1px); }
            94% { transform: translate(-1px, 1px); }
        }

        /* --- Responsive --- */
        @media (max-width: 968px) {
            .navbar { padding: 1rem 5%; }
            .menu-btn { display: block; z-index: 1001; }
            .nav-links {
                display: none; flex-direction: column; position: absolute;
                top: 100%; left: 0; width: 100%; background: rgba(2, 6, 23, 0.95);
                backdrop-filter: blur(20px); padding: 40px; gap: 25px; text-align: center; border-bottom: 2px solid var(--accent);
            }
            .nav-links.active { display: flex; }
            .hero { grid-template-columns: 1fr; text-align: center; }
            .hero-desc { border-left: none; border-top: 3px solid var(--accent); }
            .footer-container { grid-template-columns: 1fr; text-align: center; }
            .support-card { transform: translateY(0); margin-bottom: 40px; }
            .contact-pill { justify-content: center; }
            .social-icons { justify-content: center; }
        }

        .reveal {
            opacity: 0;
            filter: blur(10px);
            transition: all 0.8s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: transform, opacity;
        }
        .reveal-left { transform: translateX(-80px); }
        .reveal-right { transform: translateX(80px); }
        .reveal-bottom { transform: translateY(60px); }

        .reveal.active {
            opacity: 1;
            filter: blur(0);
            transform: translateX(0) translateY(0);
        }

        @media (max-width: 768px) {
            .reveal-left { transform: translateX(-30px); }
            .reveal-right { transform: translateX(30px); }
            .omnitrix-main {
                transform: scale(0.85);
                transform-origin: center;
            }
            .hero h1 { font-size: 2.5rem; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        .poster-frame img {
            animation: float 5s ease-in-out infinite;
        }

        .delay-1 { transition-delay: 0.1s; }
        .delay-2 { transition-delay: 0.2s; }
        .delay-3 { transition-delay: 0.3s; }

        html { scroll-behavior: smooth; }

        /* --- HUD CIRCULAR TIMER --- */
        .timer-section {
            padding: 20px 2%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: clamp(8px, 2vw, 20px);
            flex-wrap: nowrap;
            margin-top: -40px;
            position: relative;
            z-index: 100;
        }

        .hud-timer {
            position: relative;
            width: clamp(65px, 18vw, 110px);
            height: clamp(65px, 18vw, 110px);
            background: radial-gradient(circle, rgba(251, 191, 36, 0.05) 0%, transparent 70%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .hud-timer::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            padding: 2px;
            background: conic-gradient(from 0deg, var(--accent) var(--p, 0%), transparent 0%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            filter: drop-shadow(0 0 5px var(--accent));
        }

        .hud-timer h2 {
            font-family: 'JetBrains Mono', monospace;
            font-size: clamp(1rem, 3.5vw, 1.6rem);
            color: var(--skyblu);
            margin-bottom: -2px;
        }

        .hud-timer span {
            font-size: clamp(0.45rem, 1.2vw, 0.6rem);
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent);
            font-weight: 800;
        }

        @media (max-width: 480px) {
            .timer-section { 
                margin-top: 10px; 
                padding: 10px 1%;
            }
        }
    </style>
</head>
<body>

    <canvas id="systemCanvas"></canvas>

    <header class="header-branding reveal reveal-bottom">
        <img class="clg-logo" src="images/College_logo.png" alt="GASC Logo">
        <div class="v-line"></div>
        <div class="clg-text">
            <h2>GOBI ARTS & SCIENCE COLLEGE</h2>
            <p>Karattadipalayam, Gobichettipalayam</p>
        </div>
    </header>

    <nav class="navbar reveal reveal-bottom">
        <a href="Admin_page/admin_login_form.html" class="nav-logo" id="navLogo"><?= htmlspecialchars($title_main) ?><span><?= htmlspecialchars($title_highlight) ?></span></a>
        <div class="menu-btn" id="menu-toggle"><i class="fas fa-bars"></i></div>
        <ul class="nav-links" id="nav-list">
            <li><a href="#home">Home</a></li>
            <li><a href="index_about.html">About</a></li>
            <li><a href="gallery.html">Gallery</a></li>
            <li><a href="card.html">Events</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </nav>

    <div class="organizer-banner reveal reveal-left">
        <h2 id="bannerTitle"><?= htmlspecialchars($title_main) ?> <span id="bannerYear"><?= htmlspecialchars($title_highlight) ?></span></h2>
        <span style="font-size: 0.55rem;letter-spacing: 2px;" id="bannerSubtitle"><?= htmlspecialchars($settings['subtitle']) ?></span> <br>
        <span>Organized By</span>
        <h2>GAIT : GobiArts Association of Information Technology</h2>
        <h4>PG & RESEARCH DEPARTMENT OF COMPUTER SCIENCE</h4>
    </div>

    <main class="hero" id="home">
        <div class="hero-content reveal reveal-left">
            <h1>Digital <br><span>Extravaganza.</span></h1>
            <div class="hero-desc">
                Empowering the next generation of computer scientists through innovation and excellence.
            </div>
            <div class="hero-btns">
                <a href="card.html" class="btn-register">Register <i class="fas fa-arrow-right"></i></a>
                <button onclick="openVideoModal()" class="btn-how-to-register">
                    <i class="fas fa-play-circle"></i> How to Register
                </button>
            </div>
        </div>
        <div class="poster-frame reveal reveal-right">
            <img id="eventPosterImg" src="<?= htmlspecialchars($settings['poster_image']) ?>" alt="Event Poster" style="max-width: 100%; border-radius: 15px;" onerror="this.src='images/poster_2k26.jpeg'">
        </div>
    </main>

    <!-- VIDEO MODAL OVERLAY -->
    <div class="video-modal-overlay" id="videoModalOverlay">
        <div class="video-modal-container">
            <button class="video-modal-close" onclick="closeVideoModal()"><i class="fas fa-times"></i> Close</button>
            <div class="video-wrapper">
                <video id="demoVideo" controls preload="metadata">
                    <source src="images/Demo_Video.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
    </div>

    <section class="timer-section reveal reveal-bottom">
        <div class="hud-timer" id="days-hud" style="--p: 100%;">
            <h2 id="days">00</h2>
            <span>Days</span>
        </div>
        <span>:</span>
        <div class="hud-timer" id="hours-hud" style="--p: 100%;">
            <h2 id="hours">00</h2>
            <span>Hours</span>
        </div>
        <span>:</span>
        <div class="hud-timer" id="mins-hud" style="--p: 100%;">
            <h2 id="minutes">00</h2>
            <span>Mins</span>
        </div>
        <span>:</span>
        <div class="hud-timer" id="secs-hud" style="--p: 100%;">
            <h2 id="seconds">00</h2>
            <span>Secs</span>
        </div>
    </section>

    <section class="card-grid">
        <div class="card reveal reveal-bottom delay-1">
            <i class="fas fa-check-circle"></i>
            <h4>Zero Entry Fee</h4>
            <p style="color: seashell; font-size: 0.85rem;">No registration costs, just pure talent.</p>
        </div>
        <div class="card reveal reveal-bottom delay-2">
            <i class="fas fa-utensils"></i>
            <h4>Free Meals</h4>
            <p style="color: seashell; font-size: 0.85rem;">Delicious lunch for every participant.</p>
        </div>
        <div class="card reveal reveal-bottom delay-3">
            <i class="fas fa-medal"></i>
            <h4>Official Rewards</h4>
            <p style="color: seashell; font-size: 0.85rem;">Certificates and exciting prizes.</p>
        </div>
    </section>

    <section class="omnitrix-system reveal reveal-bottom" id="events">
        <div class="terminal-header">
            <span class="status-blink">● EVENT SYSTEM LIVE</span>
            <h2>EVENT DIRECTORY</h2>
        </div>

        <div class="omnitrix-main">
            <div class="radar-pulse"></div>
            <div class="ring-decoration"></div>
            <div class="internal-display">
                <div class="scanner-bar"></div>
                <div class="content-wrap">
                    <h3 id="activeName">Paper Presentation</h3>
                    <p id="activeDesc">Showcase your research and innovation through professional presentations.</p>
                    <a href="card.html#paperpresentation" class="btn-core">Explore</a>
                </div>
            </div>

            <div class="omnitrix-dial" id="eventDial">
                <div class="event-node active" data-name="Paper Presentation" data-desc="Present your technical research and innovative ideas." data-link="card.html#paperpresentation"><div class="node-inner"><i class="fas fa-file-powerpoint"></i></div></div>
                <div class="event-node" data-name="Web Design" data-desc="Show off your UI/UX skills by creating a stunning landing page." data-link="card.html#webdesign"><div class="node-inner"><i class="fas fa-window-restore"></i></div></div>
                <div class="event-node" data-name="Word Hunt" data-desc="A speed-based vocabulary challenge for technical terms." data-link="card.html#wordhunt"><div class="node-inner"><i class="fas fa-font"></i></div></div>
                <div class="event-node" data-name="Tech Quiz" data-desc="The ultimate battle of wits covering tech trends." data-link="card.html#quiz"><div class="node-inner"><i class="fas fa-brain"></i></div></div>
                <div class="event-node" data-name="Software Contest" data-desc="Solve problems by building real-world software." data-link="card.html#softwarecontest"><div class="node-inner"><i class="fas fa-laptop-code"></i></div></div>
                <div class="event-node" data-name="Tech Marketing" data-desc="Pitch and brand your product to win over judges." data-link="card.html#marketing"><div class="node-inner"><i class="fas fa-bullhorn"></i></div></div>
                <div class="event-node" data-name="Dance" data-desc="Light up the stage with your energy and performance." data-link="card.html#dance"><div class="node-inner"><i class="fas fa-skating"></i></div></div>
            </div>
        </div>
    </section>

    <footer class="footer-main reveal reveal-bottom" id="contact">
        <div class="footer-container">
            <div class="footer-section">
                <h4><i class="fas fa-university"></i> Institution</h4>
                <p><strong>Gobi Arts & Science College</strong></p>
                <p>(Autonomous)</p>
                <p>Karattadipalayam, Erode - 638 453</p>
                <div class="social-icons" style="margin-top: 15px;">
                    <a href="https://www.facebook.com/share/16hiMYKC1q/" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/gobiartsandsciencecollege" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://x.com/GASCgobi36" target="_blank"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            <div class="footer-section support-card">
                <h4>Support Network</h4>
                <a href="tel:<?= htmlspecialchars($settings['support_network']['contact1_phone']) ?>" class="contact-pill" id="pillContact1">
                    <div class="pill-icon"><i class="fas fa-phone"></i></div>
                    <div class="pill-text"><span><?= htmlspecialchars($settings['support_network']['contact1_role']) ?></span><p><?= htmlspecialchars($settings['support_network']['contact1_name']) ?></p></div>
                </a>
                <a href="tel:<?= htmlspecialchars($settings['support_network']['contact2_phone']) ?>" class="contact-pill" id="pillContact2">
                    <div class="pill-icon"><i class="fas fa-phone"></i></div>
                    <div class="pill-text"><span><?= htmlspecialchars($settings['support_network']['contact2_role']) ?></span><p><?= htmlspecialchars($settings['support_network']['contact2_name']) ?></p></div>
                </a>
                <a href="tel:<?= htmlspecialchars($settings['support_network']['contact3_phone']) ?>" class="contact-pill" id="pillContact3">
                    <div class="pill-icon"><i class="fas fa-wallet"></i></div>
                    <div class="pill-text"><span><?= htmlspecialchars($settings['support_network']['contact3_role']) ?></span><p><?= htmlspecialchars($settings['support_network']['contact3_name']) ?></p></div>
                </a>
                <a href="mailto:<?= htmlspecialchars($settings['support_network']['email']) ?>" class="contact-pill" id="pillEmail">
                    <div class="pill-icon"><i class="fas fa-envelope"></i></div>
                    <div class="pill-text"><span>Official Inquiry</span><p><?= htmlspecialchars($settings['support_network']['email']) ?></p></div>
                </a>
            </div>

            <div class="footer-section">
                <h4><i class="fas fa-user-tie"></i> Symposium Chair</h4>
                <p><strong>Dr. P. Prabhusundhar</strong></p>
                <p>Assistant Professor & GAIT Coordinator</p>
                <p>PG & Research Dept. of CS</p>
                <p style="margin-top: 15px; padding: 8px; border-radius: 8px; background: rgba(251, 191, 36, 0.1); color: #fbbf24; font-size: 0.7rem; font-weight: 700; text-align: center;">
                    <i class="fas fa-shield-alt"></i> AUTHORIZED GAIT COORDINATOR
                </p>
            </div>
        </div>
        <div class="bottom-bar">
            <div class="credit-text">
                <p style="margin-bottom: 8px;">&copy; <?= date('Y') ?> QUTRIX Official. All Rights Reserved.</p>
                <p>Designed by <strong>Mr. S. Naveen (22CS048)</strong><br>PG & Research Department of Computer Science</p>
            </div>
        </div>
    </footer>

    <button id="backToTop"><i class="fas fa-chevron-up"></i></button>

    <script>
        // --- HUD TIMER LOGIC ---
        function startHudTimer() {
            const eventTargetStr = <?= json_encode($settings['event_date']) ?>;
            const eventDate = new Date(eventTargetStr).getTime();

            setInterval(() => {
                const now = new Date().getTime();
                const diff = eventDate - now;

                if (diff <= 0) {
                    document.getElementById("days").innerText = "00";
                    document.getElementById("hours").innerText = "00";
                    document.getElementById("minutes").innerText = "00";
                    document.getElementById("seconds").innerText = "00";
                    return;
                }

                const d = Math.floor(diff / (1000 * 60 * 60 * 24));
                const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);

                document.getElementById("days").innerText = d.toString().padStart(2, '0');
                document.getElementById("hours").innerText = h.toString().padStart(2, '0');
                document.getElementById("minutes").innerText = m.toString().padStart(2, '0');
                document.getElementById("seconds").innerText = s.toString().padStart(2, '0');

                // Update Circular Progress
                document.getElementById("days-hud").style.setProperty('--p', Math.min(100, (d / 30) * 100) + '%');
                document.getElementById("hours-hud").style.setProperty('--p', (h / 24) * 100 + '%');
                document.getElementById("mins-hud").style.setProperty('--p', (m / 60) * 100 + '%');
                document.getElementById("secs-hud").style.setProperty('--p', (s / 60) * 100 + '%');

            }, 1000);
        }
        startHudTimer();

        // --- 1. REPEATABLE SCROLL REVEAL ---
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                } else {
                    entry.target.classList.remove('active');
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

        // --- 2. CANVAS ---
        const canvas = document.getElementById('systemCanvas');
        const ctx = canvas.getContext('2d');
        let points = [];
        function initCanvas() {
            canvas.width = window.innerWidth; canvas.height = window.innerHeight;
            points = [];
            for (let i = 0; i < 50; i++) {
                points.push({ x: Math.random() * canvas.width, y: Math.random() * canvas.height, vx: (Math.random() - 0.5) * 0.4, vy: (Math.random() - 0.5) * 0.4 });
            }
        }
        function drawCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = "rgba(251, 191, 36, 0.3)"; ctx.strokeStyle = "rgba(255, 255, 255, 0.03)";
            points.forEach((p, i) => {
                p.x += p.vx; p.y += p.vy;
                if (p.x < 0 || p.x > canvas.width) p.vx *= -1; if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
                ctx.beginPath(); ctx.arc(p.x, p.y, 1.2, 0, Math.PI * 2); ctx.fill();
                for (let j = i + 1; j < points.length; j++) {
                    const d = Math.hypot(p.x - points[j].x, p.y - points[j].y);
                    if (d < 180) { ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(points[j].x, points[j].y); ctx.stroke(); }
                }
            });
            requestAnimationFrame(drawCanvas);
        }
        initCanvas(); drawCanvas(); window.onresize = initCanvas;

        // --- 3. AUTO-LEVELING OMNITRIX ---
        const dial = document.getElementById('eventDial');
        const nodes = document.querySelectorAll('.event-node');
        const nameOut = document.getElementById('activeName');
        const descOut = document.getElementById('activeDesc');
        let currentDialRotation = 0;

        function typeWriter(text, element) {
            element.innerHTML = ""; let i = 0;
            const type = () => { if (i < text.length) { element.innerHTML += text.charAt(i); i++; setTimeout(type, 30); } };
            type();
        }

        function arrangeNodes() {
            const container = document.querySelector('.omnitrix-main');
            const exploreBtn = document.querySelector('.btn-core');
            const radius = container.offsetWidth / 2; 
            const angleStep = 360 / nodes.length;

            nodes.forEach((node, i) => {
                const nodeAngle = i * angleStep;
                node.style.transform = `
                    rotate(${nodeAngle}deg) 
                    translateY(-${radius}px) 
                    rotate(${-nodeAngle - currentDialRotation}deg)
                `;

                node.onclick = () => {
                    currentDialRotation = -(i * angleStep);
                    dial.style.transform = `rotate(${currentDialRotation}deg)`;

                    nodes.forEach((n, idx) => {
                        const nAngle = idx * angleStep;
                        n.style.transform = `
                            rotate(${nAngle}deg) 
                            translateY(-${radius}px) 
                            rotate(${-nAngle - currentDialRotation}deg)
                        `;
                        n.classList.remove('active');
                    });

                    node.classList.add('active');
                    nameOut.textContent = node.dataset.name;
                    exploreBtn.setAttribute('href', node.dataset.link);
                    typeWriter(node.dataset.desc, descOut);
                };
            });
        }

        window.addEventListener('load', () => {
            setTimeout(arrangeNodes, 100);
        });
        window.addEventListener('resize', arrangeNodes);

        // --- 4. NAV & TOP ---
        const menuToggle = document.getElementById('menu-toggle');
        const navList = document.getElementById('nav-list');
        menuToggle.onclick = () => { navList.classList.toggle('active'); menuToggle.querySelector('i').classList.toggle('fa-times'); };

        const btt = document.getElementById('backToTop');
        window.onscroll = () => { btt.classList.toggle('show', window.scrollY > 400); };
        btt.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });

        // --- 5. HOW TO REGISTER VIDEO MODAL ---
        function openVideoModal() {
            const overlay = document.getElementById('videoModalOverlay');
            const video = document.getElementById('demoVideo');
            if (overlay) {
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                if (video) {
                    video.currentTime = 0;
                    video.play().catch(e => console.log('Autoplay prevented:', e));
                }
            }
        }

        function closeVideoModal() {
            const overlay = document.getElementById('videoModalOverlay');
            const video = document.getElementById('demoVideo');
            if (overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = 'auto';
                if (video) {
                    video.pause();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const overlay = document.getElementById('videoModalOverlay');
            if (overlay) {
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) {
                        closeVideoModal();
                    }
                });
            }
        });

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeVideoModal();
            }
        });
    </script>
</body>
</html>
