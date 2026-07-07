<?php

declare(strict_types=1);

/**
 * Self-contained web demo for sdo-sa/hijri-converter.
 *
 * It renders an interactive converter and — on `?api=1` — runs the *real*
 * SDOSA\Hijri library server-side and returns JSON. So the page genuinely
 * exercises the package, not a JavaScript re-implementation.
 *
 *     php -S localhost:8000 -t examples/web
 *     open http://localhost:8000
 */

require __DIR__ . '/../../vendor/autoload.php';

use SDOSA\Data\UmmAlQura;
use SDOSA\Exceptions\HijriException;
use SDOSA\GregorianDate;
use SDOSA\HijriDate;

const LOCALES = ['ar' => 'العربية', 'en' => 'English', 'bn' => 'বাংলা', 'tr' => 'Türkçe'];

/** Flatten a value object into the shape the front-end renders. */
function describe(HijriDate|GregorianDate $date, string $locale): array
{
    return [
        'iso' => $date->toIso(),
        'formatted' => $date->format(),
        'year' => $date->year(),
        'month' => $date->month(),
        'day' => $date->day(),
        'monthName' => $date->monthName($locale),
        'dayName' => $date->dayName($locale),
        'notation' => $date->notation($locale),
        'long' => $date->longFormat($locale),
        'weekday' => $date->weekday(),
        'kind' => $date instanceof HijriDate ? 'hijri' : 'gregorian',
    ];
}

