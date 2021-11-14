# Segelflugstart-Manager
## Was macht es?
Der Segelflugstart-Manager ist eine "Stechuhr"/KalenderApp um Flugtage am Platz zu organisieren.

Nutzer können sich für kommende Flugtage eintragen, was sie fliegen, welche _Rolle_ sie einnehmen,
um wie viel Uhr man etwa mit ihnen rechnen kann und ob sie letztes Mal mit Fliegen zu kurz gekommen sind.
Am Flugtag kann man sich dann als anwesend eintragen, vorausgesetzt man ist nah genug am Flugplatz
(siehe _Anmerkungen_ weiter unten).

"Appfremde" Piloten können von anderen Nutzern manuell in die Liste eingetragen werden.
Damit können die Listen vervollständigt werden, auch wenn nicht jeder die Anwendung nutzt.

## Konfiguration
Die Konfigurationsdatei aus der Datenbankverbindung, E-Mail, Google-ClientID etc. ausgelesen wird,
muss unter `/src/conf.php` zu finden sein.
Um lokales entwickeln zu vereinfachen, ist diese Konfigurationsdatei in `.gitignore` eingetragen.
Die Vorlagedatei `/src/template_conf.php` kann hierzu kopiert und umbenannt werden,
um lokale Datenbanken und Testumgebungen zu konfigurieren.

### Docker und Umgebungsvariablen
Die Datenbankzugangsdaten werden in `/src/conf.php` aus Umgebungsvariablen ausgelesen, die via
`docker-compose` gesetzt werden (siehe `/docker/compose_*.php`).
Wenn auf Docker verzichtet wird, müssen die Umgebungsvariablen manuell gesetzt werden.
Alternativ können die Konstanten direkt definiert werden, ohne `getenv('SEGELFLUG_DB_...')` aufzurufen.
Umgekehrt können Konstanten um `getenv(...)` erweitert werden,
um das Arbeiten mit Containern flexibler zu machen.

### Zone
Die Zone, in der sich Nutzer als _anwesend_ eintragen können, wird hier festgelegt:
````
const ENLIST_ZONE_LATITUDE = ...;   // Längengrad-Koordinate
const ENLIST_ZONE_LONGITUDE = ...;  // Breitengrad-Koordinate
const ENLIST_ZONE_RADIUS = ...;     // Radius in Metern
````

