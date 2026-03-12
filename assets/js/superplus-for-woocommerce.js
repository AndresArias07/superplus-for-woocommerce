jQuery(function ($) {
  function getProUrl() {
    var url =
      typeof SP_WSV_Admin !== "undefined" &&
      SP_WSV_Admin &&
      SP_WSV_Admin.pro_url
        ? SP_WSV_Admin.pro_url
        : "";
    url = (url || "").toString().trim();
    return url;
  }

  $("body").on("click", ".sp-wsv-go-pro-popup-trigger", function (e) {
    e.preventDefault();
    var url = getProUrl();
    if (!url) return;
    window.open(url, "_blank", "noopener,noreferrer");
  });
});
