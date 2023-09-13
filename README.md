# Bonuspunkte & Prämien

## Allgemeines

### Ihr Ansprechpartner

Entwickler: Dennis Heinrich<br />
Internet: [https://dennis-heinri.ch](https://dennis-heinri.ch)<br />
E-Mail: [hey@dennis-heinri.ch](mailto:hey@dennis-heinri.ch)<br />
*Anfragen bitte auschließlich per E-Mail.*

### Support, Lizenz und Haftungsausschluss

Weitere Entwicklungen auf Anfrage möglich. Ein Anspruch auf die Entwicklung besteht nicht sondern geschieht auf freiwilliger Basis. Es besteht durch die Nutzung kein Anspruch auf Support oder Fehlerbehebung. Die Nutzung erfolgt auf eigene Gefahr und Verantwortung. Die Lizenzbedingungen sind in der `LICENSE` Datei des Plugins zu finden.

## Einrichtung

### Funktionsattribute in der Warenwirtschaft einrichten

Einrichtung der folgenden Artikel-Funktionsattribute, um Bonuspunkte individuell auf einen Artikel zu vergeben. Dafür geht man in der Warenwirtschaft über das Menü unter der Fensterleiste auf Artikel → Attribute und klickt auf Gruppe anlegen (Name frei wählbar, es wird aber empfohlen dh_bonuspunkte zu nutzen). Dann wird die neu erstelle Gruppe angeklickt.

Für jedes der hier aufgelisteten Attribute muss nun den kleinen Pfeil geklickt werden um dann “Funktionsattribut” anzuklicken. Dann wird jeweils in dem Feld AttributId der Name aus der Tabelle übertragen. Der Datentyp muss für jedes Funktionsattribut über das Auswahlfeld gesetzt werden. Nach Doppelklick auf den Namen kann der Name bearbeitet werden, eine Empfehlung ist ebenfalls der Tabelle zu entnehmen. Um später den Überblick beizubehalten, haben wir ebenfalls eine Beschreibung zu jedem Attribut definiert, welche Sie in dem unteren Feld eintragen können.

| Attribut-ID | Datentyp | Name | Beschreibung |
| --- | --- | --- | --- |
| bonuspunkte_pro_artikel_einmal | Ganzzahl | Bonuspunkte pro Artikel (Einmal) | Definiert wie viele Bonuspunkte der Kunde für den Kauf dieses Artikels erhält. Der Kunde erhält für diesen Artikel im Warenkorb die angegebene Summe, aber nur einmal pro Artikel. |
| bonuspunkte_pro_artikel | Ganzzahl | Bonuspunkte pro Artikel | Definiert wie viele Bonuspunkte der Kunde für den Kauf dieses Artikels erhält. Der Kunde erhält für jeden Artikel im Warenkorb die angegebene Summe. |
| bonuspunkte_pro_euro | Ganzzahl | Bonuspunkte nach Euro | Definiert wie viele Bonuspunkte der Kunde für den Kauf dieses Artikels erhält, dabei erhält der Kunde jeweils die angegebene Anzahl, welche durch die Summe des Warenkorbs bestimmt wird. |

### Plugin konfigurieren

In den Einstellungen des Plugins können Sie die Bonuspunkte für die verschiedenen Aktionen konfigurieren. Die Bonuspunkte werden dann automatisch dem Kundenkonto gutgeschrieben, wenn die Bedingungen erfüllt werden.

## 2. Erhalten von Punkten

### Einkäufe

### Registrierung

### Besuchen der Seite

## 3. Ausgeben von Punkten

### Prämien-Artikel

### Bonusguthaben

<style>
  /* This is only for the plugin tab needed */
  .markdown h2 {
    border-bottom: 4px solid #eee;
  }
  .markdown h3 {
    font-size: .9rem;
    text-decoration: underline;
  }
  .markdown table {
    margin-bottom: 1rem;
  }
</style>
