{if isset($isBookingPossible)}
    <div class="productBooking">
        {if $isBookingPossible['isBooked'] && !$isBookingPossible['myBooking']}
            <div class="isBooked">Réservé</div>
        {elseif $isBookingPossible['isBooked'] && $isBookingPossible['myBooking']}
            <div class="productTimer">
                {widget name="dioqaapiconnexion" action="displayBookingTimer"}
            </div>
        {/if}
    </div>
{/if}