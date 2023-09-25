<!-- Modal -->
<div class="modal fade" id="bookingModalTimeOut" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">Votre panier va bientôt expirer !</h4>
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