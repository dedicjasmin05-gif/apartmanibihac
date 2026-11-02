<?php
/** Predložak: Team building Bihać s konfiguratorom upita. */
if (!defined('ABSPATH')) { exit; }
$l = abu_tb_tekuci();
$tx = abu_tb_txt($l);
$d = abu_tb_podaci();
$st = isset($_GET['tb']) ? sanitize_key($_GET['tb']) : '';
$hero = function_exists('abu_vodic_naslovna') ? abu_vodic_naslovna('teambuilding', 'ab-wide') : '';
$hero_url = $hero && preg_match('/src="([^"]+)"/', $hero, $m) ? $m[1] : '';
$opis = function ($e) use ($l) {
    if ($l === 'bs' && !empty($e['kratko_bs'])) { return $e['kratko_bs']; }
    return function_exists('abu_vodic_kratko') ? abu_vodic_kratko($e, $l) : (isset($e['kratko_bs']) ? $e['kratko_bs'] : '');
};
$opcija = function ($ime_polja, $e, $vrijednost = '') use ($opis) {
    $ids = function_exists('abu_vodic_slike_ids') ? abu_vodic_slike_ids($e) : array();
    $img = $ids ? wp_get_attachment_image($ids[0], 'thumbnail', false, array('loading' => 'lazy', 'alt' => '')) : esc_html(mb_substr($e['ime'], 0, 1));
    $v = $vrijednost ? $vrijednost : $e['ime'];
    return '<label class="tbo"><input type="checkbox" name="' . esc_attr($ime_polja) . '[]" value="' . esc_attr($v) . '"><span class="tbo__img">' . $img . '</span><span><b>' . esc_html($e['ime']) . '</b><small>' . esc_html($opis($e)) . '</small></span></label>';
};
$lista = function ($k) use ($d) { return isset($d[$k]) && is_array($d[$k]) ? $d[$k] : array(); };
$akt = $lista('aktivnosti');
if (count($akt) < 6) { $akt = array_merge($akt, abu_tb_osnovne($l)); }
get_header();
?>
<section class="vh<?php echo $hero_url ? '' : ' vh--plain'; ?>"<?php echo $hero_url ? ' style="--vh-img:url(\'' . esc_url($hero_url) . '\')"' : ''; ?>>
  <div class="wrap vh__in">
    <nav class="vh__crumbs" aria-label="Putanja"><a href="<?php echo esc_url(home_url($l === 'bs' ? '/' : '/' . $l . '/')); ?>">apartmanibihac.com</a> / <span>Team building</span></nav>
    <h1 class="vh__t"><?php echo esc_html($tx['h1']); ?></h1>
    <p class="vh__lead"><?php echo esc_html($tx['lead']); ?></p>
    <p class="vh__meta"><a class="btn btn--sun" href="#tbf"><?php echo esc_html($tx['f_h']); ?></a> <a class="btn btn--glass" href="tel:+38761591320">+387 61 591 320</a>
      <span class="vh__lang"><?php foreach (abu_tb_put() as $jl => $put) : ?><a href="<?php echo esc_url(home_url('/' . $put . '/')); ?>" hreflang="<?php echo esc_attr($jl); ?>"<?php echo $jl === $l ? ' aria-current="page"' : ''; ?>><?php echo esc_html(strtoupper($jl)); ?></a><?php endforeach; ?></span></p>
  </div>
</section>

<?php if ($l === 'bs' && function_exists('abu_vijesti_blok')) { echo abu_vijesti_blok('team-building', 3, 'Novosti: team building'); } // phpcs:ignore ?>

