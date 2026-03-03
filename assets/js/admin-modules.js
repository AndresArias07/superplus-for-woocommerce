jQuery(function($){
  document.documentElement.classList.add('sp-wsv-js');

  const $grid = $('.sp-wsv-modules-grid');
  const $form = $('.sp-wsv-form');
  const $saveFab = $form.find('.sp-wsv-save-fab');
  const $toggles = $form.find('input.sp-wsv-toggle');

  function syncDirtyState(){
    let isDirty = false;
    $toggles.each(function(){
      const $input = $(this);
      const initial = $input.data('spWsvInitial') === true;
      const current = $input.prop('checked') === true;
      if (initial !== current) {
        isDirty = true;
        return false;
      }
    });

    $form.toggleClass('sp-wsv-is-dirty', isDirty);
    $saveFab.prop('disabled', !isDirty);
  }

  $toggles.each(function(){
    $(this).data('spWsvInitial', $(this).prop('checked') === true);
  });
  syncDirtyState();

  // Toggle by clicking the card (except interactive elements)
  $grid.on('click', '.sp-wsv-switch', function(e){
    const $t = $(e.target);
    if ($t.closest('a,button,input,label').length) return;

    const $checkbox = $(this).find('input.sp-wsv-toggle').first();
    if (!$checkbox.length) return;
    if ($checkbox.prop('disabled')) return;

    $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
  });

  $grid.on('change', 'input.sp-wsv-toggle', function(){
    const $card = $(this).closest('.sp-wsv-card');
    $card.toggleClass('is-active', $(this).prop('checked'));
    syncDirtyState();
  });

});


