(function($){
    'use strict';

    var HASH_PREFIX = '#sp-wsv-step-';
    var hashGuard = false;

    function hashForStep(step){
        return HASH_PREFIX + String(step);
    }

    function getStepFromHash(){
        var h = (window.location && window.location.hash) ? window.location.hash : '';
        var m = h.match(/^#sp-wsv-step-(2|3|4)$/);
        return m ? parseInt(m[1], 10) : null;
    }

    function setHash(step, replace){
        var target = hashForStep(step);

        // Si ya está, no hacemos nada
        if(window.location.hash === target){
            return;
        }

        hashGuard = true;

        if(window.history && (window.history.pushState || window.history.replaceState)){
            if(replace && window.history.replaceState){
                window.history.replaceState(null, document.title, target);
            } else if(window.history.pushState){
                window.history.pushState(null, document.title, target);
            } else {
                window.location.hash = target;
            }
        } else {
            window.location.hash = target;
        }

        // Liberar guardia en el siguiente tick
        setTimeout(function(){ hashGuard = false; }, 0);
    }

    function getMsg(key, fallback){
        var cfg = window.SP_WSV_CheckoutSteps || {};
        var i18n = cfg.i18n || {};
        var v = i18n[key];
        if(typeof v === 'string' && v.trim() !== ''){
            return v;
        }
        return fallback;
    }

    function ensureNoticesWrapper($form){
        var $wrap = $('.woocommerce-notices-wrapper').first();
        if(!$wrap.length){
            $wrap = $('<div class="woocommerce-notices-wrapper"></div>');
            $form.prepend($wrap);
        }
        return $wrap;
    }

    function scrollToNotices($wrap){
        if(!$wrap || !$wrap.length) return;
        $('html,body').stop().animate({ scrollTop: Math.max(0, $wrap.offset().top - 20) }, 200);
    }

    function showError($form, message){
        var safe = $('<div/>').text((message || '').toString()).html();
        var $wrap = ensureNoticesWrapper($form);
        $wrap.html('<ul class="woocommerce-error" role="alert"><li>' + safe + '</li></ul>');
        scrollToNotices($wrap);
    }

    function showWooMessagesHtml($form, html){
        var $wrap = ensureNoticesWrapper($form);
        $wrap.html((html || '').toString());
        scrollToNotices($wrap);
    }

    function clearNotices(){
        var $wrap = $('.woocommerce-notices-wrapper').first();
        if($wrap.length){
            $wrap.empty();
        }
    }

    function setActivePanel($form, step){
        var panel = (step === 2) ? '2' : '3-4';

        $('.sp-wsv-step-panel').removeClass('is-active');
        $('.sp-wsv-step-panel[data-step-panel="' + panel + '"]').addClass('is-active');

        // Indicadores (solo 2-4)
        $('.sp-wsv-step-indicator').removeClass('is-active');
        $('.sp-wsv-step-indicator[data-step="' + step + '"]').addClass('is-active');

        // Acciones sidebar
        $('.sp-wsv-sidebar-actions-group').removeClass('is-active');
        $('.sp-wsv-sidebar-actions-group[data-step-actions="' + step + '"]').addClass('is-active');

        $form.attr('data-step', step);
    }

    function scrollToTop(){
        $('html,body').stop().animate({ scrollTop: 0 }, 150);
    }

    function lockSidebarShipping(){
        var $wrap = $('#sp-wsv-sidebar-summary');
        if(!$wrap.length) return;

        var $inputs = $wrap.find('input[name^="shipping_method"]');
        $inputs.prop('disabled', true);
        $inputs.attr('tabindex', '-1');

        $wrap.find('.woocommerce-shipping-methods').each(function(){
            var $list = $(this);
            var $checked = $list.find('input[name^="shipping_method"]:checked');
            if($checked.length){
                $list.find('li').each(function(){
                    var $li = $(this);
                    var isSelected = $li.find('input[name^="shipping_method"]:checked').length > 0;
                    $li.toggle(!!isSelected);
                });
                return;
            }

            var $all = $list.find('li');
            if($all.length > 1){
                $all.hide().first().show();
            }
        });
    }

    function syncPlaceOrderLocation($form, step){
        var s = parseInt(step || ($form && $form.attr ? $form.attr('data-step') : '2'), 10);
        var $target = $('[data-spwsv-sidebar-placeorder="1"]').first();
        var $payment = $('#order_review .woocommerce-checkout-payment').first();
        if(!$target.length || !$payment.length) return;

        var $anchor = $('#order_review [data-spwsv-placeorder-anchor="1"]').first();
        if(!$anchor.length){
            $anchor = $('<div data-spwsv-placeorder-anchor="1"></div>');
        }
        if(!$anchor.parent().length){
            $payment.append($anchor);
        }

        var $orderPlace = $payment.find('.place-order').first();
        var $sidebarPlaces = $target.find('.place-order');

        if(s === 4){
            if($orderPlace.length){
                $target.find('.place-order').not($orderPlace).remove();
                if(!$orderPlace.closest($target).length){
                    $target.append($orderPlace);
                }
            } else if($sidebarPlaces.length > 1){
                $sidebarPlaces.slice(1).remove();
            }
            var $customBtn = $('[data-sp-wsv-place-order]').first();
            if($customBtn.length){
                $customBtn.hide();
            }
        } else {
            var $toRestore = $orderPlace.length ? $orderPlace : $sidebarPlaces.first();
            if($toRestore.length){
                $payment.find('.place-order').not($toRestore).remove();
                $target.find('.place-order').not($toRestore).remove();
                if(!$toRestore.closest($payment).length){
                    $anchor.before($toRestore);
                }
            }
            var $customBtn2 = $('[data-sp-wsv-place-order]').first();
            if($customBtn2.length){
                $customBtn2.show();
            }
        }
    }

    function validateStep2($form){
        var ok = true;
        var $firstInvalid = null;

        // Woo marca campos requeridos con .validate-required a nivel de fila.
        $('#customer_details .validate-required').each(function(){
            var $row = $(this);
            var $fields = $row.find('input, select, textarea')
                .filter(':visible')
                .filter(':not([disabled])');

            if(!$fields.length) return;

            // Si alguno de los campos visibles está vacío, marcamos inválido.
            var rowOk = true;

            $fields.each(function(){
                var $f = $(this);
                if($f.attr('type') === 'hidden') return;

                if($f.is(':checkbox')){
                    if(!$f.is(':checked')){
                        rowOk = false;
                    }
                } else {
                    var val = ($f.val() || '').toString().trim();
                    if(val === ''){
                        rowOk = false;
                    }
                }
            });

            if(!rowOk){
                ok = false;
                $row.removeClass('woocommerce-validated')
                    .addClass('woocommerce-invalid woocommerce-invalid-required-field');
                if(!$firstInvalid) $firstInvalid = $row;
            } else {
                $row.removeClass('woocommerce-invalid woocommerce-invalid-required-field')
                    .addClass('woocommerce-validated');
            }
        });

        if(!ok){
            showError($form, getMsg('required', 'Por favor, revisa los campos obligatorios antes de continuar.'));
            if($firstInvalid){
                $('html,body').stop().animate({ scrollTop: Math.max(0, $firstInvalid.offset().top - 20) }, 200);
            }
        }

        return ok;
    }

    function validateStep3($form){
        // Shipping (si existe selector)
        var needsShipping = $('input[type="radio"][name^="shipping_method"]').length > 0;
        if(needsShipping){
            var shippingSelected = $('input[type="radio"][name^="shipping_method"]:checked').length > 0;
            if(!shippingSelected){
                showError($form, getMsg('choose_shipping', 'Por favor, elige un método de envío para continuar.'));
                return false;
            }
        }

        // Payment
        var hasPayments = $('input[name="payment_method"]').length > 0;
        if(hasPayments){
            var paymentSelected = $('input[name="payment_method"]:checked').length > 0;
            if(!paymentSelected){
                showError($form, getMsg('choose_payment', 'Por favor, elige un método de pago para continuar.'));
                return false;
            }
        }

        return true;
    }

    function syncStep4Summary($form, step){
        var s = parseInt(step || ($form && $form.attr ? $form.attr('data-step') : '2'), 10);
        if(s !== 4) return;

        var $wrap = $('[data-spwsv-step4-summary="1"]').first();
        if(!$wrap.length) return;

        function clean(v){
            return (v || '').toString().trim();
        }

        function getField(name){
            var $f = $form.find('[name="' + name + '"]').first();
            return $f.length ? clean($f.val()) : '';
        }

        function formatAddress(prefix){
            var first = getField(prefix + '_first_name');
            var last = getField(prefix + '_last_name');
            var company = getField(prefix + '_company');
            var a1 = getField(prefix + '_address_1');
            var a2 = getField(prefix + '_address_2');
            var postcode = getField(prefix + '_postcode');
            var city = getField(prefix + '_city');
            var state = '';
            var $state = $form.find('[name="' + prefix + '_state"]').first();
            if($state.length){
                state = clean($state.is('select') ? $state.find('option:selected').text() : $state.val());
            }
            var country = '';
            var $country = $form.find('[name="' + prefix + '_country"]').first();
            if($country.length){
                country = clean($country.is('select') ? $country.find('option:selected').text() : $country.val());
            }

            var lines = [];
            var full = clean([first, last].filter(Boolean).join(' '));
            if(full) lines.push(full);
            if(company) lines.push(company);
            if(a1) lines.push(a1);
            if(a2) lines.push(a2);

            var cityLine = clean([postcode, city].filter(Boolean).join(' '));
            var region = clean([state, country].filter(Boolean).join(', '));
            var lastLine = clean([cityLine, region].filter(Boolean).join(', '));
            if(lastLine) lines.push(lastLine);

            return lines.join('<br>');
        }

        var useShipping = false;
        var $shipDiff = $('#ship-to-different-address-checkbox');
        if($shipDiff.length && $shipDiff.is(':checked')) {
            useShipping = true;
        }
        if(!$form.find('[name^="shipping_"]').length){
            useShipping = false;
        }

        var addressHtml = formatAddress(useShipping ? 'shipping' : 'billing');
        if(!addressHtml){
            addressHtml = '-';
        }
        $wrap.find('[data-spwsv-step4-address-value="1"]').html(addressHtml);

        var $pm = $form.find('input[name="payment_method"]:checked').first();
        var pmLabel = '';
        if($pm.length){
            var $li = $pm.closest('li');
            var $label = $li.find('label').first();
            pmLabel = clean($label.text());
        }
        $wrap.find('[data-spwsv-step4-payment-value="1"]').text(pmLabel || '-');
    }

    /**
     * Navegación centralizada a un paso.
     * - pushHash=true => crea entrada de historial (para back/forward del navegador)
     * - replaceHash=true => no crea entrada (sync inicial o corrección)
     */
    function navigateToStep($form, targetStep, opts){
        opts = opts || {};
        var currentStep = parseInt($form.attr('data-step') || '2', 10);

        // Normalizar
        targetStep = parseInt(targetStep, 10);
        if([2,3,4].indexOf(targetStep) === -1){
            return false;
        }

        if(targetStep === currentStep){
            if(opts.pushHash){
                setHash(targetStep, !!opts.replaceHash);
            }
            return true;
        }

        // Si vamos hacia delante, validamos los pasos previos
        if(targetStep > currentStep){
            // Si queremos ir a 3 o 4, necesitamos que el paso 2 esté OK
            if(currentStep <= 2 && targetStep >= 3){
                if(!validateStep2($form)){
                    return false;
                }
            }

            // Si queremos ir a 4, necesitamos envío/pago seleccionados
            if(targetStep >= 4){
                if(!validateStep3($form)){
                    return false;
                }
            }
        }

        setActivePanel($form, targetStep);
        syncStep4Summary($form, targetStep);
        syncPlaceOrderLocation($form, targetStep);
        lockSidebarShipping();
        if(opts.scrollTop){
            scrollToTop();
        }

        // Actualizar totales / shipping / payments cuando entramos en 3 o 4
        if(targetStep >= 3){
            $(document.body).trigger('update_checkout');
        }

        if(opts.pushHash){
            setHash(targetStep, !!opts.replaceHash);
        }

        return true;
    }

    function syncHashWithCurrentStep($form){
        var step = parseInt($form.attr('data-step') || '2', 10);
        // Reemplazo para no crear una entrada extra
        setHash(step, true);
    }

    function isMobile(){
        return window.matchMedia && window.matchMedia('(max-width: 980px)').matches;
    }

    function initOrderSummaryToggle(){
        var isOpen = false;
        var isInit = false;

        function normalizeOrderSummary(){
            var $wrap = $('.sp-wsv-order-summary[data-spwsv-order-summary="1"]').first();
            if(!$wrap.length) return;

            var $btn = $wrap.find('.sp-wsv-order-summary__toggle').first();
            var $body = $wrap.find('.sp-wsv-order-summary__body').first();
            if(!$btn.length || !$body.length) return;

            if(!isInit){
                isOpen = $wrap.hasClass('is-open');
                isInit = true;
            }

            $body.stop(true, true).removeAttr('style');

            if(!isMobile()){
                $btn.attr('aria-expanded', 'true');
                return;
            }

            $wrap.toggleClass('is-open', !!isOpen);
            $btn.attr('aria-expanded', isOpen ? 'true' : 'false');
        }

        $(document).on('click', '.sp-wsv-order-summary__toggle', function(e){
            e.preventDefault();

            var $wrap = $(this).closest('.sp-wsv-order-summary');
            if(!$wrap.length) return;

            var $body = $wrap.find('.sp-wsv-order-summary__body').first();
            if(!$body.length) return;

            if(!isMobile()){
                $body.stop(true, true).removeAttr('style');
                $(this).attr('aria-expanded', 'true');
                return;
            }

            isOpen = !isOpen;
            $(this).attr('aria-expanded', isOpen ? 'true' : 'false');

            if(isOpen){
                $wrap.addClass('is-open');
                $body.stop(true, true).hide().slideDown(180, function(){
                    $body.removeAttr('style');
                });
            } else {
                $body.stop(true, true).slideUp(180, function(){
                    $wrap.removeClass('is-open');
                    $body.removeAttr('style');
                });
            }
        });

        var resizeTimer = null;
        $(window).on('resize orientationchange', function(){
            if(resizeTimer) clearTimeout(resizeTimer);
            resizeTimer = setTimeout(normalizeOrderSummary, 120);
        });

        $(document.body).on('updated_checkout', normalizeOrderSummary);

        $(normalizeOrderSummary);
    }

    $(function(){
        initOrderSummaryToggle();

        var $form = $('form.sp-wsv-checkout-steps-wrapper.checkout');
        if(!$form.length) return;

        function applySidebarCoupon($f){
            var code = ($f.find('input[name="coupon_code"]').val() || '').toString().trim();
            var $status = $f.find('.sp-wsv-coupon-status').first();
            var fallbackNonce = ($f.attr('data-spwsv-nonce') || '').toString();

            $status.text('');
            if(!code){
                showError($form, getMsg('coupon_empty', 'Por favor, introduce un código de cupón.'));
                return;
            }

            var cfg = window.SP_WSV_CheckoutSteps || {};
            var params = window.wc_checkout_params || window.wc_cart_params || window.wc_cart_fragments_params || {};
            var ajaxUrl = (cfg.apply_coupon_url || '').toString();
            if(!ajaxUrl){
                ajaxUrl = (params.wc_ajax_url || '').toString();
                if(ajaxUrl){
                    ajaxUrl = ajaxUrl.replace('%%endpoint%%', 'apply_coupon');
                }
            }

            var nonce = params.apply_coupon_nonce || params.nonce || fallbackNonce;

            if(!ajaxUrl){
                showError($form, getMsg('coupon_generic_error', 'No se pudo aplicar el cupón en este momento.'));
                return;
            }

            var billingEmail = ($form.find('input[name="billing_email"]').val() || '').toString().trim();
            var data = {
                security: nonce,
                coupon_code: code
            };
            if(billingEmail){
                data.billing_email = billingEmail;
            }

            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                data: data,
                dataType: 'html'
            }).done(function(resp){
                var html = (resp || '').toString();
                if(html){
                    showWooMessagesHtml($form, html);
                } else {
                    showError($form, getMsg('coupon_generic_error', 'No se pudo aplicar el cupón en este momento.'));
                    return;
                }

                $status.text('');
                if(html.indexOf('woocommerce-error') === -1 && html.indexOf('is-error') === -1){
                    $f.find('input[name="coupon_code"]').val('');
                }
                $(document.body).trigger('applied_coupon_in_checkout', [ code ]);
                $(document.body).trigger('update_checkout', { update_shipping_method: false });
            }).fail(function(jqXHR){
                $status.text('');
                var respText = jqXHR && jqXHR.responseText ? (jqXHR.responseText || '').toString() : '';
                if(respText){
                    showWooMessagesHtml($form, respText);
                    return;
                }
                showError($form, getMsg('coupon_generic_error_retry', 'No se pudo aplicar el cupón. Revisa el código e inténtalo de nuevo.'));
            });
        }

        $(document).on('submit', '.sp-wsv-sidebar-coupon-form[data-spwsv-coupon-form="1"]', function(e){
            e.preventDefault();
            applySidebarCoupon($(this));
        });

        $(document).on('click', '.sp-wsv-sidebar-coupon-form[data-spwsv-coupon-form="1"] button[name="apply_coupon"]', function(e){
            e.preventDefault();
            applySidebarCoupon($(this).closest('.sp-wsv-sidebar-coupon-form'));
        });

        var hasStepper = $form.find('.sp-wsv-step-panel[data-step-panel]').length > 0;
        lockSidebarShipping();
        if(!hasStepper){
            return;
        }

        // Estado inicial por defecto
        setActivePanel($form, 2);

        // Si no hay hash, lo fijamos a paso 2 (replace, para que el BACK funcione hacia paso 2 desde paso 3)
        if(!window.location.hash){
            setHash(2, true);
        } else {
            // Si el hash es nuestro, intentamos respetarlo (con validación).
            var requested = getStepFromHash();
            if(requested && requested !== 2){
                // Pequeño delay para que Woo inicialice checkout antes de validar shipping/pago (si aplica)
                setTimeout(function(){
                    clearNotices();
                    var ok = navigateToStep($form, requested, { pushHash: false, scrollTop: true });
                    if(!ok){
                        // Si no se puede, dejamos el usuario en el paso actual (2) y corregimos el hash
                        syncHashWithCurrentStep($form);
                    }
                }, 50);
            }
        }

        // Limpieza de errores al cambiar inputs
        $(document).on('change input', '#customer_details input, #customer_details select, #customer_details textarea', function(){
            var $row = $(this).closest('.validate-required');
            if($row.length){
                $row.removeClass('woocommerce-invalid woocommerce-invalid-required-field');
            }
        });

        // Click en indicadores (anclas de pasos 2-4)
        $(document).on('click', '.sp-wsv-step-anchor', function(e){
            e.preventDefault();
            clearNotices();

            var step = parseInt($(this).closest('.sp-wsv-step-indicator').attr('data-step') || '2', 10);

            var ok = navigateToStep($form, step, { pushHash: true, scrollTop: true });
            if(!ok){
                // Si no se puede, mantener el hash actual (sin ensuciar historial)
                syncHashWithCurrentStep($form);
            }
        });

        // Next (botón lateral)
        $(document).on('click', '[data-sp-wsv-next]', function(e){
            e.preventDefault();
            clearNotices();

            var step = parseInt($form.attr('data-step') || '2', 10);

            if(step === 2){
                var ok2 = navigateToStep($form, 3, { pushHash: true, scrollTop: true });
                if(!ok2){
                    syncHashWithCurrentStep($form);
                }
                return;
            }

            if(step === 3){
                var ok3 = navigateToStep($form, 4, { pushHash: true, scrollTop: true });
                if(!ok3){
                    syncHashWithCurrentStep($form);
                }
                return;
            }
        });

        // Prev (botón lateral)
        $(document).on('click', '[data-sp-wsv-prev]', function(e){
            e.preventDefault();
            clearNotices();

            var step = parseInt($form.attr('data-step') || '2', 10);

            if(step === 3){
                navigateToStep($form, 2, { pushHash: true, scrollTop: true });
                return;
            }
            if(step === 4){
                navigateToStep($form, 3, { pushHash: true, scrollTop: true });
                return;
            }
        });

        // Place order (botón lateral)
        $(document).on('click', '[data-sp-wsv-place-order]', function(e){
            e.preventDefault();
            clearNotices();

            // seguridad: validar de nuevo shipping/pago
            if(!validateStep3($form)){
                syncHashWithCurrentStep($form);
                return;
            }

            var $place = $('#place_order');
            if($place.length){
                $place.trigger('click');
            } else {
                $form.trigger('submit');
            }
        });

        // Back/Forward del navegador (hashchange)
        $(window).on('hashchange', function(){
            if(hashGuard) return;

            var requested = getStepFromHash();
            if(!requested) return;

            clearNotices();
            var ok = navigateToStep($form, requested, { pushHash: false, scrollTop: true });

            if(!ok){
                // Si el usuario intenta avanzar con back/forward sin cumplir requisitos, corregimos el hash sin crear entrada
                syncHashWithCurrentStep($form);
            }
        });

        $(window).on('popstate', function(){
            if(hashGuard) return;

            var requested = getStepFromHash();
            if(!requested) return;

            clearNotices();
            var ok = navigateToStep($form, requested, { pushHash: false, scrollTop: true });

            if(!ok){
                syncHashWithCurrentStep($form);
            }
        });

        // Mantener el estado si WooCommerce refresca fragments
        $(document.body).on('updated_checkout', function(){
            var step = parseInt($form.attr('data-step') || '2', 10);
            setActivePanel($form, step);
            syncStep4Summary($form, step);
            syncPlaceOrderLocation($form, step);
            lockSidebarShipping();

            // Si estamos navegando por hash, mantener sincronizado sin ensuciar historial
            var hStep = getStepFromHash();
            if(hStep && hStep !== step){
                syncHashWithCurrentStep($form);
            }

        });

        $(document).on('click', '.sp-wsv-jump-step[data-step]', function(e){
            e.preventDefault();
            clearNotices();
            var step = parseInt($(this).attr('data-step') || '2', 10);
            navigateToStep($form, step, { pushHash: true, scrollTop: true });
        });

    });

})(jQuery);