// --- JSON API -------------------------------------------------------------
if (isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');

    // Read a query param as a plain int, treating arrays/missing values as 0.
    $int = static fn (string $key): int => is_scalar($_GET[$key] ?? null) ? (int) $_GET[$key] : 0;

    try {
        $localeParam = $_GET['locale'] ?? '';
        $locale = is_string($localeParam) && array_key_exists($localeParam, LOCALES) ? $localeParam : 'en';
        $dir = ($_GET['dir'] ?? 'g2h') === 'h2g' ? 'h2g' : 'g2h';
        $y = $int('y');
        $m = $int('m');
        $d = $int('d');

        $source = $dir === 'h2g' ? HijriDate::make($y, $m, $d) : GregorianDate::make($y, $m, $d);
        $target = $source instanceof HijriDate ? $source->toGregorian() : $source->toHijri();

        echo json_encode(
            ['ok' => true, 'source' => describe($source, $locale), 'target' => describe($target, $locale)],
            JSON_UNESCAPED_UNICODE
        );
    } catch (HijriException $e) {
        // Expected validation error — safe to surface the message.
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    } catch (\Throwable $e) {
        // Anything else (e.g. an absurd out-of-int-range year) — stay generic, never leak internals.
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid date input.'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// --- Data baked into the page ---------------------------------------------
[$gMin, $gMax] = UmmAlQura::GREGORIAN_RANGE;
[$hMin, $hMax] = UmmAlQura::HIJRI_RANGE;

$monthNames = [];
foreach (array_keys(LOCALES) as $loc) {
    $monthNames[$loc] = ['hijri' => [], 'gregorian' => []];
    for ($i = 1; $i <= 12; $i++) {
        $monthNames[$loc]['hijri'][] = HijriDate::make(1445, $i, 1)->monthName($loc);
        $monthNames[$loc]['gregorian'][] = GregorianDate::make(2024, $i, 1)->monthName($loc);
    }
}

$todayG = GregorianDate::today();
$todayH = $todayG->toHijri();

$boot = [
    'gregorianRange' => [sprintf('%04d-%02d-%02d', ...$gMin), sprintf('%04d-%02d-%02d', ...$gMax)],
    'hijriRange' => [$hMin[0], $hMax[0]],
    'months' => $monthNames,
    'today' => [
        'g' => ['y' => $todayG->year(), 'm' => $todayG->month(), 'd' => $todayG->day()],
        'h' => ['y' => $todayH->year(), 'm' => $todayH->month(), 'd' => $todayH->day()],
    ],
    'locales' => LOCALES,
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hijri ⇄ Gregorian Converter</title>
<style>
  :root {
    --bg: #0a0e13;
    --panel: rgba(21, 28, 38, 0.72);
    --panel-solid: #151c26;
    --line: rgba(255, 255, 255, 0.08);
    --ink: #e8edf2;
    --muted: #8b97a6;
    --faint: #5b6572;
    --emerald: #10b981;
    --emerald-soft: rgba(16, 185, 129, 0.14);
    --gold: #e2b64c;
    --danger: #f2686b;
    --serif: "Iowan Old Style", "Palatino Linotype", Palatino, Georgia, "Times New Roman", serif;
    --sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: var(--sans);
    color: var(--ink);
    min-height: 100vh;
    background:
      radial-gradient(1100px 620px at 78% -8%, rgba(16, 185, 129, 0.16), transparent 60%),
      radial-gradient(900px 560px at 8% 108%, rgba(226, 182, 76, 0.10), transparent 55%),
      var(--bg);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: clamp(20px, 5vw, 64px) 20px 48px;
  }
  /* faint eight-point geometric star field */
  body::before {
    content: "";
    position: fixed; inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60' viewBox='0 0 60 60'%3E%3Cpath d='M30 6l6 18 18 6-18 6-6 18-6-18-18-6 18-6z' fill='none' stroke='%23ffffff' stroke-opacity='0.03' stroke-width='1'/%3E%3C/svg%3E");
    pointer-events: none;
    z-index: 0;
  }
  .wrap { width: 100%; max-width: 680px; position: relative; z-index: 1; }

  header { text-align: center; margin-bottom: 28px; }
  .kicker {
    font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--gold); font-weight: 600; margin-bottom: 10px;
  }
  h1 { font-family: var(--serif); font-size: clamp(28px, 6vw, 40px); font-weight: 600; letter-spacing: -0.01em; }
  h1 .arrow { color: var(--emerald); font-family: var(--sans); }
  header p { color: var(--muted); margin-top: 8px; font-size: 15px; }

  .card {
    background: var(--panel);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid var(--line);
    border-radius: 22px;
    padding: clamp(20px, 4vw, 30px);
    box-shadow: 0 24px 60px -28px rgba(0, 0, 0, 0.8);
  }

  .toolbar { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; justify-content: space-between; margin-bottom: 22px; }

  .segmented { display: inline-flex; background: rgba(0,0,0,0.28); border: 1px solid var(--line); border-radius: 12px; padding: 4px; gap: 4px; }
  .segmented button {
    border: 0; background: transparent; color: var(--muted); cursor: pointer;
    font: inherit; font-size: 13.5px; font-weight: 600; padding: 8px 14px; border-radius: 9px;
    transition: background .18s ease, color .18s ease;
  }
  .segmented button[aria-selected="true"] { background: var(--emerald-soft); color: #d6fff0; box-shadow: inset 0 0 0 1px rgba(16,185,129,0.35); }

  select.locale {
    appearance: none; -webkit-appearance: none;
    background: rgba(0,0,0,0.28); color: var(--ink); border: 1px solid var(--line);
    border-radius: 12px; padding: 9px 34px 9px 14px; font: inherit; font-size: 13.5px; cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%238b97a6' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 13px center;
  }

  .field-label { font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--faint); margin-bottom: 10px; }
  .inputs { display: flex; gap: 10px; align-items: stretch; }
  input, .inputs select {
    background: rgba(0,0,0,0.30); border: 1px solid var(--line); color: var(--ink);
    border-radius: 12px; padding: 13px 14px; font: inherit; font-size: 16px; width: 100%;
    transition: border-color .16s ease, box-shadow .16s ease;
  }
  .inputs select { appearance: none; -webkit-appearance: none; }
  input:focus, .inputs select:focus { outline: 0; border-color: rgba(16,185,129,0.6); box-shadow: 0 0 0 3px var(--emerald-soft); }
  input[type=date]::-webkit-calendar-picker-indicator { filter: invert(0.7); cursor: pointer; }
  .hijri-inputs { display: none; }
  .hijri-inputs .sub { display: flex; flex-direction: column; gap: 7px;
    font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--faint); }
  .hijri-inputs .sub.yr { flex: 0 0 92px; }
  .hijri-inputs .sub.mo { flex: 1 1 auto; }
  .hijri-inputs .sub.dy { flex: 0 0 78px; }

  .today {
    margin-top: 12px; background: none; border: 0; color: var(--emerald); cursor: pointer;
    font: inherit; font-size: 13px; font-weight: 600; padding: 4px 0; display: inline-flex; gap: 6px; align-items: center;
  }
  .today:hover { text-decoration: underline; }

  .divider { display: flex; align-items: center; gap: 14px; margin: 24px 0; color: var(--faint); }
  .divider::before, .divider::after { content: ""; height: 1px; background: var(--line); flex: 1; }
  .divider .chev { color: var(--emerald); font-size: 18px; }

  .result { text-align: center; }
  .result .weekday { color: var(--gold); font-size: 14px; font-weight: 600; letter-spacing: 0.04em; }
  .result .headline { font-family: var(--serif); font-size: clamp(30px, 8vw, 46px); font-weight: 600; margin: 8px 0 4px; line-height: 1.12; }
  .result .headline .era { color: var(--gold); }
  .result[dir="rtl"] .headline, .result[dir="rtl"] .weekday { direction: rtl; }

  .chips { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; margin-top: 20px; }
  .chip { background: rgba(0,0,0,0.26); border: 1px solid var(--line); border-radius: 10px; padding: 8px 12px; font-size: 12.5px; color: var(--muted); }
  .chip b { color: var(--ink); font-weight: 600; }

  .source { text-align: center; color: var(--faint); font-size: 13px; margin-top: 18px; }
  .source b { color: var(--muted); font-weight: 600; }

  .error { display: none; text-align: center; color: var(--danger); background: rgba(242,104,107,0.08);
    border: 1px solid rgba(242,104,107,0.28); border-radius: 12px; padding: 14px; font-size: 14px; }

  .foot { text-align: center; color: var(--faint); font-size: 12.5px; margin-top: 22px; line-height: 1.6; }
  .foot code { color: var(--muted); font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
  a { color: var(--emerald); text-decoration: none; }
  a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="wrap">
  <header>
    <div class="kicker">Umm al-Qura</div>
    <h1>Hijri <span class="arrow">⇄</span> Gregorian</h1>
    <p>Exact date conversion, backed by the official Umm al-Qura calendar.</p>
  </header>

  <div class="card">
    <div class="toolbar">
      <div class="segmented" role="tablist" aria-label="Conversion direction">
        <button id="dir-g2h" role="tab" aria-selected="true">Gregorian → Hijri</button>
        <button id="dir-h2g" role="tab" aria-selected="false">Hijri → Gregorian</button>
      </div>
      <select class="locale" id="locale" aria-label="Language"></select>
    </div>

    <div id="gregorian-field">
      <div class="field-label">Gregorian date</div>
      <div class="inputs">
        <input type="date" id="g-date">
      </div>
    </div>

    <div id="hijri-field" style="display:none">
      <div class="field-label">Hijri date</div>
      <div class="inputs hijri-inputs" style="display:flex">
        <label class="sub yr">Year
          <input type="number" id="h-year" inputmode="numeric">
        </label>
        <label class="sub mo">Month
          <select id="h-month"></select>
        </label>
        <label class="sub dy">Day
          <input type="number" id="h-day" inputmode="numeric" min="1" max="30">
        </label>
      </div>
    </div>

    <button class="today" id="today-btn">◈ Use today</button>

    <div class="divider"><span class="chev">▾</span></div>

    <div class="error" id="error"></div>

    <div class="result" id="result">
      <div class="weekday" id="r-weekday">—</div>
      <div class="headline" id="r-headline">—</div>
      <div class="chips" id="r-chips"></div>
    </div>

    <div class="source" id="source"></div>
  </div>

  <p class="foot">
    Runs the real <code>SDOSA\Hijri</code> library server-side.<br>
    Supported: Gregorian <code id="rg-g"></code> · Hijri <code id="rg-h"></code>.
  </p>
</div>

<script>
const BOOT = <?= json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

const $ = (id) => document.getElementById(id);
let dir = 'g2h';

// --- populate static controls --------------------------------------------
const localeSel = $('locale');
for (const [tag, label] of Object.entries(BOOT.locales)) {
  const o = document.createElement('option'); o.value = tag; o.textContent = label; localeSel.appendChild(o);
}
localeSel.value = 'en';

$('rg-g').textContent = BOOT.gregorianRange.join(' → ');
$('rg-h').textContent = BOOT.hijriRange.join(' → ');

const gDate = $('g-date');
gDate.min = BOOT.gregorianRange[0];
gDate.max = BOOT.gregorianRange[1];

const hYear = $('h-year'), hMonth = $('h-month'), hDay = $('h-day');
hYear.min = BOOT.hijriRange[0]; hYear.max = BOOT.hijriRange[1];

function fillMonths() {
  const names = BOOT.months[localeSel.value].hijri;
  const keep = hMonth.value;
  hMonth.innerHTML = '';
  names.forEach((name, i) => {
    const o = document.createElement('option'); o.value = i + 1; o.textContent = (i + 1) + ' · ' + name;
    hMonth.appendChild(o);
  });
  if (keep) hMonth.value = keep;
}

// --- defaults -------------------------------------------------------------
function pad(n) { return String(n).padStart(2, '0'); }
gDate.value = `${BOOT.today.g.y}-${pad(BOOT.today.g.m)}-${pad(BOOT.today.g.d)}`;
hYear.value = BOOT.today.h.y; hDay.value = BOOT.today.h.d;
fillMonths(); hMonth.value = BOOT.today.h.m;

// --- direction toggle -----------------------------------------------------
function setDir(next) {
  dir = next;
  $('dir-g2h').setAttribute('aria-selected', String(next === 'g2h'));
  $('dir-h2g').setAttribute('aria-selected', String(next === 'h2g'));
  $('gregorian-field').style.display = next === 'g2h' ? '' : 'none';
  $('hijri-field').style.display = next === 'h2g' ? '' : 'none';
  convert();
}
$('dir-g2h').onclick = () => setDir('g2h');
$('dir-h2g').onclick = () => setDir('h2g');

$('today-btn').onclick = () => {
  gDate.value = `${BOOT.today.g.y}-${pad(BOOT.today.g.m)}-${pad(BOOT.today.g.d)}`;
  hYear.value = BOOT.today.h.y; hMonth.value = BOOT.today.h.m; hDay.value = BOOT.today.h.d;
  convert();
};

localeSel.onchange = () => { fillMonths(); convert(); };

// --- conversion -----------------------------------------------------------
let timer = null;
function schedule() { clearTimeout(timer); timer = setTimeout(convert, 160); }
[gDate, hYear, hMonth, hDay].forEach((el) => el.addEventListener('input', schedule));

function currentParts() {
  if (dir === 'g2h') {
    const [y, m, d] = (gDate.value || '').split('-').map(Number);
    return { y, m, d };
  }
  return { y: Number(hYear.value), m: Number(hMonth.value), d: Number(hDay.value) };
}

let seq = 0;
async function convert() {
  const p = currentParts();
  if (!p.y || !p.m || !p.d) return;
  const mine = ++seq; // guard against out-of-order responses
  const qs = new URLSearchParams({ api: '1', dir, locale: localeSel.value, y: p.y, m: p.m, d: p.d });
  try {
    const res = await fetch('?' + qs.toString());
    const data = await res.json();
    if (mine !== seq) return; // a newer request has since fired
    data.ok ? render(data) : showError(data.error);
  } catch (e) {
    if (mine !== seq) return;
    showError('Request failed: ' + e.message);
  }
}

const RTL = new Set(['ar']);
const FROM = { ar: 'من', en: 'from', bn: 'থেকে', tr: 'kaynak' };
function render(data) {
  $('error').style.display = 'none';
  $('result').style.display = '';
  const t = data.target, s = data.source;
  const loc = localeSel.value, rtl = RTL.has(loc);
  $('result').setAttribute('dir', rtl ? 'rtl' : 'ltr');

  $('r-weekday').textContent = t.dayName;
  // One whole string, day → month → year → era (era tinted gold).
  $('r-headline').innerHTML = `${t.day} ${t.monthName} ${t.year} <span class="era">${t.notation}</span>`;

  $('r-chips').innerHTML = [
    ['ISO', t.iso],
    ['Formatted', t.formatted],
    ['Weekday', t.weekday + ' (Sun=1)'],
  ].map(([k, v]) => `<span class="chip"><b>${k}</b> · ${v}</span>`).join('');

  // Echo the source date, direction-matched; keep the Latin ISO isolated so RTL doesn't scramble it.
  const src = $('source');
  src.setAttribute('dir', rtl ? 'rtl' : 'ltr');
  src.innerHTML = `${FROM[loc] || 'from'} <b>${s.long}</b> · <bdi>${s.iso}</bdi>`;
}

function showError(msg) {
  $('result').style.display = 'none';
  $('source').innerHTML = '';
  const box = $('error');
  box.style.display = 'block';
  box.textContent = msg;
}

convert();
</script>
</body>
</html>
