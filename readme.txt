=== SuperPlus for Woocommerce ===
Contributors: agenciasp
Tags: checkout, woocommerce, step-checkout, multi-step, ux
Requires Plugins: woocommerce
Requires at least: 4.7
Tested up to: 6.9
Stable tag: 1.0.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

SuperPlus adds a step-by-step checkout and UX improvements with a simple modules manager for WooCommerce.

== Description ==

SuperPlus is built for WooCommerce stores that want quick improvements without “bloated” features they won’t use. From the **Modules Manager** panel, you can enable/disable modules (disabled modules do not load hooks or assets).

It includes a free module:

* **Checkout (Step-by-step checkout)**: visual improvement of the checkout with a step flow, step header, and simple styling options.

= Included module: Checkout (Step-by-step checkout) =

Key features:

* Two **layouts** (style/flow): *Minimal step-by-step* and *WooCommerce step-by-step*.
* **Theme** selector: *No theme*, *Light*, or *Dark* (only affects colors/contrast).
* **Additional CSS** field for fine tuning (injected at the end of the module CSS).

Note: when active, the module uses its own templates for **Cart** and **Checkout** to apply the layout/structure.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the ZIP via **Plugins > Add New**.
2. Activate **SuperPlus for Woocommerce** from **Plugins**.
3. Make sure **WooCommerce** is installed and active.

== Configuration ==

1. Go to **SuperPlus > Modules** and enable the **Checkout** module (it remains enabled by default after installation).
2. Go to **SuperPlus > Settings > Checkout**.
3. Choose:
   * **Layout** (Minimal step-by-step / WooCommerce step-by-step)
   * **Theme** (No theme / Light / Dark)
   * **Additional CSS** (optional)
4. Open **Cart** and **Checkout** in another tab to preview and refresh after saving.

== Frequently Asked Questions ==

= Do I need WooCommerce? =
Yes. SuperPlus is designed for WooCommerce, and the checkout module depends on it.

= Can I disable the checkout module? =
Yes. In **SuperPlus > Modules** you can disable the module. When disabled, it stops loading module hooks, assets, and templates.

= Can it conflict with other checkout plugins? =
If another plugin also replaces checkout templates/structure, there may be incompatibilities. In that case, try disabling the SuperPlus module or keep only one checkout customization system active.

= What does the “Additional CSS” field do? =
It lets you add your own CSS without editing the plugin. It’s added at the end of the module CSS to make visual overrides easier.

== Screenshots ==

1. Modules manager (enable/disable).
2. “Checkout” module settings.
3. Step-by-step checkout view (frontend).

== Changelog ==

= 1.0.1 =
* Minor naming updates and internal improvements before review.
* Module manager.
* Free module: Checkout (step-by-step) with layout, theme, and additional CSS options.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
