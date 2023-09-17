<div class="container">
    {opcMountPoint id='dh_bonuspunkte_begin_container'}

    {if $dh_bonuspunkte_form_success }
        <div class="bg-emerald-600 text-white p-2 mb-5 rounded-sm text-lg">Die Punkte wurden in Shop-Guthaben umgewandelt</div>
    {/if}
    {if $dh_bonuspunkte_form_error != false}
        <div class="bg-red-600 text-white p-2 mb-5 rounded-sm text-lg">{$dh_bonuspunkte_form_error}</div>
    {/if}
    <h2>Mein Kontostand</h2>
    {opcMountPoint id='dh_bonuspunkte_balance'}
    <p>
        Hier können Sie Ihren aktuellen Kontostand einsehen. Die Punkte werden in der Regel direkt nach Abschluss der
        Bestellung gutgeschrieben.
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

    <h2>Punkte einlösen</h2>
    {opcMountPoint id='dh_bonuspunkte_redeem'}
    <h3>Guthaben umwandeln</h3>

    {if $dh_bonuspunkte_conversion->getShopBalance() > 0}
        <div class="mb-2">Das aktuelle Guthaben für dieses Konto beträgt <b>{$dh_bonuspunkte_conversion->getShopBalance(true)}</b></div>
    {/if}
    <div class="bg-neutral-100 p-2 rounded border mb-3" data-ref="bonusPointsExchangeWidget">
        {if $dh_bonuspunkte_conversion->isWidgetActiveForUser()}
            <form method="post">
                <input type="hidden" name="{$dh_bonuspunkte_form_name}" value="1">
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="number" class="flex-[2] form-control form-control-sm" name="convertPoints"
                           value="{$dh_bonuspunkte_conversion->getUnlockedPoints()}" min="{$dh_bonuspunkte_conversion->getMinimumTradeIn()}"
                           max="{$dh_bonuspunkte_conversion->getUnlockedPoints()}" required data-ref="bonusPointsExchangeInput">
                    <input type="submit" class="flex-[1] btn btn-sm btn-primary btn-block" value="Umwandeln"
                           data-ref="bonusPointsExchangeInputSubmit">
                </div>
                <div class="flex flex-col mt-2">
                    <div class="text-sm text-neutral-500 text-center mb-2">
                        <span data-ref="bonusPointsInput">{$dh_bonuspunkte_conversion->getUnlockedPoints()}</span> Punkte
                        ergeben
                        <span data-ref="bonusPointsExchangeRate" data-exchange-rate="{$dh_bonuspunkte_conversion->getPointsForOneEuro()}">{str_replace(".", ",", number_format($dh_bonuspunkte_conversion->calculatePointsToEuro($dh_bonuspunkte_conversion->getUnlockedPoints()), 2))}</span> € Guthaben.
                    </div>
                </div>
            </form>
        {else}
            Sie benötigen <u>mindestens {$dh_bonuspunkte_conversion->getMinimumTradeIn()} Punkte</u> für eine Umwandlung in Shop-Guthaben.
        {/if}
    </div>


    <h2>Punktehistorie</h2>
    {opcMountPoint id='dh_bonuspunkte_history'}
    <p>
        Auf dieser Seite können Sie den Verlauf (die letzten fünfzig Einträge) und den Zustand Ihrer Bonuspunkte
        einsehen. In einigen Fällen
        sind die Punkte noch nicht gebucht, da die Bestellung noch nicht abgeschlossen ist. Sobald die Bestellung
        abgeschlossen ist, werden die Punkte
        {if $dh_bonuspunkte_unlock_days <= 0}
            sofort gutgeschrieben
        {else}
            innerhalb von etwa {$dh_bonuspunkte_unlock_days} {if $dh_bonuspunkte_unlock_days == 1}Tag{else}Tagen{/if} ihrem Konto gutgeschrieben.
        {/if}
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
                <td class="{if !$item->isValued()}text-orange-600{/if}">{if $item->isValued()}Gebucht{else}Ausstehend{/if}</td>
                <td {if $item->getPoints() < 0}class="text-red-600 font-bold"{/if}>{$item->getPoints()}</td>
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