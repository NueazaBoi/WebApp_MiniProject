<?php
// ============================================================
//   CarVerse 3D | Combined 3D Experience & Homepage
//   index.php  (Exploded Showroom Edition)
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

// รถยนต์แนะนำจากฐานข้อมูล
$featured = $pdo->query("
    SELECT c.*, b.brand_name, t.type_name
    FROM   cars c
    JOIN   brands b    ON c.brand_id = b.brand_id
    JOIN   car_types t ON c.type_id  = t.type_id
    WHERE  c.status = 'available'
    ORDER  BY c.created_at DESC
    LIMIT  8
")->fetchAll();

// สถิติระบบ
$stats = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM cars)    AS total_cars,
        (SELECT COUNT(*) FROM brands) AS total_brands,
        (SELECT COUNT(*) FROM users WHERE role='user') AS total_members,
        (SELECT COUNT(*) FROM test_drive_bookings) AS total_bookings
")->fetch();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="theme-color" content="#02050c">
  <title>CARVERSE — The Art of Motion | CarVerse 3D</title>
  <script>document.documentElement.className+=' js'</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;800&family=Barlow:wght@400;500;600&family=Prompt:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/home.css">
  <style>
    :root{
      --cy:#1fc8ff; --bl:#2f6bff; --tx:#e9f7ff; --mu:#8aa3b2; --bg:#02050c;
      --line:rgba(31,200,255,.22); --glass:rgba(8,16,28,.55);
      --disp:'Barlow Condensed','Prompt',sans-serif;
      --body:Barlow,'Prompt',system-ui,sans-serif;
    }
    *{box-sizing:border-box;margin:0}
    html{background:#000}
    body{background:#000;color:var(--tx);font:400 16px/1.55 var(--body);height:600vh;overflow-x:hidden;-webkit-font-smoothing:antialiased}
    #c{position:fixed;inset:0;width:100%;height:100%;display:block;z-index:1}
    a{color:inherit}
    :focus-visible{outline:2px solid var(--cy);outline-offset:3px}

    /* ---------- NAV ---------- */
    .cv-nav{position:fixed;top:14px;left:50%;transform:translateX(-50%);width:min(1180px,95vw);display:flex;align-items:center;gap:18px;padding:9px 10px 9px 22px;border:1px solid var(--line);border-radius:999px;background:linear-gradient(180deg,rgba(16,32,52,.55),rgba(6,12,22,.55));-webkit-backdrop-filter:blur(18px) saturate(1.4);backdrop-filter:blur(18px) saturate(1.4);box-shadow:0 10px 40px rgba(0,0,0,.5),inset 0 1px 0 rgba(255,255,255,.07);z-index:40}
    .cv-logo{display:flex;align-items:center;gap:10px;font:800 20px var(--disp);letter-spacing:.14em;text-decoration:none;color:var(--tx);white-space:nowrap}
    .cv-logo i{width:22px;height:12px;border-radius:3px;background:linear-gradient(90deg,#7a4dff,#1fc8ff);box-shadow:0 0 14px rgba(47,107,255,.8)}
    .cv-logo small{font-size:.62em;color:var(--cy);letter-spacing:.2em}
    .cv-links{display:flex;gap:26px;list-style:none;margin:0 auto;padding:0;align-items:center}
    .cv-links a{color:var(--mu);font-size:14px;cursor:pointer;text-decoration:none;transition:color .2s;position:relative;padding:4px 0}
    .cv-links a::after{content:'';position:absolute;left:0;right:100%;bottom:-2px;height:1px;background:var(--cy);box-shadow:0 0 8px var(--cy);transition:right .25s}
    .cv-links a:hover,.cv-links a:focus-visible{color:var(--tx)}
    .cv-links a:hover::after{right:0}
    .cv-right{display:flex;align-items:center;gap:10px}
    .cv-cta{display:inline-block;padding:11px 22px;border-radius:999px;border:0;background:linear-gradient(135deg,#2f6bff,#1fc8ff);color:#001018;font:800 13px var(--disp);letter-spacing:.16em;text-decoration:none;cursor:pointer;box-shadow:0 0 26px rgba(47,107,255,.55);transition:transform .2s,box-shadow .2s}
    .cv-cta:hover{transform:translateY(-1px);box-shadow:0 0 38px rgba(31,200,255,.8)}
    .cv-acc{position:relative}
    .cv-acc>button{font:600 13px var(--body);color:var(--tx);background:rgba(31,200,255,.08);border:1px solid var(--line);border-radius:999px;padding:9px 16px;cursor:pointer}
    .cv-dd{position:absolute;right:0;top:calc(100% + 10px);min-width:190px;padding:8px;border:1px solid var(--line);border-radius:16px;background:rgba(6,12,22,.92);-webkit-backdrop-filter:blur(18px);backdrop-filter:blur(18px);opacity:0;pointer-events:none;transform:translateY(-6px);transition:.2s}
    .cv-acc:hover .cv-dd,.cv-acc:focus-within .cv-dd{opacity:1;pointer-events:auto;transform:none}
    .cv-dd a{display:block;padding:9px 12px;border-radius:10px;color:var(--tx);text-decoration:none;font-size:14px}
    .cv-dd a:hover{background:rgba(31,200,255,.12)}
    .cv-dd a.out{color:#ff7a3a}
    .cv-burger{display:none;width:42px;height:42px;border-radius:50%;border:1px solid var(--line);background:rgba(31,200,255,.08);cursor:pointer;position:relative}
    .cv-burger::before,.cv-burger::after{content:'';position:absolute;left:12px;right:12px;height:2px;background:var(--tx);transition:.25s}
    .cv-burger::before{top:16px}.cv-burger::after{top:24px}
    #sheet{position:fixed;inset:0;z-index:39;background:rgba(2,5,12,.94);-webkit-backdrop-filter:blur(14px);backdrop-filter:blur(14px);display:flex;flex-direction:column;justify-content:center;gap:6px;padding:90px 8vw 40px;opacity:0;pointer-events:none;transition:opacity .3s}
    #sheet.open{opacity:1;pointer-events:auto}
    #sheet a{font:800 34px var(--disp);text-transform:uppercase;letter-spacing:.04em;text-decoration:none;color:var(--tx);padding:6px 0;border-bottom:1px solid rgba(31,200,255,.12)}
    #sheet a.out{color:#ff7a3a}

    /* ---------- STAGE UI ---------- */
    #prog{position:fixed;left:0;top:0;height:2px;width:0;background:linear-gradient(90deg,#2f6bff,#1fc8ff);box-shadow:0 0 12px var(--cy);z-index:50}
    #dots{position:fixed;right:22px;top:50%;transform:translateY(-50%);z-index:12;display:flex;flex-direction:column;gap:14px}
    #dots button{position:relative;width:12px;height:12px;border-radius:50%;border:1.5px solid rgba(233,247,255,.4);background:transparent;cursor:pointer;padding:0;transition:.25s}
    #dots button.on{background:var(--cy);border-color:var(--cy);box-shadow:0 0 14px var(--cy);transform:scale(1.25)}
    #dots button span{position:absolute;right:24px;top:50%;transform:translateY(-50%);font:600 12px var(--body);letter-spacing:.12em;text-transform:uppercase;color:var(--tx);opacity:0;pointer-events:none;white-space:nowrap;transition:.2s}
    #dots button:hover span,#dots button:focus-visible span{opacity:1}
    #hud{position:fixed;left:26px;bottom:24px;z-index:12;font:500 11px/1.7 ui-monospace,Menlo,Consolas,monospace;letter-spacing:.14em;color:rgba(138,163,178,.9);text-transform:uppercase;pointer-events:none}
    #hud b{color:var(--cy);font-weight:500}
    #hint{position:fixed;left:50%;bottom:22px;transform:translateX(-50%);z-index:12;display:flex;flex-direction:column;align-items:center;gap:8px;font:600 11px var(--body);letter-spacing:.3em;color:var(--mu);pointer-events:none}
    #hint i{width:1px;height:42px;background:linear-gradient(var(--cy),transparent);animation:hint 1.8s infinite}
    @keyframes hint{0%{transform:scaleY(0);transform-origin:top}50%{transform:scaleY(1);transform-origin:top}51%{transform-origin:bottom}100%{transform:scaleY(0);transform-origin:bottom}}

    /* ---------- SCENE TEXT ---------- */
    .ov{position:fixed;left:6vw;top:50%;width:min(580px,88vw);opacity:0;pointer-events:none;z-index:2;transform:translateY(-50%)}
    .ov.r{left:auto;right:6vw;text-align:right}
    .ov.low{top:auto;bottom:11vh;transform:none}
    .ov.top{top:22%;transform:none}
    .ov-in{transform:translateY(calc(var(--d,0)*46px));filter:blur(calc(var(--b,0)*8px))}
    .ov h1,.ov h2{font:800 clamp(44px,8.4vw,128px)/.86 var(--disp);text-transform:uppercase;letter-spacing:-.005em;text-shadow:0 6px 40px rgba(0,0,0,.65)}
    .ov h2{font-size:clamp(38px,6.4vw,96px)}
    .ov h1 b,.ov h2 b{font-weight:800;background:linear-gradient(100deg,#1fc8ff,#6aa0ff 60%,#c2dcff);-webkit-background-clip:text;background-clip:text;color:transparent;filter:drop-shadow(0 0 22px rgba(31,200,255,.55))}
    .ov p{margin-top:22px;color:#a9bfcc;max-width:36ch;font-size:clamp(14px,1.25vw,17px)}
    .ov.r p{margin-left:auto}
    .ov .th{display:block;margin-top:8px;font-size:.88em;color:var(--mu)}
    .cv-btn{margin-top:28px;display:inline-block;padding:15px 30px;border:0;border-radius:999px;background:linear-gradient(135deg,#2f6bff,#1fc8ff);color:#001018;font:800 15px var(--disp);letter-spacing:.16em;cursor:pointer;pointer-events:auto;text-decoration:none;box-shadow:0 0 30px rgba(47,107,255,.55);transition:transform .2s,box-shadow .2s}
    .cv-btn:hover{transform:translateY(-2px);box-shadow:0 0 46px rgba(31,200,255,.85)}
    .ov dl{display:grid;gap:18px;margin-top:26px}
    .ov dt{font:800 clamp(38px,5.4vw,70px)/1 var(--disp);background:linear-gradient(100deg,#fff,#1fc8ff);-webkit-background-clip:text;background-clip:text;color:transparent}
    .ov dd{color:var(--mu);font-size:14px;margin:0}

    /* swatches */
    #sw{position:fixed;bottom:34px;left:50%;transform:translateX(-50%);z-index:4;display:flex;align-items:center;gap:20px;padding:10px 20px;border:1px solid var(--line);border-radius:999px;background:var(--glass);-webkit-backdrop-filter:blur(14px);backdrop-filter:blur(14px);opacity:0;pointer-events:none}
    #sws{display:flex;gap:12px}
    #sws button{width:34px;height:34px;border-radius:50%;border:2px solid rgba(255,255,255,.25);cursor:pointer;transition:.2s}
    #sws button:hover{transform:scale(1.12)}
    #sws button.on{border-color:var(--cy);box-shadow:0 0 16px var(--cy)}
    #cn{font:600 13px var(--body);letter-spacing:.1em;color:var(--tx);min-width:96px}

    /* hotspots */
    .hs{position:fixed;left:0;top:0;width:0;height:0;opacity:0;pointer-events:none;z-index:3;transition:opacity .45s}
    .hs.on{opacity:1}
    .hs i{position:absolute;left:-10px;top:-10px;width:20px;height:20px;border-radius:50%;border:1.5px solid var(--cy);background:rgba(31,200,255,.22);box-shadow:0 0 16px var(--cy);cursor:pointer;pointer-events:none}
    .hs.on i{pointer-events:auto}
    .hs i::after{content:'';position:absolute;inset:-7px;border-radius:50%;border:1px solid var(--cy);animation:ping 2s infinite}
    @keyframes ping{0%{transform:scale(.6);opacity:.9}100%{transform:scale(1.6);opacity:0}}
    .hs span{position:absolute;left:28px;top:-12px;white-space:nowrap;padding:6px 11px;background:rgba(4,10,18,.78);border:1px solid rgba(31,200,255,.4);border-radius:8px;font:500 11px ui-monospace,Menlo,Consolas,monospace;letter-spacing:.06em;color:var(--tx);-webkit-backdrop-filter:blur(8px);backdrop-filter:blur(8px)}

    /* loader */
    #ld{position:fixed;inset:0;background:radial-gradient(circle at 50% 40%,#06142a,#000 70%);z-index:60;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:18px;transition:opacity .9s}
    #ld .t{font:800 24px var(--disp);letter-spacing:.38em;padding-left:.38em}
    #bar{width:min(340px,70vw);height:3px;background:#0d1b2a;border-radius:3px;overflow:hidden}
    #bar i{display:block;height:100%;width:0;background:linear-gradient(90deg,#2f6bff,#1fc8ff);box-shadow:0 0 14px var(--cy)}
    #ld small{color:var(--mu);letter-spacing:.2em;font-size:12px}
    #pick{display:none;color:var(--mu);font-size:14px;text-align:center;max-width:80vw}

    /* ---------- CONTENT BELOW THE SCENE ---------- */
    .cv-content{position:absolute;top:600vh;left:0;width:100%;background:var(--bg);z-index:25}
    .cv-content::before{content:'';position:absolute;left:0;right:0;top:-180px;height:180px;background:linear-gradient(to bottom,transparent,var(--bg));pointer-events:none}
    .cv-wrap{width:min(1280px,94vw);margin:0 auto}
    .cv-stats{padding:70px 0 20px;border-bottom:1px solid rgba(31,200,255,.1)}
    .cv-stats .cv-wrap{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
    .cv-stat{text-align:center;padding:10px}
    .cv-stat b{display:block;font:800 clamp(44px,6vw,84px)/1 var(--disp);background:linear-gradient(100deg,#fff,#1fc8ff 70%,#2f6bff);-webkit-background-clip:text;background-clip:text;color:transparent}
    .cv-stat span{color:var(--mu);font-size:14px}
    .cv-sec{padding:90px 0 100px}
    .cv-head{text-align:center;margin-bottom:54px}
    .cv-head h3{font:800 clamp(34px,5vw,64px)/1 var(--disp);text-transform:uppercase;color:#fff}
    .cv-head h3 b{color:var(--cy);font-weight:800}
    .cv-head p{color:var(--mu);margin:14px auto 0;max-width:52ch}
    
    /* ปรับปรุง Grid ให้แสดงผล 4 คันต่อแถว บนหน้าจอคอมพิวเตอร์ */
    .cv-grid{display:grid;grid-template-columns:repeat(1,1fr);gap:24px}
    @media(min-width:580px){.cv-grid{grid-template-columns:repeat(2,1fr)}}
    @media(min-width:980px){.cv-grid{grid-template-columns:repeat(3,1fr)}}
    @media(min-width:1200px){.cv-grid{grid-template-columns:repeat(4,1fr)}}

    .cv-card{position:relative;display:block;text-decoration:none;color:inherit;border-radius:20px;overflow:hidden;background:linear-gradient(180deg,rgba(14,28,46,.75),rgba(6,12,22,.9));border:1px solid rgba(31,200,255,.18);transform:perspective(900px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg));transition:transform .25s ease-out,border-color .3s,box-shadow .3s}
    .cv-card:hover{border-color:rgba(31,200,255,.6);box-shadow:0 20px 70px rgba(31,107,255,.28),0 0 0 1px rgba(31,200,255,.2)}
    .cv-pic{position:relative;height:190px;overflow:hidden;background:radial-gradient(circle at 50% 120%,#0b2a5a,#02050c 70%)}
    .cv-pic img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .8s cubic-bezier(.2,.7,.2,1)}
    .cv-card:hover .cv-pic img{transform:scale(1.08)}
    .cv-pic::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,transparent 55%,rgba(2,5,12,.85))}
    .cv-noimg{height:100%;display:flex;align-items:center;justify-content:center;color:var(--mu);font-size:14px}
    .cv-badge{position:absolute;left:14px;top:14px;z-index:2;padding:5px 12px;border-radius:999px;font:600 12px var(--body);letter-spacing:.06em;background:rgba(31,200,255,.14);border:1px solid rgba(31,200,255,.45);color:var(--cy);-webkit-backdrop-filter:blur(8px);backdrop-filter:blur(8px)}
    .cv-body{padding:18px 20px 20px}
    .cv-meta{font-size:12px;color:var(--cy);margin-bottom:4px}
    .cv-name{font:600 18px/1.25 var(--body);color:#fff;margin-bottom:10px}
    .cv-row{display:flex;align-items:center;justify-content:space-between;gap:8px}
    .cv-price{font:800 22px var(--disp);letter-spacing:.02em;color:#fff}
    .cv-price small{font:500 12px var(--body);color:var(--mu);margin-left:3px;letter-spacing:0}
    .cv-go{padding:8px 14px;border-radius:999px;border:1px solid rgba(31,200,255,.5);color:var(--cy);font-size:12px;font-weight:600;transition:.25s;white-space:nowrap}
    .cv-card:hover .cv-go{background:var(--cy);color:#001018}
    .cv-more{text-align:center;margin-top:56px}
    .cv-more .cv-btn{margin-top:0}
    .cv-banner{margin:0 auto 100px;width:min(1200px,92vw);padding:56px 48px;border-radius:26px;border:1px solid rgba(31,200,255,.25);background:radial-gradient(120% 140% at 85% 0%,rgba(47,107,255,.35),transparent 60%),linear-gradient(180deg,#07142a,#03070f);display:flex;align-items:center;justify-content:space-between;gap:30px;flex-wrap:wrap}
    .cv-banner h4{font:800 clamp(30px,4vw,52px)/1 var(--disp);text-transform:uppercase}
    .cv-banner p{color:var(--mu);margin-top:10px;max-width:46ch}
    .cv-foot{background:#010204;border-top:1px solid rgba(31,200,255,.1);padding:46px 0 34px;color:var(--mu);font-size:14px}
    .cv-foot .cv-wrap{display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap}
    .cv-foot nav,.cv-foot div.fl{display:flex;gap:22px;flex-wrap:wrap}
    .cv-foot a{text-decoration:none;color:var(--mu)}
    .cv-foot a:hover{color:var(--cy)}
    .js .rv{opacity:0;transform:translateY(26px);transition:opacity .8s ease,transform .8s cubic-bezier(.2,.7,.2,1);transition-delay:calc(var(--i,0)*.08s)}
    .js .rv.in{opacity:1;transform:none}
    .js .cv-card.rv.in{transform:perspective(900px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg))}

    @media(max-width:980px){
      .cv-links{display:none}.cv-burger{display:block}.cv-right{margin-left:auto}
      .cv-acc{display:none}
      .ov,.ov.r{left:6vw!important;right:6vw!important;text-align:left!important}
      .ov.r p{margin-left:0}
      .ov.low{bottom:16vh}
      #dots{right:10px}#hud{display:none}
      .cv-stats .cv-wrap{grid-template-columns:repeat(2,1fr)}
    }
    @media(max-width:560px){#sw{gap:12px;padding:8px 14px;max-width:94vw}#cn{display:none}#sws button{width:30px;height:30px}.cv-banner{padding:36px 26px}}
    @media(prefers-reduced-motion:reduce){*{animation:none!important}.js .rv{transition:none;opacity:1;transform:none}}
  </style>
</head>
<body>

<canvas id="c"></canvas>
<div id="prog"></div>

<div class="cv-nav" role="navigation" aria-label="เมนูหลัก">
  <a href="index.php" class="cv-logo"><i></i>CAR<small>VERSE</small></a>
  <ul class="cv-links">
    <li><a data-go="1">Exterior</a></li>
    <li><a data-go="3">Engine</a></li>
    <li><a data-go="4">Specs</a></li>
    <li><a data-go="5">Configure</a></li>
    <li><a href="search.php">ค้นหารถ</a></li>
    <li><a href="sell_car.php">ขายรถ</a></li>
  </ul>
  <div class="cv-right">
    <?php if (isLoggedIn()): ?>
      <div class="cv-acc">
        <button type="button" aria-haspopup="true">บัญชีของฉัน ▾</button>
        <div class="cv-dd">
          <a href="my_orders.php">คำสั่งซื้อ</a>
          <a href="my_bookings.php">การจองของฉัน</a>
          <a href="profile.php">โปรไฟล์</a>
          <?php if (isAdmin()): ?><a href="add_car.php">เพิ่มรถ</a><?php endif; ?>
          <a href="logout.php" class="out">ออกจากระบบ</a>
        </div>
      </div>
    <?php else: ?>
      <a href="login.php" class="cv-cta">เข้าสู่ระบบ</a>
      <a href="register.php" class="cv-cta" style="background:transparent;border:1px solid var(--cy);color:var(--cy);box-shadow:none;">สมัครสมาชิก</a>
    <?php endif; ?>
    <button class="cv-burger" id="burger" aria-label="เปิดเมนู" aria-expanded="false"></button>
  </div>
</div>

<div id="sheet">
  <a data-go="1">Exterior</a><a data-go="3">Engine</a><a data-go="4">Specs</a><a data-go="5">Configure</a>
  <a href="search.php">ค้นหารถ</a><a href="sell_car.php">ขายรถ</a>
  <?php if (isLoggedIn()): ?>
    <a href="my_orders.php">คำสั่งซื้อ</a><a href="my_bookings.php">การจองของฉัน</a><a href="profile.php">โปรไฟล์</a>
    <?php if (isAdmin()): ?><a href="add_car.php">เพิ่มรถ</a><?php endif; ?>
    <a href="logout.php" class="out">ออกจากระบบ</a>
  <?php else: ?>
    <a href="login.php">เข้าสู่ระบบ</a>
    <a href="register.php">สมัครสมาชิก</a>
  <?php endif; ?>
</div>

<div id="dots" aria-label="ฉากแสดงรถ"></div>
<div id="hud"><span id="hud1"></span><br><span id="hud2"></span></div>
<div id="hint"><span>SCROLL</span><i></i></div>

<!-- ======== Scene copy ======== -->
<div class="ov" data-i="0"><div class="ov-in">
  <h1>The art<br>of <b>motion.</b></h1>
  <p>A machine sculpted by wind and intent. Scroll to take it apart, piece by piece.
    <span class="th">โชว์รูม 3 มิติ เลื่อนลงเพื่อสำรวจทุกชิ้นส่วน</span></p>
  <button class="cv-btn" data-go="1">BEGIN THE REVEAL</button>
</div></div>

<div class="ov r" data-i="1"><div class="ov-in">
  <h2>Every detail <b>considered.</b></h2>
  <p>An aerodynamic silhouette honed in the wind tunnel, finished in a clearcoat that reflects the whole room.</p>
</div></div>

<div class="ov" data-i="2"><div class="ov-in">
  <h2>Built around <b>the driver.</b></h2>
  <p>Carbon, leather and a cockpit engineered for pure performance.</p>
</div></div>

<div class="ov low" data-i="3"><div class="ov-in">
  <h2>Engineered<br>to the <b>core.</b></h2>
  <p>The powertrain unfolds into its components, obsessed over, balanced and tuned. Tap a marker to see what it is.
    <span class="th">แตะจุดบนชิ้นส่วนเพื่อดูรายละเอียด</span></p>
</div></div>

<div class="ov r" data-i="4"><div class="ov-in">
  <h2>By the <b>numbers.</b></h2>
  <dl>
    <div><dt>2.9s</dt><dd>0–100 km/h</dd></div>
    <div><dt>903 hp</dt><dd>V8 twin-turbo with electric assist</dd></div>
    <div><dt>350 km/h</dt><dd>Top speed</dd></div>
  </dl>
</div></div>

<div class="ov top" data-i="5"><div class="ov-in">
  <h2>Make it <b>yours.</b></h2>
  <p>Choose a custom finish. The 3D model updates live.
    <span class="th">เลือกสีตัวถังได้แบบเรียลไทม์</span></p>
</div></div>

<div id="sw"><span id="cn"></span><div id="sws"></div></div>

<div id="ld">
  <div class="t">CARVERSE 3D</div>
  <div id="bar"><i></i></div>
  <small id="pct">0%</small>
  <label id="pick">โหลดโมเดลอัตโนมัติไม่สำเร็จ เลือกไฟล์ .glb ด้วยตัวเอง: <input type="file" id="file" accept=".glb,.gltf"></label>
</div>

<!-- ======== Content below the 3D scene ======== -->
<div class="cv-content" id="showroom">

  <div class="cv-stats">
    <div class="cv-wrap">
      <div class="cv-stat rv" style="--i:0"><b data-count="<?= (int)$stats['total_cars'] ?>">0</b><span>รถในระบบ</span></div>
      <div class="cv-stat rv" style="--i:1"><b data-count="<?= (int)$stats['total_brands'] ?>">0</b><span>แบรนด์</span></div>
      <div class="cv-stat rv" style="--i:2"><b data-count="<?= (int)$stats['total_members'] ?>">0</b><span>สมาชิก</span></div>
      <div class="cv-stat rv" style="--i:3"><b data-count="<?= (int)$stats['total_bookings'] ?>">0</b><span>การจองทดลองขับ</span></div>
    </div>
  </div>

  <section class="cv-sec">
    <div class="cv-wrap">
      <div class="cv-head rv">
        <h3>รถยนต์<b> แนะนำ</b></h3>
        <p>คัดสรรรถพรีเมียมล่าสุดจากฐานข้อมูลจริง พร้อมให้คุณจองทดลองขับ</p>
      </div>

      <div class="cv-grid">
        <?php if (!$featured): ?>
          <p style="grid-column:1/-1;text-align:center;color:var(--mu)">ยังไม่มีรถที่พร้อมขายในระบบ ลองเพิ่มรถจากหน้าผู้ดูแลระบบ</p>
        <?php endif; ?>
        <?php foreach ($featured as $i => $car): ?>
        <a class="cv-card rv" style="--i:<?= (int)$i ?>" href="car_detail.php?id=<?= (int)$car['car_id'] ?>">
          <div class="cv-pic">
            <span class="cv-badge">พร้อมขาย</span>
            <?php 
              $carImg = function_exists('carImage') ? carImage($car) : (!empty($car['image_url']) ? $car['image_url'] : '');
            ?>
            <?php if (!empty($carImg)): ?>
              <img src="<?= htmlspecialchars($carImg) ?>" alt="<?= htmlspecialchars($car['model_name']) ?>" loading="lazy">
            <?php else: ?>
              <div class="cv-noimg">ไม่มีรูปภาพ</div>
            <?php endif; ?>
          </div>
          <div class="cv-body">
            <div class="cv-meta"><?= htmlspecialchars($car['brand_name']) ?> · <?= htmlspecialchars($car['type_name']) ?></div>
            <div class="cv-name"><?= htmlspecialchars($car['model_name']) ?></div>
            <div class="cv-row">
              <div class="cv-price"><?= number_format($car['price']) ?><small>บาท</small></div>
              <span class="cv-go">ดูรายละเอียด</span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="cv-more rv">
        <a href="search.php" class="cv-btn">ดูรถทั้งหมดในระบบ</a>
      </div>
    </div>
  </section>

  <div class="cv-banner rv">
    <div>
      <h4>อยากลองขับคันจริง?</h4>
      <p>เลือกรถที่ถูกใจแล้วจองคิวทดลองขับได้ในไม่กี่คลิก</p>
    </div>
    <a href="search.php" class="cv-btn" style="margin-top:0">เริ่มค้นหารถ</a>
  </div>

  <div class="cv-foot">
    <div class="cv-wrap">
      <span>© 2026 CarVerse 3D. All rights reserved.</span>
      <div class="fl"><a href="search.php">ค้นหารถ</a><a href="sell_car.php">ขายรถ</a><a href="login.php">เข้าสู่ระบบ</a></div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/shaders/CopyShader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/shaders/LuminosityHighPassShader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/EffectComposer.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/RenderPass.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/ShaderPass.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/postprocessing/UnrealBloomPass.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/objects/Reflector.js"></script>
<script>
/* ============================================================
   CONFIG — ปรับค่าหลัก ๆ ได้ที่นี่
   ============================================================ */
const CONFIG = {
  modelUrl: 'assets/models/mclaren_p1.glb',
  sizeHint: 19169040,
  length: 5.2,
  paintRegex: /carpaint/i,
  exposure: 0.8,     // ความสว่างรวม (ถ้ามืด/จ้าเกินไปปรับตรงนี้)
  paintGloss: 0.55,   // ความสะท้อนของสีรถ (ลดลง = รถดำเข้มขึ้น)
  bloom: 0.34,        // ความเรืองแสง
  colors: [['Vanta Black','#0b0b0e'],['Cyan','#0b86d6'],['Signal Red','#c1121f'],['Titanium','#8a9099'],['Volcano','#ff5a0a'],['Ice White','#e8edf2']]
};
const SCENES = ['Intro','Exterior','Interior','Engine','Specs','Configure'];

const $=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)];
const clamp=(x,a=0,b=1)=>Math.min(b,Math.max(a,x)),ss=x=>(x=clamp(x))*x*(3-2*x),mix=(a,b,t)=>a+(b-a)*t;
const reduce=matchMedia('(prefers-reduced-motion:reduce)').matches;
const isMobile=matchMedia('(max-width:900px)').matches||/Mobi|Android/i.test(navigator.userAgent);
const V3=THREE.Vector3, CX=5.5;      // CX = ตำแหน่งแกน X ของรถในฉาก

/* ============================================================
   RENDERER + POST-PROCESSING (bloom)
   ============================================================ */
const renderer=new THREE.WebGLRenderer({canvas:$('#c'),antialias:true,powerPreference:'high-performance'});
renderer.setPixelRatio(Math.min(devicePixelRatio,isMobile?1.5:1.75));
renderer.outputEncoding=THREE.sRGBEncoding;
const canComposer=!!(THREE.EffectComposer&&THREE.RenderPass&&THREE.UnrealBloomPass&&THREE.ShaderPass&&THREE.CopyShader&&THREE.LuminosityHighPassShader&&renderer.capabilities.isWebGL2);
renderer.toneMapping=canComposer?THREE.NoToneMapping:THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure=1.1;

const scene=new THREE.Scene();
scene.background=new THREE.Color(0x01030a);
scene.fog=new THREE.FogExp2(0x020a1c,.026);
const camera=new THREE.PerspectiveCamera(38,1,.05,200);

/* ============================================================
   ENVIRONMENT (สิ่งที่สะท้อนบนสีรถ) — ซอฟต์บ็อกซ์ + แท่งไฟรอบห้อง
   ============================================================ */
{const es=new THREE.Scene();es.background=new THREE.Color(0x02060f);
 const sb=(w,h,x,y,z,c,i)=>{const m=new THREE.Mesh(new THREE.PlaneGeometry(w,h),new THREE.MeshBasicMaterial({color:new THREE.Color(c).multiplyScalar(i),side:THREE.DoubleSide}));m.position.set(x,y,z);m.lookAt(0,0,0);es.add(m)};
 sb(16,7,0,10,0,0xffffff,4);sb(3,8,-9,4,0,0x33bbff,5);sb(3,8,9,4,0,0x33bbff,5);sb(12,2,0,2,-10,0xffffff,3);
 for(let a=0;a<12;a++){const t=a/12*Math.PI*2;sb(.9,9,Math.sin(t)*12,3,Math.cos(t)*12,a%3?0x3aa8ff:0xffffff,a%3?3.2:2.4)}
 scene.environment=new THREE.PMREMGenerator(renderer).fromScene(es,.02).texture;}

/* ============================================================
   HELPERS: canvas textures
   ============================================================ */
function radialTex(stops,size=256){const c=document.createElement('canvas');c.width=c.height=size;const g=c.getContext('2d'),r=g.createRadialGradient(size/2,size/2,0,size/2,size/2,size/2);stops.forEach(([o,col])=>r.addColorStop(o,col));g.fillStyle=r;g.fillRect(0,0,size,size);return new THREE.CanvasTexture(c)}
function ringTex(dense){const c=document.createElement('canvas');c.width=c.height=1024;const g=c.getContext('2d');g.translate(512,512);
 const ring=(r,w,a)=>{g.beginPath();g.arc(0,0,r,0,Math.PI*2);g.lineWidth=w;g.strokeStyle='rgba(130,225,255,'+a+')';g.stroke()};
 ring(498,5,.95);ring(468,2,.5);if(dense){ring(330,2,.3);ring(250,1.5,.22)}
 const n=dense?180:72;
 for(let i=0;i<n;i++){const a=i/n*Math.PI*2,big=i%5===0;g.save();g.rotate(a);g.beginPath();g.moveTo(0,-(big?448:458));g.lineTo(0,-468);g.lineWidth=big?3:1.5;g.strokeStyle='rgba(150,230,255,'+(big?.9:.45)+')';g.stroke();g.restore()}
 g.lineCap='round';g.lineWidth=11;g.strokeStyle='rgba(31,200,255,.95)';
 (dense?[[-.25,.3],[1.1,1.5],[2.4,2.7],[3.9,4.4],[5.25,5.6]]:[[.2,.9],[2.3,3.2],[4.4,5.2]]).forEach(([s,e])=>{g.beginPath();g.arc(0,0,dense?420:410,s,e);g.stroke()});
 const t=new THREE.CanvasTexture(c);t.anisotropy=8;return t}

/* ============================================================
   BACKDROP: sky-dome + แผงไฟรอบห้องโชว์รูม
   ============================================================ */
const sky=new THREE.Mesh(new THREE.SphereGeometry(100,32,16),new THREE.ShaderMaterial({side:THREE.BackSide,depthWrite:false,fog:false,
 vertexShader:'varying vec3 vP;void main(){vP=position;gl_Position=projectionMatrix*modelViewMatrix*vec4(position,1.);}',
 fragmentShader:'varying vec3 vP;void main(){float h=normalize(vP).y;vec3 hor=vec3(.004,.028,.1),top=vec3(0.,.004,.015);vec3 c=mix(hor,top,smoothstep(-.02,.55,h));c=mix(c,vec3(0.,.012,.04),smoothstep(0.,-.3,h));gl_FragColor=vec4(c,1.);}'}));
sky.position.x=CX;sky.renderOrder=-10;scene.add(sky);

const wall=new THREE.Group();wall.position.x=CX;scene.add(wall);
{const N=22,R=17,
  stripBlue=new THREE.MeshBasicMaterial({color:new THREE.Color(0x1fc8ff).multiplyScalar(1.6)}),
  stripWhite=new THREE.MeshBasicMaterial({color:new THREE.Color(0xffffff).multiplyScalar(1.5)}),
  panelMat=new THREE.MeshStandardMaterial({color:0x02060e,metalness:.9,roughness:.3,envMapIntensity:.12});
 for(let i=0;i<N;i++){
  const a=i/N*Math.PI*2,h=7+((i*7)%5)*1.2,g=new THREE.Group();
  g.position.set(Math.sin(a)*R,h/2,Math.cos(a)*R);g.lookAt(0,h/2,0);
  g.add(new THREE.Mesh(new THREE.BoxGeometry(2.6,h,.3),panelMat));
  const s=new THREE.Mesh(new THREE.PlaneGeometry(.12,h*.86),i%4===0?stripWhite:stripBlue);s.position.z=.17;g.add(s);
  wall.add(g);}
 const ceil=new THREE.Mesh(new THREE.TorusGeometry(R*.62,.08,8,96),stripBlue);ceil.rotation.x=Math.PI/2;ceil.position.y=11;wall.add(ceil);
 const ceil2=new THREE.Mesh(new THREE.TorusGeometry(R*.3,.06,8,96),stripWhite);ceil2.rotation.x=Math.PI/2;ceil2.position.y=12.5;wall.add(ceil2);
 const low=new THREE.Mesh(new THREE.TorusGeometry(12,.035,8,128),stripBlue);low.rotation.x=Math.PI/2;low.position.y=.05;wall.add(low);}

{const glowTex=radialTex([[0,'rgba(70,150,255,1)'],[.4,'rgba(30,100,255,.35)'],[1,'rgba(20,80,255,0)']]);
 for(let i=0;i<7;i++){const a=i/7*Math.PI*2+.4,sp=new THREE.Sprite(new THREE.SpriteMaterial({map:glowTex,color:0x2a7bff,blending:THREE.AdditiveBlending,depthWrite:false,transparent:true,opacity:.13,fog:false}));
  sp.scale.set(30,17,1);sp.position.set(CX+Math.sin(a)*22,5+(i%3)*2,Math.cos(a)*22);scene.add(sp)}}

/* ============================================================
   FLOOR: กระจกสะท้อน + เทิร์นเทเบิลเรืองแสง
   ============================================================ */
try{
  if(THREE.Reflector){
    const res=isMobile?512:1024;
    const refl=new THREE.Reflector(new THREE.CircleGeometry(70,64),{color:0x6f86a8,textureWidth:res,textureHeight:res,clipBias:.003});
    refl.rotation.x=-Math.PI/2;refl.position.set(CX,-.002,0);scene.add(refl);
  }
}catch(e){console.warn('Reflector disabled',e)}

const floorTex=radialTex([[0,'rgba(1,3,8,.62)'],[.25,'rgba(1,3,8,.74)'],[.55,'rgba(1,2,6,.94)'],[1,'rgba(1,2,6,1)']],512);
const floorMask=new THREE.Mesh(new THREE.CircleGeometry(70,64),new THREE.MeshBasicMaterial({map:floorTex,transparent:true,depthWrite:false,fog:false}));
floorMask.rotation.x=-Math.PI/2;floorMask.position.set(CX,.001,0);floorMask.renderOrder=1;scene.add(floorMask);

const shadow=new THREE.Mesh(new THREE.PlaneGeometry(4.6,7.6),new THREE.MeshBasicMaterial({map:radialTex([[0,'rgba(0,0,0,.9)'],[.55,'rgba(0,0,0,.5)'],[1,'rgba(0,0,0,0)']]),transparent:true,depthWrite:false}));
shadow.rotation.x=-Math.PI/2;shadow.position.set(CX,.008,0);shadow.renderOrder=2;scene.add(shadow);

const pool=new THREE.Mesh(new THREE.PlaneGeometry(13,13),new THREE.MeshBasicMaterial({map:radialTex([[0,'rgba(50,130,255,.28)'],[.45,'rgba(30,100,255,.08)'],[1,'rgba(20,80,255,0)']]),transparent:true,depthWrite:false,blending:THREE.AdditiveBlending,fog:false}));
pool.rotation.x=-Math.PI/2;pool.position.set(CX,.01,0);pool.renderOrder=3;scene.add(pool);

const mkRing=(R,dense,op,y)=>{const m=new THREE.Mesh(new THREE.PlaneGeometry(R*2,R*2),new THREE.MeshBasicMaterial({map:ringTex(dense),transparent:true,opacity:op,depthWrite:false,blending:THREE.AdditiveBlending,color:new THREE.Color(0x8fe6ff).multiplyScalar(1.25),fog:false}));
 m.rotation.x=-Math.PI/2;m.position.set(CX,y,0);m.renderOrder=4;scene.add(m);return m};
const ring1=mkRing(4.5,true,1,.012),ring2=mkRing(7.4,false,.6,.014);

/* ============================================================
   LIGHTS
   ============================================================ */
const sp=new THREE.SpotLight(0xd9f2ff,90,35,.5,.9,1.5);
sp.position.set(CX+.5,14,4);sp.target.position.set(CX,0,0);scene.add(sp,sp.target);
scene.add(new THREE.AmbientLight(0x040a14,.6));
[[CX+6,2.5,-3,0x0a7bff,22],[CX-6,2.5,-3,0x0a7bff,22],[CX,1.6,7,0x1fc8ff,8]].forEach(([x,y,z,c,i])=>{const l=new THREE.PointLight(c,i,20,2);l.position.set(x,y,z);scene.add(l)});
const under=new THREE.PointLight(0x2f9bff,8,8,2);under.position.set(CX,.2,0);scene.add(under);

const cone=new THREE.Mesh(new THREE.CylinderGeometry(.1,3.8,11,48,1,true),new THREE.ShaderMaterial({transparent:true,depthWrite:false,blending:THREE.AdditiveBlending,side:THREE.DoubleSide,uniforms:{c:{value:new THREE.Color(0x1fc8ff)}},
 vertexShader:'varying vec2 v;void main(){v=uv;gl_Position=projectionMatrix*modelViewMatrix*vec4(position,1.);}',
 fragmentShader:'varying vec2 v;uniform vec3 c;void main(){float a=pow(v.y,2.0)*.045;gl_FragColor=vec4(c*a,a);}'}));
cone.position.set(CX,5.5,1);cone.rotation.x=.12;scene.add(cone);

/* ฝุ่นลอยในแสง */
const dustG=new THREE.Group();dustG.position.x=CX;scene.add(dustG);
const DN=isMobile?220:420,dp=new Float32Array(DN*3);
for(let i=0;i<DN;i++){dp[i*3]=(Math.random()-.5)*26;dp[i*3+1]=Math.random()*9;dp[i*3+2]=(Math.random()-.5)*26}
const dg=new THREE.BufferGeometry();dg.setAttribute('position',new THREE.BufferAttribute(dp,3));
dustG.add(new THREE.Points(dg,new THREE.PointsMaterial({map:radialTex([[0,'rgba(255,255,255,1)'],[1,'rgba(255,255,255,0)']],64),size:.1,transparent:true,opacity:.55,depthWrite:false,blending:THREE.AdditiveBlending,color:0x7fd8ff})));

/* ============================================================
   CAR + EXPLODED PARTS
   ============================================================ */
const car=new THREE.Group();car.position.x=CX;scene.add(car);
const door=[],hood=[],paint=[],parts=[];let hl=[],size=new THREE.Vector3(1,1,1),ready=false;
const ph=new THREE.Mesh(new THREE.BoxGeometry(2,.9,4.6),new THREE.MeshBasicMaterial({color:0x1fc8ff,wireframe:true,transparent:true,opacity:.25}));ph.position.y=.5;car.add(ph);
const partsLight=new THREE.PointLight(0x5fb8ff,0,14,2);partsLight.position.set(0,3.2,0);car.add(partsLight);

/* ---- materials ---- */
const M={
  metal:new THREE.MeshStandardMaterial({color:0x7d9fc9,metalness:1,roughness:.3,envMapIntensity:.7}),
  dark:new THREE.MeshStandardMaterial({color:0x0f1a2c,metalness:.9,roughness:.4,envMapIntensity:.6}),
  titan:new THREE.MeshStandardMaterial({color:0x4f6382,metalness:1,roughness:.4,envMapIntensity:.7}),
  chrome:new THREE.MeshStandardMaterial({color:0xc8d8ec,metalness:1,roughness:.1,envMapIntensity:.8}),
  accent:new THREE.MeshStandardMaterial({color:0xff5a0a,metalness:.35,roughness:.4,emissive:0x5a1800,emissiveIntensity:.8}),
  glow:new THREE.MeshBasicMaterial({color:new THREE.Color(0x1fc8ff).multiplyScalar(2.6)})
};
const LM=new THREE.LineBasicMaterial({color:new THREE.Color(0x35d0ff).multiplyScalar(1.5),transparent:true,opacity:.5,blending:THREE.AdditiveBlending,depthWrite:false});
const lineMat=new THREE.LineBasicMaterial({color:new THREE.Color(0x35d0ff).multiplyScalar(1.4),transparent:true,opacity:0,blending:THREE.AdditiveBlending,depthWrite:false});

/* ---- little geometry helpers ---- */
const cyl=(r1,r2,h,seg,mat)=>new THREE.Mesh(new THREE.CylinderGeometry(r1,r2,h,seg),mat);
const box=(w,h,d,mat)=>new THREE.Mesh(new THREE.BoxGeometry(w,h,d),mat);
const edged=(m,th=35)=>{m.add(new THREE.LineSegments(new THREE.EdgesGeometry(m.geometry,th),LM));return m};
const rod=(a,b,r,mat)=>{const d=new V3().subVectors(b,a),m=cyl(r,r,d.length(),12,mat);m.position.copy(a).add(b).multiplyScalar(.5);m.quaternion.setFromUnitVectors(new V3(0,1,0),d.clone().normalize());return m};

/* ---- part builders (หน่วยเมตร ตามสเกลรถยาว 5.2) ---- */
function makeEngine(){
  const g=new THREE.Group();
  const block=box(.62,.30,.95,M.dark);g.add(edged(block));
  const pan=box(.5,.12,.8,M.titan);pan.position.y=-.2;g.add(edged(pan));
  for(const s of[-1,1]){
    const bank=new THREE.Group();bank.rotation.z=-s*.62;bank.position.set(s*.2,.12,0);
    const head=box(.26,.14,.95,M.metal);head.position.y=.2;bank.add(edged(head));
    for(let i=0;i<4;i++){const z=(i-1.5)*.23;
      const liner=cyl(.085,.085,.3,20,M.chrome);liner.position.set(0,.07,z);bank.add(liner);
      const cap=cyl(.05,.05,.05,16,M.glow);cap.position.set(0,.33,z);bank.add(cap)}
    const cover=box(.3,.05,1.0,M.metal);cover.position.y=.3;bank.add(edged(cover));
    g.add(bank)}
  const ple=box(.2,.11,.8,M.titan);ple.position.set(0,.34,0);g.add(edged(ple));
  for(let i=0;i<4;i++){const z=(i-1.5)*.23;[-1,1].forEach(s=>{const r=cyl(.035,.035,.22,12,M.chrome);r.rotation.z=s*Math.PI/2*.85;r.position.set(s*.13,.32,z);g.add(r)})}
  const fly=cyl(.26,.26,.07,36,M.dark);fly.rotation.x=Math.PI/2;fly.position.set(0,0,-.54);g.add(edged(fly));
  const pul=cyl(.12,.12,.06,28,M.chrome);pul.rotation.x=Math.PI/2;pul.position.set(0,-.02,.52);g.add(pul);
  return{g};
}
function makeTurbo(){
  const g=new THREE.Group();
  g.add(edged(new THREE.Mesh(new THREE.TorusGeometry(.14,.075,18,40,Math.PI*1.75),M.titan),40));
  const cm=cyl(.1,.13,.2,28,M.metal);cm.rotation.x=Math.PI/2;cm.position.z=.19;g.add(edged(cm));
  const tm=cyl(.085,.11,.16,28,M.dark);tm.rotation.x=Math.PI/2;tm.position.z=-.17;g.add(edged(tm));
  const wheel=new THREE.Group();
  for(let i=0;i<12;i++){const b=box(.012,.085,.07,M.chrome);b.position.y=.055;const a=new THREE.Group();a.rotation.z=i/12*Math.PI*2;a.add(b);wheel.add(a)}
  wheel.position.z=.3;g.add(wheel);
  const hub=cyl(.025,.025,.1,12,M.chrome);hub.rotation.x=Math.PI/2;hub.position.z=.3;g.add(hub);
  const w=new THREE.Group();w.add(g);
  return{g:w,spin:{o:wheel,a:'z',v:9}};
}
function makeBrake(){
  const g=new THREE.Group(),sp=new THREE.Group();
  const sh=new THREE.Shape();sh.absarc(0,0,.38,0,Math.PI*2,false);
  const hole=new THREE.Path();hole.absarc(0,0,.15,0,Math.PI*2,true);sh.holes.push(hole);
  for(let i=0;i<28;i++){const a=i/28*Math.PI*2,p=new THREE.Path();p.absarc(Math.cos(a)*.27,Math.sin(a)*.27,.022,0,Math.PI*2,true);sh.holes.push(p)}
  const geo=new THREE.ExtrudeGeometry(sh,{depth:.06,bevelEnabled:false,curveSegments:32});geo.translate(0,0,-.03);
  sp.add(new THREE.Mesh(geo,M.titan));
  const hub=cyl(.17,.17,.14,32,M.dark);hub.rotation.x=Math.PI/2;sp.add(hub);
  for(let i=0;i<5;i++){const a=i/5*Math.PI*2,b=cyl(.014,.014,.2,10,M.chrome);b.rotation.x=Math.PI/2;b.position.set(Math.cos(a)*.1,Math.sin(a)*.1,0);sp.add(b)}
  g.add(sp);
  const cal=new THREE.Group();
  [-1,1].forEach(s=>{const pl=box(.22,.2,.035,M.accent);pl.position.set(0,.27,s*.065);cal.add(edged(pl))});
  const br=box(.22,.04,.17,M.accent);br.position.y=.38;cal.add(edged(br));
  g.add(cal);
  g.rotation.y=Math.PI/2;                         // แกนจานเบรกชี้ไปทางซ้าย-ขวาของรถ
  const w=new THREE.Group();w.add(g);
  return{g:w,spin:{o:sp,a:'z',v:3}};
}
function makeWishbone(){
  const g=new THREE.Group(),J=new V3(.55,0,0),A=new V3(-.5,.02,.34),B=new V3(-.5,.02,-.34);
  g.add(rod(J,A,.026,M.metal),rod(J,B,.026,M.metal),rod(A,B,.016,M.titan));
  const j=new THREE.Mesh(new THREE.SphereGeometry(.06,20,16),M.chrome);j.position.copy(J);g.add(j);
  [A,B].forEach(p=>{const bu=cyl(.05,.05,.1,16,M.dark);bu.rotation.z=Math.PI/2;bu.position.copy(p);g.add(bu)});
  const w=new THREE.Group();w.add(g);return{g:w};
}
function makeSpring(){
  const g=new THREE.Group(),pts=[],turns=9,H=.8,R=.09,n=turns*24;
  for(let i=0;i<=n;i++){const t=i/n,a=t*turns*Math.PI*2;pts.push(new V3(Math.cos(a)*R,-H/2+t*H,Math.sin(a)*R))}
  g.add(new THREE.Mesh(new THREE.TubeGeometry(new THREE.CatmullRomCurve3(pts),n*2,.016,8,false),M.accent));
  g.add(cyl(.035,.035,.95,16,M.chrome));
  const top=cyl(.07,.07,.05,20,M.dark);top.position.y=.45;g.add(edged(top));
  const bot=cyl(.07,.07,.05,20,M.dark);bot.position.y=-.45;g.add(edged(bot));
  const w=new THREE.Group();w.add(g);return{g:w};
}
function makeGear(r,teeth,speed){
  const g=new THREE.Group(),w=.07;
  const body=cyl(r,r,w,40,M.metal);body.rotation.x=Math.PI/2;g.add(edged(body));
  for(let i=0;i<teeth;i++){const a=i/teeth*Math.PI*2,t=box(r*.28,r*.2,w,M.metal);t.position.set(Math.cos(a)*(r+.01),Math.sin(a)*(r+.01),0);t.rotation.z=a;g.add(t)}
  for(let i=0;i<5;i++){const a=i/5*Math.PI*2,h=cyl(r*.1,r*.1,w+.01,12,M.dark);h.rotation.x=Math.PI/2;h.position.set(Math.cos(a)*r*.55,Math.sin(a)*r*.55,0);g.add(h)}
  const ax=cyl(r*.2,r*.2,w+.05,20,M.chrome);ax.rotation.x=Math.PI/2;g.add(ax);
  const wr=new THREE.Group();wr.add(g);return{g:wr,spin:{o:g,a:'z',v:speed}};
}
function makeShaft(){
  const g=new THREE.Group();
  const s=cyl(.04,.04,1.7,20,M.metal);s.rotation.x=Math.PI/2;g.add(edged(s,40));
  [-.85,.85].forEach(z=>{const f=cyl(.11,.11,.05,28,M.dark);f.rotation.x=Math.PI/2;f.position.z=z;g.add(edged(f));
    const u=box(.12,.04,.04,M.chrome);u.position.z=z*.85;g.add(u);const u2=box(.04,.12,.04,M.chrome);u2.position.z=z*.85;g.add(u2)});
  const sl=cyl(.065,.065,.5,24,M.titan);sl.rotation.x=Math.PI/2;sl.position.z=.2;g.add(edged(sl));
  const w=new THREE.Group();w.add(g);return{g:w,spin:{o:s,a:'y',v:6}};
}
function makeCrank(){
  const g=new THREE.Group();
  const main=cyl(.04,.04,1.1,16,M.chrome);main.rotation.x=Math.PI/2;g.add(main);
  for(let i=0;i<4;i++){
    const z=(i-1.5)*.26,sgn=(i===1||i===2)?-1:1;
    const web=cyl(.1,.1,.03,24,M.dark);web.rotation.x=Math.PI/2;web.position.z=z;g.add(edged(web));
    const pin=cyl(.032,.032,.07,12,M.chrome);pin.rotation.x=Math.PI/2;pin.position.set(sgn*.06,0,z);g.add(pin);
    const pis=cyl(.075,.075,.14,24,M.chrome);pis.position.set(0,.42,z);g.add(edged(pis,40));
    [-.04,0,.04].forEach(dy=>{const gr=cyl(.077,.077,.01,24,M.dark);gr.position.set(0,.45+dy,z);g.add(gr)});
    g.add(rod(new V3(0,.35,z),new V3(sgn*.06,0,z),.016,M.titan));
  }
  const w=new THREE.Group();w.add(g);return{g:w};
}
function makeBattery(){
  const g=new THREE.Group();
  g.add(edged(box(.95,.2,.5,M.dark),40));
  for(let i=0;i<6;i++){const c=box(.11,.015,.4,M.glow);c.position.set(-.4+i*.16,.108,0);g.add(c)}
  const base=box(.97,.06,.52,M.titan);base.position.y=-.12;g.add(edged(base,40));
  const t=cyl(.03,.03,.08,12,M.accent);t.rotation.z=Math.PI/2;t.position.set(.52,.05,.15);g.add(t);
  const w=new THREE.Group();w.add(g);return{g:w};
}
function makeExhaust(){
  const g=new THREE.Group();
  [-1,1].forEach(s=>{const c=new THREE.CatmullRomCurve3([new V3(s*.34,.06,.75),new V3(s*.3,.04,.2),new V3(s*.2,0,-.25),new V3(s*.1,-.02,-.55)]);
    g.add(edged(new THREE.Mesh(new THREE.TubeGeometry(c,24,.045,14,false),M.titan),40))});
  const mu=cyl(.13,.13,.55,28,M.metal);mu.rotation.x=Math.PI/2;mu.position.z=-.75;g.add(edged(mu));
  [-1,1].forEach(s=>{const tp=cyl(.058,.058,.28,20,M.chrome);tp.rotation.x=Math.PI/2;tp.position.set(s*.07,0,-1.1);g.add(tp);
    const gl=cyl(.04,.04,.01,18,M.accent);gl.rotation.x=Math.PI/2;gl.position.set(s*.07,0,-1.245);g.add(gl)});
  const w=new THREE.Group();w.add(g);return{g:w};
}

/* ---- ลงทะเบียนชิ้นส่วน: h = ตำแหน่งตอนประกอบอยู่ในตัวรถ, a = ตำแหน่งตอนระเบิดออก (พิกัดโลคัลของรถ) ---- */
const U=CONFIG.length/5.2;
function addPart(o,cfg){
  const g=o.g;g.visible=false;car.add(g);
  const home=new V3(...cfg.h).multiplyScalar(U),away=new V3(...cfg.a).multiplyScalar(U);
  const lg=new THREE.BufferGeometry();lg.setAttribute('position',new THREE.BufferAttribute(new Float32Array(6),3));
  lg.attributes.position.setXYZ(0,home.x,home.y,home.z);
  const line=new THREE.Line(lg,lineMat);line.frustumCulled=false;line.visible=false;car.add(line);
  const p={obj:g,home,away,rh:cfg.rh||[0,0,0],ra:cfg.ra||[0,0,0],s0:(cfg.s0||1)*U,s1:(cfg.s1||1.4)*U,d:cfg.d||0,ph:Math.random()*6.28,spin:o.spin,line,label:cfg.label};
  if(cfg.label){
    const el=document.createElement('div');el.className='hs';el.innerHTML='<i></i><span>'+cfg.label+'</span>';
    el.querySelector('i').onclick=()=>el.classList.toggle('open');
    document.body.appendChild(el);p.el=el}
  parts.push(p);
}
function buildParts(){
  const FZ=1.52,RZ=-1.5;
  addPart(makeEngine(),{h:[0,.55,-.85],a:[0,2.45,-.55],ra:[0,.5,0],s1:1.6,label:'V8 Twin-Turbo · 903 hp'});
  addPart(makeTurbo(),{h:[.38,.55,-1.5],a:[1.75,1.95,-1.9],ra:[.2,-.7,0],s1:1.7,d:.06,label:'Twin-scroll turbocharger'});
  addPart(makeTurbo(),{h:[-.38,.55,-1.5],a:[-1.75,1.75,-1.7],ra:[.2,.7,0],s1:1.7,d:.1});
  [[1,1],[-1,1],[1,-1],[-1,-1]].forEach(([sx,sz],i)=>{
    addPart(makeBrake(),{h:[sx*.78,.37,sz>0?FZ:RZ],a:[sx*2.75,sz>0?.95:1.3,sz>0?2.1:-2.4],ra:[0,-sx*.35,sx*.15],s0:.85,s1:1.35,d:.08+i*.04,label:i===0?'Carbon-ceramic brakes':null});
    addPart(makeWishbone(),{h:[sx*.6,.28,sz>0?FZ:RZ],a:[sx*1.55,.45,sz>0?3.25:-3.35],rh:[0,sx>0?0:Math.PI,0],ra:[.15,(sx>0?0:Math.PI)+sx*.3,.1],s1:1.4,d:.12+i*.03});
  });
  [1,-1].forEach((sx,i)=>addPart(makeSpring(),{h:[sx*.72,.6,FZ],a:[sx*1.35,1.7,2.65],ra:[.12,0,-sx*.12],s1:1.35,d:.14+i*.05,label:i===0?'Coil-over damper':null}));
  addPart(makeGear(.26,20,1.2),{h:[0,.45,-1.9],a:[-.1,1.05,-3.7],ra:[.25,.4,0],s1:1.6,d:.18,label:'7-speed dual-clutch gearbox'});
  addPart(makeGear(.18,14,-1.7),{h:[.1,.45,-1.9],a:[.65,1.75,-4.0],ra:[.3,-.5,0],s1:1.6,d:.22});
  addPart(makeGear(.13,10,2.4),{h:[-.1,.45,-1.9],a:[-.75,.6,-3.6],ra:[.1,.6,0],s1:1.7,d:.26});
  addPart(makeShaft(),{h:[0,.3,-.9],a:[-2.75,.5,.1],ra:[0,.1,0],s1:1.3,d:.2});
  addPart(makeCrank(),{h:[0,.4,-.85],a:[-2.0,2.35,.5],ra:[0,.5,.25],s1:1.5,d:.1,label:'Forged crankshaft & pistons'});
  addPart(makeExhaust(),{h:[0,.3,-1.4],a:[2.35,.55,-.35],ra:[0,-.4,0],s1:1.25,d:.16,label:'Titanium exhaust'});
  addPart(makeBattery(),{h:[0,.3,-.3],a:[2.2,2.15,1.0],ra:[0,-.5,.12],s1:1.35,d:.12,label:'Hybrid e-motor · +176 hp'});
}

/* ---- โหลดโมเดลรถ + หาประตู/ฝากระโปรง ---- */
function build(gltf){
  const model=gltf.scene;model.updateMatrixWorld(true);
  const box0=new THREE.Box3().setFromObject(model),bs=box0.getSize(new V3()),bc=box0.getCenter(new V3());
  const ax=bs.x>bs.z?'x':'z';let sign=1;
  const hb=new THREE.Box3();model.traverse(o=>{if(o.name.startsWith('GEO_HOOD'))hb.expandByObject(o)});
  if(!hb.isEmpty())sign=Math.sign(hb.getCenter(new V3())[ax]-bc[ax])||1;
  const f=new V3();f[ax]=sign;model.rotation.y=-Math.atan2(f.x,f.z);model.updateMatrixWorld(true);
  let b2=new THREE.Box3().setFromObject(model);model.scale.setScalar(CONFIG.length/b2.getSize(new V3()).z);model.updateMatrixWorld(true);
  b2=new THREE.Box3().setFromObject(model);const c2=b2.getCenter(new V3());
  model.position.set(-c2.x,-b2.min.y,-c2.z);car.remove(ph);car.add(model);car.updateMatrixWorld(true);
  size=new THREE.Box3().setFromObject(car).getSize(new V3());

  const seen=new Set();
  model.traverse(o=>{if(!o.isMesh)return;(Array.isArray(o.material)?o.material:[o.material]).forEach(m=>{if(seen.has(m))return;seen.add(m);
    if(CONFIG.paintRegex.test(m.name)){
      paint.push(m);
      m.color.setHex(0x020203);m.metalness=.9;m.roughness=.2;m.envMapIntensity=CONFIG.paintGloss;
      m.emissive&&m.emissive.setHex(0x061c66);
      if('clearcoat' in m){m.clearcoat=1;m.clearcoatRoughness=.05}
    }
    if(/headlight/i.test(m.name)){m.emissive.setHex(0x3cc8ff);m.emissiveIntensity=5;hl.push(m)}});});

  const grp=p=>{const a=[];model.traverse(o=>{if(o.name.startsWith(p)&&!(o.parent&&o.parent.name.startsWith(p)))a.push(o)});return a};
  const pivot=(nodes,hx,hy,hz)=>{const g=new THREE.Group();g.position.set(hx,hy,hz);car.add(g);g.updateMatrixWorld(true);nodes.forEach(n=>g.attach(n));return g};
  const sides={};grp('GEO_DOOR').forEach(n=>{const b=new THREE.Box3().setFromObject(n);if(b.isEmpty())return;const s=b.getCenter(new V3()).x<0?-1:1;(sides[s]=sides[s]||[]).push([n,b])});
  for(const s in sides){const B=new THREE.Box3();sides[s].forEach(([n,b])=>B.union(b));
    const g=pivot(sides[s].map(x=>x[0]),s>0?B.min.x:B.max.x,B.max.y,(B.min.z+B.max.z)/2);door.push({g,s:+s,x:g.position.x})}
  const hn=grp('GEO_HOOD');if(hn.length){const B=new THREE.Box3();hn.forEach(n=>B.expandByObject(n));hood.push(pivot(hn,(B.min.x+B.max.x)/2,B.max.y,B.min.z))}

  buildParts();
  ready=true;
}

/* ---- loader ---- */
const bar=$('#bar i'),pct=$('#pct');
function load(url){new THREE.GLTFLoader().load(url,g=>{build(g);const l=$('#ld');l.style.opacity=0;setTimeout(()=>l.remove(),900)},
  x=>{const p=clamp(x.lengthComputable?x.loaded/x.total:x.loaded/CONFIG.sizeHint);bar.style.width=p*100+'%';pct.textContent=Math.round(p*100)+'%'},
  e=>{console.error(e);$('#pick').style.display='block'})}
$('#file').onchange=e=>{const f=e.target.files[0];if(f){$('#pick').style.display='none';load(URL.createObjectURL(f))}};
load(CONFIG.modelUrl);

/* ---- paint swatches ---- */
let target=null;
CONFIG.colors.forEach(([n,h],i)=>{const b=document.createElement('button');b.style.background=h;b.title=n;b.setAttribute('aria-label',n);
  b.onclick=()=>{$$('#sws button').forEach(x=>x.classList.remove('on'));b.classList.add('on');$('#cn').textContent=n;target=new THREE.Color(h);
    paint.forEach(m=>{m.map=null;m.needsUpdate=true})};$('#sws').appendChild(b)});

/* ============================================================
   POST-PROCESSING SETUP
   ============================================================ */
let composer=null,bloom=null,fin=null;
if(canComposer){try{
  const FinalShader={
    uniforms:{tDiffuse:{value:null},uExp:{value:CONFIG.exposure},uTime:{value:0}},
    vertexShader:'varying vec2 vUv;void main(){vUv=uv;gl_Position=projectionMatrix*modelViewMatrix*vec4(position,1.);}',
    fragmentShader:'uniform sampler2D tDiffuse;uniform float uExp,uTime;varying vec2 vUv;'+
      'vec3 aces(vec3 x){return clamp((x*(2.51*x+.03))/(x*(2.43*x+.59)+.14),0.,1.);}'+
      'float hash(vec2 p){return fract(sin(dot(p,vec2(12.9898,78.233)))*43758.5453);}'+
      'void main(){vec2 c=vUv-.5;float d=dot(c,c);vec2 off=c*d*.0035;'+
      'vec3 col=vec3(texture2D(tDiffuse,vUv+off).r,texture2D(tDiffuse,vUv).g,texture2D(tDiffuse,vUv-off).b);'+
      'col=aces(col*uExp);col=pow(col,vec3(1./2.2));col=mix(col,col*col*(3.-2.*col),.4);'+
      'col*=1.-smoothstep(.12,.72,d)*.55;'+
      'col+=(hash(vUv*vec2(1920.,1080.)+uTime)-.5)*.022;'+
      'gl_FragColor=vec4(col,1.);}'
  };
  const rt=new THREE.WebGLRenderTarget(2,2,{type:THREE.HalfFloatType,format:THREE.RGBAFormat,minFilter:THREE.LinearFilter,magFilter:THREE.LinearFilter});
  composer=new THREE.EffectComposer(renderer,rt);
  composer.addPass(new THREE.RenderPass(scene,camera));
  bloom=new THREE.UnrealBloomPass(new THREE.Vector2(innerWidth,innerHeight),CONFIG.bloom,.5,1.0);
  composer.addPass(bloom);
  fin=new THREE.ShaderPass(FinalShader);composer.addPass(fin);
}catch(e){console.warn('Bloom pipeline disabled',e);composer=null;renderer.toneMapping=THREE.ACESFilmicToneMapping}}

/* ============================================================
   CAMERA KEYFRAMES  (sx = เลื่อนตัวรถไปซ้าย/ขวาบนจอ เพื่อหลบข้อความ)
   ============================================================ */
const KF=[
 {p:[2.2,1.2,6.8],  l:[5.5,.45,0],  y:-1.35,f:38,sx: .14},
 {p:[0.2,1.2,7.0],  l:[5.5,.5,0],   y:-.4,  f:40,sx:-.14},
 {p:[5.88,1.0,-.05],l:[5.88,.82,2.5],y:.2,  f:64,sx: 0,i:1},
 {p:[12.2,3.7,-6.4],l:[5.5,1.0,-.6],y:.2,   f:41,sx: .05},
 {p:[12.4,1.9,5.6], l:[5.5,.9,0],   y:.2,   f:44,sx:-.12},
 {p:[5.5,1.8,9.0],  l:[5.5,.5,0],   y:.5,   f:36,sx: .10}];

let S=0,mx=0,my=0;
const look=new V3(),pos=new V3(),v3=new V3(),clock=new THREE.Clock();
addEventListener('pointermove',e=>{mx=e.clientX/innerWidth-.5;my=e.clientY/innerHeight-.5});

/* ---- UI wiring ---- */
const goTo=i=>scrollTo({top:i*innerHeight,behavior:'smooth'});
$$('[data-go]').forEach(a=>a.addEventListener('click',()=>{goTo(+a.dataset.go);$('#sheet').classList.remove('open');$('#burger').setAttribute('aria-expanded','false')}));
$('#burger').onclick=e=>{const o=$('#sheet').classList.toggle('open');e.currentTarget.setAttribute('aria-expanded',o)};
SCENES.forEach((n,i)=>{const b=document.createElement('button');b.setAttribute('aria-label',n);b.innerHTML='<span>'+n+'</span>';b.onclick=()=>goTo(i);$('#dots').appendChild(b)});
const dots=$$('#dots button'),ovs=$$('.ov'),swEl=$('#sw'),hint=$('#hint'),progEl=$('#prog'),hud1=$('#hud1'),hud2=$('#hud2');
let lastScene=-1,lastPct=-1;

function resize(){
  renderer.setSize(innerWidth,innerHeight,false);
  camera.aspect=innerWidth/innerHeight;camera.updateProjectionMatrix();
  if(composer){composer.setPixelRatio(renderer.getPixelRatio());composer.setSize(innerWidth,innerHeight)}
}
addEventListener('resize',resize);resize();

/* ============================================================
   MAIN LOOP
   ============================================================ */
function frame(){
  requestAnimationFrame(frame);
  const dt=Math.min(clock.getDelta(),.05),t=clock.elapsedTime;
  progEl.style.width=clamp(scrollY/(innerHeight*5))*100+'%';
  if(scrollY>innerHeight*6.05)return;                    // เลื่อนพ้นฉาก 3D แล้ว ไม่ต้องเรนเดอร์

  S+=(scrollY/innerHeight-S)*(reduce?1:1-Math.exp(-dt*4));
  const i=Math.min(5,Math.floor(S)),j=Math.min(5,i+1),f=i>=5?0:ss(S-i),A=KF[i],B=KF[j];
  const m=(a,b)=>a.map((x,k)=>mix(x,b[k],f));
  const inside=mix(A.i||0,B.i||0,f),k=1+((innerWidth<innerHeight?1.6:1)-1)*(1-inside);
  look.fromArray(m(A.l,B.l));pos.fromArray(m(A.p,B.p));pos.sub(look).multiplyScalar(k).add(look);
  pos.x+=mx*.4*(1-inside);pos.y-=my*.15*(1-inside);
  camera.position.copy(pos);camera.lookAt(look);camera.fov=mix(A.fov||A.f,B.fov||B.f,f);
  const sx=innerWidth>innerHeight?mix(A.sx||0,B.sx||0,f):0;
  camera.setViewOffset(innerWidth,innerHeight,-sx*innerWidth,0,innerWidth,innerHeight);   // เลื่อนตัวรถบนจอ + อัปเดต projection
  camera.updateMatrixWorld();
  car.rotation.y=mix(A.y,B.y,f)+Math.PI*2*ss(S/1.9);

  /* ข้อความแต่ละฉาก */
  ovs.forEach(o=>{const d=S-o.dataset.i,a=clamp(1-Math.abs(d)*2.2);o.style.opacity=a;o.style.visibility=a<.01?'hidden':'visible';o.style.setProperty('--d',d.toFixed(3));o.style.setProperty('--b',(1-a).toFixed(3))});
  const sa=clamp((S-4.4)*2);swEl.style.opacity=sa;swEl.style.pointerEvents=sa>.5?'auto':'none';
  hint.style.opacity=clamp(1-S*4);
  const sc=Math.min(5,Math.round(S));
  if(sc!==lastScene){lastScene=sc;dots.forEach((b,n)=>b.classList.toggle('on',n===sc))}

  /* ระเบิดชิ้นส่วนออก แล้วประกอบกลับตอนเลื่อนผ่าน */
  const e=ss((S-2.2)/.8)*(1-ss((S-4.0)/.8));
  const ep=Math.round(e*100);
  if(ep!==lastPct){lastPct=ep;hud1.innerHTML='SCENE <b>0'+(sc+1)+'</b> / 06 · '+SCENES[sc];hud2.innerHTML='EXPLODE <b>'+String(ep).padStart(3,'0')+'%</b>'}

  /* พื้นหลังมีชีวิต */
  ring1.rotation.z=t*.06;ring2.rotation.z=-t*.04;
  dustG.rotation.y=t*.012;
  {const a=dg.attributes.position;for(let n=0;n<DN;n++){let y=a.getY(n)+dt*.18;if(y>9)y-=9;a.setY(n,y)}a.needsUpdate=true}
  under.intensity=6+e*12;
  if(bloom)bloom.strength=CONFIG.bloom+e*.1;
  if(fin)fin.uniforms.uTime.value=t%100;

  if(ready){
    door.forEach(d=>{d.g.rotation.z=d.s*e*1.3;d.g.position.x=d.x+d.s*e*.6;d.g.position.y=e*.4});
    hood.forEach(h=>{h.rotation.x=-e*1.2;h.position.y=e*.5});
    paint.forEach(p=>{p.emissiveIntensity=e*.03;if(target)p.color.lerp(target,.12)});
    partsLight.intensity=e*16;
    lineMat.opacity=.45*e;

    parts.forEach(p=>{
      const pe=ss(clamp(e*1.35-p.d));
      p.obj.visible=pe>.003;
      if(!p.obj.visible){p.line.visible=false;return}
      p.obj.position.lerpVectors(p.home,p.away,pe);
      if(!reduce)p.obj.position.y+=Math.sin(t*.9+p.ph)*.07*pe;
      p.obj.rotation.set(mix(p.rh[0],p.ra[0],pe),mix(p.rh[1],p.ra[1],pe)+(reduce?0:Math.sin(t*.45+p.ph)*.07*pe),mix(p.rh[2],p.ra[2],pe));
      p.obj.scale.setScalar(mix(p.s0,p.s1,pe));
      if(p.spin&&!reduce)p.spin.o.rotation[p.spin.a]+=dt*p.spin.v*(.25+pe);
      const ar=p.line.geometry.attributes.position;ar.setXYZ(1,p.obj.position.x,p.obj.position.y,p.obj.position.z);ar.needsUpdate=true;
      p.line.visible=pe>.02;
      if(p.el){
        p.obj.getWorldPosition(v3);v3.project(camera);
        const on=e>.6&&v3.z<1&&Math.abs(v3.x)<1.05&&Math.abs(v3.y)<1.05;
        p.el.classList.toggle('on',on);
        p.el.style.transform='translate('+((v3.x*.5+.5)*innerWidth).toFixed(1)+'px,'+((-v3.y*.5+.5)*innerHeight).toFixed(1)+'px)';
      }
    });
  }
  if(composer)composer.render();else renderer.render(scene,camera);
}
frame();

/* ============================================================
   CONTENT SECTION: reveal / count-up / card tilt
   ============================================================ */
function countUp(el){const to=+el.dataset.count,t0=performance.now();(function s(n){const p=clamp((n-t0)/1400);el.textContent=Math.round(to*(1-Math.pow(1-p,3))).toLocaleString();if(p<1)requestAnimationFrame(s)})(t0)}
const io=new IntersectionObserver(es=>es.forEach(en=>{if(!en.isIntersecting)return;const el=en.target;el.classList.add('in');el.querySelectorAll('[data-count]').forEach(countUp);io.unobserve(el)}),{threshold:.15});
$$('.rv').forEach(el=>io.observe(el));
if(!reduce&&matchMedia('(hover:hover)').matches){
  $$('.cv-card').forEach(c=>{
    c.addEventListener('pointermove',e=>{const r=c.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;c.style.setProperty('--ry',(x*7).toFixed(2)+'deg');c.style.setProperty('--rx',(-y*7).toFixed(2)+'deg')});
    c.addEventListener('pointerleave',()=>{c.style.setProperty('--ry','0deg');c.style.setProperty('--rx','0deg')});
  });
}
</script>
</body>
</html>