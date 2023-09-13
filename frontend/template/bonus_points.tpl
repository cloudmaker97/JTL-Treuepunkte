
<div class="container">
    <h2>Mein Kontostand</h2>
    <p>
        Hier können Sie Ihren aktuellen Kontostand einsehen. Die Punkte werden in der Regel nach Abschluss der Bestellung gutgeschrieben.
        Sollten Sie Fragen zu Ihrem Kontostand haben, können Sie uns gerne kontaktieren.
    </p>
    <table class="table table-responsive">
        <tr>
            <td>
                Verfügbare Punkte
            </td>
            <td>
                {$dh_bonuspunkte_history->getTotalValuedPoints()}
            </td>
        </tr>
        <tr>
            <td>
                Ausstehende Punkte
            </td>
            <td>
                {$dh_bonuspunkte_history->getNotValuedPoints()}
            </td>
        </tr>
    </table>

    <h2>Punktehistorie</h2>
    <p>
        Auf dieser Seite können Sie den Verlauf (die letzten fünzig Einträge) und den Zustand Ihrer Bonuspunkte einsehen. In einigen Fällen
        sind die Punkte noch nicht gebucht, da die Bestellung noch nicht abgeschlossen ist. Sobald die Bestellung
        abgeschlossen ist, werden die Punkte ihrem Konto gutgeschrieben. Wird eine Bestellung storniert, werden die Punkte wieder auf den Status "Ausstehend" gesetzt.
    </p>
    <table class="table">
    <tr>
        <th>Datum</th>
        <th>Status</th>
        <th>Punkte</th>
        <th>Text</th>
        <th><!-- Kontext-URL --></th>
    </tr>
    {foreach from=$dh_bonuspunkte_history->getEntries() item=item key=key}
        {if $key >= 49}
            {break}
        {/if}
        <tr>
            <td>{$item->getCreatedAt()->format("d.m.Y")}</td>
            <td>{if $item->isValued()}Gebucht{else}Ausstehend{/if}</td>
            <td>{$item->getPoints()}</td>
            <td>{$item->getText()}</td>
            <td>
                {if $item->getOrderId() != 0}
                    <a href="/Mein-Konto?bestellung={$item->getOrderId()}">Bestellung ansehen</a>
                {/if}
            </td>
        </tr>     
    {/foreach}
    </table>
</div>