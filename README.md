# Segelflugstart-Manager
## Was macht es?
Der Segelflugstart-Manager ist eine "Stechuhr"/KalenderApp um Flugtage am Platz zu organisieren.

Nutzer können sich für kommende Flugtage eintragen, was sie fliegen, welche "Rolle" sie einnehmen, um wieviel Uhr man etwa mit ihnen rechnen kann und ob sie letztes Mal mit Fliegen zu kurz gekommen sind. Am Flugtag dann kann man sich als anwesend eintragen, vorausgesetzt man ist nah genug am Flugplatz.

"Appfremde" Piloten können von anderen Nutzern manuell in die Liste eingetragen werden. Damit können die Listen vervollständigt werden, auch wen nicht jeder die Anwendung nutzt.

## Konfiguration
Die Konfigurationsdatei in der Datenbankverbindung, E-Mail, Google-ClientID etc. eingetragen werden können, muss unter `/src/conf.php` zu finden sein. Um lokales entwickeln zu vereinfachen ist diese Konfigurationsdatei in `.gitignore` eingetragen. Die Vorlagedatei `/src/template_conf.php` kann hierzu kopiert und umbenannt werden, um lokale Datenbanken und Testumgebungen einzutragen.

Die Zone, in der sich Nutzer als "anwesend" eintragen können wird hier konfiguriert:
````
const ENLIST_ZONE_LATITUDE = ...;   // Längengrad-Koordinate
const ENLIST_ZONE_LONGITUDE = ...;  // Breitengrad-Koordinate
const ENLIST_ZONE_RADIUS = ...;     // Radius in Metern
````
Es können diverse Nutzerrollen in der Konfigurationsdatei angelegt werden. In der Liste werden diese Nutzer dann mit dem entsprechenden [fontawesome-Symbol](https://fontawesome.com/icons?d=gallery&p=2&m=free) markiert:
````
const ATTENDANCE_ROLES = 
[
    ...
    [
        'name' => 'Windenfahrer/Ausbilder',
        'symbol' => '<i title="Windenfahrer/Ausbilder" class="fas fa-truck-moving"></i>',
        'bootstrap_color' => 'warning'
    ],
    ...
]
````

## Anforderungen
Die Anwendung wurde gestestet mit:
* Apache v2.4.41
* PHP v7.3
* MYSQL v5.6

## Installation

### Composer
Die Anmeldung via Google-API sowie das Versenden von "Passwort vergessen"-Mails basieren auf _composer_ Paketen die installiert werden müssen. Anleitung zur Installation von _composer_ sind unter [getcomposer.org](https://getcomposer.org/) zu finden. Alle Pakete können dann mit dem Befehl
````
composer install
````
installiert werden.

Das "google/apiclient" Paket besteht aus vielen Google-Services die nicht verwendet werden. Der (sehr große) Ordner `/vendor/google/apiclient-services` kann nach der Installation also gelöscht werden.

### Datenbank
Mit `/scripts/create_tables.sql` werden die Datenbanktabellen angelegt. Die Auswahl der Luftfahrzeuge muss dann mit einem Datenbankclient in der Tabelle `plane` eingetragen werden. Ein Beispiel:
````
INSERT INTO plane (model, lfz, wkz, alias, available)
VALUES ('LAK17a', 'D-5957', 'XY', 'LAK', 1),
````
(Luftfahrzeuge, die gewartet werden oder aus anderen Gründen nicht verfügbar sind, können mit `available = 0` in der Auswahl ausgeblendet werden)

### Konfigurationsdatei
Wie oben beschrieben muss die Konfigurationsdatei unter `/src/conf.php` abgelgt sein.

### CSS/JS
Die Dateien `/css/main.css` und `/js/main.js` müssen "minified" sein damit sie unter
`/css/main.min.css` und `/js/main.min.js` richtig eingebunden werden.

### Moderatoren einrichten
Wenn `APPROVE_ACCOUNTS_BY_DEFAULT = false` konfiguriert ist, müssen neu registrierte Nutzer erst für die Anwendung freigeschaltet werden. In der `user`-Tabelle können Nutzer mit `is_approved = 1` freigeschaltet werden. Nutzer können auch als `is_moderator` geflagt werden. Moderatoren können unter "Nutzer bestätigen" dann neue Nutzer innerhalb der Anwendung freischalten.

## Anmerkungen
* Ob Nutzer "nah genug" am Flugpltz sind um sich als anwesend einzutragen kann aus technischen Gründen nur client-seitig geprüft werden. Es ist also damit zu rechenen, dass die Einschränkung leicht umgangen werden kann.

* Die Seite ist keine Vorlage, sondern wird für einen konkreten Verein entwickelt. Für andere Flugplätze müssen entsprechend viele Texte an diversen Stellen angepasst werden.

* Die Impressums- & Datenschutzseite wird als `/impressum_und_datenschutz.php` referenziert und muss noch hinzugefügt werden.

## Lizenz
[MIT-Lizenz](https://github.com/Duck-Mc-Muffin/Segelflugstart-Manager/blob/main/LICENSE)