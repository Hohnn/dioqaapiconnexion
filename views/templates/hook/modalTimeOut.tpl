<!-- Modal -->
<div class="modal fade" id="bookingModalTimeOut" tabindex="-1" role="dialog" aria-labelledby="timeOutModal"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-lg">
            <div class="modal-header">
                <span class="modal-title" id="timeOutModal">Votre panier va bientôt expirer !</span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p class="body-title">Commander avant l'expiration du panier</p>
            </div>

            <div class="modal-footer">
                <a href="{$urls.pages.cart}?action=show" class="btn btn-primary">Commander</a>
            </div>
        </div>
    </div>
</div>