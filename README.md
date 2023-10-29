# Bonuspunkte & Prämien

## Allgemeines

Es handelt sich um ein kostenloses Plugin, daher bedenken Sie bei Ihren Anfragen bitte, dass ich dies in meiner Freizeit entwickle und daher nicht immer sofort antworten kann. Wenn das Plugin Ihre Bedürfnisse nicht erfüllt, so können Sie gerne eine Agentur mit der kostenpflichtigen Entwicklung beauftragen. Es besteht durch die Nutzung kein Anspruch auf Support oder Fehlerbehebung. Die Nutzung erfolgt auf eigene Gefahr und Verantwortung. Die Lizenzbedingungen sind in der `LICENSE` Datei des Plugins zu finden.

Support zum Plugin gibt es ausschließlich über dieses Formular:
https://dennis-heinri.ch/projekte/jtl-support/

Dokumentation zum Plugin ist auf dieser Seite zu finden:
https://dennis-heinrich.notion.site/Bonuspunkte-Pr-mien-9d611781b5a64ad5b2ca27acf7ca67c0

## Einrichtung

### Funktionsattribute in der Warenwirtschaft einrichten

Einrichtung der folgenden Artikel-Funktionsattribute, um Bonuspunkte individuell auf einen Artikel zu vergeben. Dafür geht man in der Warenwirtschaft über das Menü unter der Fensterleiste auf Artikel → Attribute und klickt auf Gruppe anlegen (Name frei wählbar, es wird aber empfohlen dh_bonuspunkte zu nutzen). Dann wird die neu erstelle Gruppe angeklickt.

Für jedes der hier aufgelisteten Attribute muss nun den kleinen Pfeil geklickt werden um dann “Funktionsattribut” anzuklicken. Dann wird jeweils in dem Feld AttributId der Name aus der Tabelle übertragen. Der *Datentyp* muss für jedes Funktionsattribut über das Auswahlfeld gesetzt werden. Nach Doppelklick auf den Namen kann der Name dann bearbeitet werden. Die Namen entsprechen den *Namen aus der Tabelle*.

| Name | Datentyp | Beschreibung |
| --- | --- | --- |
| bonuspunkte_pro_artikel_einmal | Ganzzahl | Definiert wie viele Bonuspunkte der Kunde für den Kauf dieses Artikels erhält. Der Kunde erhält für diesen Artikel im Warenkorb die angegebene Summe, aber nur einmal pro Artikel. |
| bonuspunkte_pro_artikel | Ganzzahl | Definiert wie viele Bonuspunkte der Kunde für den Kauf dieses Artikels erhält. Der Kunde erhält für jeden Artikel im Warenkorb die angegebene Summe. |
| bonuspunkte_pro_euro | Ganzzahl | Definiert wie viele Bonuspunkte der Kunde für den Kauf dieses Artikels erhält, dabei erhält der Kunde jeweils die angegebene Anzahl, welche durch die Summe des Warenkorbs bestimmt wird. |

Diese Funktionsattribute können Sie dann bei den Artikeln in der Warenwirtschaft (in einem bestimmten Artikel) unter dem Reiter "Attribute/Merkmale" eintragen. Die Punkte werden dann automatisch dem Kundenkonto gutgeschrieben, sobald der Status der Bestellung auf "Bezahlt” gesetzt wird.

Sollte eine Bestellung storniert werden, z.B. durch geltendmachung des Widerrufsrechts, so werden die Punkte wieder abgezogen. Für bereits eingelöste Prämien kann jedoch keine Rückabwicklung erfolgen. Haben Sie hierzu eine Idee, so können Sie diese gerne mit mir teilen.

### Plugin konfigurieren

In den Einstellungen des Plugins können Sie die Bonuspunkte für die verschiedenen Aktionen konfigurieren, sofern die Punkteverarbeitung nicht andersweitig bereits erfolgt ist (die Punkte aus den Funktionsattribute haben immer Vorrang gegenüber den Plugin-Einstellungen und überschreiben diese). Die Bonuspunkte werden dann automatisch dem Kundenkonto gutgeschrieben, wenn die Bedingungen erfüllt werden.

## 2. Erhalten von Punkten

### Einkäufe

Punkte erhält der Kunde für jeden Einkauf, wenn die Bedingungen erfüllt werden. Die Bedingungen können in den Plugin-Einstellungen konfiguriert werden. Es können entweder
für alle Einkäufe Punkte vergeben werden oder nur für bestimmte Artikel oder Bedingungen (z.B. Einkaufswert in Euro, Anzahl der Artikel oder pro einmaligen Artikel im Warenkorb). Die Punkte werden dem Kundenkonto gutgeschrieben, sobald der Status der Bestellung auf "Bezahlt” gesetzt wird.

Sollte eine Bestellung in der Warenwirtschaft storniert wurde, z.B. durch geltendmachung des Widerrufsrechts, so werden die Punkte wieder abgezogen. Für bereits eingelöste Prämien kann jedoch keine Rückabwicklung erfolgen. Haben Sie hierzu eine Idee, so können Sie diese gerne mit mir teilen.

### Registrierung

Wenn Sie dies möchten, können Kunden für die Eröffnung eines Kundenkontos Bonuspunkte erhalten. Die Anzahl der Punkte kann in den Plugin-Einstellungen konfiguriert werden.
Diese Punkte können nur einmalig pro Kunde erhalten werden und werden nicht auf bereits bestehende Konten gutgeschrieben.

### Besuchen der Seite

Wenn Sie dies möchten, können Kunden für das Besuchen der Seite Bonuspunkte erhalten. Die Anzahl der Punkte kann in den Plugin-Einstellungen konfiguriert werden.
So können Sie Kunden dazu animieren, sich öfter auf Ihrer Seite einzufinden und zu stöbern. Dies funktioniert selbstverständlich nur, so lange der Kunde eingeloggt ist,
da nur so die Punkte eindeutig einem Kunden zugeordnet werden können.

## 3. Anzeige für den Kunden und Prämien

### Menüpunkt für Kunden

Im Shop wird im Dropdown im Header ein neuer Menüpunkt angezeigt, welcher den Kunden zu der Bonuspunkte-Übersicht weiterleitet. Dort kann der Kunde sehen, wie viele Punkte er bereits gesammelt hat und wie sich diese zusammensetzen. 

### Frontend-Link und Bearbeitung

Es handelt sich bei der Übersichtsseite um einen "Frontend-Link", welchen Sie im Shop-Backend unter "Eigene Inhalte → Seiten" und dann in der Lingruppe "HIDDEN → Bonuspunkte" finden. Dort können Sie dem Seiteninhalt auch mit eigenen Texten erweitern, wenn Sie weiter runter scrollen und dann das Feld "Inhalt" bearbeiten und anschließend speichern.

### Prämien-Artikel
(Aktuell in Entwicklung)
Wenn Artikel mit einem Funktionsattribut ausgestattet ist, kann über das Funktionsattribut angeben werden, wie viele Punkte zum Einlösen erforderlich sind. Die Anzahl der benötigten Punkte für diesen Artikel werden dem Kunden einerseits auf der Artikel-Detailseite in der Nähe des "In den Warenkorb"-Buttons angezeigt, als auch im Warenkorb selbst.

### Bonusguthaben
Ein Kunde kann seine Treuepunkte gegen Guthaben im Shop auf der Seite für Bonuspunkte eintauschen. Der Shop-Betreiber kann einstellen, wie viele Punkte für einen Euro benötigt werden und ob der Umtausch in Shop-Guthaben für Kunden erlaubt sein soll.


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
