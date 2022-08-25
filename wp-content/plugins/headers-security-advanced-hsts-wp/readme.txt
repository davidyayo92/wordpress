=== Plugin Name ===
Contributors: unicorn03, unicorn07
Donate link: https://tentacleplugins.com/
Tags: headers security, hsts, http headers, insecure content, force ssl, headers, login security, xss, clickjacking, mitm, cross origin, cross site, privacy, csp
Requires at least: 4.7
Tested up to: 6.0 
Stable tag: 5.0.04
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Best all-in-one WordPress security plugin, uses HTTP & HSTS response headers to avoid vulnerabilities: XSS, injection, clickjacking. Force HTTP/HTTPS.

== Description ==

= ENGLISH =

**Headers Security Advanced & HSTS WP** is Best all-in-one a free plug-in for all WordPress users. Deactivating this plugin will return your site configuration exactly to the state it was in before.

The **Headers Security Advanced & HSTS WP** project implements HTTP response headers that your site can use to increase the security of your website. The plug-in will automatically set up all Best Practices (you don't have to think about anything), these HTTP response headers can prevent modern browsers from running into easily predictable vulnerabilities. The Headers Security Advanced & HSTS WP project wants to popularize and increase awareness and usage of these headers for all wordpress users.

This plugin is developed by TentaclePlugins, we care about WordPress security and best practices.

Check out the best features of **Headers Security Advanced & HSTS WP:**

  * HSA Limit Login to block brute force attacks.
  * X-XSS-Protection
  * Expect-CT
  * Access-Control-Allow-Origin
  * Access-Control-Allow-Methods
  * Access-Control-Allow-Headers
  * X-Content-Security-Policy
  * X-Content-Type-Options
  * X-Frame-Options
  * X-Permitted-Cross-Domain-Policies
  * X-Powered-By
  * Content-Security-Policy
  * Referrer-Policy
  * HTTP Strict Transport Security / HSTS
  * Content-Security-Policy
  * Clear-Site-Data
  * Cross-Origin-Embedder-Policy-Report-Only
  * Cross-Origin-Opener-Policy-Report-Only
  * Cross-Origin-Embedder-Policy
  * Cross-Origin-Opener-Policy
  * Cross-Origin-Resource-Policy
  * Permissions-Policy
  * Strict-dynamic
  * Strict-Transport-Security
  * FLoC (Federated Learning of Cohorts)

**Headers Security Advanced & HSTS WP** is based on **OWASP CSRF** to protect your wordpress site. Using OWASP CSRF, once the plugin is installed, it will provide full CSRF mitigation without having to call a method to use nonce on the output. The site will be secure despite having other vulnerable plugins (CSRF).

HTTP security headers are a critical part of your website's security. After automatic implementation with Headers Security Advanced & HSTS WP, they protect you from the most notorious types of attacks your site might encounter. These headers protect against XSS, code injection, clickjacking, etc.

We have implemented **FLoC (Federated Learning of Cohorts)**, using best practices. First, using **Headers Security Advanced & HSTS WP** prevents the browser from including your site in the "cohort calculation" on **FLoC (Federated Learning of Cohorts)**. This means that nothing can call document.interestCohort() to get the FLoC ID of the currently used client. Obviously, this does nothing outside of your currently visited site and does not "disable" FLoC on the client beyond that scope.

Even though **FLoC** is still fairly new and not yet widely supported, as programmers we think that privacy protection elements are important, so we choose to give you the feature of being opt out of FLoC! We’ve created a special **“automatic blocking of FLoC”** feature, trying to always **offer the best tool with privacy protection and cyber security** as main targets and focus.

Analyze your site before and after using *Headers Security Advanced & HSTS WP* security headers are self-configured according to HTTP Security Headers and HTTP Strict Transport Security / HSTS best practices.

* Check HTTP Security Headers on <a href="https://securityheaders.com/" target="_blank">securityheaders.com</a> 
* Check HTTP Strict Transport Security / HSTS at <a href="https://hstspreload.org/" target="_blank">hstspreload.org</a>
* Check WebPageTest at <a href="https://www.webpagetest.org/" target="_blank">webpagetest.org</a>
* Check HSTS test website <a href="https://gf.dev/hsts-test/" target="_blank">gf.dev/hsts-test</a>

This plugin is updated periodically, our limited support is free, we are available for your feedback (bugs, compatibility issues or recommendations for next updates). We are usually fast :-D.

== Frequently Asked Questions ==

= How do you get an A+ grade? =

To earn an A+ grade, your site must issue all HTTP response headers that we check. This indicates a high level of commitment to improving the security of your visitors.

= What headers are recommended? =

Over an HTTP connection we get Content-Security-Policy, X-Content-Type-Options, X-Frame-Options and X-XSS-Protection. Via an HTTPS connection, 2 additional headers are checked for presence which are Strict-Transport-Security and Public-Key-Pins.

* Once the plug-in is activated it performs a test (before and after): <a href="https://securityheaders.com/" target="_blank">https://securityheaders.com/</a>

= Can the plugin create slowdowns? =

No, Headers Security Advanced & HSTS WP is Fast, Secure and does not affect the SEO and speed of your website.

= What is HSTS (Strict Transport Security)? =

It was created as a solution to force the browser to use secure connections when a site is running on HTTPS. It is a security header that is added to the web server and reflected in the response header as Strict-Transport-Security. HSTS is important because it addresses the following anomalies:

= Check before and after using Preload HSTS =

This step is important to submit your website and/or domain to an approved HSTS list. Google officially compiles this list and it is used by Chrome, Firefox, Opera, Safari, IE11 and Edge. You can forward your site to the official HSTS preload directory. ('https://hstspreload.org/')

= how to use HTTP Strict Transport Security (HSTS) =

If you want to use Preload HSTS for your site, there are a few requirements before you can activate it.

* Have a valid SSL certificate. You can't do any of this anyway without it.
* You must redirect all HTTP traffic to HTTPS (recommended via permanent 301 redirects). This means that your site should be HTTPS only.
* You need to serve all subdomains in HTTPS as well. If you have subdomains, you will need an SSL certificate.

The HSTS header on your base domain (for example: example.com) is already configured you just need to activate the plug-in.

If you want to check the HSTS status of your site, you can do so here: <a href="https://hstspreload.org/" target="_blank">https://hstspreload.org/</a>

= Can I report a bug or request a feature? =

You can report bugs or request new features right <a href="mailto:support@tentacleplugins.com">click here !</a>

= Disable FLoC, Google's advertising technology =

FLoC is a mega tracker that monitors user activity on all sites, stores the information in the browser, and then uses machine learning to place users into cohorts with similar interests. This way, advertisers can target groups of people with similar interests. Plus, according to Google's own testing, FLoC achieves at least 95% more conversions than cookies.

= Who is disabling FLoC by Google? =

Scott Helme reported that as of May 3, already 967 of the first 1 million domains had disabled FLoC's interest-cohort in their Permissions-Policy header. That list included some big sites like The Guardian and IKEA.

== Installation ==

= ITALIAN =

1. Vai in Plugin 'Aggiungi nuovo'.
2. Cerca Headers Security Advanced & HSTS WP.
3. Cerca questo plugin, scaricalo e attivalo.
4. Vai in 'impostazioni' > 'Permalink'. Cambia il tuo url di login alla voce 'Security Url'.
5. Puoi cambiare questa opzione quando vuoi, Headers Security Advanced & HSTS WP viene impostato in automatico.

= ENGLISH =

1. Go to Plugins 'Add New'.
2. Search for Headers Security Advanced & HSTS WP.
3. Search for this plugin, download and activate it.
4. Go to 'settings' > 'Permalink'. Change your login url to 'Security Url'.
5. You can change this option whenever you want, Headers Security Advanced & HSTS WP is set automatically.

= FRANÇAIS =

1. Allez dans Plugins 'Add new'.
2. Recherchez Headers Security Advanced & HSTS WP.
3. Recherchez ce plugin, téléchargez-le et activez-le.
4. Allez dans "Paramètres" > "Lien permanent". Changez votre url de connexion en 'Security Url'.
5. Vous pouvez modifier cette option quand vous le souhaitez, Headers Security Advanced & HSTS WP est réglé automatiquement.

= DEUTSCH =

1. Gehen Sie zu Plugins 'Neu hinzufügen'.
2. Suchen Sie nach Headers Security Advanced & HSTS WP.
3. Suchen Sie nach diesem Plugin, laden Sie es herunter und aktivieren Sie es.
4. Gehen Sie zu "Einstellungen" > "Permalink". Ändern Sie Ihre Login-Url in 'Security Url'.
5. Sie können diese Option jederzeit ändern, Headers Security Advanced & HSTS WP wird automatisch eingestellt.

== Screenshots ==

1. Check HTTP Security Headers (AFTER)
2. Check HTTP Security Headers (BEFORE)
3. Check HTTP Strict Transport Security / HSTS (list)
4. Check WebPageTest (AFTER)
5. Check WebPageTest (BEFORE)
6. Setting on single site installation
7. Check HTTP Security Headers - Serpworx (AFTER)
8. Check HTTP Security Headers - Serpworx (BEFORE)
9. Site-wide security setting

== Changelog ==

= 5.0.04 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 5.0.04 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- New: new header security directives test final test (Permissions-Policy).

= 5.0.03 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 5.0.03 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- Update: some issues that could occur on some browsers and operating systems that implemented payment systems (Stripe, Paypal) have been resolved and fixed;
- New: new header security directives (Permissions-Policy) have been implemented and tested, here are some directives: new security directives for the header (Permissions-Policy) have been implemented and tested, here are some directives: accelerometer, ambient-light-sensor, autoplay, battery, camera, cross-origin-isolated, display-capture, document-domain, encrypted-media, execution-while-not-rendered, execution-while-out-of-viewport, fullscreen, geolocation, gyroscope, keyboard-map, magnetometer, microphone, midi=, navigation-override, payment, picture-in-picture, publickey-credentials-get, screen-wake-lock, sync-xhr, usb, web-share, xr-spatial-tracking, gamepad, conversion-measurement, focus-without-user-activation, serial, window-placement, vertical-scroll.

= 5.0.02 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 5.0.02 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- Fixed: We fixed a problem with "Parse error: syntax error" that could occur on some websites;
- Fixed: We fixed a problem with "Payment gateweay";
- Fixed: We fixed a problem with "Permissions-Policy" that could occur on some websites;

= 5.0.01 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 5.0.01 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- Fixed: issue with using gateweay of stripe payments (in praticular the use of external layers like checkout.stripe.com);
- Fixed: issue with the use of some stric-dynamic directives that could cause a warning to be displayed in the DOM;
- Update: Fixed eliminated annoying bugs and we are ready to reduce the weight of the plugin by 18%;
- Update: Compatibility with Cloudflare CDN Alternatives, Fastly Deliver, Akamai CDN, CloudFront CDN, Google Cloud CDN, Microsoft Azure CDN , Tata Communications CDN, StackPath CDN.

= 4.8.98 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.98 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- Fixed: We fixed and used the strict-origin-when-cross-origin referrer policy setting. This header retains much of the usefulness of the referrer, mitigating the risk of data leakage between cross-origins.

= 4.8.96 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.96 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- Fixed: Fixed issue that could show in own console log an error of (syntax error);
- Upgrade: Speeded up loading and compatibility with some third-party plugins;
- Upgrade: Updated some optimization functions of Wordpress version 6.0;

= 4.8.94 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.94 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- Update: optimization and resolution external application compatibility;
- Fixed: solved problem with some headers and debug optimizations;

= 4.8.93 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.93 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- Fixed: optimization and resolution external application compatibility;
- Fixed: solved problem with some headers and debug optimizations;
- Update: We fixed some issues that could occur with the "full screen" method;

= 4.8.92 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.90 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- Fixed: Compatibility with version 6.0 of Wordpress
- Fixed: redirection errors could occur ERR_TOO_MANY_REDIRECTS 

= 4.8.91 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.90 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- New: Compatibility with version 6.0 of Wordpress
- Update: We fixed some issues that could occur with the "full screen" method

= 4.8.90 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.90 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- New: compatibility Wordpress 6.0

= 4.8.89 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.89 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- Fixed: We fixed an issue that could occur with a renamed version of a header parameter, now we have optimized the request;

= 4.8.88 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.88 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- New: Added New X-Permitted-Cross-Domain-Policies;
- New: Optimization with the Serpworx tool (Check Your Security Headers);
- Add: Added new "Feature-Policies" such as: push=(), vibrate=(), fullscreen();
- Fixed: We fixed a problem with the debug.log file that could show the following warning (PHP Notice: Undefined index);

= 4.8.86 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.86 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- Fixed: We fixed a problem with the debug.log file that could show the following warning (PHP Notice: Undefined index);
- Fixed: fixed the problem with the wordpress widget, it could cause the wrong display of the favicon;

= 4.8.85 =
We don't want to tell you what to do, but here's the thing: if you updated Headers Security Advanced & HSTS WP plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.85 (we've exterminated some bugs, fixed some annoying pixels and refreshed the graphics) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Enjoy
- Fixed: We fixed a problem with the debug.log file that could show the following warning (PHP Notice: Undefined index);

= 4.8.6 =
We don't want to tell you what to do, but here's the thing: if you updated the plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.6 (we've improved some crazy programmer stuff) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Now let's get started right away to the next code and update to do 😀 we're crazy but we like this one
- Fixed: We have fixed an issue with the X-Frame-Options header;

= 4.8.3 =
We don't want to tell you what to do, but here's the thing: if you updated the plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.3 (we've improved some crazy programmer stuff) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Now let's get started right away to the next code and update to do 😀 we're crazy but we like this one
- Fixed: This is the latest version to fix and make compatible with themes, plugins that could create conflicts with Vimeo and Youtube implementation.

= 4.8.0 =
We don't want to tell you what to do, but here's the thing: if you updated the plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.8.0 (we've improved some crazy programmer stuff) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Now let's get started right away to the next code and update to do 😀 we're crazy but we like this one
- Fixed: We have fixed some issues with Vimeo viewing

= 4.7.30 =
We don't want to tell you what to do, but here's the thing: if you updated the plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.7.30 (we've improved some crazy programmer stuff) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Now let's get started right away to the next code and update to do 😀 we're crazy but we like this one
- Fixed: We found some bugs and now the plugin is more optimized and happy :-D 
- Fixed: We have fixed some issues with Vimeo viewing
- Update: Wordpress 5.9

= 4.7.20 =
We don't want to tell you what to do, but here's the thing: if you updated the plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this version 4.7.20 (we've improved some crazy programmer stuff) and everything works like a charm. So we're in agreement? Tap "update" and we'll give you the coolest, fastest and most awesome plugin out there with the best updates in the world. Now let's get started right away to the next code and update to do 😀 we're crazy but we like this one
- New: Wordpress 5.9
- Fixed: We've listened to your feedback and have momentarily disabled the ability to customize the url

= 4.7.15 =
We don’t want to tell you what to do, but here’s the thing: if you updated the plugin last time, you saw that when we propose to do it, we don’t just say it. Well, we’ve added and fixed a lot of things with this 4.7.15 version (we’ve improved some crazy programmer stuff) and everything works like a charm. So are we on board? Tap on “update” and we’ll give you the coolest, fastest, most awesome plugin out there with the best updates in the world. Now let’s get started right away to the next code and update to do 😀 we are crazy but we like this
* Fixed: we have solved the error that was shown in QueryMonitor Undefined property

= 4.7.1 =
We don’t want to tell you what to do, but here’s the thing: if you updated the plugin last time, you saw that when we propose to do it, we don’t just say it. Well, we’ve added and fixed a lot of things with this 4.7.1 version (we’ve improved some crazy programmer stuff) and everything works like a charm. So are we on board? Tap on “update” and we’ll give you the coolest, fastest, most awesome plugin out there with the best updates in the world. Now let’s get started right away to the next code and update to do 😀 we are crazy but we like this
* Fixed: "All the little beings that generated errors and bugs have been exterminated. We know we are very attentive to details"
* Update: "Third-party plugin optimization such as cache, cloudflare and redirects"

= 4.7.0 =
IMPORTANT: This update optimizes and fixes some issues that may occur with a cache manager.
We don't want to tell you what to do, but here's the thing: if you updated the plugin last time, you saw that when we propose to do it, we don't just say it. Well, we've added and fixed a lot of things with this 4.7.0 version (we've improved some crazy programmer stuff) and everything works like a charm. So are we on board? Tap on "update" and we'll give you the coolest, fastest, most awesome plugin out there with the best updates in the world. Now let's get started right away to the next code and update to do :D we are crazy but we like this
* Update: "X Powered By"
* Update: Content Security Policy optimization (CSP Header) and internal testing with Chrome, Firefox, Safari, Edge 
* Updated: "accelerometer block"
* Updated: "gyroscope block"
* Updated: "magnetometer block"
* Updated: "usb block"