# Tales of Rave — boutique photo (WordPress + WooCommerce)

Shop for [Tales of Rave](https://www.instagram.com/talesofrave/) (Mathou), activist photographer at free parties and protests.
Black-and-white documentary look, French content, built built from existing, open-source (GPL) building blocks:

| Need | Provided by |
|---|---|
| Base design | **Twenty Twenty-Five** (official WordPress block theme) |
| Photo-shop look, homepage, carousels | **Tales of Rave** child theme (`themes/tales-of-rave`) |
| Shop, accounts, inventory, order emails | **WooCommerce** |
| Payments | **WooCommerce Stripe Gateway**, **WooCommerce PayPal Payments** |
| Shipped / Delivered statuses + emails, GPS stripping | **Photo Shop Core** plugin (`plugins/photo-shop-core`) |
| Contact / custom-order form | **Contact Form 7** |
| SEO | **The SEO Framework** |
| Caching | **WP Super Cache** |
| Backups | **UpdraftPlus** |
| Lightbox | WordPress core (enabled for every image in `theme.json`) |

## Run it locally

Requirements: Docker, Node.js 18+.

```bash
npm install
npm run start    # starts WordPress at http://localhost:8888 (admin / password)
npm run setup    # activates the theme, configures WooCommerce, creates categories, sample products and pages
```

`npm run stop` stops it, `npm run destroy` deletes everything.

## What the setup creates

- Site in French (WordPress + WooCommerce translations, French price format)
- Categories: Tirages photo, Tirages grand format, Téléchargements, Commandes sur mesure
- A `Format` attribute (A4, A3, A2, 50x70 cm) for variable print products
- 5 sample products (2 featured, shown in « La sélection »)
- Home page: manifesto hero, latest prints carousel, the « Fête libre et répression » exhibition,
  values (consent, collective memory, proof) with a photo-removal request, shop, bookings, press
- Contact page (custom prints, reportages, weddings, hosting the exhibition, photo removal)
- Draft *Mentions légales* and *CGV* pages (required by French law)

## Daily use

- **Add a photo for sale:** Products → Add new. Upload a web-sized image (about 2000 px) as the product image.
  For digital downloads, tick *Virtual* + *Downloadable* and attach the full-resolution file.
  For prints in several sizes, choose *Variable product* and use the `Size` attribute.
- **Feature a photo:** click the star in the products list.
- **Orders:** WooCommerce → Orders. Change the status to *Shipped* or *Delivered* (one by one or with
  bulk actions) and the customer gets an email automatically. Edit the email texts in
  WooCommerce → Settings → Emails.
- **Carousel on any block:** select a Gallery or Product Collection's product template and pick the *Carrousel* style. Other styles: *Noir et blanc* (images), *Pancarte* (cardboard-sign label), *Souligné rouge* (headings).

## Before going live

1. **Hosting** with PHP 8.1+, HTTPS (Let's Encrypt) and Imagick. Copy `themes/tales-of-rave` and
   `plugins/photo-shop-core` to `wp-content/`, install the plugins listed above from the admin.
2. **Payments:** connect Stripe and PayPal in WooCommerce → Settings → Payments. Test in sandbox mode first.
3. **Downloads:** WooCommerce → Settings → Products → Downloads → set the method to *X-Accel-Redirect/X-Sendfile*
   if your server supports it.
4. **Backups:** in UpdraftPlus, send backups to remote storage (Backblaze B2, S3, Google Drive…), never only on the server.
5. **Legal:** fill in *Mentions légales*, *CGV* and the privacy policy page; add a cookie banner if you add analytics.
6. **Email deliverability:** use an SMTP plugin (e.g. FluentSMTP) so order emails don't land in spam.

## Fonts

Archivo and Archivo Black (SIL Open Font License, see `assets/fonts/OFL.txt`) are self-hosted: no request to Google, which is simpler for GDPR.

## Development

```bash
composer install
composer run lint   # WordPress Coding Standards (PHPCS)
composer run fix    # auto-fix
```

Only the custom theme and plugin are in git; WordPress, third-party plugins, the database and uploads are not.
