<?php
/**
 * Plugin loginpopup: Zeigt einen Hinweistext nach dem Login als Pop-up an
 * Der Hinweistext wird aus einer konfigurierbaren DokuWiki-Seite geladen.
 */

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(__DIR__ . '/../../../') . '/');
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'action.php');

class action_plugin_loginpopup extends DokuWiki_Action_Plugin {

    /**
     * Registriert den Event Hook
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, 'handle_popup');
    }

    /**
     * Zeigt den Hinweis als Pop-up nach Login (nur bei eingeloggten Benutzern)
     */
    public function handle_popup(Doku_Event $event, $param) {
        global $INFO, $ID;

        // Nur eingeloggte Nutzer berücksichtigen
        if (empty($_SERVER['REMOTE_USER'])) return;

        // Popup-Inhalt aus konfigurierter Seite laden
        $page = $this->getConf('popup_page');
        if (empty($page)) return;
        $popupText = p_wiki_xhtml($page);
        if (empty($popupText)) return;

        // TTL aus der Konfiguration holen (in Sekunden)
        $ttl = (int) $this->getConf('popup_ttl');
        if ($ttl <= 0) $ttl = 3600;

        // HTML + JS zur Anzeige des Modals mit localStorage-Steuerung
        echo "<script>
          const now = Math.floor(Date.now() / 1000);
          const nextAllowed = localStorage.getItem('loginpopup_next') || 0;
          if (parseInt(nextAllowed) <= now) {
            document.write(`
              <div id='loginpopup-modal' style='position:fixed;top:0;left:0;width:100%;height:100%;background:#00000099;z-index:9999;display:flex;align-items:center;justify-content:center;'>
                <div style='background:#fff;padding:20px;max-width:600px;border-radius:8px;box-shadow:0 0 20px #000;'>
                  <h2>Hinweis</h2>
                  <div>{$popupText}</div>
                  <button onclick=\"document.getElementById('loginpopup-modal').remove()\" style='margin-top:20px;'>Schließen</button>
                </div>
              </div>`);
            localStorage.setItem('loginpopup_next', now + {$ttl});
          }
        </script>";
    }
}
