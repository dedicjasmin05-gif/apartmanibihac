<?php
/** Predložak stranice vodiča (restorani, izlasci, izleti, rafting). */
if (!defined('ABSPATH')) { exit; }
list($k, $l) = abu_vodic_tekuci();
$d = abu_vodic_def()[$k];
$lista = abu_vodic_podaci($k);
$grupe = array();
foreach ($lista as $e) {
    $g = isset($e['tip']) ? $e['tip'] : (isset($e['kategorija']) ? $e['kategorija'] : (isset($e['grupa']) ? $e['grupa'] : ''));
    $g = trim(explode('|', $g)[0]);
    if ($g !== '') { $grupe[$g] = isset($grupe[$g]) ? $grupe[$g] + 1 : 1; }
}
arsort($grupe);
$hero = '';
foreach ($lista as $e) { $ids = abu_vodic_slike_ids($e); if ($ids) { $hero = wp_get_attachment_image_url($ids[0], 'ab-wide'); break; } }
$wa = function_exists('ab_wa') ? ab_wa() : 'https://wa.me/38761591320';
get_header();
?>
<section class="vh<?php echo $hero ? '' : ' vh--plain'; ?>"<?php echo $hero ? ' style="--vh-img:url(\'' . esc_url($hero) . '\')"' : ''; ?>>
  <div class="wrap vh__in">
    <nav class="vh__crumbs" aria-label="Putanja"><a href="<?php echo esc_url(home_url($l === 'bs' ? '/' : '/' . $l . '/')); ?>">apartmanibihac.com</a> / <span><?php echo esc_html($d['h1'][$l]); ?></span></nav>
    <h1 class="vh__t"><?php echo esc_html($d['h1'][$l]); ?></h1>
    <p class="vh__lead"><?php echo esc_html($d['uvod'][$l]); ?></p>
    <p class="vh__meta"><b><?php echo count($lista); ?></b> <?php echo esc_html(abu_vodic_t('mjesta', $l)); ?> · <?php echo esc_html(abu_vodic_t('azurirano', $l)); ?> <?php echo esc_html(date_i18n('n/Y', @filemtime(__DIR__ . '/../sadrzaj/' . $d['dat']) ?: time())); ?>
      <span class="vh__lang"><?php foreach ($d['put'] as $jl => $put) : ?><a href="<?php echo esc_url(home_url('/' . $put . '/')); ?>" hreflang="<?php echo esc_attr($jl); ?>"<?php echo $jl === $l ? ' aria-current="page"' : ''; ?>><?php echo esc_html(strtoupper($jl)); ?></a><?php endforeach; ?></span></p>
  </div>
</section>

<?php if ($l === 'bs' && function_exists('abu_vijesti_blok')) { echo abu_vijesti_blok($d['kat'], 3, 'Novosti: ' . trim(explode(':', $d['h1']['bs'])[0])); } // phpcs:ignore ?>

