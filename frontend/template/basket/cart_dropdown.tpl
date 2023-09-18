{block name='basket-cart-dropdown-buttons'}
    {row class="cart-dropdown-buttons"}
        {col cols=12 lg=6}
            {* todo Make button disabled *}
            {button variant="outline-primary" type="link" block=true  size="sm" href="{get_static_route id='bestellvorgang.php'}?wk=1" class="cart-dropdown-next"}
                {lang key='nextStepCheckout' section='checkout'}
            {/button}
        {/col}
        {col cols=12 lg=6}
            {button variant="primary" type="link" block=true  size="sm" title="{lang key='gotoBasket'}" href="{get_static_route id='warenkorb.php'}"}
                {lang key='gotoBasket'}
            {/button}
        {/col}
    {/row}
{/block}
