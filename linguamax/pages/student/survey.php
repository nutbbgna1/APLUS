<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ภารกิจนักสำรวจแห่งการเรียนรู้</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Itim&family=Mali:wght@400;600;700&family=Bai+Jamjuree:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* ============================================================
   ปรับแต่งง่ายๆ ตรงนี้ได้เลยค่ะ
   ============================================================ */
:root{
  --ink:#2B3A67;          /* สีตัวอักษรหลัก (น้ำเงินหมึก) */
  --ink-soft:#6B7AA1;
  --cream:#FFF6E3;        /* พื้นหลังกระดาษ */
  --paper:#FFFFFF;
  --mango:#FFB627;
  --coral:#FF6B6B;
  --mint:#5FCF98;
  --sky:#4EA5D9;
  --grape:#A277D6;
  --shadow:0 6px 0 rgba(43,58,103,.14);
  --wobble:255px 15px 225px 15px / 15px 225px 15px 255px;
  --wobble2:15px 225px 15px 255px / 225px 15px 255px 15px;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  font-family:'Bai Jamjuree','Mali',system-ui,sans-serif;
  color:var(--ink);
  background-color:var(--cream);
  background-image:
    radial-gradient(circle at 1px 1px, rgba(43,58,103,.10) 1px, transparent 0);
  background-size:26px 26px;
  min-height:100vh;
  padding:20px 16px 64px;
  overflow-x:hidden;
}
/* ก้อนเมฆพื้นหลัง */
.blob{position:fixed;border-radius:50%;filter:blur(50px);opacity:.35;z-index:-1;pointer-events:none}
.blob-a{width:340px;height:340px;background:var(--mango);top:-120px;left:-100px}
.blob-b{width:300px;height:300px;background:var(--sky);bottom:-110px;right:-90px}
.blob-c{width:240px;height:240px;background:var(--mint);top:45%;right:-120px}

.wrap{max-width:720px;margin:0 auto}

