<div class="bookingTimeContainer {if !isset($timed) || !$timed}d-none{/if}" title="Temps de réservation des produits">
    <i class="material-icons">timer</i>
    <span data-date="{if isset($date)}{$date}{/if}" data-time="{if isset($timed)}{$timed}{else}null{/if}"
        class="bookingTime">{if isset($timed)}{$timed}{/if}</span>
</div>