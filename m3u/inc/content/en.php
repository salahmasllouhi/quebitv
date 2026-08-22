<?php
/**
 * The English article body, as a provisioning seed.
 *
 * m3u-pages-setup.php writes this into post_content once, and after that the
 * block editor owns it — this file is not read on render. It exists so the page
 * can be rebuilt from the repo rather than from a database backup.
 *
 * This is the prose from the old page, with everything WordPress broke removed:
 * the ~460 lines of unwrapped CSS, the ~40 lines of unwrapped JavaScript, the
 * hand-built table of contents (m3u-article.php now generates one from the h2
 * ids, so it can never drift), the five FAQ entries (they moved to
 * m3u-strings.php, where the accordion and the FAQPage schema read the same
 * array), and the inline style attributes carrying the old teal palette.
 *
 * @package Quebec_IPTV
 */

return <<<'HTML'
<!-- wp:paragraph -->
<p>Need to pull Xtream Codes credentials out of an <strong>M3U playlist</strong> URL? Use the free converter above — it runs entirely in your browser — then read on for what these files are and how to get the most out of them.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>An M3U playlist is the backbone of most streaming setups, connecting your player to thousands of live channels and on-demand titles. Millions of people handle these files daily without ever learning what is inside them, which is why so much time gets lost to problems that take thirty seconds to fix once you know the shape of the format.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 id="what-is-m3u">What is an M3U playlist file?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>An <strong>M3U playlist</strong> is a plain text file holding references to media files or streaming URLs. It was built for MP3 audio — the name is short for "MP3 URL" — and grew into the standard way IPTV services deliver channel lists. The extension is either <code>.m3u</code> for a standard playlist or <code>.m3u8</code> for the UTF-8 version that supports international characters.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Open one in a text editor and you will see a structured list of URLs and metadata telling your player where each channel lives. That simplicity is the point: a provider can deliver tens of thousands of channels through a single file that is trivial to update.</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<figure class="wp-block-image"><img src="https://quebeciptv.co/wp-content/uploads/2026/02/image-1.png" alt="M3U editor view of a playlist file structure, showing how IPTV streaming entries are laid out"/></figure>
<!-- /wp:image -->

<!-- wp:html -->
<div class="m3u-callout"><p><strong>Quick fact:</strong> a typical provider playlist carries anywhere from 1,000 to 50,000 channels, organised with categories, logos and electronic programme guide data.</p></div>
<!-- /wp:html -->

<!-- wp:heading {"level":3} -->
<h3>Basic M3U structure</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every playlist opens with the <code>#EXTM3U</code> header, followed by one entry per item. A minimal example looks like this:</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<div class="m3u-code">#EXTM3U
#EXTINF:-1 tvg-id="channel1" tvg-logo="logo.png" group-title="Sports",Sports Channel HD
http://example.com/live/username/password/12345.ts
#EXTINF:-1 tvg-id="channel2" tvg-logo="logo2.png" group-title="Movies",Movie Channel HD
http://example.com/live/username/password/12346.ts</div>
<!-- /wp:html -->

