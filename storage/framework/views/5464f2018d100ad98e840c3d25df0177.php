
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
<script>
    tailwind.config = { darkMode: 'class', theme: { extend: {
        fontFamily: { sans: ['"Plus Jakarta Sans"','system-ui','sans-serif'], display: ['Sora','sans-serif'] },
        colors: {
            brand: { 50:'#f0f9fd',100:'#ddf0f9',200:'#bce3f2',300:'#8ccfe8',400:'#54b3d8',500:'#2ca0d4',600:'#2185b8',700:'#1d6d97',800:'#1b5a7c',900:'#194c68' },
            gold:  { 300:'#f0dca6',400:'#e7c884',500:'#d9b25f',600:'#c1974a' },
        },
    } } }
</script>
<style>
    :root{
        --bg:#eaf0f7; --panel:#ffffff; --panel-2:#f7fafd;
        --border:rgba(12,42,64,.09); --text:#102636; --muted:#5f7488;
        --teal:#2ca0d4; --teal-deep:#1b5a7c; --gold:#d9b25f;
        --ring:rgba(44,160,212,.30); --shadow:0 10px 34px -14px rgba(9,45,74,.28);
        --shadow-sm:0 2px 10px -4px rgba(9,45,74,.16);
    }
    .dark{
        --bg:#060a12; --panel:rgba(255,255,255,.04); --panel-2:rgba(255,255,255,.02);
        --border:rgba(255,255,255,.08); --text:#e8eff7; --muted:#93a5b9;
        --ring:rgba(44,160,212,.40); --shadow:0 18px 48px -18px rgba(0,0,0,.7); --shadow-sm:0 6px 20px -8px rgba(0,0,0,.5);
    }
    *{ -webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility; }
    body{
        font-family:'Plus Jakarta Sans',system-ui,sans-serif;
        color:var(--text);
        background:
            radial-gradient(1100px 560px at 100% -8%, rgba(44,160,212,.14), transparent 58%),
            radial-gradient(820px 460px at -8% 108%, rgba(27,90,124,.16), transparent 55%),
            radial-gradient(600px 400px at 50% 120%, rgba(217,178,95,.06), transparent 60%),
            var(--bg);
        background-attachment: fixed;
    }
    h1,h2,h3,h4,.font-display{ font-family:'Sora',sans-serif; letter-spacing:-.015em; }

    /* Cards */
    .lux-card{
        background:var(--panel);
        border:1px solid var(--border);
        border-radius:1.15rem;
        box-shadow:var(--shadow);
        backdrop-filter:blur(14px) saturate(1.1);
        -webkit-backdrop-filter:blur(14px) saturate(1.1);
        position:relative;
        transition:transform .28s cubic-bezier(.2,.7,.3,1), box-shadow .28s, border-color .28s;
    }
    .lux-card::before{
        content:""; position:absolute; inset:0; border-radius:inherit; pointer-events:none;
        background:linear-gradient(180deg, rgba(255,255,255,.35), transparent 42%);
        opacity:.5; mix-blend-mode:overlay;
    }
    .dark .lux-card::before{ background:linear-gradient(180deg, rgba(255,255,255,.08), transparent 40%); }
    .lux-hover:hover{ transform:translateY(-3px); box-shadow:0 22px 50px -18px rgba(9,45,74,.34); border-color:rgba(44,160,212,.4); }

    /* Fields */
    .lux-field{
        width:100%; border-radius:.85rem; border:1px solid var(--border);
        background:var(--panel-2); color:var(--text);
        transition:border-color .2s, box-shadow .2s, background .2s;
    }
    .lux-field::placeholder{ color:var(--muted); opacity:.7; }
    .lux-field:focus{ outline:none; border-color:var(--teal); box-shadow:0 0 0 4px var(--ring); background:var(--panel); }
    select.lux-field option{ color:#102636; }

    /* Buttons */
    .lux-btn{
        background:linear-gradient(135deg,#43b4e0,#2ca0d4 45%,#1b6d97);
        color:#fff; border-radius:.85rem; font-weight:600;
        box-shadow:0 10px 24px -10px rgba(44,160,212,.65), inset 0 1px 0 rgba(255,255,255,.28);
        transition:transform .18s, box-shadow .2s, filter .2s; border:0;
    }
    .lux-btn:hover{ transform:translateY(-1px); filter:brightness(1.06); box-shadow:0 16px 30px -10px rgba(44,160,212,.75), inset 0 1px 0 rgba(255,255,255,.28); }
    .lux-btn:active{ transform:translateY(0); }
    .lux-btn:disabled{ filter:grayscale(.4) brightness(.9); opacity:.6; cursor:not-allowed; }

    .lux-ghost{
        border:1px solid var(--border); background:var(--panel-2); color:var(--text);
        border-radius:.85rem; font-weight:600; transition:border-color .2s, background .2s, transform .18s;
    }
    .lux-ghost:hover{ border-color:rgba(44,160,212,.5); background:var(--panel); transform:translateY(-1px); }

    /* Sidebar (always luxe-dark) */
    .lux-sidebar{
        background:
            radial-gradient(600px 300px at 20% -10%, rgba(44,160,212,.28), transparent 60%),
            linear-gradient(185deg,#0e2f45 0%,#0c2438 45%,#081726 100%);
        border-right:1px solid rgba(255,255,255,.06);
    }
    .lux-nav{ color:#a9c2d3; border:1px solid transparent; transition:all .2s; position:relative; }
    .lux-nav:hover{ color:#fff; background:rgba(255,255,255,.06); }
    .lux-nav.active{
        color:#fff;
        background:linear-gradient(135deg, rgba(44,160,212,.28), rgba(44,160,212,.08));
        border-color:rgba(44,160,212,.35);
        box-shadow:0 8px 22px -12px rgba(44,160,212,.7);
    }
    .lux-nav.active::before{
        content:""; position:absolute; left:-1px; top:50%; transform:translateY(-50%);
        width:3px; height:60%; border-radius:3px; background:linear-gradient(#e7c884,#d9b25f);
    }

    /* Topbar glass */
    .lux-topbar{
        background:color-mix(in srgb, var(--panel) 82%, transparent);
        backdrop-filter:blur(16px) saturate(1.2); -webkit-backdrop-filter:blur(16px) saturate(1.2);
        border-bottom:1px solid var(--border);
    }

    /* Tables */
    .lux-card thead{ background:linear-gradient(180deg, rgba(44,160,212,.10), rgba(44,160,212,.02)); }
    .lux-card tbody tr{ transition:background .18s; }

    /* Gold accent text */
    .text-gold{ color:var(--gold); }
    .lux-divider{ height:1px; background:linear-gradient(90deg,transparent,var(--border),transparent); }

    /* Scrollbars */
    *::-webkit-scrollbar{ width:10px; height:10px; }
    *::-webkit-scrollbar-thumb{ background:linear-gradient(#2ca0d4,#1b5a7c); border-radius:20px; border:2px solid transparent; background-clip:padding-box; }
    *::-webkit-scrollbar-track{ background:transparent; }

    @keyframes lux-rise{ from{ opacity:0; transform:translateY(10px); } to{ opacity:1; transform:none; } }
    .lux-rise{ animation:lux-rise .5s cubic-bezier(.2,.7,.3,1) both; }
    [x-cloak]{ display:none!important; }
</style>
<?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/partials/theme.blade.php ENDPATH**/ ?>