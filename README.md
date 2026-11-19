# IndexNow — plugin di Kris

Avvisa i motori di [IndexNow](https://www.indexnow.org/) (Bing, Yandex, Seznam, Naver…) delle pagine cambiate.

- **Quando:** dopo ogni pubblicazione delle modifiche in attesa (le pagine toccate, in ogni lingua) e, con «Avvisa anche a ogni salvataggio», dopo ogni salvataggio sul sito pubblico (le pagine della sitemap che rendono il contenuto salvato).
- **Chiave:** creata al primo invio e servita da `index.php?kris_plugin=indexnow&endpoint=chiave`, nella radice del sito, così vale per tutti i suoi indirizzi.
- **Dove:** `https://api.indexnow.org/indexnow`, unico dominio dichiarato. Da `localhost` e dai domini `.test` non parte niente; ogni invio finisce nel registro del plugin con la risposta.
- Imposta `base_url` in `settings.seo`: gli indirizzi mandati sono quelli canonici.

Contratto dei plugin: `docs/2.0/plugin.md` in [kris-cms](https://github.com/fellarrusto/kris-cms).
