<?php
/**
 * French copy for the M3U converter. english => francais (Quebec).
 *
 * Keys are the exact English defaults passed to m3u_str(); a key that does not
 * match character for character falls back to English silently, so copy edited
 * in m3u-strings.php has to be mirrored here.
 *
 * "Quebec" is written without the accent throughout, as everywhere else on the
 * site — it is the brand name here, not the place name.
 *
 * @package Quebec_IPTV
 */

return array(

    // ── Hero ─────────────────────────────────────────────────────────────────
    'Free tool' => 'Outil gratuit',
    'M3U editor and playlist converter' => 'Editeur M3U et convertisseur de liste de lecture',
    'Paste an M3U playlist URL and get the Xtream Codes server, username and password out of it — instantly, and without the URL ever leaving your browser.'
        => 'Collez l’URL d’une liste de lecture M3U et récupérez le serveur, le nom d’utilisateur et le mot de passe Xtream Codes — instantanément, sans que l’URL ne quitte jamais votre navigateur.',
    'Open the converter' => 'Ouvrir le convertisseur',

    // ── The tool ─────────────────────────────────────────────────────────────
    'M3U to Xtream Codes converter' => 'Convertisseur M3U vers Xtream Codes',
    'Works with both URL shapes: query parameters and path-based playlists.'
        => 'Fonctionne avec les deux formats d’URL : paramètres de requête et listes de lecture par chemin.',
    'Your M3U playlist URL' => 'L’URL de votre liste de lecture M3U',
    'http://example.com:8080/get.php?username=…&password=…'
        => 'http://exemple.com:8080/get.php?username=…&password=…',
    'Extract credentials' => 'Extraire les identifiants',
    'Nothing you paste leaves your browser. The conversion runs entirely on this page — no request is made, and nothing is stored or logged.'
        => 'Rien de ce que vous collez ne quitte votre navigateur. La conversion se fait entièrement sur cette page — aucune requête n’est envoyée, rien n’est conservé ni journalisé.',
    'Credentials extracted' => 'Identifiants extraits',
    'Server URL' => 'URL du serveur',
    'Username' => 'Nom d’utilisateur',
    'Password' => 'Mot de passe',
    'Copy all three' => 'Tout copier',
    'Copied' => 'Copié',
    'Could not copy — select the values and copy them by hand.'
        => 'Copie impossible — sélectionnez les valeurs et copiez-les à la main.',

    // ── Errors ───────────────────────────────────────────────────────────────
    'Enter an M3U playlist URL first.' => 'Entrez d’abord l’URL d’une liste de lecture M3U.',
    'That does not look like a URL. Check it and try again.'
        => 'Cela ne ressemble pas à une URL. Vérifiez-la et réessayez.',
    'No username and password found in that URL. Xtream playlists usually look like /get.php?username=…&password=… or /username/password/.'
        => 'Aucun nom d’utilisateur ni mot de passe trouvé dans cette URL. Les listes Xtream ressemblent généralement à /get.php?username=…&password=… ou /nomutilisateur/motdepasse/.',

    // ── Article + TOC ────────────────────────────────────────────────────────
    'On this page' => 'Sur cette page',

    // ── FAQ ──────────────────────────────────────────────────────────────────
    'Questions about M3U playlists' => 'Questions sur les listes de lecture M3U',

    'What is the difference between M3U and M3U8 playlist files?'
        => 'Quelle est la différence entre les fichiers M3U et M3U8 ?',
    'The difference is character encoding and what the file is for. A plain M3U uses ASCII, which covers basic Latin characters; an M3U8 uses UTF-8, so channel names with accents and non-Latin scripts survive. M3U8 is also the format HTTP Live Streaming requires, which is what lets a stream change quality as your connection does.'
        => 'La différence tient à l’encodage des caractères et à l’usage du fichier. Un M3U simple utilise l’ASCII, qui couvre les caractères latins de base ; un M3U8 utilise l’UTF-8, ce qui préserve les noms de chaînes accentués et les alphabets non latins. Le M3U8 est aussi le format exigé par le HTTP Live Streaming, celui qui permet à un flux d’ajuster sa qualité selon votre connexion.',

    'Can I edit my M3U file by hand?' => 'Puis-je modifier mon fichier M3U à la main ?',
    'Yes — it is a plain text file, so Notepad, TextEdit or VS Code will all open it. You can delete channels you never watch, reorder groups, or change stream URLs. Save it as UTF-8 when you are done, or accented channel names will come back as question marks.'
        => 'Oui — c’est un simple fichier texte, que Bloc-notes, TextEdit ou VS Code ouvriront sans problème. Vous pouvez supprimer les chaînes que vous ne regardez jamais, réorganiser les groupes ou modifier les URL de flux. Enregistrez-le en UTF-8 une fois terminé, sinon les noms de chaînes accentués reviendront en points d’interrogation.',

    'Why do some channels in my playlist not work?'
        => 'Pourquoi certaines chaînes de ma liste ne fonctionnent-elles pas ?',
    'Usually an expired stream URL, server maintenance, a regional restriction, or peak-hour load. Reload the playlist first — that fixes most of it. If the same handful of channels fail every time, the problem is on the provider’s side and worth reporting to them.'
        => 'Le plus souvent : une URL de flux expirée, une maintenance du serveur, une restriction régionale ou la charge aux heures de pointe. Rechargez d’abord la liste — cela règle la majorité des cas. Si les mêmes chaînes échouent chaque fois, le problème vient du fournisseur et vaut la peine de lui être signalé.',

    'How often should I refresh my playlist?'
        => 'À quelle fréquence dois-je actualiser ma liste de lecture ?',
    'Weekly is a good habit. Providers add channels and move stream URLs more often than people expect. Most IPTV apps can refresh on a schedule — a 24-hour interval keeps things current without hammering the server.'
        => 'Une fois par semaine est une bonne habitude. Les fournisseurs ajoutent des chaînes et déplacent les URL de flux plus souvent qu’on ne le croit. La plupart des applications IPTV peuvent actualiser automatiquement — un intervalle de 24 heures garde tout à jour sans surcharger le serveur.',

    'Is your converter safe to use with my real playlist URL?'
        => 'Votre convertisseur est-il sûr avec ma vraie URL de liste de lecture ?',
    'Yes. Everything happens inside your browser: the URL you paste is never sent to us, never logged and never stored. You can confirm that by opening your browser’s network tab while you use it — there is no request. Your playlist URL contains your subscription credentials, so it is a fair thing to check on any tool that asks for one.'
        => 'Oui. Tout se passe dans votre navigateur : l’URL que vous collez ne nous est jamais envoyée, jamais journalisée, jamais conservée. Vous pouvez le vérifier en ouvrant l’onglet réseau de votre navigateur pendant que vous l’utilisez — aucune requête n’est faite. L’URL de votre liste contient vos identifiants d’abonnement : c’est une vérification légitime à faire sur tout outil qui vous en demande une.',

    // ── HowTo steps ──────────────────────────────────────────────────────────
    'Load the URL straight into a media player' => 'Charger l’URL directement dans un lecteur',
    'Copy the playlist URL, open "Open Network Stream" in VLC, PotPlayer or IINA, and paste it. The whole channel list appears within seconds.'
        => 'Copiez l’URL de la liste, ouvrez « Ouvrir un flux réseau » dans VLC, PotPlayer ou IINA, et collez-la. Toute la liste des chaînes apparaît en quelques secondes.',

    'Convert it to Xtream Codes credentials' => 'La convertir en identifiants Xtream Codes',
    'TiviMate, IPTV Smarters and XCIPTV all behave better with a server, username and password than with a raw playlist file. Use the converter at the top of this page to pull those three values out.'
        => 'TiviMate, IPTV Smarters et XCIPTV fonctionnent tous mieux avec un serveur, un nom d’utilisateur et un mot de passe qu’avec un fichier de liste brut. Utilisez le convertisseur en haut de cette page pour en extraire ces trois valeurs.',

    'Import it into Kodi' => 'L’importer dans Kodi',
    'Install the PVR IPTV Simple Client add-on, point it at the playlist URL, and live TV appears inside Kodi’s own interface with guide data and recording.'
        => 'Installez l’extension PVR IPTV Simple Client, pointez-la vers l’URL de la liste, et la télé en direct apparaît dans l’interface de Kodi, avec le guide et l’enregistrement.',

    'Build a shorter, custom channel list' => 'Créer une liste de chaînes plus courte et personnalisée',
    'Open the file in a text editor, delete the entries you never watch, and save it with the same extension. A shorter list loads faster and is far easier to navigate.'
        => 'Ouvrez le fichier dans un éditeur de texte, supprimez les entrées que vous ne regardez jamais et enregistrez-le avec la même extension. Une liste plus courte se charge plus vite et se parcourt bien plus facilement.',

    'Set it up on a smart TV' => 'La configurer sur un téléviseur intelligent',
    'Smart IPTV, SS IPTV and OTTPlayer all accept a playlist URL on Samsung, LG and similar sets, which turns the TV into a receiver with no extra hardware.'
        => 'Smart IPTV, SS IPTV et OTTPlayer acceptent tous une URL de liste sur les téléviseurs Samsung, LG et similaires, ce qui transforme le téléviseur en récepteur sans matériel supplémentaire.',

    'Use it on a phone or tablet' => 'L’utiliser sur téléphone ou tablette',
    'GSE Smart IPTV, IPTV Smarters and Perfect Player accept either the playlist URL or Xtream credentials, so the same subscription travels with you.'
        => 'GSE Smart IPTV, IPTV Smarters et Perfect Player acceptent soit l’URL de la liste, soit les identifiants Xtream : le même abonnement vous suit partout.',

    'Manage several playlists at once' => 'Gérer plusieurs listes à la fois',
    'Web-based managers can merge, deduplicate and refresh multiple playlists on a schedule, which is worth it once you are maintaining more than one.'
        => 'Les gestionnaires en ligne peuvent fusionner, dédoublonner et actualiser automatiquement plusieurs listes — utile dès que vous en maintenez plus d’une.',

    // ── Closing band ─────────────────────────────────────────────────────────
    'A playlist is only as good as the service behind it'
        => 'Une liste de lecture ne vaut que le service qui la nourrit',
    'Quebec IPTV gives you both an M3U playlist and Xtream Codes credentials, on 40,000+ channels and 200,000+ films. Try it for 24 hours without a card.'
        => 'Quebec IPTV vous donne à la fois une liste M3U et des identifiants Xtream Codes, sur plus de 40 000 chaînes et 200 000 films. Essayez 24 heures, sans carte.',
    'See plans and pricing' => 'Voir les forfaits et les tarifs',
    'Start a 24-hour trial' => 'Commencer un essai de 24 h',

    // ── Schema ───────────────────────────────────────────────────────────────
    'A free browser-based tool that extracts the Xtream Codes server URL, username and password from any M3U playlist link. Nothing is uploaded.'
        => 'Un outil gratuit, exécuté dans le navigateur, qui extrait l’URL du serveur, le nom d’utilisateur et le mot de passe Xtream Codes de n’importe quel lien de liste M3U. Rien n’est téléversé.',
    'How to use an M3U playlist' => 'Comment utiliser une liste de lecture M3U',
);