<div class="wrap">
  <div class="tb-why">
    <?php foreach ($tx['why'] as $w) : ?><div><b><?php echo esc_html($w[0]); ?></b><span><?php echo esc_html($w[1]); ?></span></div><?php endforeach; ?>
  </div>

  <form class="tbf" id="tbf" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <h2 class="h2"><?php echo esc_html($tx['f_h']); ?></h2>
    <p class="tbf__p"><?php echo esc_html($tx['f_p']); ?></p>
    <?php if ($st === 'ok') : ?><p class="tbf__ok" role="status"><?php echo esc_html($tx['ok']); ?></p><?php elseif ($st === 'greska') : ?><p class="tbf__err" role="alert"><?php echo esc_html($tx['err']); ?></p><?php endif; ?>
    <input type="hidden" name="action" value="abu_tb"><input type="hidden" name="lang" value="<?php echo esc_attr($l); ?>"><input type="hidden" name="t0" value="<?php echo esc_attr(time()); ?>">
    <label class="tb-hp" aria-hidden="true">Web <input type="text" name="web_stranica" tabindex="-1" autocomplete="off"></label>

    <fieldset class="tbf__step"><legend class="tbf__h"><i>1</i><?php echo esc_html($tx['s1']); ?></legend><p class="tbf__p"><?php echo esc_html($tx['s1p']); ?></p>
      <div class="tbf__row">
        <label class="f"><?php echo esc_html($tx['osobe']); ?><input type="number" name="osobe" min="2" max="1000" value="20"></label>
        <label class="f"><?php echo esc_html($tx['od']); ?><input type="date" name="od" min="<?php echo esc_attr(current_time('Y-m-d')); ?>"></label>
        <label class="f"><?php echo esc_html($tx['do']); ?><input type="date" name="do" min="<?php echo esc_attr(current_time('Y-m-d')); ?>"></label>
        <label class="f"><?php echo esc_html($tx['budzet']); ?><select name="budzet"><?php foreach ($tx['budzeti'] as $v => $n) : ?><option value="<?php echo esc_attr($v); ?>"><?php echo esc_html($n); ?></option><?php endforeach; ?></select></label>
      </div>
    </fieldset>

    <fieldset class="tbf__step"><legend class="tbf__h"><i>2</i><?php echo esc_html($tx['s2']); ?></legend><p class="tbf__p"><?php echo esc_html($tx['s2p']); ?></p>
      <div class="tbf__grid">
        <?php echo $opcija('smjestaj', array('ime' => $tx['nasi'], 'kratko_bs' => $tx['nasi_k'], 'kratko_en' => $tx['nasi_k'], 'kratko_de' => $tx['nasi_k'])); // phpcs:ignore ?>
        <?php foreach ($lista('hoteli') as $e) { echo $opcija('smjestaj', $e); } // phpcs:ignore ?>
      </div>
    </fieldset>

    <fieldset class="tbf__step"><legend class="tbf__h"><i>3</i><?php echo esc_html($tx['s3']); ?></legend><p class="tbf__p"><?php echo esc_html($tx['s3p']); ?></p>
      <div class="tbf__grid"><?php foreach ($lista('rafting') as $e) { echo $opcija('rafting', $e); } // phpcs:ignore ?></div>
    </fieldset>

    <fieldset class="tbf__step"><legend class="tbf__h"><i>4</i><?php echo esc_html($tx['s4']); ?></legend><p class="tbf__p"><?php echo esc_html($tx['s4p']); ?></p>
      <div class="tbf__grid"><?php foreach ($akt as $e) { echo $opcija('aktivnosti', $e); } // phpcs:ignore ?></div>
    </fieldset>

    <?php if ($lista('hrana')) : ?>
    <fieldset class="tbf__step"><legend class="tbf__h"><i>5</i><?php echo esc_html($tx['s5']); ?></legend><p class="tbf__p"><?php echo esc_html($tx['s5p']); ?></p>
      <div class="tbf__grid"><?php foreach ($lista('hrana') as $e) { echo $opcija('hrana', $e); } // phpcs:ignore ?></div>
    </fieldset>
    <?php endif; ?>

    <?php if ($lista('izvodjaci')) : ?>
    <fieldset class="tbf__step"><legend class="tbf__h"><i>6</i><?php echo esc_html($tx['s6']); ?></legend><p class="tbf__p"><?php echo esc_html($tx['s6p']); ?></p>
      <div class="tbf__grid"><?php foreach ($lista('izvodjaci') as $e) { echo $opcija('izvodjaci', $e, $e['ime'] . (!empty($e['grupa']) ? ' (' . $e['grupa'] . ')' : '')); } // phpcs:ignore ?></div>
    </fieldset>
    <?php endif; ?>

    <fieldset class="tbf__step"><legend class="tbf__h"><i>7</i><?php echo esc_html($tx['s7']); ?></legend><p class="tbf__p"><?php echo esc_html($tx['s7p']); ?></p>
      <div class="tbf__row">
        <label class="f"><?php echo esc_html($tx['ime']); ?><input type="text" name="ime" autocomplete="name" required></label>
        <label class="f"><?php echo esc_html($tx['firma']); ?><input type="text" name="firma" autocomplete="organization"></label>
        <label class="f"><?php echo esc_html($tx['email']); ?><input type="email" name="email" autocomplete="email"></label>
        <label class="f"><?php echo esc_html($tx['tel']); ?><input type="tel" name="tel" autocomplete="tel"></label>
      </div>
      <label class="f" style="margin-top:12px"><?php echo esc_html($tx['nap']); ?><textarea name="nap"></textarea></label>
      <label style="display:flex;gap:8px;align-items:flex-start;margin-top:12px;font-size:.92rem"><input type="checkbox" required> <?php echo esc_html($tx['privola']); ?></label>
    </fieldset>

    <div class="tbf__sum"><span><b data-tb-n>0</b> <?php echo esc_html($tx['odabrano']); ?></span><button class="btn btn--sun" type="submit"><?php echo esc_html($tx['posalji']); ?></button></div>
  </form>

  <section class="faq" aria-labelledby="tbfaq">
    <h2 id="tbfaq" class="h2"><?php echo esc_html($tx['faq_h']); ?></h2>
    <?php foreach ($tx['faq'] as $f) : ?><details><summary><?php echo esc_html($f[0]); ?></summary><p><?php echo esc_html($f[1]); ?></p></details><?php endforeach; ?>
  </section>
</div>
<script>
(function(){var f=document.getElementById('tbf');if(!f)return;var n=f.querySelector('[data-tb-n]');var u=function(){n.textContent=f.querySelectorAll('.tbo input:checked').length;};f.addEventListener('change',u);u();
f.addEventListener('submit',function(e){var em=f.querySelector('[name=email]').value.trim(),t=f.querySelector('[name=tel]').value.trim();if(!em&&!t){e.preventDefault();f.querySelector('[name=email]').focus();alert(<?php echo wp_json_encode($tx['err']); ?>);}});})();
</script>
<?php get_footer();