<!-- wp:heading -->
<h2 id="why-matters">Why the format matters</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The M3U format became essential for one reason: it is not owned by anybody. According to <a href="https://www.statista.com/topics/1945/online-video-usage-in-the-united-states/" target="_blank" rel="noopener">Statista's streaming research</a>, most internet users worldwide now watch video through streaming services, and open playlist formats do a great deal of that delivery.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Unlike proprietary formats that tie you to one application, an M3U playlist works everywhere. VLC, Kodi, TiviMate, IPTV Smarters — the same file feeds all of them. That portability is why cord-cutters keep choosing it over whatever a given app would prefer.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>What you get from an open format</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Three things, mainly. It runs unmodified on Windows, macOS, Linux, Android, iOS and smart TVs. It stays small — a few kilobytes even with thousands of entries — so it is easy to move around. And the extended version carries real metadata: channel logos, categories and programme guide identifiers, which is what turns a list of URLs into something that looks like a TV guide.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 id="seven-ways">Seven ways to use your playlist</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Most people use a playlist exactly one way and never discover the rest. Here are seven that are worth knowing.</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<figure class="wp-block-image"><img src="https://quebeciptv.co/wp-content/uploads/2026/02/image-2.png" alt="An m3u editor showing playlist channel entries and the URL structure behind them"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"level":3} -->
<h3>1. Load the URL straight into a media player</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The shortest path. Copy your playlist link, open "Open Network Stream" in VLC, PotPlayer or IINA, and paste it. The whole channel lineup appears within seconds, with nothing installed and nothing configured.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>2. Convert it to Xtream Codes credentials</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>TiviMate, IPTV Smarters and XCIPTV all behave better given a server address, username and password than a raw playlist file — you get catch-up TV, proper series organisation and faster channel switching. The converter at the top of this page pulls those three values out of your URL without sending it anywhere.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>3. Import it into Kodi</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Kodi's PVR IPTV Simple Client add-on brings live TV into Kodi's own interface, complete with guide data and recording. Go to Settings, then Add-ons, and search for it. Point it at your playlist URL and your guide URL, and you are done.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>4. Build a shorter, custom channel list</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Nobody watches forty thousand channels. Open the file in any text editor, delete the entries you will never use, and save it with the same extension. A trimmed playlist loads faster and is far less tiring to scroll through.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>5. Set it up on a smart TV</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Samsung, LG and most other modern sets support playlist playback through Smart IPTV, SS IPTV or OTTPlayer. Upload the URL and the television becomes a full IPTV receiver with no extra hardware. For receivers specifically, our <a href="https://quebeciptv.co/en/blog/iptv-enigma-tv-enigma2-setup">Enigma2 setup guide</a> covers the box-side configuration in detail.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>6. Use it on a phone or tablet</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>GSE Smart IPTV, IPTV Smarters and Perfect Player all accept either the playlist URL or Xtream credentials. Enter them once and the same subscription follows you onto any connection.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>7. Manage several playlists at once</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Once you are maintaining more than one list, a web-based manager earns its keep: merging, deduplicating and refreshing on a schedule beats editing files by hand every week.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 id="formats">Understanding the different formats</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Not every M3U file is the same, and knowing which one you have explains most compatibility surprises.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Standard M3U</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Plain file paths or URLs, one per line, no metadata. It works, but there are no channel names, no logos and no categories — which is why it survives mostly for local media collections rather than IPTV.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>M3U Plus, or extended M3U</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>This is the one providers actually ship. The <code>#EXTINF</code> directive adds channel names, durations, logos via <code>tvg-logo</code>, guide identifiers via <code>tvg-id</code> and categories via <code>group-title</code>. Everything that makes a playlist navigable lives in this line.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>M3U8, the HLS playlist</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>M3U8 uses UTF-8 encoding and is what HTTP Live Streaming requires. As <a href="https://developer.apple.com/documentation/http-live-streaming" target="_blank" rel="noopener">Apple's HLS documentation</a> describes, this is the format that enables adaptive bitrate streaming — the stream drops or raises quality as your connection changes, rather than stalling.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 id="mistakes">Four mistakes worth avoiding</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Experienced users make these too. Each one costs an evening if you do not recognise it.</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<figure class="wp-block-image"><img src="https://quebeciptv.co/wp-content/uploads/2026/02/image-3.png" alt="Comparison chart of M3U and M3U8 HLS playlist formats for IPTV streaming"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"level":3} -->
<h3>Using an expired link</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Provider playlist URLs often carry authentication tokens with an expiry. When a playlist stops working all at once, do not troubleshoot individual channels — request a fresh URL. Any decent provider, ours included, hands you a renewable link rather than a disposable one.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Saving with the wrong encoding</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Edit a playlist in an editor set to ASCII and every accented channel name comes back mangled. Always save as UTF-8, particularly with French, Nordic or Arabic channel names in the list.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Skipping the guide URL</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A playlist without its accompanying EPG gives you channels but no schedules, descriptions or upcoming programmes. Ask your provider for both URLs — they are separate, and the second one is easy to forget.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Sharing the URL publicly</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Your playlist URL contains your username and password in plain text. Posting it on a forum hands your subscription to strangers and usually gets the account suspended for sharing.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<div class="m3u-warning"><p><strong>Treat the URL as a password.</strong> It carries your credentials. That is also why the converter on this page runs entirely in your browser and never transmits what you paste.</p></div>
<!-- /wp:html -->

<!-- wp:heading -->
<h2 id="best-practices">Getting the best out of it</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Four habits separate a setup that works from one you are forever fixing.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Refresh weekly</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Channel lineups and stream URLs move more often than most people expect. Refresh weekly, or let your app do it on a 24-hour schedule.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Match the app to the device</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>TiviMate is excellent on Android TV, IINA is the nicest option on macOS, and VLC works everywhere without being the best anywhere. Pick per device rather than standardising on one.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Give it a stable connection</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Wired ethernet beats Wi-Fi for anything you watch on a big screen. If your router supports quality-of-service rules, giving streaming traffic priority removes most buffering complaints outright.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Keep the credentials somewhere safe</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Save the playlist URL and your Xtream credentials in a password manager. Setting up a new device then takes a minute instead of an email exchange with support.</p>
<!-- /wp:paragraph -->
HTML;