<div class="wrap vd" data-vodic>
  <div class="vd__bar">
    <div class="vd__chips" role="group" aria-label="Filter">
      <button type="button" class="vd__chip is-on" data-f=""><?php echo esc_html(abu_vodic_t('sve', $l)); ?> <span><?php echo count($lista); ?></span></button>
      <?php foreach ($grupe as $g => $n) : ?><button type="button" class="vd__chip" data-f="<?php echo esc_attr(sanitize_title($g)); ?>"><?php echo esc_html(abu_vodic_tip($g, $l)); ?> <span><?php echo (int) $n; ?></span></button><?php endforeach; ?>
    </div>
  </div>
  <div class="vd__map" id="vd-map" aria-label="<?php echo esc_attr(abu_vodic_t('karta', $l)); ?>"></div>

  <ol class="vd__list">
  <?php foreach ($lista as $i => $e) :
      $g = isset($e['tip']) ? $e['tip'] : (isset($e['kategorija']) ? $e['kategorija'] : (isset($e['grupa']) ? $e['grupa'] : ''));
      $g = trim(explode('|', $g)[0]);
      $ids = abu_vodic_slike_ids($e);
      $map = !empty($e['lat']) ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($e['lat'] . ',' . $e['lng']) : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($e['ime'] . ' ' . (isset($e['adresa']) ? $e['adresa'] : 'Bihać'));
      $tel = isset($e['telefon']) ? trim($e['telefon']) : '';
      $kako = !empty($e['kako_doci_' . $l]) ? $e['kako_doci_' . $l] : (!empty($e['kako_doci_bs']) ? $e['kako_doci_bs'] : '');
      ?>
    <li class="vp" id="<?php echo esc_attr($e['slug'] ?? sanitize_title($e['ime'])); ?>" data-g="<?php echo esc_attr(sanitize_title($g)); ?>"<?php if (!empty($e['lat'])) : ?> data-lat="<?php echo esc_attr($e['lat']); ?>" data-lng="<?php echo esc_attr($e['lng']); ?>"<?php endif; ?> data-ime="<?php echo esc_attr($e['ime']); ?>">
      <div class="vp__m"><div class="vp__gal<?php echo $ids ? '' : ' vp__gal--none'; ?>" data-gal>
        <?php if ($ids) : foreach ($ids as $n => $aid) :
            $kr = get_post_meta($aid, '_abu_kredit', true); ?>
          <a href="<?php echo esc_url(wp_get_attachment_image_url($aid, 'full')); ?>" data-c="<?php echo esc_attr($e['ime'] . ($kr ? ' · ' . abu_vodic_t('foto', $l) . ': ' . $kr : '')); ?>"><?php echo wp_get_attachment_image($aid, 'ab-card', false, array('loading' => $i < 2 && $n === 0 ? 'eager' : 'lazy', 'alt' => $e['ime'] . ($n ? ' ' . ($n + 1) : '') . ', Bihać')); ?></a>
        <?php endforeach; else : ?>
          <span class="vp__ph"><?php echo esc_html(mb_substr($e['ime'], 0, 1)); ?></span>
        <?php endif; ?>
        </div><?php if (count($ids) > 1) : ?><span class="vp__n"><?php echo count($ids); ?> foto</span><?php endif; ?>
      </div>
      <div class="vp__b">
        <p class="vp__tip"><?php if ($i === 0 && $k === 'rafting') : ?><span class="vp__pick"><?php echo esc_html(abu_vodic_t('preporuka', $l)); ?></span> <?php endif; ?><?php echo esc_html(abu_vodic_tip($g, $l)); ?>
          <?php if (!empty($e['udaljenost_km'])) : ?> · <?php echo esc_html(round($e['udaljenost_km'])); ?> km <?php echo esc_html(abu_vodic_t('udaljenost', $l)); ?><?php endif; ?>
          <?php if (!empty($e['uz_rijeku'])) : ?> · <?php echo esc_html(abu_vodic_t('uz_rijeku', $l)); ?><?php endif; ?></p>
        <h2 class="vp__t"><?php echo esc_html($e['ime']); ?></h2>
        <p class="vp__k"><?php echo esc_html(abu_vodic_kratko($e, $l)); ?></p>
        <div class="vp__o"><?php echo wpautop(esc_html(abu_vodic_opis($e, $l))); // phpcs:ignore ?></div>
        <?php if (!empty($e['specijaliteti'])) : ?><p class="vp__sp"><b><?php echo esc_html(abu_vodic_t('probati', $l)); ?>:</b> <?php foreach (array_slice((array) $e['specijaliteti'], 0, 6) as $s) : ?><span><?php echo esc_html($s); ?></span><?php endforeach; ?></p><?php endif; ?>
        <dl class="vp__dl">
          <?php if (!empty($e['adresa'])) : ?><div><dt><?php echo esc_html(abu_vodic_t('adresa', $l)); ?></dt><dd><?php echo esc_html($e['adresa']); ?></dd></div><?php endif; ?>
          <?php if (!empty($e['radno_vrijeme'])) : ?><div><dt><?php echo esc_html(abu_vodic_t('radno', $l)); ?></dt><dd><?php echo esc_html($e['radno_vrijeme']); ?></dd></div><?php endif; ?>
          <?php if (!empty($e['radno_vrijeme_sezona'])) : ?><div><dt><?php echo esc_html(abu_vodic_t('radno', $l)); ?></dt><dd><?php echo esc_html($e['radno_vrijeme_sezona']); ?></dd></div><?php endif; ?>
          <?php if (!empty($e['cijene'])) : ?><div><dt><?php echo esc_html(abu_vodic_t('cijene', $l)); ?></dt><dd><?php echo esc_html($e['cijene']); ?></dd></div><?php endif; ?>
          <?php if (!empty($e['ulaznica'])) : ?><div><dt><?php echo esc_html(abu_vodic_t('ulaz', $l)); ?></dt><dd><?php echo esc_html($e['ulaznica']); ?></dd></div><?php endif; ?>
          <?php if (!empty($e['trajanje'])) : ?><div><dt><?php echo esc_html(abu_vodic_t('trajanje', $l)); ?></dt><dd><?php echo esc_html($e['trajanje']); ?></dd></div><?php endif; ?>
          <?php if ($kako) : ?><div><dt><?php echo esc_html(abu_vodic_t('kako', $l)); ?></dt><dd><?php echo esc_html($kako); ?><?php echo !empty($e['voznja_min']) ? ' (~' . (int) $e['voznja_min'] . ' min)' : ''; ?></dd></div><?php endif; ?>
        </dl>
        <p class="vp__a">
          <a class="vbtn" href="<?php echo esc_url($map); ?>" target="_blank" rel="noopener"><?php echo esc_html(abu_vodic_t('karta', $l)); ?></a>
          <?php if ($tel) : ?><a class="vbtn" href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $tel)); ?>"><?php echo esc_html(abu_vodic_t('nazovi', $l)); ?></a><?php endif; ?>
          <?php if (!empty($e['web'])) : ?><a class="vbtn" href="<?php echo esc_url($e['web']); ?>" target="_blank" rel="noopener"><?php echo esc_html(abu_vodic_t('web', $l)); ?></a><?php endif; ?>
          <?php if (!empty($e['facebook'])) : ?><a class="vbtn vbtn--fb" href="<?php echo esc_url($e['facebook']); ?>" target="_blank" rel="noopener">Facebook</a><?php endif; ?>
          <?php if (!empty($e['instagram'])) : ?><a class="vbtn vbtn--ig" href="<?php echo esc_url($e['instagram']); ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
          <?php if (!empty($e['tripadvisor'])) : ?><a class="vbtn" href="<?php echo esc_url($e['tripadvisor']); ?>" target="_blank" rel="noopener">Tripadvisor</a><?php endif; ?>
        </p>
      </div>
    </li>
    <?php if ($i === 5) : ?>
    <li class="vcta">
      <div><b><?php echo esc_html(abu_vodic_t('smjestaj_h', $l)); ?></b><span><?php echo esc_html(abu_vodic_t('smjestaj_p', $l)); ?></span></div>
      <p><a class="btn btn--sun" href="<?php echo esc_url(home_url($l === 'bs' ? '/smjestaj/' : '/' . $l . '/')); ?>"><?php echo esc_html(abu_vodic_t('pogledaj', $l)); ?></a> <a class="btn btn--glass" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener">WhatsApp</a></p>
    </li>
    <?php endif; ?>
  <?php endforeach; ?>
  </ol>

  <section class="vcta vcta--end">
    <div><b><?php echo esc_html(abu_vodic_t('smjestaj_h', $l)); ?></b><span><?php echo esc_html(abu_vodic_t('smjestaj_p', $l)); ?></span></div>
    <p><a class="btn btn--sun" href="<?php echo esc_url(home_url($l === 'bs' ? '/smjestaj/' : '/' . $l . '/')); ?>"><?php echo esc_html(abu_vodic_t('pogledaj', $l)); ?></a> <a class="btn btn--glass" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener">WhatsApp</a></p>
  </section>

  <nav class="vd__more" aria-label="Vodič">
    <?php foreach (abu_vodic_def() as $ok => $od) : if ($ok === $k) { continue; } ?>
      <a href="<?php echo esc_url(home_url('/' . $od['put'][$l] . '/')); ?>"><?php echo esc_html($od['h1'][$l]); ?> →</a>
    <?php endforeach; ?>
    <a href="<?php echo esc_url(home_url($l === 'bs' ? '/team-building-bihac/' : ($l === 'en' ? '/en/team-building-bihac/' : '/de/teambuilding-bihac/'))); ?>">Team building Bihać →</a>
  </nav>
</div>
<?php get_footer();
