<?php
/**
 * Template Name: Live Broadcasts
 */

/* ── Direct cURL fetch ── */
function cd_lichess_fetch() {
  if ( ! function_exists('curl_init') ) return array('error' => 'cURL not available', 'data' => array());

  $ch = curl_init();
  curl_setopt_array($ch, array(
    CURLOPT_URL            => 'https://lichess.org/api/broadcast/top',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => array(
      'Accept: application/json',
      'User-Agent: CheckmateDaily/1.0 (https://checkmatedaily.com)',
    ),
    CURLOPT_ENCODING       => '',
  ));

  $body = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  curl_close($ch);

  if ($err)          return array('error' => 'cURL error: ' . $err, 'code' => $code, 'raw' => '', 'data' => array());
  if ($code !== 200) return array('error' => 'HTTP ' . $code,       'code' => $code, 'raw' => substr($body,0,300), 'data' => array());
  if (empty($body))  return array('error' => 'Empty response body', 'code' => $code, 'raw' => '', 'data' => array());

  return array('error' => '', 'code' => $code, 'raw' => substr($body, 0, 200), 'body' => $body);
}

$result      = cd_lichess_fetch();
$fetch_error = $result['error'] ?? '';
$fetch_code  = $result['code']  ?? 0;

/* ── Parse — /api/broadcast/top returns {active:[...], upcoming:[...], past:[...]} ── */
$live     = array();
$upcoming = array();
$past     = array();

if ( ! $fetch_error && ! empty($result['body']) ) {
  $json = json_decode($result['body'], true);
  if ($json) {
    $live     = isset($json['active'])   ? array_values($json['active'])   : array();
    $upcoming = isset($json['upcoming']) ? array_values($json['upcoming']) : array();
    $past     = isset($json['past'])     ? array_values($json['past'])     : array();
  }
}

/* ── Card renderer ── */
function cd_bcast_api_card($b) {
  $tour   = $b['tour'];
  $round  = $b['round'];
  $isLive = !empty($round['ongoing']);
  $url    = $round['url'] ?? $tour['url']
          ?? ('https://lichess.org/broadcast/'.($tour['slug']??$tour['id']??'').'/'.($round['slug']??$round['id']??''));
  $img        = esc_url($tour['image'] ?? '');
  $title      = esc_html($tour['name'] ?? 'Broadcast');
  $round_name = esc_html($round['name'] ?? '');
  $ts         = isset($round['startsAt']) ? (int)$round['startsAt'] : 0;
  if ($ts > 0 && $ts < 9999999999) $ts *= 1000;
  if ($ts) {
    try {
      $dt = new DateTime('@'.intval($ts/1000));
      $dt->setTimezone(new DateTimeZone('Asia/Kolkata'));
      $date = $dt->format('j M Y, g:i A').' IST';
    } catch(Exception $e) { $date = ''; }
  } else {
    $date = $isLive ? 'In progress' : '';
  }
  ob_start(); ?>
  <article class="bcard <?php echo $isLive ? 'bcard--live' : ''; ?>">
    <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" class="bcard__link">
      <div class="bcard__img">
        <?php if($img): ?>
          <img src="<?php echo $img; ?>" alt="<?php echo $title; ?>" loading="lazy">
        <?php else: ?>
          <div class="bcard__fallback">♟</div>
        <?php endif; ?>
        <?php if($isLive): ?>
          <div class="bcard__badge"><span class="bcard__dot"></span>LIVE</div>
        <?php endif; ?>
      </div>
      <div class="bcard__body">
        <?php if($round_name): ?>
          <div class="bcard__round"><?php echo $round_name; ?></div>
        <?php endif; ?>
        <h3 class="bcard__title"><?php echo $title; ?></h3>
        <?php if($date): ?>
          <div class="bcard__date"><?php echo esc_html($date); ?></div>
        <?php endif; ?>
      </div>
    </a>
  </article>
  <?php return ob_get_clean();
}

get_header();
?>

<style>
#bcp { background: var(--cd-gray-light); }

/* ── Hero ── */
.bcast-hero {
  background: var(--cd-black);
  border-bottom: 3px solid var(--cd-blue);
  padding: 40px 0 36px;
}
.bcast-hero__eye {
  display: flex; align-items: center; gap: 8px; margin-bottom: 10px;
}
.bcast-hero__tag {
  font-size: 11px; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: rgba(255,255,255,.5);
}
.bcast-hero__tag strong { color: var(--cd-blue); }
.bpulse {
  width: 8px; height: 8px; border-radius: 50%; background: #22c55e;
  animation: bpp 1.5s infinite;
}
@keyframes bpp {
  0%,100% { box-shadow: 0 0 0 0 rgba(34,197,94,.7); }
  50%     { box-shadow: 0 0 0 6px rgba(34,197,94,0); }
}
.bcast-hero h1 {
  font-family: var(--cd-font-serif); font-size: 34px; font-weight: 900;
  color: #fff; margin-bottom: 8px;
}
.bcast-hero p {
  font-size: 13px; color: rgba(255,255,255,.62); line-height: 1.6; max-width: 560px;
}

