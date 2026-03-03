# SuperPlus for Woocommerce

SuperPlus for Woocommerce is a **module manager** for WooCommerce: enable only what you need, without loading unnecessary features.

Includes the module: **Step-by-step checkout**.

---

## Requirements

- WordPress 6.0+
- WooCommerce (required)
- PHP 7.0+

---

## What’s included?

### ✅ Free module: Checkout (Step-by-step checkout)

A visual improvement to the checkout page with a step flow and simple styling options.

**Available options:**
- **Layout**:
  - Minimal step-by-step
  - WooCommerce step-by-step
- **Theme**:
  - No theme (uses your active theme styles)
  - Light
  - Dark
- **Additional CSS** (optional): injected at the end of the module CSS for fine tuning.

> Note: when the module is active, it uses its own templates for **Cart** and **Checkout** to apply the layout/structure.

---

## Installation

1. Install the plugin (ZIP or folder) in `wp-content/plugins/`.
2. Activate **SuperPlus for Woocommerce**.
3. Confirm **WooCommerce** is installed and active.

---

## Quick setup

1. **SuperPlus > Modules**  
   Enable the **Checkout** module (it remains enabled by default after installation).
2. **SuperPlus > Settings > Checkout**  
   Choose layout, theme, and (if you want) add CSS.
3. Open **Cart** and **Checkout** in another tab to preview and refresh after saving.

---

## FAQ

**Can I disable the module?**  
Yes. In **SuperPlus > Modules**. Disabled modules do not load hooks or assets.

**Is it compatible with other plugins that modify checkout?**  
If another plugin also replaces checkout/templates, there could be conflicts. In that case, try disabling the SuperPlus module.

---

## Changelog

### 1.0.1
- Minor naming updates and internal improvements before review.
- Module manager.
- Free module: Step-by-step checkout (layout, theme, and additional CSS).