/* ---------- ป้ายหัวเรื่อง ---------- */
.hero{text-align:center;padding:28px 0 10px}
.badge{
  display:inline-block;font-family:'Bai Jamjuree',sans-serif;font-weight:700;
  font-size:.8rem;letter-spacing:.12em;background:var(--ink);color:var(--mango);
  padding:7px 18px;border-radius:99px;margin-bottom:18px;
}
h1{
  font-family:'Itim',cursive;font-weight:400;line-height:1.15;
  font-size:clamp(2.2rem,8vw,3.6rem);
  text-shadow:3px 3px 0 var(--mango), 6px 6px 0 rgba(43,58,103,.12);
}
h1 .line2{display:block;color:var(--coral);text-shadow:3px 3px 0 #fff, 6px 6px 0 rgba(43,58,103,.12)}
.hero p{font-family:'Mali',cursive;margin:20px auto 0;max-width:30rem;font-size:1.05rem;line-height:1.9;color:var(--ink-soft)}

/* ---------- แถบตราปั๊ม ---------- */
.passport{
  display:flex;align-items:center;justify-content:center;gap:6px;
  margin:26px auto 22px;flex-wrap:nowrap;
}
.stamp{
  width:52px;height:52px;flex:none;border-radius:50%;
  border:3px dashed rgba(43,58,103,.3);background:rgba(255,255,255,.7);
  display:grid;place-items:center;font-size:1.4rem;
  color:transparent;transition:transform .35s cubic-bezier(.3,1.6,.5,1),background .3s,border-color .3s;
}
.stamp.done{
  border:3px solid var(--ink);background:var(--mango);color:var(--ink);
  transform:rotate(-8deg) scale(1.06);
}
.stamp.now{border-color:var(--coral);background:#fff;animation:pulse 1.6s ease-in-out infinite}
@keyframes pulse{50%{transform:scale(1.12)}}
.track{width:22px;height:4px;border-radius:99px;background:rgba(43,58,103,.2);flex:none}
.track.done{background:var(--ink)}

/* ---------- การ์ดหลัก ---------- */
.card{
  background:var(--paper);border:3px solid var(--ink);
  border-radius:var(--wobble);
  padding:32px 26px;box-shadow:var(--shadow);
}
.card:nth-of-type(even){border-radius:var(--wobble2)}
.screen{display:none}
.screen.active{display:block;animation:pop .45s cubic-bezier(.2,1.3,.4,1)}
@keyframes pop{from{opacity:0;transform:translateY(18px) scale(.97)}}

.eyebrow{
  font-family:'Bai Jamjuree',sans-serif;font-weight:700;font-size:.85rem;
  letter-spacing:.1em;color:var(--coral);margin-bottom:6px;
}
.sec-title{font-family:'Itim',cursive;font-size:1.9rem;line-height:1.3;margin-bottom:4px}
.sec-sub{font-family:'Mali',cursive;color:var(--ink-soft);font-size:.95rem;margin-bottom:26px}

/* ---------- คำถาม ---------- */
.q{margin-bottom:30px}
.q-head{display:flex;gap:12px;align-items:flex-start;margin-bottom:14px}
.q-num{
  flex:none;width:34px;height:34px;border-radius:50%;background:var(--ink);color:#fff;
  display:grid;place-items:center;font-family:'Bai Jamjuree',sans-serif;font-weight:700;font-size:.95rem;
}
.q-text{font-family:'Mali',cursive;font-weight:600;font-size:1.15rem;line-height:1.6;padding-top:3px}
.q-hint{font-family:'Bai Jamjuree',sans-serif;font-size:.82rem;color:var(--ink-soft);margin:-8px 0 12px 46px}

.opts{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px}
.opts.wide{grid-template-columns:1fr}
.opt{
  position:relative;display:flex;align-items:center;gap:10px;
  background:#fff;border:2.5px solid rgba(43,58,103,.22);border-radius:16px;
  padding:13px 14px;cursor:pointer;font-family:'Mali',cursive;font-size:.98rem;line-height:1.4;
  transition:transform .18s,border-color .18s,background .18s,box-shadow .18s;
  -webkit-tap-highlight-color:transparent;
}
.opt .emo{font-size:1.35rem;flex:none}
.opt input{position:absolute;opacity:0;width:1px;height:1px}
.opt:hover{transform:translateY(-3px);border-color:var(--ink)}
.opt input:focus-visible + .emo{outline:3px solid var(--sky);outline-offset:4px;border-radius:6px}
.opt.picked{
  border-color:var(--ink);box-shadow:0 4px 0 var(--ink);
  transform:translateY(-3px) rotate(-1deg);
}
.opt.picked::after{
  content:'✓';position:absolute;top:-10px;right:-8px;width:26px;height:26px;
  background:var(--ink);color:#fff;border-radius:50%;display:grid;place-items:center;
  font-size:.85rem;font-weight:700;
}
.c1.picked{background:#FFF1D6}.c2.picked{background:#FFE3E3}
.c3.picked{background:#DEF7EA}.c4.picked{background:#DDEEFA}
.c5.picked{background:#EFE4FB}

input[type=text],textarea{
  width:100%;font-family:'Mali',cursive;font-size:1.25rem;color:var(--ink);
  background:#fff;border:2.5px solid rgba(43,58,103,.22);border-radius:16px;
  padding:16px 20px;transition:border-color .2s,box-shadow .2s;
}
textarea{min-height:120px;resize:vertical;line-height:1.8}
input[type=text]:focus,textarea:focus{outline:none;border-color:var(--ink);box-shadow:0 4px 0 var(--ink)}
::placeholder{color:#B9C0D4}

/* ---------- ปุ่ม ---------- */
.nav{display:flex;gap:12px;align-items:center;justify-content:space-between;margin-top:34px}
.btn{
  font-family:'Itim',cursive;font-size:1.15rem;border:3px solid var(--ink);
  background:var(--mango);color:var(--ink);padding:13px 30px;border-radius:99px;
  cursor:pointer;box-shadow:0 5px 0 var(--ink);transition:transform .12s,box-shadow .12s;
}
.btn:hover{transform:translateY(2px);box-shadow:0 3px 0 var(--ink)}
.btn:active{transform:translateY(5px);box-shadow:0 0 0 var(--ink)}
.btn:focus-visible{outline:3px solid var(--sky);outline-offset:4px}
.btn-ghost{background:#fff;box-shadow:0 5px 0 rgba(43,58,103,.25);border-color:rgba(43,58,103,.35);color:var(--ink-soft);font-size:1rem;padding:12px 22px}
.btn-big{width:100%;font-size:1.3rem;padding:16px}
.btn-coral{background:var(--coral);color:#fff}

.nudge{
  font-family:'Mali',cursive;color:var(--coral);font-size:.95rem;
  margin-top:16px;display:none;
}
.nudge.show{display:block;animation:shake .4s}
@keyframes shake{25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}

/* ---------- หน้าจบ ---------- */
.finish{text-align:center}
.trophy{font-size:5rem;display:block;animation:bounce 1.6s ease-in-out infinite}
@keyframes bounce{50%{transform:translateY(-14px) rotate(6deg)}}
.summary{
  text-align:left;background:var(--cream);border:2.5px dashed rgba(43,58,103,.3);
  border-radius:18px;padding:20px;margin:24px 0;font-family:'Bai Jamjuree',sans-serif;font-size:.9rem;
  max-height:280px;overflow:auto;
}
.summary dt{font-weight:700;margin-top:12px}
.summary dt:first-child{margin-top:0}
.summary dd{color:var(--ink-soft);margin-top:2px}

.confetti{position:fixed;top:-20px;width:12px;height:12px;z-index:99;pointer-events:none;animation:fall linear forwards}
@keyframes fall{to{transform:translateY(105vh) rotate(720deg);opacity:0}}

.foot{text-align:center;font-family:'Bai Jamjuree',sans-serif;font-size:.8rem;color:var(--ink-soft);margin-top:26px}

@media (max-width:560px){
  .card{padding:26px 18px}
  .stamp{width:42px;height:42px;font-size:1.15rem}
  .track{width:12px}
  .nav{flex-direction:column-reverse}
  .nav .btn{width:100%}
}
@media (prefers-reduced-motion:reduce){
  *,*::before,*::after{animation:none!important;transition:none!important}
}
</style>
</head>
<body>
<div class="blob blob-a"></div><div class="blob blob-b"></div><div class="blob blob-c"></div>

<div class="wrap">

  <!-- ========== หน้าต้อนรับ ========== -->
  <section class="screen active" id="s0">
    <div class="hero">
      <span class="badge">แบบสอบถามนักเรียน</span>
      <h1>ภารกิจนักสำรวจ<span class="line2">แห่งการเรียนรู้</span></h1>
      <p>สวัสดีจ้า นักสำรวจตัวน้อย! 🕵️ ครูกำลังสร้างเว็บไซต์สุดเจ๋งให้ทุกคนได้เล่นและเรียนไปพร้อมกัน มาช่วยครูออกแบบกันเถอะ ตอบตามใจได้เลย ไม่มีถูกไม่มีผิดนะ</p>
    </div>
    <div class="card">
      <p style="font-family:'Mali',cursive;text-align:center;line-height:2;margin-bottom:22px">
        มี <b>4 ด่าน</b> ทั้งหมด <b>10 คำถาม</b><br>
        ใช้เวลาแค่ประมาณ 5 นาทีเอง ⏱️<br>
        ตอบครบทุกด่านจะได้รับ <b>เหรียญนักสำรวจ</b> 🏅
      </p>
      <div style="display:flex; flex-direction:column; gap:12px;">
          <button class="btn btn-big" onclick="go(1)">เริ่มภารกิจ! 🚀</button>
          <button class="btn btn-ghost" onclick="window.location.href='?page=dashboard'">← กลับหน้า Dashboard</button>
      </div>
    </div>
  </section>

  <!-- ========== แถบตราปั๊ม ========== -->
  <div class="passport" id="passport" style="display:none">
    <div class="stamp" data-s="1">🧒</div><div class="track" data-t="1"></div>
    <div class="stamp" data-s="2">✨</div><div class="track" data-t="2"></div>
    <div class="stamp" data-s="3">🎮</div><div class="track" data-t="3"></div>
    <div class="stamp" data-s="4">🌈</div>
  </div>

  <!-- ========== ด่านที่ 1–4 (สร้างด้วย JS) ========== -->
  <div id="sections"></div>

  <!-- ========== หน้าจบ ========== -->
  <section class="screen" id="sEnd">
    <div class="card finish">
      <span class="trophy">🏅</span>
      <h2 style="font-family:'Itim',cursive;font-size:2rem;margin:12px 0 6px">ภารกิจสำเร็จ!</h2>
      <p style="font-family:'Mali',cursive;color:var(--ink-soft);line-height:1.9">
        ขอบคุณมากนะ นักสำรวจ! 💛<br>ครูจะเอาไอเดียของหนูไปสร้างเว็บให้สนุกที่สุดเลย
      </p>
      <dl class="summary" id="summary"></dl>
      <div style="display:flex; flex-direction:column; gap:12px;">
          <button class="btn btn-big btn-coral" id="sendBtn" onclick="submitAnswers()">ส่งคำตอบให้ครู 📨</button>
          <button class="btn btn-ghost" onclick="go(SECTIONS.length - 1)">← กลับไปแก้ไขข้อมูล</button>
      </div>
      <p class="nudge" id="sendMsg" style="color:var(--mint)"></p>
    </div>
    <p class="foot">แบบสอบถามนี้ไม่เก็บข้อมูลส่วนตัวอื่นใดนอกจากที่หนูพิมพ์เอง</p>
  </section>

</div>

<script>
/* ============================================================
   ⚙️ ตั้งค่าที่นี่
   submitUrl : ใส่ลิงก์ปลายทางที่จะรับคำตอบ เช่น Google Apps Script
               web app หรือ Formspree  ถ้าเว้นว่าง = ดาวน์โหลดเป็นไฟล์แทน
   ============================================================ */
const CONFIG = { submitUrl: window.location.href.split('?')[0].replace('index.php', 'api/survey.php') };

/* ============================================================
   📝 คำถามทั้งหมด — แก้ไข/เพิ่ม/ลบได้ตรงนี้เลย
   type: 'text' | 'textarea' | 'one' (เลือกได้ 1) | 'many' (เลือกได้หลายข้อ)
   ============================================================ */
const SECTIONS = [
 {
  eyebrow:"ด่านที่ 1 จาก 4", title:"รู้จักนักสำรวจ 🧒",
  sub:"บอกครูหน่อยว่าหนูเป็นใครเอ่ย",
  qs:[
   {id:"nickname", n:1, type:"text", q:"ชื่อเล่นของหนูคืออะไรเอ่ย?", ph:"พิมพ์ชื่อเล่นตรงนี้..."},
   {id:"grade", n:2, type:"one", q:"หนูอยู่ชั้นไหนนะ?",
    opts:[["🌱","ป.1"],["🌿","ป.2"],["🍀","ป.3"],["🌳","ป.4"],["⭐","ป.5"],["🌟","ป.6"]]}
  ]
 },
 {
  eyebrow:"ด่านที่ 2 จาก 4", title:"พลังวิเศษของหนู ✨",
  sub:"วิชาไหนคือพลังของหนู แล้ววิชาไหนที่อยากเก่งขึ้น",
  qs:[
   {id:"fav", n:3, type:"many", q:"วิชาไหนที่หนูรู้สึกว่า “สนุกที่สุด!” 😄", hint:"เลือกได้หลายข้อเลยนะ",
    opts:[["➕","คณิตศาสตร์"],["📖","ภาษาไทย"],["🔤","ภาษาอังกฤษ"],["🔬","วิทยาศาสตร์"],["🎨","ศิลปะ"],["🎵","ดนตรี"],["⚽","พละ"],["🌏","สังคม"]]},
   {id:"hard", n:4, type:"many", q:"วิชาไหนที่หนูอยากเก่งขึ้น แต่รู้สึกว่ายากจัง 🤔", hint:"เลือกได้หลายข้อ ไม่ต้องอาย ครูจะช่วยเอง",
    opts:[["➕","คณิตศาสตร์"],["📖","ภาษาไทย"],["🔤","ภาษาอังกฤษ"],["🔬","วิทยาศาสตร์"],["🎨","ศิลปะ"],["🎵","ดนตรี"],["⚽","พละ"],["🌏","สังคม"]]},
   {id:"helper", n:5, type:"one", q:"ถ้ามี “ตัวช่วยวิเศษ” ในเว็บ หนูอยากได้อะไรมากที่สุด?",
    opts:[["📺","วิดีโอการ์ตูนสอนบทเรียน"],["🎯","เกมฝึกทำโจทย์"],["🎧","นิทานเสียงให้ฟัง"],["🎁","แบบฝึกหัดที่มีรางวัล"],["🤖","ครูตัวการ์ตูนคอยตอบคำถาม"],["🖍️","ใบงานให้ปริ้นไปทำ"]]}
  ]
 },
 {
  eyebrow:"ด่านที่ 3 จาก 4", title:"โลกแห่งเกม 🎮",
  sub:"ด่านนี้สนุกแน่! บอกครูว่าหนูชอบเกมแบบไหน",
  qs:[
   {id:"gametype", n:6, type:"many", q:"หนูชอบเกมแนวไหนมากที่สุด?", hint:"เลือกได้หลายข้อ",
    opts:[["🏆","ตอบคำถามแข่งกับเพื่อน"],["🗺️","ผจญภัยเก็บแต้ม"],["🧩","จับคู่ / จิ๊กซอว์"],["👗","แต่งตัว แต่งห้อง"],["🌻","ปลูกผัก เลี้ยงสัตว์"],["🖌️","วาดรูป ระบายสี"],["🏃","วิ่งเก็บของ"],["🕵️","สืบสวนหาคำตอบ"]]},
   {id:"reward", n:7, type:"many", q:"ถ้าเล่นเกมชนะ หนูอยากได้รางวัลแบบไหน? 🏆", hint:"เลือกได้หลายข้อ",
    opts:[["⭐","เหรียญ / ดาวสะสม"],["🐣","ตัวการ์ตูนใหม่"],["📋","ชื่อขึ้นกระดานคนเก่ง"],["✨","สติกเกอร์น่ารัก"],["🎀","ของแต่งตัวให้ตัวละคร"],["🔓","ปลดล็อกด่านลับ"]]},
   {id:"play", n:8, type:"one", q:"หนูชอบเล่นคนเดียว หรือเล่นกับเพื่อน?",
    opts:[["🧘","คนเดียวชิลๆ"],["🤝","แข่งกับเพื่อนมันส์กว่า"],["💫","ชอบทั้งสองแบบ"]]}
  ]
 },
 {
  eyebrow:"ด่านสุดท้าย", title:"คำถามจากจินตนาการ 🌈",
  sub:"ปล่อยจินตนาการได้เต็มที่เลย ไม่มีคำตอบผิดนะ",
  qs:[
   {id:"dream", n:9, type:"textarea", q:"ถ้าหนูออกแบบเกมเองได้ 1 เกม หนูอยากให้เป็นเกมเกี่ยวกับอะไร?",
    ph:"เช่น เกมขี่มังกรตอบเลข เกมทำอาหารกับเพื่อน..."},
   {id:"note", n:10, type:"textarea", optional:true, q:"อยากบอกอะไรครูอีกไหมเอ่ย? กระซิบมาได้เลย! 💌",
    ph:"ข้อนี้ไม่ตอบก็ได้นะ"}
  ]
 }
];

/* ============================ ระบบ ============================ */
const answers = {};
let current = 0;
const colors = ["c1","c2","c3","c4","c5"];

function build(){
  const host = document.getElementById("sections");
  SECTIONS.forEach((sec, i) => {
    const el = document.createElement("section");
    el.className = "screen"; el.id = "s" + (i+1);
    let html = `<div class="card">
      <div class="eyebrow">${sec.eyebrow}</div>
      <h2 class="sec-title">${sec.title}</h2>
      <p class="sec-sub">${sec.sub}</p>`;

    sec.qs.forEach(q => {
      html += `<div class="q" data-q="${q.id}">
        <div class="q-head"><div class="q-num">${q.n}</div><div class="q-text">${q.q}</div></div>`;
      if(q.hint) html += `<div class="q-hint">${q.hint}</div>`;

      if(q.type === "text"){
        html += `<input type="text" id="in_${q.id}" placeholder="${q.ph||""}" oninput="answers['${q.id}']=this.value">`;
      } else if(q.type === "textarea"){
        html += `<textarea id="in_${q.id}" placeholder="${q.ph||""}" oninput="answers['${q.id}']=this.value"></textarea>`;
      } else {
        const many = q.type === "many";
        html += `<div class="opts${q.opts.length<4?' wide':''}">`;
        q.opts.forEach((o, k) => {
          html += `<label class="opt ${colors[k%5]}">
            <input type="${many?'checkbox':'radio'}" name="${q.id}" value="${o[1]}"
                   onchange="pick(this,${many})">
            <span class="emo">${o[0]}</span><span>${o[1]}</span></label>`;
        });
        html += `</div>`;
      }
      html += `</div>`;
    });

    html += `<p class="nudge" id="nudge${i+1}">อุ๊ปส์! ยังตอบไม่ครบทุกข้อนะ ลองดูอีกที 👀</p>
      <div class="nav">
        ${i===0?'<span></span>':`<button class="btn btn-ghost" onclick="go(${i})">← ย้อนกลับ</button>`}
        <button class="btn" onclick="next(${i})">${i===SECTIONS.length-1?'ส่งภารกิจ! 🎉':'ไปต่อ →'}</button>
      </div></div>`;
    el.innerHTML = html;
    host.appendChild(el);
  });
}

function pick(input, many){
  const q = input.closest(".q");
  if(!many) q.querySelectorAll(".opt").forEach(o => o.classList.remove("picked"));
  input.closest(".opt").classList.toggle("picked", input.checked);
  const id = input.name;
  answers[id] = many
    ? [...q.querySelectorAll("input:checked")].map(x => x.value)
    : input.value;
}

function go(n){
  document.querySelectorAll(".screen").forEach(s => s.classList.remove("active"));
  document.getElementById(n === "end" ? "sEnd" : "s" + n).classList.add("active");
  document.getElementById("passport").style.display =
    (n === 0) ? "none" : "flex";
  current = n;
  paintStamps(n);
  window.scrollTo({top:0, behavior:"smooth"});
}

function paintStamps(n){
  const done = n === "end" ? 5 : n;
  document.querySelectorAll(".stamp").forEach(s => {
    const i = +s.dataset.s;
    s.classList.toggle("done", i < done);
    s.classList.toggle("now", i === done);
  });
  document.querySelectorAll(".track").forEach(t =>
    t.classList.toggle("done", +t.dataset.t < done));
}

function next(i){
  const sec = SECTIONS[i];
  const missing = sec.qs.filter(q => {
    if(q.optional) return false;
    const a = answers[q.id];
    return !a || (Array.isArray(a) && !a.length) || (typeof a === "string" && !a.trim());
  });
  const nudge = document.getElementById("nudge" + (i+1));
  if(missing.length){
    nudge.classList.add("show");
    document.querySelector(`[data-q="${missing[0].id}"]`)
      .scrollIntoView({behavior:"smooth", block:"center"});
    return;
  }
  nudge.classList.remove("show");
  if(i === SECTIONS.length - 1){ finish(); } else { go(i+2); }
}

function finish(){
  const dl = document.getElementById("summary");
  dl.innerHTML = "";
  SECTIONS.flatMap(s => s.qs).forEach(q => {
    const a = answers[q.id];
    const txt = Array.isArray(a) ? a.join(", ") : (a || "—");
    dl.insertAdjacentHTML("beforeend",
      `<dt>${q.n}. ${q.q}</dt><dd>${txt || "—"}</dd>`);
  });
  go("end");
  confetti();
}

function confetti(){
  if(window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  const cols = ["#FFB627","#FF6B6B","#5FCF98","#4EA5D9","#A277D6"];
  for(let i=0;i<70;i++){
    const c = document.createElement("div");
    c.className = "confetti";
    c.style.left = Math.random()*100 + "vw";
    c.style.background = cols[i%5];
    c.style.borderRadius = Math.random()>.5 ? "50%" : "3px";
    c.style.animationDuration = (2.2 + Math.random()*2) + "s";
    c.style.animationDelay = (Math.random()*1.2) + "s";
    document.body.appendChild(c);
    setTimeout(() => c.remove(), 6000);
  }
}

async function submitAnswers(){
  const btn = document.getElementById("sendBtn");
  const msg = document.getElementById("sendMsg");
  const payload = {...answers, submittedAt:new Date().toISOString()};
  btn.disabled = true; btn.textContent = "กำลังส่ง...";

  if(CONFIG.submitUrl){
    try{
      const formData = new FormData();
      formData.append('payload', JSON.stringify(payload));
      
      const res = await fetch(CONFIG.submitUrl, {
        method:"POST",
        body: formData
      });
      if (!res.ok) {
        let errStr = "API Error";
        try { const errObj = await res.json(); if(errObj.error) errStr = errObj.error; } catch(ex){}
        throw new Error(errStr);
      }
      btn.textContent = "ส่งเรียบร้อยแล้ว ✅";
      msg.textContent = "ครูได้รับคำตอบของหนูแล้วนะ ขอบคุณจ้า 💛 (+50 XP)";
      msg.classList.add("show");
      
      // Redirect to dashboard
      setTimeout(() => {
          window.location.href = '?page=dashboard';
      }, 2500);
    }catch(e){
      btn.disabled = false; btn.textContent = "ลองส่งอีกครั้ง 🔁";
      msg.style.color = "var(--coral)";
      msg.textContent = "ส่งไม่สำเร็จ: " + e.message;
      msg.classList.add("show");
    }
  } else {
    const blob = new Blob([JSON.stringify(payload,null,2)], {type:"application/json"});
    const a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = `คำตอบ-${answers.nickname||"นักสำรวจ"}.json`;
    a.click(); URL.revokeObjectURL(a.href);
    btn.textContent = "บันทึกเรียบร้อย ✅";
    msg.textContent = "ไฟล์คำตอบถูกดาวน์โหลดแล้ว ส่งให้ครูได้เลย 💛";
    msg.classList.add("show");
  }
}

build();
</script>
</body>
</html>
