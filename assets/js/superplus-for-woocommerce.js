jQuery(function($){
  function getProUrl() {
    var url = (typeof SP_WSV_Admin !== "undefined" && SP_WSV_Admin && SP_WSV_Admin.pro_url) ? SP_WSV_Admin.pro_url : "";
    url = (url || "").toString().trim();
    return url;
  }

  $("body").on("click", ".sp-wsv-go-pro-popup-trigger, #toplevel_page_sp-wsv a[href*=\"page=sp-wsv-get-pro\"]", function(e){
    e.preventDefault();
    var url = getProUrl();
    if (!url) return;

    let btn = $("<a/>")
      .text("Obtén versión PRO")
      .addClass("sp-wsv-go-pro-link")
      .attr("href", url)
      .attr("target", "_blank")
      .attr("rel", "noopener noreferrer")
      .prop("outerHTML");

    window.crear_sp_popup("go-pro-popup", "Obtén versión PRO", btn);
  });

  $("body").on("click", ".sp-wsv-pro-banner.is-dismissible .notice-dismiss", function(){
    if (typeof SP_WSV_Admin === "undefined") return;
    if (!SP_WSV_Admin || !SP_WSV_Admin.ajax_url || !SP_WSV_Admin.nonce) return;
    $.post(SP_WSV_Admin.ajax_url, {
      action: "sp_wsv_dismiss_pro_banner",
      nonce: SP_WSV_Admin.nonce
    });
  });
});


