

<!-- Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered"  role="document">
        <div class="modal-content">
        <form action="{Context::getContext()->link->getModuleLink('dioqaapiconnexion', 'bookingExpiredCart')}" method="post">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">Votre Panier à expiré</h4>
                {* <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button> *}
            </div>
            {if $products && $products|count}
                <div class="modal-body">
                    <p class="body-title">C'est produits sont encore disponible !</p>
                    <ul class="row">
                        {foreach from=$products key=key item=product}
                        <li class="col-12">
                            <div>
                                <input class="close-radio" type="radio" id="close{$key}" name="book_product_{$product['id_product']}_customization_{$product['id_customization']}" value="false">
                                <label class="close-label" for="close{$key}">
                                    <span class="material-icons">close</span>
                                </label>
                            </div>
                            
                            <span class="name">{$product['name']}</span>

                            <div>
                                <input class="close-radio" type="radio" id="set{$key}" name="book_product_{$product['id_product']}_customization_{$product['id_customization']}" value="true" checked>
                                <label class="btn btn-primary" for="set{$key}">Réserver</label>
                            </div>
                        </li>
                        {/foreach}
                    </ul>
                </div>
            {else}
                <div class="modal-body">
                    <p class="body-title">Vos produit ne sont plus disponible</p>
                </div>
            {/if}
            <div class="modal-footer">
                {if $products && $products|count}
                    <button type="submit" name="bookingExpiredCart" class="btn btn-primary">Valider</button>
                {else}
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                {/if}

            </div>
            </form>
        </div>
    </div>
</div>