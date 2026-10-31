<?php
get_header();
$f = array('mjesto' => ab_get('mjesto'), 'osobe' => (int) ab_get('osobe'), 'dolazak' => ab_get('dolazak'), 'odlazak' => ab_get('odlazak'), 'oprema' => ab_get('oprema'));
$lista = ab_upit_objekti($f);
$ima_dat = ab_datumi_ok($f['dolazak'], $f['odlazak']);
$qs = $ima_dat ? array('dolazak' => $f['dolazak'], 'odlazak' => $f['odlazak'], 'osobe' => $f['osobe']) : array();
$oprema = get_terms(array('taxonomy' => 'oprema', 'hide_empty' => true));
?>
<section class="page-head">
  <div class="wrap">
    <h1 class="h1"><?php echo esc_html($f['mjesto'] ? 'Smještaj ' . $f['mjesto'] : 'Apartmani i vikendice u Bihaću i na Uni'); ?></h1>
    <p class="lead"><?php
      echo esc_html(count($lista) . ' ' . (count($lista) === 1 ? 'objekt' : 'objekata'));
      if ($ima_dat) { echo esc_html(' slobodno od ' . date_i18n('j. n.', strtotime($f['dolazak'])) . ' do ' . date_i18n('j. n. Y.', strtotime($f['odlazak']))); }
      ?></p>
  </div>
</section>
<div class="wrap list-layout">
  <form class="filters" method="get" action="<?php echo esc_url(home_url('/smjestaj/')); ?>">
    <label><span>Mjesto</span><select name="mjesto"><option value="">Sva mjesta</option><?php foreach (array_keys(ab_mjesta()) as $mj) : ?><option <?php selected($f['mjesto'], $mj); ?>><?php echo esc_html($mj); ?></option><?php endforeach; ?></select></label>
    <label><span>Dolazak</span><input type="date" name="dolazak" value="<?php echo esc_attr($f['dolazak']); ?>" min="<?php echo esc_attr(current_time('Y-m-d')); ?>"></label>
    <label><span>Odlazak</span><input type="date" name="odlazak" value="<?php echo esc_attr($f['odlazak']); ?>" min="<?php echo esc_attr(current_time('Y-m-d')); ?>"></label>
    <label><span>Osoba</span><input type="number" name="osobe" min="1" max="30" value="<?php echo $f['osobe'] ? (int) $f['osobe'] : ''; ?>" placeholder="bilo koliko"></label>
    <?php if ($oprema && !is_wp_error($oprema)) : ?>
      <label><span>Oprema</span><select name="oprema"><option value="">Bilo koja</option><?php foreach ($oprema as $t) : ?><option value="<?php echo esc_attr($t->slug); ?>" <?php selected($f['oprema'], $t->slug); ?>><?php echo esc_html($t->name); ?></option><?php endforeach; ?></select></label>
    <?php endif; ?>
    <button class="btn btn--deep" type="submit">Prikaži</button>
  </form>
  <div>
    <?php if ($lista) : ?>
      <div class="grid-cards grid-cards--list"><?php foreach ($lista as $o) { echo ab_kartica($o['id'], $o['slobodno'], $qs); } // phpcs:ignore ?></div>
    <?php else : ?>
      <div class="empty">
        <h2 class="h3">Za ove uvjete nema slobodnog objekta</h2>
        <p>Probajte druge datume ili manje filtera. Ako žurite, pošaljite nam termin i broj osoba na WhatsApp, a mi provjerimo i objekte koji još nisu na sajtu.</p>
        <p><a class="btn btn--sun" href="<?php echo esc_url(ab_wa('Pozdrav, tražim smještaj ' . ($f['mjesto'] ? 'u mjestu ' . $f['mjesto'] . ' ' : '') . ($ima_dat ? 'od ' . $f['dolazak'] . ' do ' . $f['odlazak'] : '') . ($f['osobe'] ? ' za ' . $f['osobe'] . ' osoba' : '') . '.')); ?>" target="_blank" rel="noopener">Pošalji termin na WhatsApp</a></p>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php get_footer();