### Nutzerrollen
Es können diverse Nutzerrollen in der Konfigurationsdatei angelegt werden.
In der Liste werden diese Nutzer dann mit dem entsprechenden
[fontawesome-Symbol](https://fontawesome.com/icons?d=gallery&p=2&m=free) markiert:
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

### Luftfahrzeuge hinzufügen
Luftfahrzeuge müssen manuell in der Datenbank hinzugefügt werden. Ein Beispiel:
  ````
  INSERT INTO plane (model, lfz, wkz, alias, available)
  VALUES ('LAK17a', 'D-5957', 'XY', 'LAK', 1), ...
  ````
Luftfahrzeuge, die gewartet werden oder aus anderen Gründen nicht verfügbar sind,
können mit `available = 0` in der Auswahl ausgeblendet werden.

Bei einer Installation mit Docker werden alle SQL-Dateien in `/docker/database/`
im Buildprozess ausgeführt. Hier kann also z. B. eine `insert_planes.sql`
als Teil des Datenbankimages abgelegt werden.

**Wichtig:** Die Dateien werden in alphabetischer Reihenfolge ausgeführt!

## Installation mit Docker
> Da das Projekt für die individuelle Nutzung an vielen Stellen angepasst werden muss,
> gibt es keine fertigen Images auf öffentlichen Repositories.
> Mit [`docker save/load`](https://docs.docker.com/engine/reference/commandline/save/)
> lassen sich lokal erstellte Images trotzdem leicht auf Produktivservern deployen.

Die App besteht aus einem Image für die PHP/Apache Webapp und einem Image für die Datenbank,
die beide via `docker-compose` erstellt und gestartet werden.

Alle SQL-Dateien unter `/docker/database` werden im Buildprozess
für das Datenbankimage automatisch ausgeführt.
`/docker/database/create_tables.sql` erstellt also die Datenbank und alle Tabellen automatisch.
Hier kann auch eine SQL-Datei abgelegt werden, um auswählbare Luftfahrzeuge für die Nutzer hinzuzufügen
(siehe _Luftfahrzeuge hinzufügen_ weiter oben).

Die `docker-compose`-Dateien erwarten eine `/docker/password_db_user.txt` als das Passwort für
die Datenbankverbindung und eine `/docker/password_db_admin.txt` für den Administratorzugang zur Datenbank.
Beide Dateien müssen erstellt werden und enthalten jeweils nur ein Passwort.
Für die Produktionsumgebung sollten sichere Passwörter gewählt werden!

````
echo "..." > docker/password_db_admin.txt
echo "..." > docker/password_db_user.txt
````

Bevor die Images erstellt werden können, muss `/src/conf.php` vorhanden sein.
Dort werden _Sign in via Google_, E-Mail-Provider und andere Einstellungen vorgenommen
(siehe _Konfiguration_ weiter oben). Impressum und Datenschutzerklärung muss ebenfalls unter
`/src/templates/legal.php` vorher selbst eingebunden werden.

In der `php-custom.ini` können Einstellungen für PHP angepasst werden.
`short_open_tag = On` ist zwingend notwendig und sollte nicht aus der Datei entfernt werden!

Für Produktivumgebung und Entwicklungsumgebung stehen verschiedene `docker-compose`-Dateien zur Verfügung:

### Entwicklungsumgebung
````
docker-compose -f docker/compose_dev.yml up -d --build
````
In der Entwicklungsumgebung wird der Workspace in das Hostsystem gemountet,
um die Dateien im Container leichter bearbeiten zu können.
PHP ist unter anderem so konfiguriert, dass Errors ausgegeben werden, um Debugging zu erleichtern.

### Produktionsumgebung
````
docker-compose -f docker/compose_prod.yml up -d --build
````
Dann können die Images mit Hilfe von
[`docker save/load`](https://docs.docker.com/engine/reference/commandline/save/)
auf den Produktivserver hochgeladen und gestartet werden.

## Manuelle Installation

### Anforderungen
Die Anwendung wurde getestet mit:
* Apache v2.4.41 und v2.4.51
* PHP v7.3 und v8.0.12
* MySQL v5.6 und MariaDB v10.7.1

### PHP
**⚠️ In der `php.ini` muss `short_open_tag = On` aktiv sein!️ ⚠️**

### Composer
Die Anmeldung via Google-API sowie das Versenden von "Passwort vergessen"-Mails basieren
auf _composer_ Paketen die installiert werden müssen.
Anleitung zur Installation von _composer_ sind unter [getcomposer.org](https://getcomposer.org/) zu finden.
Alle Pakete können dann mit dem Befehl
````
composer install
````
installiert werden.

Das "google/apiclient" Paket besteht aus vielen Google-Services die nicht verwendet werden.
Der (sehr große) Ordner `/vendor/google/apiclient-services` kann nach der Installation also gelöscht werden.

### Datenbank
Mit `/docker/database/create_tables.sql` werden die Datenbanktabellen angelegt.
Die vom Nutzer wählbaren Luftfahrzeuge müssen dann noch in der Tabelle `plane` eingetragen werden
(siehe "Luftfahrzeuge hinzufügen" weiter unten).

Das Skript sollte **nicht** mit auf den Produktivserver hochgeladen werden!

### Konfigurationsdatei
Wie oben beschrieben muss die Konfigurationsdatei unter `/src/conf.php` abgelgt sein.

### CSS/JS
Die Dateien `/css/main.css` und `/js/main.js` müssen "minified" sein, damit sie unter
`/css/main.min.css` und `/js/main.min.js` richtig eingebunden werden.

### Moderatoren einrichten
Wenn `APPROVE_ACCOUNTS_BY_DEFAULT = false` konfiguriert ist,
müssen neu registrierte Nutzer erst für die Anwendung freigeschaltet werden.
In der `user`-Tabelle können Nutzer mit `is_approved = 1` freigeschaltet werden.
Nutzer können auch als `is_moderator` geflagt werden.
Moderatoren können in der Fußleiste unter "Nutzer bestätigen" dann neue Nutzer innerhalb der Anwendung
freischalten.

## Anmerkungen
* Ob Nutzer "nah genug" am Flugplatz sind um sich als anwesend einzutragen kann aus technischen Gründen
  nur client-seitig geprüft werden. Es ist also damit zu rechenen, dass die Einschränkung leicht
 umgangen werden kann.
* Die Seite ist keine Vorlage, sondern wird für einen konkreten Verein entwickelt.
  Für andere Flugplätze müssen entsprechend viele Texte an diversen Stellen angepasst werden.
* Das Impressum und die Datenschutzerklärung werden unter `/src/templates/legal.php` eingebunden und
  muss vom Betreiber selbst erstellt werden.

## Lizenz
[MIT-Lizenz](https://github.com/Duck-Mc-Muffin/Segelflugstart-Manager/blob/main/LICENSE)