/* ── Tabs ── */
.btabs {
  display: flex;
  border-bottom: 2px solid var(--cd-gray-mid);
  margin: 28px 0 24px;
  background: #fff;
  border-radius: 4px 4px 0 0;
  overflow: hidden;
}
.btab {
  flex: 1; background: none; border: none; cursor: pointer;
  font-family: var(--cd-font-sans); font-size: 13px; font-weight: 700;
  color: var(--cd-gray-text); padding: 13px 10px;
  border-bottom: 3px solid transparent;
  display: flex; align-items: center; justify-content: center; gap: 7px;
  transition: color .15s, border-color .15s, background .15s;
  text-transform: uppercase; letter-spacing: .06em;
}
.btab:hover { background: var(--cd-gray-light); color: var(--cd-blue); }
.btab.active { color: var(--cd-blue); border-bottom-color: var(--cd-blue); }
.btab__dot {
  width: 7px; height: 7px; border-radius: 50%; background: #22c55e;
  animation: bpp 1.5s infinite;
}
.btab__n {
  background: var(--cd-gray-mid); color: var(--cd-gray-text);
  border-radius: 20px; font-size: 10px; font-weight: 800; padding: 2px 8px;
}
.btab.active .btab__n { background: var(--cd-blue); color: #fff; }

/* ── Panels ── */
.bpanel { display: none; }
.bpanel.active { display: block; }

/* ── Grid ── */
.bgrid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 32px;
}
@media (max-width: 860px) { .bgrid { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 540px) { .bgrid { grid-template-columns: 1fr; } }

/* ── Cards ── */
.bcard {
  background: #fff; border-radius: 5px; overflow: hidden;
  border: 1px solid var(--cd-gray-mid);
  transition: transform .2s ease, box-shadow .2s ease;
}
.bcard:hover { transform: translateY(-4px); box-shadow: 0 10px 28px rgba(0,0,0,.1); }
.bcard--live { border-color: rgba(34,197,94,.5); }
.bcard__link {
  display: block; text-decoration: none; color: inherit;
}
.bcard__img {
  position: relative; height: 150px; overflow: hidden;
  background: var(--cd-blue-deeper);
}
.bcard__img img {
  width: 100%; height: 100%; object-fit: cover; display: block;
  transition: transform .35s;
}
.bcard:hover .bcard__img img { transform: scale(1.05); }
.bcard__fallback {
  display: flex; align-items: center; justify-content: center;
  width: 100%; height: 100%; font-size: 48px; opacity: .15; color: #fff;
  background: repeating-conic-gradient(rgba(255,255,255,.05) 0% 25%, transparent 0% 50%) 0 0/24px 24px, var(--cd-blue-deeper);
}
.bcard__badge {
  position: absolute; top: 10px; left: 10px;
  background: rgba(34,197,94,.92); color: #fff;
  font-size: 10px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
  padding: 4px 9px; border-radius: 3px;
  display: flex; align-items: center; gap: 5px;
}
.bcard__dot {
  width: 6px; height: 6px; border-radius: 50%; background: #fff;
  animation: bpp 1.4s infinite;
}
.bcard__body { padding: 13px 14px 15px; }
.bcard__round {
  display: inline-block; background: var(--cd-blue); color: #fff;
  font-size: 9px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
  padding: 3px 8px; border-radius: 2px; margin-bottom: 8px;
}
.bcard__title {
  font-family: var(--cd-font-serif); font-size: 14px; font-weight: 700;
  line-height: 1.3; color: var(--cd-black); margin-bottom: 6px;
}
.bcard__date { font-size: 11px; color: var(--cd-gray-muted); font-weight: 600; }

/* ── Empty ── */
.bempty {
  text-align: center; padding: 48px 20px;
  font-size: 14px; color: var(--cd-gray-text);
  background: #fff; border-radius: 5px;
}
</style>

<div id="bcp">

  <section class="bcast-hero">
    <div class="container">
      <div class="bcast-hero__eye">
        <?php if (!empty($live)): ?>
          <span class="bpulse"></span>
        <?php endif; ?>
        <span class="bcast-hero__tag">Powered by <strong>Lichess</strong></span>
      </div>
      <h1>Live Broadcasts</h1>
      <p>Follow top-level chess tournaments in real time — move by move, game by game.</p>
    </div>
  </section>

  <div class="container" style="padding-top:0; padding-bottom:48px;">

    <div class="btabs">
      <button class="btab active" data-tab="live">
        <?php if (!empty($live)): ?><span class="btab__dot"></span><?php endif; ?>
        Live <span class="btab__n"><?php echo count($live); ?></span>
      </button>
      <button class="btab" data-tab="upcoming">
        Upcoming <span class="btab__n"><?php echo count($upcoming); ?></span>
      </button>
      <button class="btab" data-tab="past">
        Past <span class="btab__n"><?php echo count($past); ?></span>
      </button>
    </div>

    <div id="tab-live" class="bpanel active">
      <?php if (empty($live)): ?>
        <p class="bempty">No live broadcasts right now. Check the <strong>Upcoming</strong> tab.</p>
      <?php else: ?>
        <div class="bgrid">
          <?php foreach ($live as $b) echo cd_bcast_api_card($b); ?>
        </div>
      <?php endif; ?>
    </div>

    <div id="tab-upcoming" class="bpanel">
      <?php if (empty($upcoming)): ?>
        <p class="bempty">No upcoming broadcasts scheduled yet.</p>
      <?php else: ?>
        <div class="bgrid">
          <?php foreach ($upcoming as $b) echo cd_bcast_api_card($b); ?>
        </div>
      <?php endif; ?>
    </div>

    <div id="tab-past" class="bpanel">
      <?php if (empty($past)): ?>
        <p class="bempty">No past broadcasts available.</p>
      <?php else: ?>
        <div class="bgrid">
          <?php foreach ($past as $b) echo cd_bcast_api_card($b); ?>
        </div>
      <?php endif; ?>
    </div>

  </div>

</div>

<script>
document.querySelectorAll('.btab').forEach(function(b) {
  b.addEventListener('click', function() {
    document.querySelectorAll('.btab').forEach(function(x) { x.classList.remove('active'); });
    b.classList.add('active');
    document.querySelectorAll('.bpanel').forEach(function(p) { p.classList.remove('active'); });
    document.getElementById('tab-' + b.dataset.tab).classList.add('active');
  });
});
</script>

<?php get_footer(); ?>